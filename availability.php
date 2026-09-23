<?php
declare(strict_types=1);
require_once __DIR__.'/includes/db.php';require_once __DIR__.'/includes/functions.php';
$user=require_login();if($_SERVER['REQUEST_METHOD']!=='POST'||!csrf_is_valid($_POST['csrf_token']??null)){http_response_code(400);exit('Requête invalide.');}
$id=filter_input(INPUT_POST,'equipment_id',FILTER_VALIDATE_INT);$start=(string)($_POST['start_date']??'');$end=(string)($_POST['end_date']??'');$reason=trim((string)($_POST['reason']??''));
if(!$id||!$start||!$end||$end<$start){flash('error','Choisissez une période cohérente.');header('Location: '.url('product.php?id='.(int)$id));exit;}
$pdo=db();$q=$pdo->prepare('SELECT owner_id FROM equipment WHERE id=:id');$q->execute(['id'=>$id]);if((int)$q->fetchColumn()!==(int)$user['id']){http_response_code(403);exit('Action interdite.');}
$q=$pdo->prepare('INSERT INTO equipment_unavailability(equipment_id,owner_id,start_date,end_date,reason) VALUES(:equipment,:owner,:start,:end,:reason)');$q->execute(['equipment'=>$id,'owner'=>$user['id'],'start'=>$start,'end'=>$end,'reason'=>$reason?:null]);
flash('success','La période indisponible a été ajoutée au calendrier.');header('Location: '.url('product.php?id='.$id));
