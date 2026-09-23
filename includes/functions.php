<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float $value): string
{
    return number_format($value, 0, ',', ' ') . ' €';
}

function condition_label(string $condition): string
{
    return [
        'neuf' => 'Comme neuf',
        'tres_bon' => 'Très bon état',
        'bon' => 'Bon état',
        'use' => 'État d’usage',
    ][$condition] ?? $condition;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function calculate_circular_score(int $purchaseYear, int $reuseCount, bool $repaired, int $distanceKm): int
{
    $age = max(0, (int) date('Y') - $purchaseYear);
    $secondHand = 15;
    $ageScore = min(15, (int) round(($age / 5) * 15));
    $reuseScore = min(35, (int) round(($reuseCount / 10) * 35));
    $repairScore = $repaired ? 20 : 8;
    $localScore = $distanceKm <= 25 ? 15 : ($distanceKm <= 100 ? 8 : 3);
    return min(100, $secondHand + $ageScore + $reuseScore + $repairScore + $localScore);
}

function equipment_image(?string $path, string $categorySlug = 'camera'): string
{
    if ($path && str_starts_with($path, 'uploads/')) {
        return url($path);
    }
    $allowed = ['camera', 'lens', 'video', 'lighting', 'audio', 'accessory'];
    $slug = in_array($categorySlug, $allowed, true) ? $categorySlug : 'camera';
    return asset('images/catalog/' . $slug . '.webp');
}

function score_tier(int $score): array
{
    if ($score >= 70) return [
        'class' => 'green', 'label' => 'Impact faible', 'symbol' => '✓', 'discount' => 5,
        'summary' => 'Choix recommandé : proximité, réemploi et durée d’usage favorables.',
    ];
    if ($score >= 50) return [
        'class' => 'orange', 'label' => 'Impact moyen', 'symbol' => '≈', 'discount' => 0,
        'summary' => 'Choix équilibré : certains critères de circularité peuvent encore progresser.',
    ];
    return [
        'class' => 'red', 'label' => 'Impact élevé', 'symbol' => '!', 'discount' => 0,
        'summary' => 'Vigilance : distance, faible réemploi ou remplacement pèsent sur ce niveau.',
    ];
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'full_name' => (string) $user['full_name'],
        'email' => (string) $user['email'],
        'role' => (string) $user['role'],
        'identity_verified' => (bool) $user['identity_verified'],
    ];
}

function require_login(string $role = ''): array
{
    $user = current_user();
    if (!$user) {
        $next = $_SERVER['REQUEST_URI'] ?? url('index.php');
        header('Location: ' . url('login.php?next=' . rawurlencode($next)));
        exit;
    }
    return $user;
}

function safe_next_url(?string $next): string
{
    if (!$next || !str_starts_with($next, '/') || str_starts_with($next, '//')) return url('account.php');
    return $next;
}

function rental_days(string $start, string $end): int
{
    $from = new DateTimeImmutable($start);
    $to = new DateTimeImmutable($end);
    return max(1, (int) $from->diff($to)->days + 1);
}

function transaction_quote(array $item, string $type, ?string $start = null, ?string $end = null): array
{
    if ($type === 'purchase') {
        $amount = (float) $item['sale_price'];
        $shipping = 24.90;
        return ['days'=>0,'item'=>$amount,'shipping'=>$shipping,'insurance'=>0.0,'total'=>$amount+$shipping];
    }
    $days = rental_days((string)$start, (string)$end);
    $grossAmount = $days * (float) $item['rental_price_day'];
    $tier = score_tier((int)($item['circular_score'] ?? 0));
    $discount = $tier['class'] === 'green' ? round($grossAmount * 0.05, 2) : 0.0;
    $amount = $grossAmount - $discount;
    $shipping = 19.90;
    $insurance = max(8.90, round($amount * 0.08, 2));
    return ['days'=>$days,'item'=>$amount,'gross_item'=>$grossAmount,'discount'=>$discount,'shipping'=>$shipping,'insurance'=>$insurance,'total'=>$amount+$shipping+$insurance];
}

function date_range_is_available(PDO $pdo, int $equipmentId, string $start, string $end): bool
{
    $sql = "SELECT COUNT(*) FROM equipment_unavailability WHERE equipment_id=:equipment AND start_date<=:end_date AND end_date>=:start_date";
    $q=$pdo->prepare($sql);$q->execute(['equipment'=>$equipmentId,'start_date'=>$start,'end_date'=>$end]);
    if((int)$q->fetchColumn()>0)return false;
    $sql = "SELECT COUNT(*) FROM transaction_requests WHERE equipment_id=:equipment AND transaction_type='rental' AND status IN ('pending','accepted') AND rental_start<=:end_date AND rental_end>=:start_date";
    $q=$pdo->prepare($sql);$q->execute(['equipment'=>$equipmentId,'start_date'=>$start,'end_date'=>$end]);
    return (int)$q->fetchColumn()===0;
}
