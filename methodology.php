<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Méthodologie';
$pageDescription = 'Hypothèses du simulateur et de l’indicateur circulaire Focal-Shift.';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-hero"><div class="container narrow"><p class="eyebrow">Transparence</p><h1>Des indicateurs utiles, jamais présentés comme des certitudes.</h1><p>Focal-Shift distingue les données de référence, les règles de calcul et les recommandations.</p></div></section>
    <section class="section"><div class="container article-layout">
        <article class="prose-card"><h2>Simulateur de cote</h2><p><strong>Donnée :</strong> prix neuf de référence enregistré dans MySQL et daté. Si le modèle exact n’existe pas, le système utilise la moyenne de sa catégorie et le signale.</p><p><strong>Hypothèse :</strong> coefficient d’état de 1,00 à 0,50 ; dépréciation de 15 % par an pendant deux ans, puis 8 % avec plancher à 20 %.</p><p><strong>Calcul :</strong> prix neuf × coefficient d’âge × coefficient d’état. Le tarif locatif conseillé correspond à 1,8 % à 2,5 % de la valeur estimée selon la catégorie.</p><p><strong>Limite :</strong> l’estimation n’est ni une expertise, ni une offre d’achat, ni une garantie de revenu.</p></article>
        <article class="prose-card"><h2>Indicateur circulaire</h2><p>Le calcul interne observe quatre signaux : la durée d’usage, le nombre de remises en circulation, la réparation plutôt que le remplacement et la proximité entre les membres. Le nombre technique n’est pas affiché : l’utilisateur reçoit un niveau directement compréhensible.</p><ul class="impact-method"><li><strong>✓ Impact faible — vert :</strong> les critères sont favorables. Le locataire bénéficie de 5 % de réduction sur les frais de service.</li><li><strong>≈ Impact moyen — orange :</strong> le choix reste cohérent, mais la distance ou l’historique de réemploi peut progresser. Aucune réduction.</li><li><strong>! Impact élevé — rouge :</strong> plusieurs critères appellent à la vigilance, notamment un trajet long ou un faible réemploi. Aucune réduction.</li></ul><p><strong>Pourquoi cet indicateur ?</strong> Il aide à comparer rapidement deux offres et récompense l’usage local d’un équipement déjà entretenu. Il ne mesure pas des émissions de CO₂ et ne remplace pas une analyse de cycle de vie.</p></article>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
