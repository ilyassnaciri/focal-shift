<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/plain; charset=utf-8');
$pdo = db();
$demoPassword = getenv('FOCAL_DEMO_PASSWORD');
$password = password_hash($demoPassword !== false && $demoPassword !== '' ? $demoPassword : bin2hex(random_bytes(24)), PASSWORD_DEFAULT);

try {
    $pdo->beginTransaction();
    $user = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,identity_verified,rating_avg,rating_count,transaction_count)
        VALUES ('Ilyass N.','ilyass.demo@focal-shift.local',:password,'renter',1,4.92,7,5)
        ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),role='renter',identity_verified=1");
    $user->execute(['password' => $password]);
    $pdo->prepare("UPDATE users SET password_hash=:password WHERE email='claire.demo@focal-shift.local'")->execute(['password' => $password]);

    $claireId = (int)$pdo->query("SELECT id FROM users WHERE email='claire.demo@focal-shift.local' LIMIT 1")->fetchColumn();
    $ilyassId = (int)$pdo->query("SELECT id FROM users WHERE email='ilyass.demo@focal-shift.local' LIMIT 1")->fetchColumn();
    $equipment = $pdo->prepare("SELECT id FROM equipment WHERE brand='Blackmagic' AND model='Pocket Cinema 6K Pro' LIMIT 1");
    $equipment->execute();
    $equipmentId = (int)$equipment->fetchColumn();

    if (!$claireId || !$ilyassId || !$equipmentId) throw new RuntimeException('Les données de base Claire/Blackmagic sont introuvables.');

    $update = $pdo->prepare("UPDATE equipment SET owner_id=:owner,description=:description,sale_price=NULL,rental_price_day=78,available_for_sale=0,available_for_rental=1,status='reserved',circular_score=96 WHERE id=:id");
    $update->execute(['owner'=>$claireId,'id'=>$equipmentId,'description'=>'Kit tournage complet proposé par Claire avec cage, SSD, alimentation secteur et contrôle des connectiques.']);

    $exists = $pdo->prepare("SELECT COUNT(*) FROM transaction_requests WHERE equipment_id=:equipment AND buyer_id=:buyer AND transaction_type='rental'");
    $exists->execute(['equipment'=>$equipmentId,'buyer'=>$ilyassId]);
    if ((int)$exists->fetchColumn() === 0) {
        $request = $pdo->prepare("INSERT INTO transaction_requests (equipment_id,buyer_id,transaction_type,rental_start,rental_end,payment_method,item_amount,shipping_amount,insurance_amount,total_amount,status,created_at) VALUES (:equipment,:buyer,'rental','2026-09-10','2026-09-12','card',222.30,19.90,17.78,259.98,'accepted','2026-09-08 14:30:00')");
        $request->execute(['equipment'=>$equipmentId,'buyer'=>$ilyassId]);
    }
    $pdo->commit();
    echo "V14 installée.\n- Claire possède la Blackmagic Pocket Cinema 6K Pro actuellement louée.\n- Ilyass dispose d'une location terminée avec action de restitution.\n- Utilisez les boutons de connexion rapide en développement.\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo 'Échec : ' . $exception->getMessage();
}
