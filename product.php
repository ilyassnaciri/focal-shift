<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Équipement introuvable.');
}

$viewer = current_user();
$statement = db()->prepare(
    "SELECT e.*, c.name AS category_name, c.slug AS category_slug,
            u.full_name, u.identity_verified, u.rating_avg, u.rating_count, u.transaction_count, u.created_at AS member_since,
            (SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id = e.id ORDER BY ep.id LIMIT 1) AS image_url
     FROM equipment e
     JOIN categories c ON c.id = e.category_id
     JOIN users u ON u.id = e.owner_id
     WHERE e.id = :id AND (e.status IN ('published','reserved') OR e.owner_id = :viewer)"
);
$statement->execute(['id' => $id, 'viewer' => (int)($viewer['id'] ?? 0)]);
$item = $statement->fetch();
if (!$item) {
    http_response_code(404);
    exit('Équipement introuvable.');
}
$isOwner = $viewer && (int) $viewer['id'] === (int) $item['owner_id'];
$isReserved = $item['status'] === 'reserved';
$unavailable = [];
try {
    $availabilityQuery = db()->prepare('SELECT start_date,end_date,reason FROM equipment_unavailability WHERE equipment_id=:id AND end_date>=CURRENT_DATE ORDER BY start_date LIMIT 8');
    $availabilityQuery->execute(['id'=>$id]);
    $unavailable = $availabilityQuery->fetchAll();
} catch (Throwable $ignored) {}

$pageTitle = $item['brand'] . ' ' . $item['model'];
$pageDescription = 'Consultez cet équipement et ses marqueurs de confiance Focal-Shift.';
$activePage = 'catalogue';
$score = (int) $item['circular_score'];
$tier = score_tier($score);
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <div class="container breadcrumb"><a href="<?= url(!empty($item['available_for_rental']) ? 'catalogue-location.php' : 'catalogue-vente.php') ?>">Catalogue</a><span aria-hidden="true">/</span><span><?= e($item['brand'] . ' ' . $item['model']) ?></span></div>
    <section class="section product-detail">
        <div class="container product-detail-grid">
            <div class="detail-image-wrap">
                <img src="<?= e(equipment_image($item['image_url'], $item['category_slug'])) ?>" alt="<?= e($item['brand'] . ' ' . $item['model']) ?>">
                <span class="demo-label">Photo de l’équipement</span>
            </div>
            <div class="detail-panel">
                <?php if ($item['status']==='pending_verification'): ?><div class="availability-badge is-reserved">Vérification du produit requise avant publication</div><?php endif; ?>
                <?php if ($isReserved): ?><div class="availability-badge is-reserved">Réservé / actuellement loué</div><?php endif; ?>
                <p class="eyebrow"><?= e($item['category_name']) ?> · <?= e(condition_label($item['condition_grade'])) ?></p>
                <h1><?= e($item['brand'] . ' ' . $item['model']) ?></h1>
                <p class="detail-description"><?= e($item['description']) ?></p>
                <div class="price-options">
                    <?php if ($item['available_for_rental']): ?>
                    <div><span>Location</span><strong><?= money((float) $item['rental_price_day']) ?><small>/jour</small></strong></div>
                    <?php endif; ?>
                    <?php if ($item['available_for_sale']): ?>
                    <div><span>Achat</span><strong><?= money((float) $item['sale_price']) ?></strong></div>
                    <?php endif; ?>
                </div>
                <?php if ($isOwner): ?>
                <section class="owner-offer-actions" aria-labelledby="owner-actions-title">
                    <div><p class="eyebrow">Gestion de l’annonce</p><h2 id="owner-actions-title">Cette offre vous appartient.</h2><p>Modifiez ses informations, gérez sa disponibilité ou supprimez-la.</p></div>
                    <div class="owner-action-buttons">
                        <a class="button" href="<?= url('edit-offer.php?id=' . (int) $item['id']) ?>">Modifier l’annonce</a>
                        <form method="post" action="<?= url('offer-action.php') ?>">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="equipment_id" value="<?= (int) $item['id'] ?>">
                            <input type="hidden" name="action" value="<?= $isReserved ? 'publish' : 'reserve' ?>">
                            <button class="button button-outline" type="submit"><?= $isReserved ? 'Remettre disponible' : 'Déclarer réservé / loué' ?></button>
                        </form>
                        <form method="post" action="<?= url('offer-action.php') ?>" onsubmit="return confirm('Supprimer définitivement cette annonce, sa photo et ses conversations ? Cette action est irréversible.');">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="equipment_id" value="<?= (int) $item['id'] ?>"><input type="hidden" name="action" value="delete">
                            <button class="button button-danger" type="submit">Supprimer l’annonce</button>
                        </form>
                    </div>
                    <?php if ($item['available_for_rental']): ?>
                    <form class="availability-form" method="post" action="<?= url('availability.php') ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="equipment_id" value="<?= (int)$item['id'] ?>">
                        <h3>Bloquer une période de location</h3><p>Indiquez les dates où votre matériel ne doit pas pouvoir être réservé.</p>
                        <div class="availability-form-grid"><label><span>Du</span><input type="date" name="start_date" min="<?=date('Y-m-d')?>" required></label><label><span>Au</span><input type="date" name="end_date" min="<?=date('Y-m-d')?>" required></label><label><span>Motif facultatif</span><input type="text" name="reason" maxlength="160" placeholder="Tournage personnel, entretien…"></label><button class="button button-secondary" type="submit">Ajouter au calendrier</button></div>
                    </form>
                    <?php endif; ?>
                </section>
                <?php elseif ($isReserved): ?>
                <div class="reserved-notice"><strong>Cette offre n’est pas disponible actuellement.</strong><span>Elle est réservée ou en cours de location.</span></div>
                <?php else: ?>
                <div class="buyer-actions">
                    <?php if ($item['available_for_sale']): ?><a class="button" href="<?= url('transaction.php?id=' . (int)$item['id'] . '&type=purchase') ?>">Acheter · voir le prix total</a><?php endif; ?>
                    <?php if ($item['available_for_rental']): ?><a class="button button-secondary" href="<?= url('transaction.php?id=' . (int)$item['id'] . '&type=rental') ?>">Louer · choisir les dates</a><?php endif; ?>
                    <a class="arrow-link" href="<?= url('messages.php?equipment_id=' . (int) $item['id']) ?>">Poser une question au propriétaire <span aria-hidden="true">→</span></a>
                </div>
                <p class="microcopy text-center">Le détail inclut la livraison et, pour la location, l’assurance estimée.</p>
                <?php endif; ?>

                <div class="owner-card">
                    <div class="avatar" aria-hidden="true"><?= e(strtoupper(substr($item['full_name'], 0, 1))) ?></div>
                    <div><strong><?= e($item['full_name']) ?></strong><span><?= $item['identity_verified'] ? '✓ Identité vérifiée' : 'Vérification en cours' ?></span></div>
                    <div class="owner-stats"><strong><?= e((string) $item['rating_avg']) ?>/5</strong><span><?= (int) $item['transaction_count'] ?> transactions</span></div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($item['available_for_rental']): ?>
    <section class="section availability-section"><div class="container availability-public"><div><p class="eyebrow">Calendrier de location</p><h2>Disponibilités annoncées</h2><p>Les périodes ci-dessous ne peuvent pas être sélectionnées. Le contrôle final est effectué lors de la demande.</p></div><div class="availability-list"><?php if(!$unavailable):?><p class="availability-open"><strong>Disponible</strong><span>Aucune période bloquée à venir.</span></p><?php else:?><?php foreach($unavailable as $period):?><p><strong><?=date('d/m/Y',strtotime($period['start_date']))?> – <?=date('d/m/Y',strtotime($period['end_date']))?></strong><span><?=e($period['reason']?:'Indisponible')?></span></p><?php endforeach;?><?php endif;?></div></div></section>
    <?php endif; ?>

    <section class="section trust-detail-section">
        <div class="container detail-info-grid">
            <article>
                <p class="eyebrow">Protection Focal-Shift</p>
                <h2>La réassurance est visible avant l’action.</h2>
                <ul class="check-list">
                    <li>Identité et réputation du propriétaire</li>
                    <li>État photographié avant et après location</li>
                    <li>Paiement et caution encadrés avant la remise du matériel</li>
                    <li>Processus d’incident avec réparation prioritaire</li>
                </ul>
            </article>
            <article class="circular-card circular-card-<?= e($tier['class']) ?>">
                <div class="impact-emblem impact-emblem-<?= e($tier['class']) ?>" aria-hidden="true"><?= e($tier['symbol']) ?></div>
                <div><p class="eyebrow">Indicateur circulaire</p><h2><?= e($tier['label']) ?></h2><p><?= e($tier['summary']) ?></p><?php if ($tier['class']==='green' && !empty($item['available_for_rental'])): ?><p class="discount-callout">Avantage locataire : −5 % sur le montant de la location.</p><?php endif; ?><a href="<?= url('methodology.php') ?>">Comprendre les trois niveaux</a></div>
            </article>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
