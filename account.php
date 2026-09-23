<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
$pdo = db();
$offers = [];
$statement = $pdo->prepare('SELECT id,brand,model,status,verification_status,circular_score FROM equipment WHERE owner_id=:id ORDER BY created_at DESC');
$statement->execute(['id' => $user['id']]);
$offers = $statement->fetchAll();
$statement = $pdo->prepare('SELECT COUNT(*) FROM conversations WHERE buyer_id=:buyer OR seller_id=:seller');
$statement->execute(['buyer' => $user['id'], 'seller' => $user['id']]);
$conversationCount = (int) $statement->fetchColumn();
$myRequests = [];
$incomingRequests = [];
try {
    $statement = $pdo->prepare("SELECT tr.*,e.brand,e.model FROM transaction_requests tr JOIN equipment e ON e.id=tr.equipment_id WHERE tr.buyer_id=:id ORDER BY tr.created_at DESC LIMIT 5");
    $statement->execute(['id'=>$user['id']]);
    $myRequests=$statement->fetchAll();
    $statement = $pdo->prepare("SELECT tr.*,e.brand,e.model,u.full_name buyer_name FROM transaction_requests tr JOIN equipment e ON e.id=tr.equipment_id JOIN users u ON u.id=tr.buyer_id WHERE e.owner_id=:id ORDER BY tr.created_at DESC LIMIT 5");
    $statement->execute(['id'=>$user['id']]);
    $incomingRequests=$statement->fetchAll();
} catch (Throwable $ignored) {}
$pageTitle = 'Mon espace';
$activePage = 'account';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-hero page-hero-compact"><div class="container"><p class="eyebrow">Espace membre</p><h1>Bonjour, <?= e(explode(' ', $user['full_name'])[0]) ?>.</h1><p><?= e($user['email']) ?> · <?= $user['identity_verified'] ? 'Identité vérifiée' : 'Vérification à finaliser' ?></p></div></section>
    <section class="section"><div class="container account-grid">
        <article class="account-card"><span class="account-number"><?= count($offers) ?></span><h2>Mes offres</h2><p>Équipements publiés en vente ou location.</p><?php if ($offers): ?><ul class="account-list"><?php foreach ($offers as $offer): ?><li><a href="<?=url('product.php?id='.(int)$offer['id'])?>"><?= e($offer['brand'] . ' ' . $offer['model']) ?></a><span class="offer-list-meta"><b class="offer-status offer-status-<?=e($offer['status'])?>"><?= $offer['status']==='reserved'?'Réservé / loué':'Disponible' ?></b><b class="offer-verification <?=($offer['verification_status']??'')==='approved'?'verified':'unverified'?>"><?=($offer['verification_status']??'')==='approved'?'Produit vérifié':((in_array($offer['verification_status']??'',['requested','received'],true))?'Vérification demandée':'Produit non vérifié')?></b></span></li><?php endforeach; ?></ul><?php endif; ?><a class="button button-secondary" href="<?= url('deposit.php') ?>">Publier une offre</a></article>
        <article class="account-card"><span class="account-number"><?= $conversationCount ?></span><h2>Messagerie</h2><p>Conversations liées à une offre.</p><a class="button button-secondary" href="<?= url('messages.php') ?>">Ouvrir mes échanges</a></article>
        <article class="account-card"><span class="account-number"><?= $user['identity_verified'] ? '✓' : '—' ?></span><h2>Confiance</h2><p>Identité, réputation et historique renforcent la réassurance.</p><form method="post" action="<?= url('logout.php') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="text-button" type="submit">Se déconnecter</button></form></article>
        <article class="account-card"><span class="account-number"><?=count($myRequests)?></span><h2>Mes demandes</h2><p>Achats et locations transmis aux propriétaires.</p><?php if($myRequests):?><ul class="account-list"><?php foreach($myRequests as $request): $rentalEnded=$request['transaction_type']==='rental'&&!empty($request['rental_end'])&&$request['rental_end']<=date('Y-m-d'); ?><li><a href="<?=url('product.php?id='.(int)$request['equipment_id'])?>"><?=e($request['brand'].' '.$request['model'])?></a><span><?=e($request['transaction_type']==='purchase'?'Achat':'Location')?> · <?=money((float)$request['total_amount'])?><?php if($request['transaction_type']==='rental'&&!empty($request['rental_start'])): ?> · du <?=date('d/m/Y',strtotime($request['rental_start']))?> au <?=date('d/m/Y',strtotime($request['rental_end']))?><?php endif; ?></span><?php if($request['transaction_type']==='rental'):?><a class="return-link <?= $rentalEnded?'return-link-ready':'' ?>" href="<?=url('return-inspection.php?transaction_id='.(int)$request['id'])?>"><?= $rentalEnded?'Rendre le produit · ajouter les photos':'Préparer l’état de retour' ?></a><?php endif;?></li><?php endforeach;?></ul><?php else:?><p class="microcopy">Aucune demande pour le moment.</p><?php endif;?></article>
        <article class="account-card"><span class="account-number"><?=count($incomingRequests)?></span><h2>Demandes reçues</h2><p>Intérêt des acheteurs et locataires pour vos offres.</p><?php if($incomingRequests):?><ul class="account-list"><?php foreach($incomingRequests as $request):?><li><a href="<?=url('product.php?id='.(int)$request['equipment_id'])?>"><?=e($request['brand'].' '.$request['model'])?></a><span><?=e($request['buyer_name'])?> · <?=money((float)$request['total_amount'])?></span></li><?php endforeach;?></ul><?php else:?><p class="microcopy">Aucune demande reçue.</p><?php endif;?></article>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
