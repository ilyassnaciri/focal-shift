<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    echo json_encode(['ok' => false, 'message' => 'Session expirée. Rechargez la page.']);
    exit;
}

$categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
$brand = trim((string) ($_POST['brand'] ?? ''));
$model = trim((string) ($_POST['model'] ?? ''));
$year = filter_input(INPUT_POST, 'purchase_year', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1990, 'max_range' => (int) date('Y')]]);
$condition = (string) ($_POST['condition_grade'] ?? '');
$reuseCount = filter_input(INPUT_POST, 'reuse_count', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999]]);
$distanceKm = filter_input(INPUT_POST, 'distance_km', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 5000]]);
$repaired = isset($_POST['repaired']) && $_POST['repaired'] === '1';

if (!$categoryId || $brand === '' || $model === '' || strlen($brand) > 60 || strlen($model) > 80 || !$year || $reuseCount === false || $distanceKm === false || !in_array($condition, ['neuf', 'tres_bon', 'bon', 'use'], true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Complétez tous les champs avec des valeurs valides.']);
    exit;
}

$pdo = db();
$exact = $pdo->prepare(
    'SELECT pr.*, c.rental_yield_rate FROM price_reference pr JOIN categories c ON c.id = pr.category_id
     WHERE pr.category_id = :category AND LOWER(pr.brand) = LOWER(:brand) AND LOWER(pr.model) = LOWER(:model) LIMIT 1'
);
$exact->execute(['category' => $categoryId, 'brand' => $brand, 'model' => $model]);
$reference = $exact->fetch();
$matchType = 'exact';

if (!$reference) {
    $fallback = $pdo->prepare(
        'SELECT AVG(pr.new_price_reference) AS new_price_reference, c.rental_yield_rate
         FROM price_reference pr JOIN categories c ON c.id = pr.category_id
         WHERE pr.category_id = :category GROUP BY c.rental_yield_rate'
    );
    $fallback->execute(['category' => $categoryId]);
    $reference = $fallback->fetch();
    $matchType = 'category_average';
}

if (!$reference || !$reference['new_price_reference']) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Aucune référence disponible pour cette catégorie.']);
    exit;
}

$age = max(0, (int) date('Y') - (int) $year);
if ($age === 0) {
    $ageCoefficient = 1.0;
} elseif ($age <= 2) {
    $ageCoefficient = 0.85 ** $age;
} else {
    $ageCoefficient = (0.85 ** 2) * (0.92 ** ($age - 2));
}
$ageCoefficient = max(0.20, $ageCoefficient);
$conditionCoefficients = ['neuf' => 1.0, 'tres_bon' => 0.85, 'bon' => 0.70, 'use' => 0.50];
$conditionCoefficient = $conditionCoefficients[$condition];

$resaleValue = round((float) $reference['new_price_reference'] * $ageCoefficient * $conditionCoefficient, 2);
$rentalRate = (float) ($reference['rental_yield_rate'] ?: 0.02);
$rentalDay = $matchType === 'exact' && !empty($reference['daily_rental_reference'])
    ? (float) $reference['daily_rental_reference']
    : max(40, round($resaleValue * $rentalRate, 2));
$saleMin = round($resaleValue * 0.80, 2);
$saleMax = round($resaleValue * 1.20, 2);
$rentalMin = round($rentalDay * 0.80, 2);
$rentalMax = round($rentalDay * 1.20, 2);
$rentalTakeRate = 0.15;
$saleTakeRate = 0.10;
$netDaily = $rentalDay * (1 - $rentalTakeRate);
$saleNet = $resaleValue * (1 - $saleTakeRate);
$breakEvenDays = (int) ceil($saleNet / $netDaily);
$circularScore = calculate_circular_score((int) $year, (int) $reuseCount, $repaired, (int) $distanceKm);
$scoreTier = score_tier($circularScore);

$insert = $pdo->prepare(
    'INSERT INTO simulator_requests
    (category_id, brand, model, purchase_year, condition_grade, resale_value_estimate, rental_price_suggested, match_type, circular_score, discount_percent)
    VALUES (:category, :brand, :model, :year, :condition, :resale, :rental, :match_type, :circular_score, :discount_percent)'
);
$insert->execute([
    'category' => $categoryId, 'brand' => $brand, 'model' => $model, 'year' => $year,
    'condition' => $condition, 'resale' => $resaleValue, 'rental' => $rentalDay, 'match_type' => $matchType,
    'circular_score' => $circularScore, 'discount_percent' => $scoreTier['discount'],
]);

$_SESSION['last_simulation'] = [
    'request_id' => (int) $pdo->lastInsertId(),
    'category_id' => (int) $categoryId,
    'brand' => $brand,
    'model' => $model,
    'sale_min' => $saleMin,
    'sale_max' => $saleMax,
    'rental_min' => $rentalMin,
    'rental_max' => $rentalMax,
];

echo json_encode([
    'ok' => true,
    'data' => [
        'resale_value' => $resaleValue,
        'sale_net' => round($saleNet, 2),
        'rental_day' => $rentalDay,
        'sale_min' => $saleMin,
        'sale_max' => $saleMax,
        'rental_min' => $rentalMin,
        'rental_max' => $rentalMax,
        'rental_10' => round($netDaily * 10, 2),
        'rental_30' => round($netDaily * 30, 2),
        'rental_60' => round($netDaily * 60, 2),
        'break_even_days' => $breakEvenDays,
        'match_type' => $matchType,
        'score_class' => $scoreTier['class'],
        'score_label' => $scoreTier['label'],
        'score_symbol' => $scoreTier['symbol'],
        'score_summary' => $scoreTier['summary'],
        'discount_percent' => $scoreTier['discount'],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
