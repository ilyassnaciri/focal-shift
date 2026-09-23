<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Requête invalide ou session expirée.');
}

$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
if (!$equipmentId || !in_array($action, ['reserve', 'publish', 'delete'], true)) {
    http_response_code(400);
    exit('Action invalide.');
}

$pdo = db();
$filesToDelete = [];
try {
    $pdo->beginTransaction();
    $find = $pdo->prepare('SELECT id, owner_id, brand, model FROM equipment WHERE id=:id FOR UPDATE');
    $find->execute(['id' => $equipmentId]);
    $offer = $find->fetch();
    if (!$offer || (int) $offer['owner_id'] !== (int) $user['id']) {
        $pdo->rollBack();
        http_response_code(403);
        exit('Vous ne pouvez gérer que vos propres annonces.');
    }

    if ($action === 'delete') {
        $photos = $pdo->prepare('SELECT url FROM equipment_photos WHERE equipment_id=:id');
        $photos->execute(['id' => $equipmentId]);
        $filesToDelete = $photos->fetchAll(PDO::FETCH_COLUMN);
        $delete = $pdo->prepare('DELETE FROM equipment WHERE id=:id AND owner_id=:owner');
        $delete->execute(['id' => $equipmentId, 'owner' => $user['id']]);
        $pdo->commit();
        foreach ($filesToDelete as $path) {
            if (is_string($path) && str_starts_with($path, 'uploads/')) {
                $absolute = __DIR__ . '/' . $path;
                if (is_file($absolute)) unlink($absolute);
            }
        }
        flash('success', 'L’annonce « ' . $offer['brand'] . ' ' . $offer['model'] . ' » a été supprimée définitivement.');
        header('Location: ' . url('account.php'));
        exit;
    }

    $newStatus = $action === 'reserve' ? 'reserved' : 'published';
    $update = $pdo->prepare('UPDATE equipment SET status=:status WHERE id=:id AND owner_id=:owner');
    $update->execute(['status' => $newStatus, 'id' => $equipmentId, 'owner' => $user['id']]);
    $pdo->commit();
    flash('success', $newStatus === 'reserved'
        ? 'Annonce marquée comme réservée/louée : elle n’apparaît plus parmi les offres disponibles.'
        : 'Annonce remise en ligne : elle apparaît de nouveau dans le catalogue.');
    header('Location: ' . url('product.php?id=' . $equipmentId));
    exit;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash('error', 'La modification n’a pas pu être enregistrée. Lancez upgrade-v9.php puis réessayez.');
    header('Location: ' . url('product.php?id=' . $equipmentId));
    exit;
}
