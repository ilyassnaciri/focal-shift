<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$catalogueMode = $catalogueMode ?? (in_array($_GET['mode'] ?? '', ['sale', 'rental'], true) ? $_GET['mode'] : 'sale');
$isRentalCatalogue = $catalogueMode === 'rental';
$cataloguePath = $isRentalCatalogue ? 'catalogue-location.php' : 'catalogue-vente.php';
$pageTitle = $isRentalCatalogue ? 'Catalogue location' : 'Catalogue vente';
$pageDescription = $isRentalCatalogue ? 'Louez du matériel photo et vidéo vérifié.' : 'Achetez du matériel photo et vidéo décrit et vérifié.';
$activePage = $isRentalCatalogue ? 'catalogue-rental' : 'catalogue-sale';

$pdo = db();
$categories = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
$brands = $pdo->query("SELECT DISTINCT brand FROM equipment WHERE status = 'published' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
$locations = [
    'Paris' => [48.8566, 2.3522], 'Boulogne-Billancourt' => [48.8397, 2.2399],
    'Montreuil' => [48.8638, 2.4485], 'Saint-Denis' => [48.9362, 2.3574],
    'Versailles' => [48.8014, 2.1301], 'Nanterre' => [48.8924, 2.2153],
];
$allowedConditions = ['neuf', 'tres_bon', 'bon', 'use'];
$allowedRadii = [5, 10, 25, 50, 100];

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'category' => filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: null,
    'brand' => trim((string) ($_GET['brand'] ?? '')),
    'mode' => $catalogueMode,
    'max_price' => filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT) ?: null,
    'condition' => in_array($_GET['condition'] ?? '', $allowedConditions, true) ? $_GET['condition'] : '',
    'location' => array_key_exists($_GET['location'] ?? '', $locations) ? $_GET['location'] : '',
    'radius' => in_array((int) ($_GET['radius'] ?? 25), $allowedRadii, true) ? (int) ($_GET['radius'] ?? 25) : 25,
];

$where = ["e.status = 'published'"];
$params = [];
if ($filters['q'] !== '') {
    $where[] = '(e.brand LIKE :query_brand OR e.model LIKE :query_model OR e.description LIKE :query_description)';
    $params['query_brand'] = '%' . $filters['q'] . '%';
    $params['query_model'] = '%' . $filters['q'] . '%';
    $params['query_description'] = '%' . $filters['q'] . '%';
}
if ($filters['category']) {
    $where[] = 'e.category_id = :category';
    $params['category'] = $filters['category'];
}
if ($filters['brand'] !== '') {
    $where[] = 'e.brand = :brand';
    $params['brand'] = $filters['brand'];
}
if ($filters['condition'] !== '') {
    $where[] = 'e.condition_grade = :condition';
    $params['condition'] = $filters['condition'];
}
if ($filters['mode'] === 'sale') {
    $where[] = 'e.available_for_sale = 1';
    if ($filters['max_price']) {
        $where[] = 'e.sale_price <= :max_price';
        $params['max_price'] = $filters['max_price'];
    }
} elseif ($filters['mode'] === 'rental') {
    $where[] = 'e.available_for_rental = 1';
    if ($filters['max_price']) {
        $where[] = 'e.rental_price_day <= :max_price';
        $params['max_price'] = $filters['max_price'];
    }
} elseif ($filters['max_price']) {
    $where[] = '((e.available_for_sale = 1 AND e.sale_price <= :max_sale) OR (e.available_for_rental = 1 AND e.rental_price_day <= :max_rental))';
    $params['max_sale'] = $filters['max_price'];
    $params['max_rental'] = $filters['max_price'];
}

$sql = "SELECT e.*, c.name AS category_name, c.slug AS category_slug, u.full_name, u.identity_verified,
               (SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id = e.id ORDER BY ep.id LIMIT 1) AS image_url
        FROM equipment e
        JOIN categories c ON c.id = e.category_id
        JOIN users u ON u.id = e.owner_id
        WHERE " . implode(' AND ', $where) . '
        ORDER BY e.created_at DESC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$items = $statement->fetchAll();

function catalogue_distance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earth = 6371;
    $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

if ($filters['location'] !== '') {
    [$originLat, $originLon] = $locations[$filters['location']];
    $nearbyItems = [];
    foreach ($items as $item) {
        if ($item['latitude'] === null || $item['longitude'] === null) continue;
        $item['search_distance'] = catalogue_distance($originLat, $originLon, (float) $item['latitude'], (float) $item['longitude']);
        if ($item['search_distance'] <= $filters['radius']) $nearbyItems[] = $item;
    }
    $items = $nearbyItems;
    usort($items, fn($a, $b) => ($a['search_distance'] ?? 9999) <=> ($b['search_distance'] ?? 9999));
}
$impactCounts = ['green' => 0, 'orange' => 0, 'red' => 0];
foreach ($items as $item) $impactCounts[score_tier((int) $item['circular_score'])['class']]++;

function map_position(?float $lat, ?float $lon): array
{
    if ($lat === null || $lon === null) return [50, 50];
    $left = max(4, min(96, (($lon - 2.12) / (2.58 - 2.12)) * 100));
    $top = max(5, min(95, ((48.98 - $lat) / (48.98 - 48.72)) * 100));
    return [$left, $top];
}

require __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= asset('vendor/leaflet/leaflet.css') ?>">
<main id="main-content" class="catalogue-page">
    <section class="page-hero page-hero-compact">
        <div class="container">
            <p class="eyebrow"><?= $isRentalCatalogue ? 'Catalogue location' : 'Catalogue vente' ?></p>
            <h1><?= $isRentalCatalogue ? 'Louez pour votre prochain projet.' : 'Achetez avec des informations claires.' ?></h1>
            <p><?= $isRentalCatalogue ? 'Comparez le tarif journalier, les disponibilités, l’assurance estimée et l’état vérifié avant votre demande.' : 'Comparez le prix, la description et l’état de chaque produit avant de contacter le vendeur.' ?></p>
        </div>
    </section>

    <section class="section catalogue-section">
        <div class="container catalogue-layout">
            <aside class="filters-panel" aria-labelledby="filters-title">
                <form method="get" action="<?= url($cataloguePath) ?>">
                    <div class="filter-heading">
                        <h2 id="filters-title">Filtrer</h2>
                        <a href="<?= url($cataloguePath) ?>">Réinitialiser</a>
                    </div>
                    <div class="field">
                        <label for="q">Recherche</label>
                        <input id="q" name="q" type="search" value="<?= e($filters['q']) ?>" placeholder="Sony, objectif, stabilisateur…">
                    </div>
                    <div class="field">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <option value="">Toutes</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) $filters['category'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="brand">Marque</label>
                        <select id="brand" name="brand">
                            <option value="">Toutes</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= e($brand) ?>" <?= $filters['brand'] === $brand ? 'selected' : '' ?>><?= e($brand) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="condition">État du produit</label>
                        <select id="condition" name="condition"><option value="">Tous les états</option><?php foreach ($allowedConditions as $condition): ?><option value="<?= e($condition) ?>" <?= $filters['condition'] === $condition ? 'selected' : '' ?>><?= e(condition_label($condition)) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="field">
                        <label for="location">Emplacement</label>
                        <select id="location" name="location"><option value="">Toute l’Île-de-France</option><?php foreach ($locations as $city => $_coords): ?><option value="<?= e($city) ?>" <?= $filters['location'] === $city ? 'selected' : '' ?>><?= e($city) ?></option><?php endforeach; ?></select>
                    </div>
                    <div class="field">
                        <label for="radius">Rayon de recherche</label>
                        <select id="radius" name="radius" <?= $filters['location'] === '' ? 'aria-describedby="radius-help"' : '' ?>><?php foreach ($allowedRadii as $radius): ?><option value="<?= $radius ?>" <?= $filters['radius'] === $radius ? 'selected' : '' ?>><?= $radius ?> km</option><?php endforeach; ?></select><small id="radius-help">Sélectionnez une ville ou utilisez « Autour de moi ».</small>
                    </div>
                    <input type="hidden" name="mode" value="<?= e($catalogueMode) ?>">
                    <div class="field">
                        <label for="max_price">Prix maximum (€)</label>
                        <input id="max_price" name="max_price" type="number" min="1" step="1" value="<?= e($filters['max_price'] ? (string) $filters['max_price'] : '') ?>" placeholder="Ex. 100">
                    </div>
                    <button class="button button-block" type="submit">Afficher les résultats</button>
                </form>
            </aside>
            <div>
                <div class="results-heading">
                    <p><strong><?= count($items) ?></strong> équipement<?= count($items) > 1 ? 's' : '' ?></p>
                    <div class="catalogue-actions">
                        <button class="geo-button" type="button" data-near-me><span aria-hidden="true">◎</span> Autour de moi</button>
                        <div class="view-switch" aria-label="Mode d’affichage">
                            <button type="button" class="is-active" data-view="grid" aria-pressed="true">Grille</button>
                            <button type="button" data-view="map" aria-pressed="false">Carte</button>
                        </div>
                        <a class="arrow-link" href="<?= url('deposit.php') ?>">Publier une offre <span aria-hidden="true">→</span></a>
                    </div>
                </div>
                <p class="geo-status" role="status" aria-live="polite" data-geo-status></p>
                <?php if ($items): ?>
                    <div class="product-grid product-grid-catalogue" data-grid-view>
                        <?php foreach ($items as $item): ?>
                            <?php require __DIR__ . '/includes/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="catalogue-map" data-map-view hidden aria-label="Carte des équipements disponibles">
                        <div class="map-surface" data-map-fallback>
                            <span class="map-river" aria-hidden="true"></span>
                            <span class="map-road road-one" aria-hidden="true"></span><span class="map-road road-two" aria-hidden="true"></span>
                            <span class="map-city city-paris">Paris</span><span class="map-city city-versailles">Versailles</span><span class="map-city city-saint-denis">Saint-Denis</span>
                            <?php foreach ($items as $item): [$left, $top] = map_position(isset($item['latitude']) ? (float) $item['latitude'] : null, isset($item['longitude']) ? (float) $item['longitude'] : null); $tier = score_tier((int) $item['circular_score']); ?>
                                <a class="map-pin map-pin-<?= e($tier['class']) ?>" style="--x:<?= round($left, 2) ?>%;--y:<?= round($top, 2) ?>%" href="<?= url('product.php?id=' . (int) $item['id']) ?>" data-map-pin data-lat="<?= e((string) ($item['latitude'] ?? '')) ?>" data-lon="<?= e((string) ($item['longitude'] ?? '')) ?>" data-name="<?= e($item['brand'] . ' ' . $item['model']) ?>" data-city="<?= e($item['city'] ?? 'Île-de-France') ?>" data-level="<?= e($tier['label']) ?>" data-symbol="<?= e($tier['symbol']) ?>" data-tier="<?= e($tier['class']) ?>" data-url="<?= url('product.php?id=' . (int) $item['id']) ?>" aria-label="<?= e($item['brand'] . ' ' . $item['model'] . ', ' . ($item['city'] ?? 'Île-de-France') . ', ' . $tier['label']) ?>">
                                    <span><?= e($tier['symbol']) ?></span>
                                    <i><strong><?= e($item['brand'] . ' ' . $item['model']) ?></strong><small><?= e($item['city'] ?? 'Île-de-France') ?> · <?= e($tier['label']) ?></small></i>
                                </a>
                            <?php endforeach; ?>
                            <div class="user-position" data-user-position hidden><span></span><i>Vous êtes ici</i></div>
                        </div>
                        <div id="leaflet-map" class="leaflet-map" data-leaflet-map hidden></div>
                        <div class="map-legend"><span><i class="legend-dot green"></i>✓ Impact faible · -5%</span><span><i class="legend-dot orange"></i>≈ Impact moyen</span><span><i class="legend-dot red"></i>! Impact élevé</span></div>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h2>Aucun équipement ne correspond.</h2>
                        <p>Élargissez les filtres ou déposez le premier équipement de cette sélection.</p>
                        <a class="button button-secondary" href="<?= url($cataloguePath) ?>">Réinitialiser les filtres</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<script src="<?= asset('vendor/leaflet/leaflet.js') ?>"></script>
<script src="<?= asset('js/catalogue.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
