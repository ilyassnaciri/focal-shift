<?php declare(strict_types=1); ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand brand-footer" href="<?= url('index.php') ?>"><img class="brand-logo brand-logo-footer" src="<?= asset('images/brand/focal-shift-logo.png') ?>" alt="Focal-Shift" width="110" height="60"></a>
            <p>La confiance devient une fonctionnalité du produit.</p>
        </div>
        <div>
            <h2 class="footer-title">Découvrir</h2>
            <a href="<?= url('catalogue-vente.php') ?>">Catalogue vente</a><br>
            <a href="<?= url('catalogue-location.php') ?>">Catalogue location</a><br>
            <a href="<?= url('blog.php') ?>">Blog</a><br>
            <a href="<?= url('contact.php') ?>">Contact</a><br>
            <a href="<?= url('glossary.php') ?>">Glossaire</a><br>
            <a href="<?= url('faq.php') ?>">FAQ & protection</a>
        </div>
        <div>
            <h2 class="footer-title">Conformité</h2>
            <a href="<?= url('privacy.php') ?>">RGPD & données personnelles</a><br>
            <a href="<?= url('legal.php') ?>">Mentions légales</a><br>
            <button class="text-button" type="button" data-open-consent>Gérer mes préférences</button>
        </div>
        <div>
            <h2 class="footer-title">Nous suivre</h2>
            <div class="social-links">
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Focal-Shift sur Instagram"><span aria-hidden="true">◎</span> Instagram</a>
                <a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="Focal-Shift sur LinkedIn"><span aria-hidden="true">in</span> LinkedIn</a>
                <a href="https://www.youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="Focal-Shift sur YouTube"><span aria-hidden="true">▶</span> YouTube</a>
            </div>
        </div>
    </div>
</footer>

<section class="cookie-banner" data-consent-banner hidden aria-labelledby="consent-title" role="dialog" aria-modal="true">
    <div>
        <h2 id="consent-title">Votre choix, sans détour</h2>
        <p>Un cookie de session essentiel sécurise les comptes et formulaires. La mesure d’audience reste désactivée sans votre accord.</p>
    </div>
    <div class="cookie-actions">
        <button class="button button-secondary" type="button" data-consent="refused">Tout refuser</button>
        <button class="button" type="button" data-consent="granted">Autoriser la mesure</button>
    </div>
</section>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
