<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$user = require_login();
$pdo = db();
$errors = [];
$selectedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$equipmentId = filter_input(INPUT_GET, 'equipment_id', FILTER_VALIDATE_INT) ?: null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) $errors[] = 'Session expirée.';
    $body = trim((string) ($_POST['body'] ?? ''));
    if ($body === '' || mb_strlen($body) > 1000) $errors[] = 'Le message doit contenir entre 1 et 1 000 caractères.';
    $action = $_POST['action'] ?? '';
    if (!$errors && $action === 'start') {
        $equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare("SELECT e.id,e.owner_id,e.brand,e.model,u.full_name FROM equipment e JOIN users u ON u.id=e.owner_id WHERE e.id=:id AND e.status='published'");
        $stmt->execute(['id' => $equipmentId]);
        $equipment = $stmt->fetch();
        if (!$equipment) $errors[] = 'Offre introuvable.';
        elseif ((int) $equipment['owner_id'] === (int) $user['id']) $errors[] = 'Vous ne pouvez pas vous contacter vous-même.';
        else {
            $pdo->beginTransaction();
            $find = $pdo->prepare('SELECT id FROM conversations WHERE equipment_id=:equipment AND buyer_id=:buyer AND seller_id=:seller LIMIT 1');
            $find->execute(['equipment'=>$equipmentId,'buyer'=>$user['id'],'seller'=>$equipment['owner_id']]);
            $selectedId = (int) ($find->fetchColumn() ?: 0);
            if (!$selectedId) {
                $create = $pdo->prepare('INSERT INTO conversations (equipment_id,buyer_id,seller_id) VALUES (:equipment,:buyer,:seller)');
                $create->execute(['equipment'=>$equipmentId,'buyer'=>$user['id'],'seller'=>$equipment['owner_id']]);
                $selectedId = (int) $pdo->lastInsertId();
            }
            $send = $pdo->prepare('INSERT INTO messages (conversation_id,sender_id,body) VALUES (:conversation,:sender,:body)');
            $send->execute(['conversation'=>$selectedId,'sender'=>$user['id'],'body'=>$body]);
            $pdo->prepare('UPDATE conversations SET updated_at=CURRENT_TIMESTAMP WHERE id=:id')->execute(['id'=>$selectedId]);
            $pdo->commit();
            header('Location: ' . url('messages.php?id=' . $selectedId)); exit;
        }
    } elseif (!$errors && $action === 'send') {
        $selectedId = filter_input(INPUT_POST, 'conversation_id', FILTER_VALIDATE_INT);
        $check = $pdo->prepare('SELECT id FROM conversations WHERE id=:id AND (buyer_id=:user OR seller_id=:user2)');
        $check->execute(['id'=>$selectedId,'user'=>$user['id'],'user2'=>$user['id']]);
        if (!$check->fetchColumn()) $errors[] = 'Conversation non autorisée.';
        else {
            $send=$pdo->prepare('INSERT INTO messages (conversation_id,sender_id,body) VALUES (:conversation,:sender,:body)');
            $send->execute(['conversation'=>$selectedId,'sender'=>$user['id'],'body'=>$body]);
            $pdo->prepare('UPDATE conversations SET updated_at=CURRENT_TIMESTAMP WHERE id=:id')->execute(['id'=>$selectedId]);
            header('Location: '.url('messages.php?id='.$selectedId)); exit;
        }
    }
}

$newEquipment = null;
if ($equipmentId) {
    $stmt=$pdo->prepare("SELECT e.id,e.owner_id,e.brand,e.model,c.slug AS category_slug,(SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id=e.id ORDER BY ep.id LIMIT 1) AS image_url,u.full_name FROM equipment e JOIN users u ON u.id=e.owner_id JOIN categories c ON c.id=e.category_id WHERE e.id=:id AND e.status='published'");
    $stmt->execute(['id'=>$equipmentId]); $newEquipment=$stmt->fetch();
}

$stmt=$pdo->prepare("SELECT cv.*,e.brand,e.model,c.slug AS category_slug,(SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id=e.id ORDER BY ep.id LIMIT 1) AS image_url,CASE WHEN cv.buyer_id=:user THEN seller.full_name ELSE buyer.full_name END AS other_name FROM conversations cv JOIN equipment e ON e.id=cv.equipment_id JOIN categories c ON c.id=e.category_id JOIN users buyer ON buyer.id=cv.buyer_id JOIN users seller ON seller.id=cv.seller_id WHERE cv.buyer_id=:buyer OR cv.seller_id=:seller ORDER BY cv.updated_at DESC");
$stmt->execute(['user'=>$user['id'],'buyer'=>$user['id'],'seller'=>$user['id']]); $conversations=$stmt->fetchAll();
$selectedConversation=null;$messages=[];
if($selectedId){$stmt=$pdo->prepare("SELECT cv.*,e.brand,e.model,c.slug AS category_slug,(SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id=e.id ORDER BY ep.id LIMIT 1) AS image_url,CASE WHEN cv.buyer_id=:user THEN seller.full_name ELSE buyer.full_name END AS other_name FROM conversations cv JOIN equipment e ON e.id=cv.equipment_id JOIN categories c ON c.id=e.category_id JOIN users buyer ON buyer.id=cv.buyer_id JOIN users seller ON seller.id=cv.seller_id WHERE cv.id=:id AND (cv.buyer_id=:buyer OR cv.seller_id=:seller)");$stmt->execute(['user'=>$user['id'],'id'=>$selectedId,'buyer'=>$user['id'],'seller'=>$user['id']]);$selectedConversation=$stmt->fetch();if($selectedConversation){$stmt=$pdo->prepare('SELECT m.*,u.full_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.conversation_id=:id ORDER BY m.created_at');$stmt->execute(['id'=>$selectedId]);$messages=$stmt->fetchAll();}}

$pageTitle='Messagerie';$activePage='account';require __DIR__.'/includes/header.php';
?>
<main id="main-content"><section class="page-hero page-hero-compact"><div class="container"><p class="eyebrow">Échanges sécurisés</p><h1>Messagerie Focal-Shift.</h1><p>Chaque conversation reste rattachée à une offre pour conserver le contexte.</p></div></section>
<section class="section message-section"><div class="container message-layout">
<aside class="conversation-list"><h2>Conversations</h2><?php if(!$conversations):?><p class="empty-small">Aucun échange pour le moment.</p><?php endif;?><?php foreach($conversations as $conversation):?><a class="conversation-item <?= (int)$selectedId===(int)$conversation['id']?'is-active':''?>" href="<?=url('messages.php?id='.(int)$conversation['id'])?>"><img src="<?=e(equipment_image($conversation['image_url'],$conversation['category_slug']))?>" alt=""><span><strong><?=e($conversation['other_name'])?></strong><small><?=e($conversation['brand'].' '.$conversation['model'])?></small></span></a><?php endforeach;?></aside>
<div class="conversation-panel">
<?php if($errors):?><div class="error-summary" role="alert"><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif;?>
<?php if($newEquipment):?><div class="message-product"><img src="<?=e(equipment_image($newEquipment['image_url'],$newEquipment['category_slug']))?>" alt=""><div><p class="eyebrow">Nouvel échange</p><h2><?=e($newEquipment['brand'].' '.$newEquipment['model'])?></h2><p>Avec <?=e($newEquipment['full_name'])?></p></div></div><form class="message-compose" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="start"><input type="hidden" name="equipment_id" value="<?=(int)$newEquipment['id']?>"><label for="start-body">Votre message</label><textarea id="start-body" name="body" rows="5" maxlength="1000" required placeholder="Bonjour, votre équipement est-il disponible…"></textarea><button class="button" type="submit">Envoyer au propriétaire</button></form>
<?php elseif($selectedConversation):?><div class="message-product compact"><img src="<?=e(equipment_image($selectedConversation['image_url'],$selectedConversation['category_slug']))?>" alt=""><div><p class="eyebrow">Conversation avec <?=e($selectedConversation['other_name'])?></p><h2><?=e($selectedConversation['brand'].' '.$selectedConversation['model'])?></h2></div></div><div class="message-thread" aria-live="polite"><?php foreach($messages as $message):?><article class="message-bubble <?= (int)$message['sender_id']===(int)$user['id']?'is-mine':''?>"><strong><?=e($message['full_name'])?></strong><p><?=nl2br(e($message['body']))?></p><time><?=e(date('d/m H:i',strtotime($message['created_at'])))?></time></article><?php endforeach;?></div><form class="message-compose inline" method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="send"><input type="hidden" name="conversation_id" value="<?=(int)$selectedConversation['id']?>"><label class="sr-only" for="reply-body">Répondre</label><textarea id="reply-body" name="body" rows="2" maxlength="1000" required placeholder="Écrire un message…"></textarea><button class="button" type="submit">Envoyer</button></form>
<?php else:?><div class="message-empty"><span aria-hidden="true">✦</span><h2>Sélectionnez une conversation.</h2><p>Ou contactez un propriétaire depuis une fiche équipement.</p><a class="button button-secondary" href="<?=url('catalogue-location.php')?>">Explorer les locations</a></div><?php endif;?>
</div></div></section></main>
<?php require __DIR__.'/includes/footer.php';?>
