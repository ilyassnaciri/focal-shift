<?php
declare(strict_types=1);
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/functions.php';
$pdo=db(); $updated=false; $error='';
$tables=['newsletter_subscribers','equipment_unavailability','transaction_requests'];
$ready=true;
foreach($tables as $table){
    $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=:table');
    $q->execute(['table'=>$table]);
    if(!(bool)$q->fetchColumn()){$ready=false;}
}
if($_SERVER['REQUEST_METHOD']==='POST'&&csrf_is_valid($_POST['csrf_token']??null)&&!$ready){
 try{
  $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,email VARCHAR(160) NOT NULL UNIQUE,profile_type ENUM('photographe','videaste','studio_agence','proprietaire','passionne','autre') NOT NULL,consented_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',source VARCHAR(60) NOT NULL DEFAULT 'homepage') ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS equipment_unavailability (id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,equipment_id INT UNSIGNED NOT NULL,owner_id INT UNSIGNED NOT NULL,start_date DATE NOT NULL,end_date DATE NOT NULL,reason VARCHAR(160) NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,CONSTRAINT fk_v9_unavailability_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,CONSTRAINT fk_v9_unavailability_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,INDEX idx_v9_unavailability_dates (equipment_id,start_date,end_date)) ENGINE=InnoDB");
  $pdo->exec("CREATE TABLE IF NOT EXISTS transaction_requests (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,equipment_id INT UNSIGNED NOT NULL,buyer_id INT UNSIGNED NOT NULL,transaction_type ENUM('purchase','rental') NOT NULL,rental_start DATE NULL,rental_end DATE NULL,payment_method ENUM('card','bank_transfer','split_payment') NOT NULL,item_amount DECIMAL(10,2) NOT NULL,shipping_amount DECIMAL(8,2) NOT NULL DEFAULT 0,insurance_amount DECIMAL(8,2) NOT NULL DEFAULT 0,total_amount DECIMAL(10,2) NOT NULL,status ENUM('pending','accepted','declined','cancelled') NOT NULL DEFAULT 'pending',created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,CONSTRAINT fk_v9_transaction_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id),CONSTRAINT fk_v9_transaction_buyer FOREIGN KEY (buyer_id) REFERENCES users(id),INDEX idx_v9_transaction_equipment (equipment_id,status),INDEX idx_v9_transaction_buyer (buyer_id,created_at)) ENGINE=InnoDB");
  $updated=true;$ready=true;
 }catch(Throwable $exception){$error='La migration a échoué : '.$exception->getMessage();}
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mise à niveau V9</title><style>body{margin:0;background:#111817;color:#17201f;font:16px/1.5 system-ui}.box{width:min(calc(100% - 32px),780px);margin:55px auto;background:#fffdf8;border-radius:24px;padding:38px}h1{font:500 3rem Georgia,serif}.status{padding:16px;border-radius:12px;background:<?=$ready?'#dcebdd':'#f2e5d6'?>}.button{display:inline-flex;border:0;border-radius:999px;background:#17201f;color:white;padding:14px 22px;font-weight:800;cursor:pointer}.error{background:#fff0ed;border-left:5px solid #a43c31;padding:15px}a{color:inherit;font-weight:800}</style></head><body><main class="box"><p>FOCAL-SHIFT · VERSION 9</p><h1>Transactions et disponibilité</h1><div class="status"><strong><?=$ready?'La V9 est prête.':'Trois tables doivent être ajoutées.'?></strong><br>Newsletter, indisponibilités vendeur et demandes d’achat/location.</div><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><?php if(!$ready):?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><button class="button" type="submit">Installer la V9 sans effacer mes données</button></form><?php else:?><p><a href="<?=url('index.php')?>">Ouvrir la V9 →</a></p><?php endif;?><p><small>Cette migration conserve les comptes, annonces, photos, messages et simulations.</small></p></main></body></html>
