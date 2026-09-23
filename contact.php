<?php
declare(strict_types=1);
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/functions.php';
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim((string)($_POST['full_name']??''));
    $email=filter_var(trim((string)($_POST['email']??'')),FILTER_VALIDATE_EMAIL);
    $profile=(string)($_POST['profile_type']??'');
    $subject=trim((string)($_POST['subject']??''));
    $message=trim((string)($_POST['message']??''));
    if(!csrf_is_valid($_POST['csrf_token']??null))$errors[]='La session a expiré. Rechargez la page.';
    if(trim((string)($_POST['website']??''))!=='')$errors[]='Votre demande ne peut pas être envoyée.';
    if(mb_strlen($name)<2||mb_strlen($name)>120)$errors[]='Indiquez votre nom.';
    if(!$email)$errors[]='Indiquez une adresse email valide.';
    if(!in_array($profile,['acheteur','vendeur','partenaire','presse','autre'],true))$errors[]='Choisissez votre profil.';
    if(mb_strlen($subject)<3||mb_strlen($subject)>160)$errors[]='Précisez l’objet de votre demande.';
    if(mb_strlen($message)<20||mb_strlen($message)>3000)$errors[]='Votre message doit contenir entre 20 et 3 000 caractères.';
    if(($_POST['privacy_consent']??'')!=='1')$errors[]='Confirmez que vous avez lu les informations sur vos données.';
    if(!$errors){
        $q=db()->prepare('INSERT INTO contact_requests(full_name,email,profile_type,subject,message,consent_at) VALUES(:name,:email,:profile,:subject,:message,CURRENT_TIMESTAMP)');
        $q->execute(['name'=>$name,'email'=>$email,'profile'=>$profile,'subject'=>$subject,'message'=>$message]);
        flash('success','Votre message a bien été transmis. Nous vous répondrons par email.');
        header('Location: '.url('contact.php'));exit;
    }
}
$pageTitle='Contact';$pageDescription='Contactez l’équipe Focal-Shift pour une question sur l’achat, la location, la vente ou un partenariat.';$activePage='contact';require __DIR__.'/includes/header.php';
?>
<main id="main-content"><section class="page-hero page-hero-compact"><div class="container"><p class="eyebrow">Nous contacter</p><h1>Parlons de votre besoin.</h1><p>Une question sur une offre, une location, la vente de votre matériel ou un partenariat ? Écrivez-nous depuis ce formulaire.</p></div></section>
<section class="section contact-section"><div class="container contact-layout"><div class="contact-intro"><p class="eyebrow">Équipe Focal-Shift</p><h2>Le bon interlocuteur, sans détour.</h2><p>Votre demande est enregistrée uniquement pour permettre à l’équipe de vous répondre. Elle n’est pas utilisée pour la newsletter sans un consentement séparé.</p><ul class="contact-points"><li><strong>Acheteurs et locataires</strong><span>Disponibilité, livraison, paiement et assurance.</span></li><li><strong>Vendeurs</strong><span>Publication, estimation et gestion des périodes.</span></li><li><strong>Partenaires</strong><span>Assurance, fabricants, studios et médias.</span></li></ul><p><a class="arrow-link" href="<?=url('glossary.php')?>">Consulter le glossaire <span aria-hidden="true">→</span></a></p></div>
<form class="form-card contact-form" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label class="contact-honeypot" aria-hidden="true">Site web<input type="text" name="website" tabindex="-1" autocomplete="off"></label><?php if($errors):?><div class="error-summary" tabindex="-1"><h2>Vérifiez votre demande</h2><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif;?><div class="form-grid"><label class="field"><span>Nom complet</span><input type="text" name="full_name" maxlength="120" value="<?=e($_POST['full_name']??'')?>" required autocomplete="name"></label><label class="field"><span>Email</span><input type="email" name="email" maxlength="160" value="<?=e($_POST['email']??'')?>" required autocomplete="email"></label></div><label class="field"><span>Vous êtes</span><select name="profile_type" required><option value="">Choisir un profil</option><?php foreach(['acheteur'=>'Acheteur ou locataire','vendeur'=>'Vendeur ou propriétaire','partenaire'=>'Partenaire','presse'=>'Presse ou média','autre'=>'Autre'] as $key=>$label):?><option value="<?=$key?>" <?=($_POST['profile_type']??'')===$key?'selected':''?>><?=e($label)?></option><?php endforeach;?></select></label><label class="field"><span>Objet</span><input type="text" name="subject" maxlength="160" value="<?=e($_POST['subject']??'')?>" required></label><label class="field"><span>Votre message</span><textarea name="message" rows="7" maxlength="3000" required><?=e($_POST['message']??'')?></textarea><small>20 à 3 000 caractères.</small></label><label class="contact-consent"><input type="checkbox" name="privacy_consent" value="1" required><span>J’ai lu la <a href="<?=url('privacy.php')?>">politique de confidentialité</a> et j’accepte que mes données soient utilisées pour répondre à ma demande.</span></label><button class="button button-block" type="submit">Envoyer mon message</button></form></div></section></main>
<?php require __DIR__.'/includes/footer.php'; ?>
