<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_is_valid($_POST['csrf_token'] ?? null)) {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    flash('success', 'Vous êtes déconnecté.');
}
header('Location: ' . url('login.php'));
exit;
