<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = db();
$messages = [];
$errors = [];

function column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=:schema AND table_name=:table AND column_name=:column');
    $stmt->execute(['schema'=>DB_NAME,'table'=>$table,'column'=>$column]);
    return (int) $stmt->fetchColumn() > 0;
}

function add_column(PDO $pdo, string $table, string $column, string $definition, array &$messages): void
{
    if (!column_exists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        $messages[] = "Colonne $table.$column ajoutée";
    } else {
        $messages[] = "Colonne $table.$column déjà présente";
    }
}

$alreadyReady = column_exists($pdo, 'equipment', 'city')
    && column_exists($pdo, 'simulator_requests', 'circular_score')
    && (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='" . DB_NAME . "' AND table_name IN ('conversations','messages')")->fetchColumn() === 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Session expirée. Rechargez cette page.';
    } else {
        try {
            add_column($pdo, 'equipment', 'city', "VARCHAR(100) NOT NULL DEFAULT 'Paris' AFTER distance_km", $messages);
            add_column($pdo, 'equipment', 'latitude', 'DECIMAL(9,6) NULL AFTER city', $messages);
            add_column($pdo, 'equipment', 'longitude', 'DECIMAL(9,6) NULL AFTER latitude', $messages);
            add_column($pdo, 'equipment', 'service_discount', 'TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER longitude', $messages);
            add_column($pdo, 'simulator_requests', 'circular_score', 'TINYINT UNSIGNED NULL AFTER match_type', $messages);
            add_column($pdo, 'simulator_requests', 'discount_percent', 'TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER circular_score', $messages);

            $pdo->exec("CREATE TABLE IF NOT EXISTS conversations (
                id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                equipment_id INT UNSIGNED NOT NULL,
                buyer_id INT UNSIGNED NOT NULL,
                seller_id INT UNSIGNED NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_conversation_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
                CONSTRAINT fk_conversation_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_conversation_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY uq_conversation_offer (equipment_id,buyer_id,seller_id),
                INDEX idx_conversation_users (buyer_id,seller_id,updated_at)
            ) ENGINE=InnoDB");
            $messages[] = 'Table conversations prête';
            $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
                id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                conversation_id BIGINT UNSIGNED NOT NULL,
                sender_id INT UNSIGNED NOT NULL,
                body VARCHAR(1000) NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_message_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
                CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_message_thread (conversation_id,created_at)
            ) ENGINE=InnoDB");
            $messages[] = 'Table messages prête';

            $configuredDemoPassword = getenv('FOCAL_DEMO_PASSWORD');
            $demoPassword = password_hash($configuredDemoPassword !== false && $configuredDemoPassword !== '' ? $configuredDemoPassword : bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,identity_verified,rating_avg,rating_count,transaction_count)
                VALUES ('Alex M.','acheteur.demo@focal-shift.local',:password,'renter',1,4.90,8,6)
                ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),role='renter'");
            $stmt->execute(['password'=>$demoPassword]);
            $stmt = $pdo->prepare("UPDATE users SET password_hash=:password WHERE email='claire.demo@focal-shift.local' AND (password_hash IS NULL OR password_hash='')");
            $stmt->execute(['password'=>$demoPassword]);
            $messages[] = 'Comptes de référence prêts';

            $pdo->exec("UPDATE equipment SET
                city = CASE MOD(id,6) WHEN 0 THEN 'Versailles' WHEN 1 THEN 'Paris' WHEN 2 THEN 'Boulogne-Billancourt' WHEN 3 THEN 'Saint-Denis' WHEN 4 THEN 'Montreuil' ELSE 'Nanterre' END,
                latitude = CASE MOD(id,6) WHEN 0 THEN 48.801400 WHEN 1 THEN 48.856600 WHEN 2 THEN 48.839700 WHEN 3 THEN 48.936200 WHEN 4 THEN 48.863800 ELSE 48.892400 END,
                longitude = CASE MOD(id,6) WHEN 0 THEN 2.130100 WHEN 1 THEN 2.352200 WHEN 2 THEN 2.239900 WHEN 3 THEN 2.357400 WHEN 4 THEN 2.448500 ELSE 2.215300 END,
                service_discount = CASE WHEN circular_score>=70 THEN 5 ELSE 0 END
                WHERE latitude IS NULL OR longitude IS NULL");
            $messages[] = 'Localisation approximative et avantages calculés';
            $alreadyReady = true;
        } catch (Throwable $exception) {
            $errors[] = 'Mise à niveau interrompue : ' . $exception->getMessage();
        }
    }
}

$tableCount = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='" . DB_NAME . "'")->fetchColumn();
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mise à niveau Focal-Shift V2</title><style>body{margin:0;background:#111817;color:#17201f;font:16px/1.5 system-ui}.box{width:min(calc(100% - 32px),820px);margin:50px auto;background:#fffdf8;border-radius:24px;padding:38px}h1{font:500 3rem Georgia,serif}.status{padding:16px;border-radius:12px;background:<?= $alreadyReady?'#dcebdd':'#f2e5d6' ?>}.button{display:inline-flex;border:0;border-radius:999px;background:#17201f;color:white;padding:14px 22px;font-weight:800;cursor:pointer}.log{margin:24px 0;padding:18px;background:#f4f1ea;border-radius:12px}.error{background:#fff0ed;border-left:5px solid #a43c31;padding:15px}a{color:inherit;font-weight:800}</style></head><body><main class="box"><p>FOCAL-SHIFT · INSTALLATEUR LOCAL</p><h1>Mise à niveau V2</h1><div class="status"><strong><?= $alreadyReady?'La V2 est prête.':'La base V1 a été détectée.' ?></strong><br><?= $tableCount ?> tables · <?= $userCount ?> membres. La mise à niveau conserve les équipements et comptes existants.</div><?php if($errors):?><div class="error"><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif;?><?php if($messages):?><div class="log"><strong>Journal</strong><ul><?php foreach($messages as $message):?><li><?=e($message)?></li><?php endforeach;?></ul></div><?php endif;?><?php if(!$alreadyReady):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><button class="button" type="submit">Installer la V2 sans effacer mes données</button></form><?php else:?><p><a href="<?=url('diagnostic.php')?>">Contrôler le diagnostic →</a></p><p><a href="<?=url('index.php')?>">Ouvrir Focal-Shift V2 →</a></p><?php endif;?><p><small>Cette page doit être supprimée ou bloquée avant une mise en ligne publique.</small></p></main></body></html>
