<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Données et cookies';
$pageDescription = 'Principes RGPD et confidentialité appliqués à Focal-Shift.';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-hero"><div class="container narrow"><p class="eyebrow">Privacy by design</p><h1>Collecter moins, expliquer mieux.</h1><p>Cette page décrit les traitements de données et les protections associées.</p></div></section>
    <section class="section"><div class="container article-layout">
        <article class="prose-card"><h2>Mesures actives</h2><ul><li>Cookie de session essentiel pour sécuriser les formulaires.</li><li>Préférence de consentement conservée localement dans le navigateur.</li><li>Aucun outil publicitaire externe chargé par défaut.</li><li>Protection CSRF, validation des formulaires et contrôle des images téléversées.</li></ul></article>
        <article class="prose-card"><h2>Engagements de conformité</h2><ul><li>Information claire sur les finalités, bases légales, destinataires et durées.</li><li>Refus des traceurs aussi simple que leur acceptation.</li><li>Minimisation, habilitations par rôle, chiffrement en transit et journalisation.</li><li>Droits d’accès, rectification, effacement, opposition et portabilité.</li><li>Encadrement contractuel des partenaires d’identité, paiement et assurance.</li></ul><p>Les durées de conservation et responsabilités sont documentées avant toute mise en production publique.</p></article>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
