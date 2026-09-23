<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Marketplace premium et circulaire de matériel photo et vidéo.';
$activePage = $activePage ?? '';
$flashMessage = pull_flash();
$user = current_user();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#17201f">
    <title><?= e($pageTitle) ?> · Focal-Shift</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<a class="skip-link" href="#main-content">Aller au contenu principal</a>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= url('index.php') ?>" aria-label="Focal-Shift, accueil">
            <img class="brand-logo" src="<?= asset('images/brand/focal-shift-logo.png') ?>" alt="" width="110" height="60">
            <span class="sr-only">Focal-Shift</span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="main-nav">
            <span class="sr-only">Ouvrir le menu</span><span aria-hidden="true">Menu</span>
        </button>
        <nav id="main-nav" class="main-nav" aria-label="Navigation principale">
            <a <?= $activePage === 'catalogue-sale' ? 'aria-current="page"' : '' ?> href="<?= url('catalogue-vente.php') ?>">Acheter</a>
            <a <?= $activePage === 'catalogue-rental' ? 'aria-current="page"' : '' ?> href="<?= url('catalogue-location.php') ?>">Louer</a>
            <a <?= $activePage === 'simulator' ? 'aria-current="page"' : '' ?> href="<?= url('simulator.php') ?>">Simulateur</a>
            <a <?= $activePage === 'blog' ? 'aria-current="page"' : '' ?> href="<?= url('blog.php') ?>">Blog</a>
            <a <?= $activePage === 'contact' ? 'aria-current="page"' : '' ?> href="<?= url('contact.php') ?>">Contact</a>
            <a <?= $activePage === 'faq' ? 'aria-current="page"' : '' ?> href="<?= url('faq.php') ?>">FAQ</a>
            <?php if ($user): ?>
                <a class="nav-account" <?= $activePage === 'account' ? 'aria-current="page"' : '' ?> href="<?= url('account.php') ?>"><span class="nav-avatar" aria-hidden="true"><?= e(strtoupper(substr($user['full_name'], 0, 1))) ?></span><?= e(explode(' ', $user['full_name'])[0]) ?></a>
            <?php else: ?>
                <a <?= $activePage === 'login' ? 'aria-current="page"' : '' ?> href="<?= url('login.php') ?>">Connexion</a>
            <?php endif; ?>
            <a class="button button-small" <?= $activePage === 'deposit' ? 'aria-current="page"' : '' ?> href="<?= url('deposit.php') ?>">Publier une offre</a>
        </nav>
    </div>
</header>
<?php if ($flashMessage): ?>
<div class="container flash flash-<?= e($flashMessage['type']) ?>" role="status">
    <?= e($flashMessage['message']) ?>
</div>
<?php endif; ?>
