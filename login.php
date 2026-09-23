<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) { header('Location: ' . url('account.php')); exit; }
$pdo = db();
$errors = [];
$next = safe_next_url($_GET['next'] ?? $_POST['next'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) $errors[] = 'La session a expiré. Rechargez la page.';
    if (!$errors) {
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $foundUser = $stmt->fetch();
        if (!$foundUser || !$foundUser['password_hash'] || !password_verify($password, $foundUser['password_hash'])) {
            $errors[] = 'Adresse ou mot de passe incorrect.';
        } else {
            login_user($foundUser);
            flash('success', 'Bienvenue ' . $foundUser['full_name'] . '.');
            header('Location: ' . $next);
            exit;
        }
    }
}

$pageTitle = 'Connexion';
$pageDescription = 'Accès acheteur, locataire, vendeur ou bailleur Focal-Shift.';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="auth-hero"><div class="container"><p class="eyebrow eyebrow-light">Votre espace privé</p><h1>Un seul accès.<br>Toutes vos activités.</h1></div></section>
    <section class="section auth-section"><div class="container auth-single-layout">
        <div class="auth-benefits"><p class="eyebrow eyebrow-light">Compte unique</p><h2>Achetez, louez, vendez ou publiez avec le même compte.</h2><ul class="check-list"><li>Toutes les fonctionnalités avec un seul accès</li><li>Messagerie liée à chaque offre</li><li>Historique et profil centralisés</li></ul></div>
        <div class="auth-card">
                <p class="eyebrow">Connexion membre</p><h2>Bienvenue chez Focal-Shift</h2>
                <?php if ($errors): ?><div class="error-summary" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="next" value="<?= e($next) ?>">
                    <div class="field"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" autocomplete="email" required></div>
                    <div class="field"><label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
                    <button class="button button-block" type="submit">Se connecter</button>
                </form>
                <?php if (APP_ENV === 'development'): ?><div class="demo-access"><p><strong>Parcours prêts pour la présentation</strong><br><small>Accès local sans saisie de mot de passe.</small></p><div class="demo-access-grid"><form method="post" action="<?=url('demo-login.php')?>"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="demo_email" value="claire.demo@focal-shift.local"><button class="button button-outline button-block" type="submit">Claire · ses offres</button></form><form method="post" action="<?=url('demo-login.php')?>"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="demo_email" value="ilyass.demo@focal-shift.local"><button class="button button-outline button-block" type="submit">Ilyass · rendre le produit</button></form></div></div><?php endif; ?>
                <p class="auth-register">Nouveau membre ? <a href="<?= url('register.php') ?>">Créer un compte</a></p>
        </div>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
