<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$categories = db()->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
$references = db()->query('SELECT brand, model FROM price_reference ORDER BY brand, model')->fetchAll();
$demoReferences = db()->query('SELECT pr.brand, pr.model, pr.category_id, pr.daily_rental_reference, c.name AS category_name FROM price_reference pr JOIN categories c ON c.id = pr.category_id WHERE pr.daily_rental_reference >= 48 ORDER BY pr.daily_rental_reference DESC LIMIT 6')->fetchAll();
$pageTitle = 'Simulateur de cote';
$pageDescription = 'Comparez une estimation de revente et le potentiel locatif de votre équipement.';
$activePage = 'simulator';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="simulator-hero">
        <div class="container simulator-intro">
            <div>
                <p class="eyebrow eyebrow-light">L’outil d’aide à la décision</p>
                <h1>Vendre ou louer ?<br>Comparez avant de choisir.</h1>
            </div>
            <p>Le simulateur combine un référentiel SQL et des règles métier transparentes. Les résultats sont indicatifs et ne constituent ni une offre d’achat ni une garantie de revenu.</p>
        </div>
    </section>

    <section class="section simulator-section">
        <div class="container simulator-flow">
            <form class="simulator-form simulator-wizard" data-simulator-form>
                <div class="wizard-heading"><div><p class="eyebrow">Simulation express</p><h2>Deux étapes, puis votre estimation.</h2></div><span data-step-label>Étape 1 sur 2</span></div>
                <div class="wizard-progress" aria-hidden="true"><span data-step-progress></span></div>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <fieldset class="wizard-step is-active" data-step="1"><legend>Quel matériel souhaitez-vous estimer ?</legend><div class="demo-products"><p>Exemples premium pour la démonstration</p><div><?php foreach ($demoReferences as $demo): ?><button type="button" data-demo-product data-category="<?= (int)$demo['category_id'] ?>" data-brand="<?= e($demo['brand']) ?>" data-model="<?= e($demo['model']) ?>"><strong><?= e($demo['brand'].' '.$demo['model']) ?></strong><span><?= money((float)$demo['daily_rental_reference']) ?>/jour · <?= e($demo['category_name']) ?></span></button><?php endforeach; ?></div></div><div class="field"><label for="sim_category">Catégorie</label><select id="sim_category" name="category_id" required><option value="">Choisir une catégorie</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" data-image="<?= asset('images/catalog/' . $category['slug'] . '.webp') ?>" data-category-name="<?= e($category['name']) ?>"><?= e($category['name']) ?></option><?php endforeach; ?></select></div><div class="form-grid"><div class="field"><label for="sim_brand">Marque</label><input id="sim_brand" name="brand" list="brand-list" autocomplete="off" required><datalist id="brand-list"><?php foreach (array_unique(array_column($references, 'brand')) as $brand): ?><option value="<?= e($brand) ?>"><?php endforeach; ?></datalist></div><div class="field"><label for="sim_model">Modèle</label><input id="sim_model" name="model" list="model-list" autocomplete="off" required><datalist id="model-list"><?php foreach ($references as $reference): ?><option value="<?= e($reference['model']) ?>"><?= e($reference['brand']) ?></option><?php endforeach; ?></datalist></div></div><figure class="simulator-product-preview" data-simulator-preview hidden><img src="" alt="" data-simulator-preview-image><figcaption><span>Catégorie sélectionnée</span><strong data-simulator-preview-caption></strong></figcaption></figure></fieldset>
                <fieldset class="wizard-step" data-step="2" hidden><legend>Précisez son état et son utilisation</legend><div class="form-grid"><div class="field"><label for="sim_year">Année d’achat</label><input id="sim_year" name="purchase_year" type="number" min="1990" max="<?= date('Y') ?>" value="<?= date('Y') - 2 ?>" required></div><div class="field"><label for="sim_condition">État constaté</label><select id="sim_condition" name="condition_grade" required><option value="neuf">Comme neuf</option><option value="tres_bon" selected>Très bon état</option><option value="bon">Bon état</option><option value="use">État d’usage</option></select></div><div class="field"><label for="sim_reuse">Locations déjà réalisées</label><input id="sim_reuse" name="reuse_count" type="number" min="0" max="999" value="3" required></div><div class="field"><label for="sim_distance">Distance prévue</label><input id="sim_distance" name="distance_km" type="number" min="0" max="5000" value="25" required></div></div><label class="check-row simulator-check"><input type="checkbox" name="repaired" value="1"> Une réparation professionnelle a prolongé sa durée de vie</label><p class="wizard-note">Le résultat affichera une estimation de vente, un tarif de location et les fourchettes recommandées.</p></fieldset>
                <div class="wizard-actions"><button class="button button-outline" type="button" data-step-prev hidden>Retour</button><button class="button" type="button" data-step-next>Continuer</button><button class="button" type="submit" data-step-submit hidden>Afficher ma simulation</button></div>
                <p class="form-status" role="status" aria-live="polite" data-simulator-status></p>
            </form>

            <section class="simulator-result" aria-labelledby="result-title" data-simulator-result hidden>
                <div class="result-placeholder" data-result-placeholder hidden>
                    <h2 id="result-title">Votre simulation sera présentée après la dernière étape.</h2>
                </div>
                <div class="result-content" data-result-content tabindex="-1" hidden>
                    <div class="result-topline"><span class="assumption-badge">Hypothèse de simulation</span><span data-match-label></span></div>
                    <p class="result-kicker">Valeur de revente estimée</p>
                    <p class="result-main-value" data-resale-value>—</p>
                    <div class="comparison-grid">
                        <article><span>Vendre maintenant</span><strong data-sale-net>—</strong><small>net après frais hypothétiques</small></article>
                        <article class="highlight"><span>Louer</span><strong data-rental-day>—</strong><small>tarif conseillé par jour</small></article>
                    </div>
                    <div class="rental-projections">
                        <div><span>10 jours</span><strong data-rental-10>—</strong></div>
                        <div><span>30 jours</span><strong data-rental-30>—</strong></div>
                        <div><span>60 jours</span><strong data-rental-60>—</strong></div>
                    </div>
                    <div class="decision-box">
                        <span>Seuil de récupération par location</span>
                        <strong><span data-break-even>—</span> jours</strong>
                        <p>Nombre de jours nécessaires pour que les loyers nets cumulés égalent le produit net d’une vente immédiate.</p>
                    </div>
                    <div class="sim-score-card" data-score-card>
                        <div class="impact-emblem" data-score-symbol aria-hidden="true">—</div>
                        <div><span class="result-kicker">Indicateur circulaire</span><strong data-score-label>—</strong><p data-score-benefit>—</p></div>
                    </div>
                    <details class="method-details"><summary>Voir les hypothèses de calcul</summary><ul><li>Commission location : 15 %</li><li>Commission vente : 10 %</li><li>Dépréciation : 15 %/an pendant 2 ans, puis 8 %/an</li><li>Plancher : 20 % du prix neuf de référence</li></ul></details>
                    <a class="button button-block" href="<?= url('deposit.php') ?>">Poursuivre vers la vérification et la publication</a>
                </div>
            </section>
        </div>
    </section>
    <section class="section methodology-teaser"><div class="container"><p><strong>Transparence :</strong> l’estimation s’appuie sur un référentiel daté et des règles explicables. <a href="<?= url('methodology.php') ?>">Consulter la méthodologie complète</a>.</p></div></section>
</main>
<script src="<?= asset('js/simulator.js') ?>" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
