<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/config.php';

$checks = [];
$checks[] = ['PHP 8.1 ou supérieur', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION];
$checks[] = ['Extension PDO MySQL', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'chargée' : 'absente'];
$checks[] = ['Extension Fileinfo', extension_loaded('fileinfo'), extension_loaded('fileinfo') ? 'chargée' : 'absente'];
$checks[] = ['Dossier uploads inscriptible', is_writable(__DIR__ . '/uploads'), is_writable(__DIR__ . '/uploads') ? 'oui' : 'non'];

$databaseOk = false;
$databaseDetail = 'non testée';
if (extension_loaded('pdo_mysql')) {
    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $tableCount = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'focal_shift'")->fetchColumn();
        $equipmentCount = (int) $pdo->query('SELECT COUNT(*) FROM equipment')->fetchColumn();
        $requiredColumns = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = 'focal_shift' AND ((table_name='equipment' AND column_name='city') OR (table_name='simulator_requests' AND column_name='circular_score'))")->fetchColumn();
        $statusType = (string) $pdo->query("SELECT COLUMN_TYPE FROM information_schema.columns WHERE table_schema = 'focal_shift' AND table_name='equipment' AND column_name='status'")->fetchColumn();
        $v7Ready = str_contains($statusType, "'reserved'");
        $v9Tables = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='focal_shift' AND table_name IN ('newsletter_subscribers','equipment_unavailability','transaction_requests')")->fetchColumn();
        $v9Ready = $v9Tables === 3;
        $v10Ready = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='focal_shift' AND table_name='contact_requests'")->fetchColumn();
        $databaseOk = $tableCount >= 12 && $equipmentCount >= 1 && $requiredColumns === 2 && $v7Ready && $v9Ready && $v10Ready;
        $databaseDetail = $tableCount . ' tables · ' . $equipmentCount . ' équipements · ' . ($v10Ready ? 'modules V10 actifs' : 'ouvrez upgrade-v10.php');
    } catch (Throwable $exception) {
        $databaseDetail = 'échec : vérifiez MySQL et l’import SQL';
    }
}
$checks[] = ['Base focal_shift', $databaseOk, $databaseDetail];
$allOk = !in_array(false, array_column($checks, 1), true);
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Diagnostic Focal-Shift</title><style>body{margin:0;background:#f4f1ea;color:#17201f;font:16px/1.5 system-ui}.box{width:min(calc(100% - 32px),760px);margin:60px auto;background:#fffdf8;border:1px solid #ccd1cf;border-radius:22px;padding:34px}h1{font:500 2.7rem Georgia,serif}.status{padding:14px;border-radius:12px;margin:20px 0;background:<?= $allOk ? '#dcebdd' : '#fff0ed' ?>}table{width:100%;border-collapse:collapse}td{padding:13px 8px;border-bottom:1px solid #d5d9d7}td:first-child{font-weight:750}.ok{color:#286547}.ko{color:#a43c31}a{color:inherit;font-weight:750}</style></head><body><main class="box"><p>FOCAL-SHIFT · CONTRÔLE TECHNIQUE</p><h1>Diagnostic WampServer</h1><div class="status"><strong><?= $allOk ? 'Tout est prêt.' : 'Une correction est nécessaire.' ?></strong> <?= $allOk ? 'Le service peut être ouvert.' : 'Consultez README.md pour corriger les éléments rouges.' ?></div><table><tbody><?php foreach($checks as [$label,$ok,$detail]): ?><tr><td><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td><td class="<?= $ok ? 'ok' : 'ko' ?>"><?= $ok ? '✓' : '✕' ?> <?= htmlspecialchars((string)$detail, ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table><p><a href="<?= htmlspecialchars(BASE_URL . '/index.php', ENT_QUOTES, 'UTF-8') ?>">Ouvrir Focal-Shift →</a></p><p><small>Supprimez ou bloquez cette page avant tout déploiement public.</small></p></main></body></html>
