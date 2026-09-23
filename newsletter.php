<?php
declare(strict_types=1);
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST'||!csrf_is_valid($_POST['csrf_token']??null)){http_response_code(400);exit('Requête invalide.');}
$email=filter_var(trim((string)($_POST['email']??'')),FILTER_VALIDATE_EMAIL);
$profiles=['photographe','videaste','studio_agence','proprietaire','passionne','autre'];
$profile=(string)($_POST['profile_type']??'');
if(!$email||!in_array($profile,$profiles,true)||($_POST['newsletter_consent']??'')!=='1'){flash('error','Indiquez un email valide, votre profil et confirmez votre accord.');header('Location: '.url('index.php#newsletter'));exit;}
$q=db()->prepare("INSERT INTO newsletter_subscribers(email,profile_type,status,source) VALUES(:email,:profile,'active','homepage') ON DUPLICATE KEY UPDATE profile_type=VALUES(profile_type),status='active',consented_at=CURRENT_TIMESTAMP");
$q->execute(['email'=>$email,'profile'=>$profile]);
flash('success','Merci. Votre inscription à la newsletter est enregistrée.');
header('Location: '.url('index.php#newsletter'));
