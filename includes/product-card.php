<?php
declare(strict_types=1);
$score = (int) ($item['circular_score'] ?? 0);
$tier = score_tier($score);
?>
<article class="product-card" data-product-card data-lat="<?= e(isset($item['latitude']) ? (string) $item['latitude'] : '') ?>" data-lon="<?= e(isset($item['longitude']) ? (string) $item['longitude'] : '') ?>">
    <a class="product-image" href="<?= url('product.php?id=' . (int) $item['id']) ?>" aria-label="Voir <?= e($item['brand'] . ' ' . $item['model']) ?>">
        <img src="<?= e(equipment_image($item['image_url'] ?? null, $item['category_slug'] ?? 'camera')) ?>" alt="<?= e($item['brand'] . ' ' . $item['model']) ?>" loading="lazy">
        <?php if (!empty($item['available_for_rental'])): ?><span class="offer-type-badge offer-type-rental">À louer</span><?php else: ?><span class="offer-type-badge offer-type-sale">À acheter</span><?php endif; ?>
        <span class="score-badge score-<?= e($tier['class']) ?>" aria-label="<?= e($tier['label']) ?>"><b aria-hidden="true"><?= e($tier['symbol']) ?></b><small><?= e($tier['label']) ?></small></span>
    </a>
    <div class="product-body">
        <div class="product-meta"><span><?= e($item['category_name'] ?? '') ?></span><span><?= e(condition_label($item['condition_grade'])) ?></span></div>
        <h3><a href="<?= url('product.php?id=' . (int) $item['id']) ?>"><?= e($item['brand'] . ' ' . $item['model']) ?></a></h3>
        <p class="product-description"><?= e(mb_strimwidth((string)($item['description'] ?? ''), 0, 145, '…')) ?></p>
        <div class="product-location"><span><?= e($item['city'] ?? 'Île-de-France') ?></span><span data-distance><?= isset($item['search_distance']) ? e((string) round((float) $item['search_distance'])) . ' km' : '' ?></span></div>
        <div class="product-prices">
            <?php if (!empty($item['available_for_rental'])): ?><span><strong><?= money((float) $item['rental_price_day']) ?></strong>/jour</span><?php endif; ?>
            <?php if (!empty($item['available_for_sale'])): ?><span><strong><?= money((float) $item['sale_price']) ?></strong> à l’achat</span><?php endif; ?>
        </div>
        <p class="verified-line"><?= !empty($item['identity_verified']) ? '<span aria-hidden="true">✓</span> Propriétaire vérifié' : 'Profil en attente de vérification' ?></p>
        <div class="impact-pill impact-<?= e($tier['class']) ?>"><span class="impact-symbol" aria-hidden="true"><?= e($tier['symbol']) ?></span><?= e($tier['label']) ?><?php if ($tier['class'] === 'green' && !empty($item['available_for_rental'])): ?> · −5 % sur la location<?php endif; ?></div>
    </div>
</article>
