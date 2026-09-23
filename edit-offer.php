<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$user = require_login();
$pdo = db();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Annonce introuvable.'); }

$find = $pdo->prepare("SELECT e.*, c.slug AS category_slug, (SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id=e.id ORDER BY ep.id LIMIT 1) AS image_url FROM equipment e JOIN categories c ON c.id=e.category_id WHERE e.id=:id AND e.owner_id=:owner");
$find->execute(['id'=>$id, 'owner'=>$user['id']]);
$offer = $find->fetch();
if (!$offer) { http_response_code(403); exit('Vous ne pouvez modifier que vos propres annonces.'); }

$categories = $pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$cities = ['Paris','Boulogne-Billancourt','Montreuil','Saint-Denis','Versailles','Nanterre'];
$errors = [];
$values = array_merge($offer, decode_technical_specs($offer['technical_specs'] ?? null, (string)$offer['brand'], (string)$offer['model']));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_merge(['category_id','brand','model','purchase_year','condition_grade','description','sale_price','rental_price_day','reuse_count','distance_km','city'], array_keys(technical_spec_fields())) as $key) $values[$key] = trim((string)($_POST[$key] ?? ''));
    $values['repaired'] = isset($_POST['repaired']);
    $offerMode=(string)($_POST['offer_mode']??'');
    $values['available_for_sale']=$offerMode==='sale';
    $values['available_for_rental']=$offerMode==='rental';
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) $errors[]='La session a expiré. Rechargez la page.';
    $categoryId=filter_var($values['category_id'],FILTER_VALIDATE_INT);
    $year=filter_var($values['purchase_year'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1990,'max_range'=>(int)date('Y')]]);
    $reuse=filter_var($values['reuse_count'],FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>999]]);
    $distance=filter_var($values['distance_km'],FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>5000]]);
    $sale=$values['sale_price']!==''?filter_var($values['sale_price'],FILTER_VALIDATE_FLOAT):null;
    $rental=$values['rental_price_day']!==''?filter_var($values['rental_price_day'],FILTER_VALIDATE_FLOAT):null;
    if(!$categoryId) $errors[]='Choisissez une catégorie.';
    if($values['brand']===''||mb_strlen($values['brand'])>60) $errors[]='Renseignez une marque valide.';
    if($values['model']===''||mb_strlen($values['model'])>80) $errors[]='Renseignez un modèle valide.';
    if(!$year) $errors[]='Renseignez une année valide.';
    if(!in_array($values['condition_grade'],['neuf','tres_bon','bon','use'],true)) $errors[]='Choisissez un état valide.';
    if(!in_array($offerMode,['sale','rental'],true)) $errors[]='Choisissez la vente ou la location.';
    if($values['available_for_sale']&&(!$sale||$sale<=0)) $errors[]='Renseignez un prix de vente supérieur à 0.';
    if($values['available_for_rental']&&(!$rental||$rental<=0)) $errors[]='Renseignez un tarif journalier supérieur à 0.';
    if(mb_strlen($values['description'])<30) $errors[]='La description doit contenir au moins 30 caractères.';
    $technicalSpecs=technical_specs_from_post($values);
    foreach(technical_spec_fields() as $specKey=>$specLabel) if(mb_strlen($technicalSpecs[$specKey])<2) $errors[]='Complétez la caractéristique « '.$specLabel.' ».';
    if($reuse===false||$distance===false) $errors[]='Vérifiez les informations de circularité.';
    if(!in_array($values['city'],$cities,true)) $errors[]='Choisissez une zone valide.';

    $newImage = null;
    $upload=$_FILES['photo']??null;
    if($upload && $upload['error']!==UPLOAD_ERR_NO_FILE){
        if($upload['error']!==UPLOAD_ERR_OK||$upload['size']>MAX_UPLOAD_BYTES) $errors[]='La nouvelle photo est invalide ou dépasse 5 Mo.';
        else { $mime=(new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']); $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp']; if(!isset($ext[$mime])) $errors[]='Format photo accepté : JPG, PNG ou WebP.'; else { $newImage='uploads/'.bin2hex(random_bytes(12)).'.'.$ext[$mime]; if(!move_uploaded_file($upload['tmp_name'],__DIR__.'/'.$newImage)){ $errors[]='Impossible d’enregistrer la nouvelle photo.'; $newImage=null; } } }
    }
    if($errors&&$newImage&&is_file(__DIR__.'/'.$newImage)){unlink(__DIR__.'/'.$newImage);$newImage=null;}

    if(!$errors){
        $score=calculate_circular_score((int)$year,(int)$reuse,(bool)$values['repaired'],(int)$distance); $tier=score_tier($score); $oldImage=$offer['image_url'];
        try{
            $pdo->beginTransaction();
            $update=$pdo->prepare('UPDATE equipment SET category_id=:category,brand=:brand,model=:model,purchase_year=:year,condition_grade=:condition,description=:description,technical_specs=:technical_specs,sale_price=:sale,rental_price_day=:rental,available_for_sale=:for_sale,available_for_rental=:for_rental,circular_score=:score,reuse_count=:reuse,repaired=:repaired,distance_km=:distance,city=:city,service_discount=:discount WHERE id=:id AND owner_id=:owner');
            $update->execute(['category'=>$categoryId,'brand'=>$values['brand'],'model'=>$values['model'],'year'=>$year,'condition'=>$values['condition_grade'],'description'=>$values['description'],'technical_specs'=>json_encode($technicalSpecs,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'sale'=>$values['available_for_sale']?(float)$sale:null,'rental'=>$values['available_for_rental']?(float)$rental:null,'for_sale'=>(int)$values['available_for_sale'],'for_rental'=>(int)$values['available_for_rental'],'score'=>$score,'reuse'=>$reuse,'repaired'=>(int)$values['repaired'],'distance'=>$distance,'city'=>$values['city'],'discount'=>$tier['discount'],'id'=>$id,'owner'=>$user['id']]);
            if($newImage){$pdo->prepare('DELETE FROM equipment_photos WHERE equipment_id=:id')->execute(['id'=>$id]);$pdo->prepare("INSERT INTO equipment_photos(equipment_id,url,photo_type) VALUES(:id,:url,'listing')")->execute(['id'=>$id,'url'=>$newImage]);}
            $pdo->commit();
            if($newImage&&is_string($oldImage)&&str_starts_with($oldImage,'uploads/')&&is_file(__DIR__.'/'.$oldImage)) unlink(__DIR__.'/'.$oldImage);
            flash('success','Les modifications de l’annonce ont été enregistrées.'); header('Location: '.url('product.php?id='.$id)); exit;
        }catch(Throwable $exception){if($pdo->inTransaction())$pdo->rollBack();if($newImage&&is_file(__DIR__.'/'.$newImage))unlink(__DIR__.'/'.$newImage);$errors[]='La modification n’a pas pu être enregistrée.';}
    }
}

$pageTitle='Modifier mon annonce'; $activePage='account'; require __DIR__.'/includes/header.php';
?>
<main id="main-content"><section class="page-hero page-hero-compact"><div class="container narrow"><p class="eyebrow">Gestion propriétaire</p><h1>Modifier l’annonce.</h1><p>Les changements seront visibles immédiatement.</p></div></section><section class="section form-section"><div class="container narrow"><form class="form-card" method="post" enctype="multipart/form-data" data-deposit-form novalidate>
<input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="equipment_id" value="<?=(int)$id?>">
<?php if($errors):?><div class="error-summary" role="alert"><h2>Vérifiez ces informations</h2><ul><?php foreach($errors as $error):?><li><?=e($error)?></li><?php endforeach;?></ul></div><?php endif;?>
<fieldset><legend>Équipement</legend><img class="edit-photo-preview" src="<?=e(equipment_image($offer['image_url'],$offer['category_slug']))?>" alt="Photo actuelle"><div class="field"><label for="photo">Remplacer la photo <small>facultatif · 5 Mo max</small></label><input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"></div><div class="form-grid">
<div class="field"><label for="category_id">Catégorie</label><select id="category_id" name="category_id" required><?php foreach($categories as $category):?><option value="<?=(int)$category['id']?>" <?=(int)$values['category_id']===(int)$category['id']?'selected':''?>><?=e($category['name'])?></option><?php endforeach;?></select></div>
<div class="field"><label for="purchase_year">Année d’achat</label><input id="purchase_year" name="purchase_year" type="number" min="1990" max="<?=date('Y')?>" value="<?=e((string)$values['purchase_year'])?>" required></div>
<div class="field"><label for="brand">Marque</label><input id="brand" name="brand" maxlength="60" value="<?=e((string)$values['brand'])?>" required></div><div class="field"><label for="model">Modèle</label><input id="model" name="model" maxlength="80" value="<?=e((string)$values['model'])?>" required></div>
<div class="field"><label for="condition_grade">État</label><select id="condition_grade" name="condition_grade"><?php foreach(['neuf'=>'Comme neuf','tres_bon'=>'Très bon état','bon'=>'Bon état','use'=>'État d’usage'] as $key=>$label):?><option value="<?=$key?>" <?=$values['condition_grade']===$key?'selected':''?>><?=$label?></option><?php endforeach;?></select></div>
<div class="field"><label for="city">Zone de remise</label><select id="city" name="city"><?php foreach($cities as $city):?><option value="<?=e($city)?>" <?=$values['city']===$city?'selected':''?>><?=e($city)?></option><?php endforeach;?></select></div></div><div class="field"><label for="description">Description</label><textarea id="description" name="description" rows="5" minlength="30" maxlength="1200" required><?=e((string)$values['description'])?></textarea></div></fieldset>
<fieldset><legend>Prix et disponibilité</legend><div class="choice-grid"><label class="choice-card"><input type="radio" name="offer_mode" value="sale" data-price-toggle="sale" <?=$values['available_for_sale']?'checked':''?> required><span><strong>Vendre uniquement</strong></span></label><label class="choice-card"><input type="radio" name="offer_mode" value="rental" data-price-toggle="rental" <?=$values['available_for_rental']?'checked':''?> required><span><strong>Louer uniquement</strong></span></label></div><div class="form-grid"><div class="field"><label for="sale_price">Prix de vente (€)</label><input id="sale_price" name="sale_price" type="number" min="1" step=".01" value="<?=e((string)$values['sale_price'])?>" data-price-field="sale"></div><div class="field"><label for="rental_price_day">Location par jour (€)</label><input id="rental_price_day" name="rental_price_day" type="number" min="1" step=".01" value="<?=e((string)$values['rental_price_day'])?>" data-price-field="rental"></div></div></fieldset>
<fieldset><legend>Fiche technique</legend><div class="form-grid"><?php foreach(technical_spec_fields() as $specKey=>$specLabel):?><div class="field"><label for="<?=e($specKey)?>"><?=e($specLabel)?></label><input id="<?=e($specKey)?>" name="<?=e($specKey)?>" maxlength="500" value="<?=e((string)$values[$specKey])?>" required></div><?php endforeach;?></div></fieldset>
<fieldset><legend>Informations circulaires</legend><div class="form-grid"><div class="field"><label for="reuse_count">Locations déjà réalisées</label><input id="reuse_count" name="reuse_count" type="number" min="0" max="999" value="<?=e((string)$values['reuse_count'])?>"></div><div class="field"><label for="distance_km">Distance estimée (km)</label><input id="distance_km" name="distance_km" type="number" min="0" max="5000" value="<?=e((string)$values['distance_km'])?>"></div></div><label class="check-row"><input type="checkbox" name="repaired" <?=$values['repaired']?'checked':''?>> Réparation professionnelle ayant prolongé sa durée de vie</label></fieldset>
<div class="form-submit"><button class="button" type="submit">Enregistrer les modifications</button><a class="button button-outline" href="<?=url('product.php?id='.$id)?>">Annuler</a></div></form></div></section></main>
<?php require __DIR__.'/includes/footer.php';?>
