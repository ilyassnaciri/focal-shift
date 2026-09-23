<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Le club du matériel créatif en mouvement';
$pageDescription = 'Estimez, louez ou vendez du matériel photo et vidéo premium dans un cadre de confiance.';
$activePage = 'home';

$featuredSale = [];
$featuredRental = [];
try {
    $baseQuery = "SELECT e.*, c.name AS category_name, c.slug AS category_slug, u.full_name, u.identity_verified,
                (SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id = e.id ORDER BY ep.id LIMIT 1) AS image_url
         FROM equipment e JOIN categories c ON c.id = e.category_id JOIN users u ON u.id = e.owner_id
         WHERE e.status = 'published' AND %s = 1 ORDER BY e.created_at DESC LIMIT 3";
    $featuredRental = db()->query(sprintf($baseQuery, 'e.available_for_rental'))->fetchAll();
    $featuredSale = db()->query(sprintf($baseQuery, 'e.available_for_sale'))->fetchAll();
} catch (Throwable $ignored) {
    // db() affiche déjà une aide d'installation en cas de connexion impossible.
}

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="hero hero-premium">
        <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="<?= asset('images/hero-premium.webp') ?>?v=6" aria-hidden="true">
            <source src="<?= asset('video/hero-premium.mp4') ?>?v=6" type="video/mp4">
        </video>
        <div class="hero-overlay" aria-hidden="true"></div>
        <div class="container hero-grid hero-grid-premium">
            <div class="hero-copy">
                <p class="eyebrow eyebrow-light">Le club premium · Photo & vidéo</p>
                <h1 class="hero-title">Le matériel qu’il vous faut, au moment où votre projet l’exige.</h1>
                <p class="hero-lead">Achetez ou louez des équipements photo et vidéo vérifiés. Propriétaires et bailleurs peuvent aussi valoriser leur matériel dans un parcours encadré.</p>
                <div class="hero-actions">
                    <a class="button" href="<?= url('catalogue-location.php') ?>" data-track="hero_rental">Louer un équipement</a>
                    <a class="button button-secondary" href="<?= url('catalogue-vente.php') ?>" data-track="hero_catalogue">Acheter un équipement</a>
                </div>
                <ul class="trust-inline" aria-label="Engagements de confiance">
                    <li>Identité vérifiée</li>
                    <li>État documenté</li>
                    <li>Paiement sécurisé*</li>
                </ul>
                <p class="microcopy">*Les conditions de protection sont précisées avant chaque transaction.</p>
            </div>
            <div class="hero-proof" aria-label="Indicateurs clés Focal-Shift">
                <div><strong>12</strong><span>équipements disponibles</span></div>
                <div><strong>3 niveaux</strong><span>impact lisible et expliqué</span></div>
                <div><strong>-5%</strong><span>pour une location à faible impact</span></div>
            </div>
        </div>
        <a class="scroll-cue" href="#promesses"><span>Découvrir</span><i aria-hidden="true"></i></a>
    </section>

    <section id="promesses" class="section value-section" aria-labelledby="two-promises">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Deux situations très concrètes</p>
                <h2 id="two-promises">Le bon matériel doit servir un projet, pas rester dans une valise.</h2>
            </div>
            <div class="duo-grid">
                <article class="promise-card promise-owner">
                    <h3>J’ai un tournage à préparer</h3>
                    <p>Je loue si le besoin est ponctuel. J’achète si cet équipement devient central dans mon activité. Dans les deux cas, je vois immédiatement le prix, l’état et ce qui est inclus.</p>
                    <a class="arrow-link" href="<?= url('catalogue-location.php') ?>">Trouver mon matériel <span aria-hidden="true">→</span></a>
                </article>
                <article class="promise-card promise-renter">
                    <h3>Mon matériel dort entre deux projets</h3>
                    <p>Je décide de le vendre ou de le louer. Le simulateur m’aide à fixer un prix réaliste ; pour une location, un contrôle confirme son état avant la mise en ligne.</p>
                    <a class="arrow-link" href="<?= url('simulator.php') ?>">Estimer mon matériel <span aria-hidden="true">→</span></a>
                </article>
            </div>
        </div>
    </section>

    <section class="section instant-products" aria-labelledby="instant-products-title">
        <div class="container">
            <div class="section-heading section-heading-row"><div><p class="eyebrow">Disponible maintenant</p><h2 id="instant-products-title">Deux catalogues, un choix sans ambiguïté.</h2><p>Louez pour un projet ponctuel ou achetez pour vous équiper durablement. Un produit n’apparaît jamais dans les deux catalogues.</p></div></div>
            <div class="home-catalogue-block"><div class="home-catalogue-heading"><h3>Matériel à louer</h3><a class="arrow-link" href="<?= url('catalogue-location.php') ?>">Voir toutes les locations →</a></div><div class="product-grid"><?php foreach ($featuredRental as $item): ?><?php require __DIR__ . '/includes/product-card.php'; ?><?php endforeach; ?></div></div>
            <div class="home-catalogue-block"><div class="home-catalogue-heading"><h3>Matériel à acheter</h3><a class="arrow-link" href="<?= url('catalogue-vente.php') ?>">Voir toutes les ventes →</a></div><div class="product-grid"><?php foreach ($featuredSale as $item): ?><?php require __DIR__ . '/includes/product-card.php'; ?><?php endforeach; ?></div></div>
        </div>
    </section>

    <section class="section dark-section" aria-labelledby="trust-title">
        <div class="container trust-layout">
            <div>
                <p class="eyebrow eyebrow-light">Notre différence</p>
                <h2 id="trust-title">La confiance devient une fonctionnalité du produit.</h2>
                <p>Chaque étape réduit le risque perçu : identité, état du matériel, échange documenté et traitement clair des incidents.</p>
            </div>
            <ol class="trust-steps">
                <li><span>01</span><div><strong>Profil vérifié</strong><small>Identité et réputation</small></div></li>
                <li><span>02</span><div><strong>État documenté</strong><small>Photos avant et après</small></div></li>
                <li><span>03</span><div><strong>Transaction encadrée</strong><small>Paiement et préautorisation documentés</small></div></li>
                <li><span>04</span><div><strong>Incident géré</strong><small>Réparation privilégiée</small></div></li>
            </ol>
        </div>
    </section>

    <section class="section circular-section" aria-labelledby="circular-title">
        <div class="container circular-grid">
            <div>
                <p class="eyebrow">Indicateur circulaire</p>
                <h2 id="circular-title">Mesurer la remise en circulation, sans inventer un bilan carbone.</h2>
                <p>Notre indice interne valorise l’ancienneté utile, les réutilisations, la réparation et la proximité. Il ne constitue pas une mesure d’émissions de CO₂.</p>
                <a class="arrow-link" href="<?= url('methodology.php') ?>">Lire la méthodologie <span aria-hidden="true">→</span></a>
            </div>
            <div class="impact-levels" aria-label="Trois niveaux de l’indicateur circulaire">
                <article class="impact-level impact-level-green"><span aria-hidden="true">✓</span><div><strong>Impact faible</strong><small>Proximité, réemploi et réparation favorables. −5 % pour le locataire.</small></div></article>
                <article class="impact-level impact-level-orange"><span aria-hidden="true">≈</span><div><strong>Impact moyen</strong><small>Choix équilibré, sans réduction, avec des critères à améliorer.</small></div></article>
                <article class="impact-level impact-level-red"><span aria-hidden="true">!</span><div><strong>Impact élevé</strong><small>Distance ou faible réemploi : vigilance, sans réduction.</small></div></article>
            </div>
        </div>
    </section>

    <section class="section editorial-teaser">
        <div class="container editorial-teaser-grid">
            <div><p class="eyebrow">Le Blog Focal-Shift</p><h2>Les professionnels partagent ce que les fiches techniques ne disent pas.</h2></div>
            <div><p>Interviews, tests produits, coulisses de tournage et conseils de réparation pour choisir avec davantage de discernement.</p><a class="button button-secondary" href="<?= url('blog.php') ?>">Découvrir le Blog</a></div>
        </div>
    </section>

    <section class="section partners-section" aria-labelledby="partners-title"><div class="container"><div class="section-heading text-center"><p class="eyebrow">Écosystème de confiance</p><h2 id="partners-title">Les partenaires qui nous font confiance.</h2><p>Assurance, fabricants et technologies réunis autour d’un matériel mieux utilisé.</p></div><div class="partners-grid">
        <div class="partner-card"><img src="<?=asset('images/partners/allianz.svg')?>" alt="Allianz"><span>Assurance</span></div>
        <div class="partner-card"><img src="<?=asset('images/partners/nikon.svg')?>" alt="Nikon"><span>Imagerie</span></div>
        <div class="partner-card"><img src="<?=asset('images/partners/kodak.svg')?>" alt="Kodak"><span>Imagerie</span></div>
        <div class="partner-card"><img src="<?=asset('images/partners/sony.svg')?>" alt="Sony"><span>Technologie</span></div>
        <div class="partner-card"><img src="<?=asset('images/partners/canon.svg')?>" alt="Canon"><span>Optique</span></div>
    </div><p class="partner-note">Partenariats présentés à titre de projection commerciale dans cette version de démonstration.</p></div></section>

    <section class="section key-figures-section" aria-labelledby="key-figures-title">
        <div class="container"><div class="section-heading"><p class="eyebrow">Chiffres clés</p><h2 id="key-figures-title">Une communauté qui remet le matériel en mouvement.</h2></div><div class="key-figures-grid">
            <article><strong data-counter="12">12</strong><span>équipements disponibles dans le catalogue</span></article>
            <article><strong data-counter="49">49</strong><span>transactions déjà réalisées par les membres présentés</span></article>
            <article><strong data-counter="4.8" data-suffix="/5">4,8/5</strong><span>note moyenne des profils de démonstration</span></article>
            <article><strong data-counter="5" data-suffix=" %">5 %</strong><span>de réduction sur les frais pour une location à impact faible</span></article>
        </div><p class="data-note">Indicateurs calculés à partir des données de démonstration de la plateforme.</p></div>
    </section>

    <section class="section testimonials-section" aria-labelledby="reviews-title"><div class="container"><div class="section-heading"><p class="eyebrow">Avis et succès clients</p><h2 id="reviews-title">Le matériel circule, les projets avancent.</h2></div><div class="testimonials-grid">
        <blockquote><p>« J’ai testé le Nikon Z8 sur un week-end avant de confirmer mon choix. Le calendrier et le prix complet m’ont permis de décider sereinement. »</p><footer><img src="<?=asset('images/testimonials/sarah.webp')?>" alt="Portrait fictif de Sarah M."><span><strong>Sarah M.</strong><small>Photographe événementielle</small></span></footer></blockquote>
        <blockquote><p>« Mon objectif dormait dix mois par an. Il finance maintenant son entretien et reste disponible pour mes propres tournages. »</p><footer><img src="<?=asset('images/testimonials/yanis.webp')?>" alt="Portrait fictif de Yanis R."><span><strong>Yanis R.</strong><small>Réalisateur indépendant</small></span></footer></blockquote>
        <blockquote><p>« Les photos d’état et la messagerie liée à l’offre ont rendu la remise beaucoup plus professionnelle. »</p><footer><img src="<?=asset('images/testimonials/lina.webp')?>" alt="Portrait fictif représentant Studio Horizon"><span><strong>Studio Horizon</strong><small>Agence de production</small></span></footer></blockquote>
    </div><p class="data-note">Témoignages fictifs utilisés pour illustrer l’expérience cible de la V13.</p></div></section>

    <section id="newsletter" class="section newsletter-section" aria-labelledby="newsletter-title"><div class="container newsletter-grid"><div><p class="eyebrow eyebrow-light">Newsletter</p><h2 id="newsletter-title">Recevez les nouveaux tests et équipements.</h2><p>Un email éditorial pour suivre les interviews, essais produits et opportunités du catalogue.</p></div><form method="post" action="<?=url('newsletter.php')?>"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label><span>Votre email</span><input type="email" name="email" placeholder="vous@exemple.fr" required autocomplete="email"></label><label><span>Qui êtes-vous ?</span><select name="profile_type" required><option value="">Choisir mon profil</option><option value="photographe">Photographe</option><option value="videaste">Vidéaste</option><option value="studio_agence">Studio ou agence</option><option value="proprietaire">Propriétaire de matériel</option><option value="passionne">Passionné</option><option value="autre">Autre</option></select></label><label class="newsletter-consent"><input type="checkbox" name="newsletter_consent" value="1" required><span>J’accepte de recevoir les actualités Focal-Shift.</span></label><button class="button" type="submit">S’inscrire</button></form></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
