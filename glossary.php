<?php
declare(strict_types=1);
require_once __DIR__.'/includes/functions.php';
$terms=[
'Achat sécurisé'=>'Parcours qui présente le prix du produit, la livraison et le moyen de paiement avant l’envoi de la demande.',
'Circular Score'=>'Indice interne sur 100 qui valorise le réemploi, la réparation, l’ancienneté utile et la proximité. Ce n’est pas un bilan carbone.',
'Consentement'=>'Accord libre et clair donné par une personne pour un usage précis de ses données. Il peut être retiré.',
'Donnée personnelle'=>'Information permettant d’identifier directement ou indirectement une personne, par exemple un nom, un email ou une adresse IP.',
'Disponibilité'=>'Période pendant laquelle un équipement peut être loué. Le propriétaire peut bloquer certaines dates.',
'Location assurée'=>'Parcours de location qui ajoute une estimation de protection au prix de la période et de la livraison.',
'Marketplace'=>'Plateforme qui met en relation des propriétaires de matériel et des personnes souhaitant acheter ou louer.',
'PDO'=>'Outil PHP utilisé pour interroger MySQL avec des requêtes préparées et réduire le risque d’injection SQL.',
'RGPD'=>'Règlement européen qui encadre la collecte, l’utilisation, la conservation et la protection des données personnelles.',
'Requête préparée'=>'Méthode qui sépare la commande SQL des données saisies afin de limiter les injections malveillantes.',
'Responsive'=>'Interface qui s’adapte à la taille de l’écran, notamment ordinateur, tablette et smartphone.',
'XSS'=>'Attaque consistant à injecter du code dans une page. L’échappement des contenus affichés réduit ce risque.',
'CSRF'=>'Attaque qui force un utilisateur connecté à envoyer une action non souhaitée. Un jeton unique protège les formulaires.',
'WCAG'=>'Référentiel international donnant des règles pour rendre les services numériques accessibles aux personnes handicapées.'
];ksort($terms,SORT_NATURAL|SORT_FLAG_CASE);
$pageTitle='Glossaire';$pageDescription='Définitions simples des termes utilisés sur Focal-Shift.';$activePage='';require __DIR__.'/includes/header.php';
?>
<main id="main-content"><section class="page-hero page-hero-compact"><div class="container"><p class="eyebrow">Comprendre Focal-Shift</p><h1>Glossaire.</h1><p>Les mots utiles de la marketplace, expliqués simplement.</p></div></section><section class="section"><div class="container glossary-grid"><?php foreach($terms as $term=>$definition):?><article id="<?=e(strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',iconv('UTF-8','ASCII//TRANSLIT',$term))))?>"><h2><?=e($term)?></h2><p><?=e($definition)?></p></article><?php endforeach;?></div></section></main>
<?php require __DIR__.'/includes/footer.php'; ?>
