<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Accès refusé.');
}
if (APP_ENV !== 'development') {
    http_response_code(404);
    exit('Compte de démonstration indisponible.');
}
$allowed = ['claire.demo@focal-shift.local', 'ilyass.demo@focal-shift.local'];
$email = mb_strtolower(trim((string)($_POST['demo_email'] ?? '')));
if (!in_array($email, $allowed, true)) {
    http_response_code(403);
    exit('Compte de démonstration invalide.');
}
$statement = db()->prepare('SELECT * FROM users WHERE email=:email LIMIT 1');
$statement->execute(['email' => $email]);
$user = $statement->fetch();
if (!$user) {
    flash('error', 'Exécutez upgrade-v14.php pour installer les exemples de démonstration.');
    header('Location: ' . url('login.php'));
    exit;
}
login_user($user);
flash('success', 'Mode démonstration : ' . $user['full_name'] . '.');
header('Location: ' . url('account.php'));
exit;
