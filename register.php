<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = db();
$errors = [];
$role = 'both';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['full_name'] ?? ''));
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) $errors[] = 'Session expirée.';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Renseignez un nom valide.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Renseignez une adresse e-mail valide.';
    if (strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    if (!isset($_POST['terms'])) $errors[] = 'Vous devez accepter les conditions d’utilisation.';
    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (full_name,email,password_hash,role,identity_verified) VALUES (:name,:email,:password,:role,0)');
            $stmt->execute(['name'=>$name,'email'=>$email,'password'=>password_hash($password,PASSWORD_DEFAULT),'role'=>$role]);
            $id = (int) $pdo->lastInsertId();
            login_user(['id'=>$id,'full_name'=>$name,'email'=>$email,'role'=>$role,'identity_verified'=>0]);
            flash('success', 'Compte créé : il est maintenant visible dans la table users de phpMyAdmin.');
            header('Location: ' . url('account.php'));
            exit;
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000' ? 'Cette adresse existe déjà.' : 'Création impossible.';
        }
    }
}
$pageTitle = 'Créer un compte';
$activePage = 'login';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-hero page-hero-compact"><div class="container narrow"><p class="eyebrow">Nouveau membre</p><h1>Rejoindre le cercle.</h1><p>Un compte unique pour rechercher, échanger et publier selon vos besoins.</p></div></section>
    <section class="section"><div class="container narrow"><form class="form-card" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($errors): ?><div class="error-summary" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <div class="field"><label for="full_name">Nom affiché</label><input id="full_name" name="full_name" maxlength="120" required></div>
        <div class="field"><label for="email">Adresse e-mail</label><input id="email" name="email" type="email" required></div>
        <div class="field"><label for="password">Mot de passe</label><input id="password" name="password" type="password" minlength="8" required><small>8 caractères minimum.</small></div>
        <div class="unified-account-note"><strong>Un compte, toutes les possibilités</strong><p>Après votre inscription, vous pourrez acheter, louer, vendre ou proposer un équipement à la location avec le même espace membre.</p></div>
        <label class="check-row"><input type="checkbox" name="terms" required> J’accepte les conditions d’utilisation et la politique de confidentialité.</label>
        <button class="button button-block auth-submit" type="submit">Créer mon compte</button>
    </form></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
