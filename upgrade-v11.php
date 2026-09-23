<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();
$queries = [
    "UPDATE equipment SET available_for_rental = 0, rental_price_day = NULL WHERE available_for_sale = 1 AND available_for_rental = 1 AND MOD(id, 2) = 1",
    "UPDATE equipment SET available_for_sale = 0, sale_price = NULL WHERE available_for_sale = 1 AND available_for_rental = 1 AND MOD(id, 2) = 0",
    "ALTER TABLE equipment MODIFY status ENUM('draft','pending_verification','verified','published','reserved','archived') NOT NULL DEFAULT 'draft'",
    "ALTER TABLE equipment ADD COLUMN verification_status ENUM('not_required','requested','received','approved','rejected') NOT NULL DEFAULT 'not_required' AFTER status",
    "ALTER TABLE equipment ADD COLUMN verified_condition ENUM('neuf','tres_bon','bon','use') NULL AFTER verification_status",
    "ALTER TABLE equipment ADD COLUMN verified_score TINYINT UNSIGNED NULL AFTER verified_condition",
    "ALTER TABLE equipment ADD COLUMN verified_rental_price DECIMAL(8,2) NULL AFTER verified_score",
    "ALTER TABLE equipment ADD COLUMN verification_notes TEXT NULL AFTER verified_rental_price",
    "CREATE TABLE IF NOT EXISTS rental_return_inspections (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, transaction_request_id BIGINT UNSIGNED NOT NULL, submitted_by INT UNSIGNED NOT NULL, photo_url VARCHAR(255) NOT NULL, condition_status ENUM('unchanged','minor_change','damage_reported') NOT NULL, notes TEXT NULL, submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_return_transaction FOREIGN KEY (transaction_request_id) REFERENCES transaction_requests(id) ON DELETE CASCADE, CONSTRAINT fk_return_user FOREIGN KEY (submitted_by) REFERENCES users(id), UNIQUE KEY uq_return_user_transaction (transaction_request_id, submitted_by)) ENGINE=InnoDB"
];
foreach ($queries as $query) { try { $pdo->exec($query); echo "OK\n"; } catch (Throwable $e) { echo "IGNORÉ : {$e->getMessage()}\n"; } }
echo "Migration V11 terminée. Supprimez ce fichier après exécution.\n";
