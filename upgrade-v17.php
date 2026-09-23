<?php
declare(strict_types=1);
require_once __DIR__.'/includes/db.php';
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

try {
    $pdo->exec("ALTER TABLE equipment ADD COLUMN technical_specs JSON NULL AFTER verification_notes");
    echo "OK : colonne technical_specs ajoutée.\n";
} catch (Throwable $exception) {
    echo "IGNORÉ : {$exception->getMessage()}\n";
}

$pdo->exec("UPDATE equipment SET status='published' WHERE status='pending_verification'");
$pdo->exec("UPDATE equipment SET verification_status='not_required' WHERE verification_status IS NULL");
$pdo->exec("UPDATE equipment SET verification_status='approved', verified_condition=condition_grade, verified_score=circular_score, verified_rental_price=rental_price_day, verification_notes='Contrôle de démonstration Focal-Shift validé' WHERE id IN (1,2,3,4,5,7,8,10) AND verification_status='not_required'");

$knownSpecs = require __DIR__.'/includes/product-specifications.php';
$products = $pdo->query("SELECT id,brand,model FROM equipment WHERE technical_specs IS NULL")->fetchAll();
$statement = $pdo->prepare("UPDATE equipment SET technical_specs=:specs WHERE id=:id");
foreach ($products as $product) {
    $key = trim($product['brand'].' '.$product['model']);
    $specs = $knownSpecs[$key] ?? [
        'type_format' => 'Équipement photo/vidéo · '.$key,
        'resolution_performance' => 'Performances à confirmer selon la documentation constructeur',
        'compatibility' => 'Compatibilité à vérifier avant la transaction',
        'connectivity' => 'Connectiques visibles sur les photos de l’annonce',
        'power' => 'Alimentation et autonomie selon les accessoires inclus',
        'weight_dimensions' => 'Poids et dimensions à confirmer selon la configuration',
        'included_accessories' => 'Accessoires détaillés dans la description',
        'highlights' => 'Produit publié ; contrôle Focal-Shift disponible',
    ];
    $statement->execute(['specs'=>json_encode($specs,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'id'=>$product['id']]);
}

echo "Migration V17 terminée : annonces visibles immédiatement, statut de vérification séparé et fiches techniques activées.\n";
