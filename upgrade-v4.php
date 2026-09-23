<?php
declare(strict_types=1);
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/functions.php';
$pdo=db();$updated=false;$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!csrf_is_valid($_POST['csrf_token']??null)){$error='Session expirée. Rechargez la page.';}
    else{try{$pdo->exec("UPDATE users SET role='both' WHERE role<>'both'");$updated=true;}catch(Throwable $exception){$error='Mise à niveau interrompue : '.$exception->getMessage();}}
}
$userCount=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$unifiedCount=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='both'")->fetchColumn();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mise à niveau Focal-Shift V4</title><style>body{margin:0;background:#111817;color:#17201f;font:16px/1.5 system-ui}.box{width:min(calc(100% - 32px),780px);margin:55px auto;background:#fffdf8;border-radius:24px;padding:38px}h1{font:500 3rem Georgia,serif}.status{padding:16px;border-radius:12px;background:<?=($updated||$userCount===$unifiedCount)?'#dcebdd':'#f2e5d6'?>}.button{display:inline-flex;border:0;border-radius:999px;background:#17201f;color:white;padding:14px 22px;font-weight:800;cursor:pointer}.error{background:#fff0ed;border-left:5px solid #a43c31;padding:15px}a{color:inherit;font-weight:800}</style></head><body><main class="box"><p>FOCAL-SHIFT · INSTALLATEUR LOCAL</p><h1>Compte unique V4</h1><div class="status"><strong><?=($updated||$userCount===$unifiedCount)?'Tous les comptes sont polyvalents.':'La base est prête pour la mise à niveau.'?></strong><br><?=$unifiedCount?> compte(s) polyvalent(s) sur <?=$userCount?>.</div><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><?php if(!$updated&&$userCount!==$unifiedCount):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><button class="button" type="submit">Activer le compte unique pour tous</button></form><?php else:?><p><a href="<?=url('login.php')?>">Ouvrir la connexion →</a></p><?php endif;?><p><small>Retirez ou bloquez cette page avant une mise en ligne publique.</small></p></main></body></html>
