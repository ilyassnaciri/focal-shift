<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db();
$user = require_login();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$cityOptions = [
    'Paris' => [48.8566, 2.3522], 'Boulogne-Billancourt' => [48.8397, 2.2399],
    'Montreuil' => [48.8638, 2.4485], 'Saint-Denis' => [48.9362, 2.3574],
    'Versailles' => [48.8014, 2.1301], 'Nanterre' => [48.8924, 2.2153],
];
$errors = [];
$simulationLimits = $_SESSION['last_simulation'] ?? null;
$values = [
    'category_id' => '', 'brand' => '', 'model' => '', 'purchase_year' => '',
    'condition_grade' => 'tres_bon', 'description' => '', 'sale_price' => '',
    'rental_price_day' => '', 'available_for_sale' => false, 'available_for_rental' => false,
    'reuse_count' => '1', 'repaired' => false, 'distance_km' => '25', 'city' => 'Paris',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($values) as $key) {
        if (in_array($key, ['available_for_sale', 'available_for_rental'], true)) {
            continue;
        } elseif ($key === 'repaired') {
            $values[$key] = isset($_POST[$key]);
        } else {
            $values[$key] = trim((string) ($_POST[$key] ?? ''));
        }
    }
    $offerMode = (string) ($_POST['offer_mode'] ?? '');
    $values['available_for_sale'] = $offerMode === 'sale';
    $values['available_for_rental'] = $offerMode === 'rental';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) $errors[] = 'La session a expiré. Rechargez la page puis réessayez.';
    if (!filter_var($values['category_id'], FILTER_VALIDATE_INT)) $errors[] = 'Choisissez une catégorie.';
    if ($values['brand'] === '' || mb_strlen($values['brand']) > 60) $errors[] = 'Renseignez une marque valide.';
    if ($values['model'] === '' || mb_strlen($values['model']) > 80) $errors[] = 'Renseignez un modèle valide.';
    $year = filter_var($values['purchase_year'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1990, 'max_range' => (int) date('Y')]]);
    if (!$year) $errors[] = 'Renseignez une année comprise entre 1990 et aujourd’hui.';
    if (!in_array($values['condition_grade'], ['neuf', 'tres_bon', 'bon', 'use'], true)) $errors[] = 'Choisissez un état valide.';
    if (!in_array($offerMode, ['sale', 'rental'], true)) $errors[] = 'Choisissez la vente ou la location.';

    $salePrice = $values['sale_price'] !== '' ? filter_var($values['sale_price'], FILTER_VALIDATE_FLOAT) : null;
    $rentalPrice = $values['rental_price_day'] !== '' ? filter_var($values['rental_price_day'], FILTER_VALIDATE_FLOAT) : null;
    if ($values['available_for_sale'] && (!$salePrice || $salePrice <= 0)) $errors[] = 'Renseignez un prix de vente supérieur à 0.';
    if ($values['available_for_rental'] && (!$rentalPrice || $rentalPrice <= 0)) $errors[] = 'Renseignez un tarif de location supérieur à 0.';
    $sameSimulation = is_array($simulationLimits)
        && (int) ($simulationLimits['category_id'] ?? 0) === (int) $values['category_id']
        && mb_strtolower(trim((string) ($simulationLimits['brand'] ?? ''))) === mb_strtolower($values['brand'])
        && mb_strtolower(trim((string) ($simulationLimits['model'] ?? ''))) === mb_strtolower($values['model']);
    if (!$sameSimulation) {
        $errors[] = 'Réalisez d’abord une simulation pour ce produit afin d’obtenir sa fourchette de prix.';
    } elseif ($values['available_for_sale'] && ($salePrice < (float) $simulationLimits['sale_min'] || $salePrice > (float) $simulationLimits['sale_max'])) {
        $errors[] = 'Le prix de vente doit rester dans la fourchette de ±20 % calculée par le simulateur.';
    } elseif ($values['available_for_rental'] && ($rentalPrice < (float) $simulationLimits['rental_min'] || $rentalPrice > (float) $simulationLimits['rental_max'])) {
        $errors[] = 'Le tarif journalier doit rester dans la fourchette de ±20 % calculée par le simulateur.';
    }
    if (mb_strlen($values['description']) < 30) $errors[] = 'Décrivez l’équipement en au moins 30 caractères.';
    if (!isset($cityOptions[$values['city']])) $errors[] = 'Choisissez une zone de remise disponible.';

    $reuseCount = filter_var($values['reuse_count'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999]]);
    $distanceKm = filter_var($values['distance_km'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 5000]]);
    if ($reuseCount === false) $errors[] = 'Le nombre de remises en circulation doit être positif.';
    if ($distanceKm === false) $errors[] = 'La distance doit être comprise entre 0 et 5 000 km.';

    $imagePath = null;
    $upload = $_FILES['photo'] ?? null;
    if (!$upload || $upload['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Ajoutez au moins une photo pour documenter l’état.';
    } elseif ($upload['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'La photo n’a pas pu être envoyée.';
    } elseif ($upload['size'] > MAX_UPLOAD_BYTES) {
        $errors[] = 'La photo dépasse 5 Mo.';
    } else {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime])) {
            $errors[] = 'Format accepté : JPG, PNG ou WebP.';
        } else {
            $fileName = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
            $target = __DIR__ . '/uploads/' . $fileName;
            if (!move_uploaded_file($upload['tmp_name'], $target)) {
                $errors[] = 'Impossible d’enregistrer la photo.';
            } else {
                $imagePath = 'uploads/' . $fileName;
            }
        }
    }

    if ($errors && $imagePath && is_file(__DIR__ . '/' . $imagePath)) {
        unlink(__DIR__ . '/' . $imagePath);
        $imagePath = null;
    }

    if (!$errors) {
        $score = calculate_circular_score((int) $year, (int) $reuseCount, (bool) $values['repaired'], (int) $distanceKm);
        $tier = score_tier($score);
        [$latitude, $longitude] = $cityOptions[$values['city']];
        try {
            $pdo->beginTransaction();
            $insert = $pdo->prepare(
                "INSERT INTO equipment
                (owner_id, category_id, brand, model, purchase_year, condition_grade, description,
                 sale_price, rental_price_day, available_for_sale, available_for_rental, status,
                 circular_score, reuse_count, repaired, distance_km, city, latitude, longitude, service_discount)
                VALUES
                (:owner_id, :category_id, :brand, :model, :purchase_year, :condition_grade, :description,
                 :sale_price, :rental_price_day, :available_for_sale, :available_for_rental, :status,
                 :circular_score, :reuse_count, :repaired, :distance_km, :city, :latitude, :longitude, :service_discount)"
            );
            $insert->execute([
                'owner_id' => (int) $user['id'],
                'category_id' => (int) $values['category_id'],
                'brand' => $values['brand'], 'model' => $values['model'], 'purchase_year' => (int) $year,
                'condition_grade' => $values['condition_grade'], 'description' => $values['description'],
                'sale_price' => $values['available_for_sale'] ? (float) $salePrice : null,
                'rental_price_day' => $values['available_for_rental'] ? (float) $rentalPrice : null,
                'available_for_sale' => (int) $values['available_for_sale'],
                'available_for_rental' => (int) $values['available_for_rental'],
                'status' => $values['available_for_rental'] ? 'pending_verification' : 'published',
                'circular_score' => $score, 'reuse_count' => (int) $reuseCount,
                'repaired' => (int) $values['repaired'], 'distance_km' => (int) $distanceKm,
                'city' => $values['city'], 'latitude' => $latitude, 'longitude' => $longitude,
                'service_discount' => $tier['discount'],
            ]);
            $equipmentId = (int) $pdo->lastInsertId();
            $photoInsert = $pdo->prepare("INSERT INTO equipment_photos (equipment_id, url, photo_type) VALUES (:id, :url, 'listing')");
            $photoInsert->execute(['id' => $equipmentId, 'url' => $imagePath]);
            $pdo->commit();
            if ($values['available_for_rental']) {
                flash('success', 'Produit enregistré. La mise en location sera publiée après vérification de son état et de son fonctionnement.');
                header('Location: ' . url('verification.php?id=' . $equipmentId));
            } else {
                flash('success', 'Annonce de vente publiée : elle apparaît maintenant dans le catalogue.');
                header('Location: ' . url('product.php?id=' . $equipmentId));
            }
            exit;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($imagePath && is_file(__DIR__ . '/' . $imagePath)) unlink(__DIR__ . '/' . $imagePath);
            $errors[] = 'Une erreur est survenue lors de l’enregistrement. Aucune donnée partielle n’a été conservée.';
        }
    }
}

$pageTitle = 'Publier une offre';
$pageDescription = 'Publiez un équipement photo ou vidéo avec validation front et serveur.';
$activePage = 'deposit';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-hero page-hero-compact">
        <div class="container narrow">
            <p class="eyebrow">Remise en circulation</p>
            <h1>Publier une offre.</h1>
            <p>Une annonce documentée inspire plus confiance. Tous les champs marqués * sont obligatoires.</p>
        </div>
    </section>
    <section class="section form-section">
        <div class="container narrow">
            <form class="form-card" method="post" enctype="multipart/form-data" data-deposit-form novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php if ($errors): ?>
                    <div class="error-summary" role="alert" tabindex="-1" data-error-summary>
                        <h2>Vérifiez les informations suivantes</h2>
                        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <fieldset>
                    <legend>1. Identifier l’équipement</legend>
                    <aside class="pricing-assistant" data-pricing-assistant>
                        <div><span class="eyebrow">Assistant tarifaire</span><h3>Votre dernière estimation, ici</h3><p data-pricing-message>Le système propose un prix central. Vous pourrez le modifier librement dans une limite de −20 % à +20 %.</p></div>
                        <div class="pricing-suggestions" data-pricing-values hidden><span>Vente conseillée <strong data-suggested-sale>—</strong></span><span>Location / jour <strong data-suggested-rental>—</strong></span></div>
                        <div class="pricing-actions"><a class="button button-outline" href="<?= url('simulator.php') ?>">Ouvrir le simulateur</a><button class="button" type="button" data-apply-prices hidden>Appliquer les fourchettes</button></div>
                    </aside>
                    <div class="form-grid">
                        <div class="field"><label for="category_id">Catégorie *</label><select id="category_id" name="category_id" required><option value="">Choisir</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $values['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
                        <div class="field"><label for="purchase_year">Année d’achat *</label><input id="purchase_year" name="purchase_year" type="number" min="1990" max="<?= date('Y') ?>" value="<?= e((string) $values['purchase_year']) ?>" required></div>
                        <div class="field"><label for="brand">Marque *</label><input id="brand" name="brand" maxlength="60" value="<?= e((string) $values['brand']) ?>" required></div>
                        <div class="field"><label for="model">Modèle *</label><input id="model" name="model" maxlength="80" value="<?= e((string) $values['model']) ?>" required></div>
                        <div class="field"><label for="condition_grade">État *</label><select id="condition_grade" name="condition_grade" required><?php foreach (['neuf'=>'Comme neuf','tres_bon'=>'Très bon état','bon'=>'Bon état','use'=>'État d’usage'] as $value=>$label): ?><option value="<?= $value ?>" <?= $values['condition_grade'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
                        <div class="field"><label for="city">Zone de remise *</label><select id="city" name="city" required><?php foreach ($cityOptions as $city => $coords): ?><option value="<?= e($city) ?>" <?= $values['city'] === $city ? 'selected' : '' ?>><?= e($city) ?></option><?php endforeach; ?></select><small>Position approximative affichée sur la carte, jamais une adresse privée.</small></div>
                        <div class="field file-field"><label for="photo">Photo d’état * <small>JPG, PNG, WebP · 5 Mo max</small></label><input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required><span class="file-feedback" data-file-feedback>Aucun fichier choisi</span></div>
                    </div>
                    <div class="field"><label for="description">Description *</label><textarea id="description" name="description" rows="5" minlength="30" maxlength="1200" required placeholder="État optique, accessoires inclus, traces d’usage…"><?= e((string) $values['description']) ?></textarea><small>30 caractères minimum. Ne saisissez pas de donnée personnelle.</small></div>
                </fieldset>

                <fieldset>
                    <legend>2. Choisir la mise en circulation</legend>
                    <div class="choice-grid">
                        <label class="choice-card"><input type="radio" name="offer_mode" value="sale" data-price-toggle="sale" <?= $values['available_for_sale'] ? 'checked' : '' ?> required><span><strong>Vendre uniquement</strong><small>Prix unique, produit absent de la location</small></span></label>
                        <label class="choice-card"><input type="radio" name="offer_mode" value="rental" data-price-toggle="rental" <?= $values['available_for_rental'] ? 'checked' : '' ?> required><span><strong>Louer uniquement</strong><small>Tarif journalier, produit absent de la vente</small></span></label>
                    </div>
                    <div class="form-grid">
                        <div class="field"><label for="sale_price">Prix de vente (€)</label><input id="sale_price" name="sale_price" type="number" min="1" step="0.01" value="<?= e((string) $values['sale_price']) ?>" data-price-field="sale"><div class="price-gauge" data-gauge-wrap="sale" hidden><div><span data-price-min="sale">−20 %</span><strong>Prix modifiable dans l’intervalle</strong><span data-price-max="sale">+20 %</span></div><input type="range" data-price-gauge="sale" aria-label="Ajuster le prix de vente dans la fourchette autorisée"></div></div>
                        <div class="field"><label for="rental_price_day">Location par jour (€)</label><input id="rental_price_day" name="rental_price_day" type="number" min="1" step="0.01" value="<?= e((string) $values['rental_price_day']) ?>" data-price-field="rental"><div class="price-gauge" data-gauge-wrap="rental" hidden><div><span data-price-min="rental">−20 %</span><strong>Prix modifiable dans l’intervalle</strong><span data-price-max="rental">+20 %</span></div><input type="range" data-price-gauge="rental" aria-label="Ajuster le tarif de location dans la fourchette autorisée"></div></div>
                    </div>
                </fieldset>

                <fieldset class="verification-step">
                    <legend>3. Préparer la vérification pour une location</legend>
                    <p class="fieldset-intro">Toute offre de location passe par un contrôle avant publication. Le propriétaire choisit un dépôt partenaire ou un envoi suivi. Le contrôle confirme l’état, le fonctionnement, le score et le tarif journalier.</p>
                    <ol class="verification-timeline"><li><strong>Pré-demande</strong><span>Informations et photos initiales</span></li><li><strong>Contrôle</strong><span>État, fonctions et accessoires</span></li><li><strong>Validation</strong><span>Score et prix de location confirmés</span></li><li><strong>Publication</strong><span>Badge Produit vérifié visible</span></li></ol>
                </fieldset>

                <fieldset>
                    <legend>4. Calculer l’indice circulaire</legend>
                    <p class="fieldset-intro">Ces données permettent d’établir le niveau d’impact de l’offre.</p>
                    <div class="form-grid">
                        <div class="field"><label for="reuse_count">Locations déjà réalisées</label><input id="reuse_count" name="reuse_count" type="number" min="0" max="999" value="<?= e((string) $values['reuse_count']) ?>"><small>Nombre de locations ou de nouveaux usages déjà effectués avec cet appareil.</small></div>
                        <div class="field"><label for="distance_km">Distance estimée de la transaction (km)</label><input id="distance_km" name="distance_km" type="number" min="0" max="5000" value="<?= e((string) $values['distance_km']) ?>"></div>
                    </div>
                    <label class="check-row"><input type="checkbox" name="repaired" <?= $values['repaired'] ? 'checked' : '' ?>> Réparation professionnelle ayant prolongé sa durée de vie</label>
                    <p class="field-explanation">À cocher uniquement si une panne a été corrigée et que l’appareil fonctionne normalement. Cette information valorise la réparabilité et l’allongement de la durée de vie.</p>
                </fieldset>

                <div class="form-submit"><button class="button" type="submit">Enregistrer et poursuivre</button><p>Une offre de vente peut être publiée immédiatement. Une offre de location reste invisible jusqu’à la validation du contrôle.</p></div>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
