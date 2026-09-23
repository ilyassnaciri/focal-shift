<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pdo = db(); $messages=[]; $errors=[]; $updated=false;

function v3_column_exists(PDO $pdo,string $table,string $column):bool{
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=:schema AND table_name=:table AND column_name=:column');
    $stmt->execute(['schema'=>DB_NAME,'table'=>$table,'column'=>$column]); return (int)$stmt->fetchColumn()>0;
}
$v2Ready=v3_column_exists($pdo,'equipment','service_discount')&&v3_column_exists($pdo,'equipment','city')&&v3_column_exists($pdo,'simulator_requests','discount_percent');
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!csrf_is_valid($_POST['csrf_token']??null)){$errors[]='Session expirée. Rechargez la page.';}
    elseif(!$v2Ready){$errors[]='La base V2 doit être installée avant la V3. Lancez d’abord upgrade-v2.php.';}
    else{try{
        $pdo->beginTransaction();
        $pdo->exec('UPDATE equipment SET service_discount=CASE WHEN circular_score>=70 THEN 5 ELSE 0 END');
        $pdo->exec('UPDATE simulator_requests SET discount_percent=CASE WHEN circular_score>=70 THEN 5 ELSE 0 END WHERE circular_score IS NOT NULL');
        $pdo->commit(); $updated=true; $messages[]='Les avantages locataire ont été alignés sur la règle V3 : 5 % uniquement pour un impact faible.';
        $messages[]='Les comptes, équipements, photos et conversations existants ont été conservés.';
    }catch(Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();$errors[]='Mise à niveau interrompue : '.$exception->getMessage();}}
}
$tableCount=(int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".DB_NAME."'")->fetchColumn();
$offerCount=(int)$pdo->query('SELECT COUNT(*) FROM equipment')->fetchColumn();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mise à niveau Focal-Shift V3</title><style>body{margin:0;background:#111817;color:#17201f;font:16px/1.5 system-ui}.box{width:min(calc(100% - 32px),820px);margin:50px auto;background:#fffdf8;border-radius:24px;padding:38px}h1{font:500 3rem Georgia,serif}.status{padding:16px;border-radius:12px;background:<?=($updated?'#dcebdd':'#f2e5d6')?>}.button{display:inline-flex;border:0;border-radius:999px;background:#17201f;color:white;padding:14px 22px;font-weight:800;cursor:pointer}.log{margin:24px 0;padding:18px;background:#f4f1ea;border-radius:12px}.error{background:#fff0ed;border-left:5px solid #a43c31;padding:15px}a{color:inherit;font-weight:800}</style></head><body><main class="box"><p>FOCAL-SHIFT · INSTALLATEUR LOCAL</p><h1>Mise à niveau V3</h1><div class="status"><strong><?= $updated?'La V3 est prête.':($v2Ready?'La base V2 a été détectée.':'Préparation V2 requise.') ?></strong><br><?= $tableCount ?> tables · <?= $offerCount ?> équipements. Aucune donnée existante ne sera supprimée.</div><?php if($errors):?><div class="error"><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif;?><?php if($messages):?><div class="log"><strong>Journal</strong><ul><?php foreach($messages as $message):?><li><?=e($message)?></li><?php endforeach;?></ul></div><?php endif;?><?php if(!$updated&&$v2Ready):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><button class="button" type="submit">Installer la V3 sans effacer mes données</button></form><?php elseif($updated):?><p><a href="<?=url('diagnostic.php')?>">Contrôler le diagnostic →</a></p><p><a href="<?=url('index.php')?>">Ouvrir Focal-Shift V3 →</a></p><?php endif;?><p><small>Retirez ou bloquez cette page avant une mise en ligne publique.</small></p></main></body></html>
