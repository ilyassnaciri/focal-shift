<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $exception) {
        http_response_code(503);
        $detail = APP_ENV === 'development'
            ? '<p class="technical-detail">Détail local : ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>'
            : '';
        exit(
            '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Base indisponible</title>' .
            '<style>body{font:16px system-ui;background:#f4f1ea;color:#17201f;padding:3rem}.box{max-width:720px;margin:auto;background:white;padding:2rem;border-radius:20px}.technical-detail{font-family:monospace;font-size:.85rem;overflow-wrap:anywhere}</style>' .
            '<main class="box"><h1>Connexion à la base impossible</h1><p>Démarrez MySQL dans WampServer puis importez <strong>database/focal_shift.sql</strong> dans phpMyAdmin.</p>' .
            $detail . '</main></html>'
        );
    }
}

