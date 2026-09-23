-- FOCAL-SHIFT - Base applicative MySQL 8 / MariaDB 11
-- ATTENTION : ce script réinitialise uniquement les tables de la base focal_shift.

CREATE DATABASE IF NOT EXISTS focal_shift
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE focal_shift;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS simulator_requests;
DROP TABLE IF EXISTS transaction_requests;
DROP TABLE IF EXISTS equipment_unavailability;
DROP TABLE IF EXISTS newsletter_subscribers;
DROP TABLE IF EXISTS contact_requests;
DROP TABLE IF EXISTS equipment_photos;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS price_reference;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NULL,
  role ENUM('owner','renter','both') NOT NULL DEFAULT 'both',
  identity_verified TINYINT(1) NOT NULL DEFAULT 0,
  rating_avg DECIMAL(3,2) NULL,
  rating_count INT UNSIGNED NOT NULL DEFAULT 0,
  transaction_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  rental_yield_rate DECIMAL(5,4) NOT NULL DEFAULT 0.0200,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE price_reference (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  brand VARCHAR(60) NOT NULL,
  model VARCHAR(80) NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  new_price_reference DECIMAL(10,2) NOT NULL,
  daily_rental_reference DECIMAL(8,2) NULL,
  source_label VARCHAR(160) NOT NULL DEFAULT 'Référentiel tarifaire interne',
  reference_date DATE NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reference_category FOREIGN KEY (category_id) REFERENCES categories(id),
  UNIQUE KEY uq_reference_model (brand, model, category_id),
  INDEX idx_reference_lookup (category_id, brand, model)
) ENGINE=InnoDB;

CREATE TABLE equipment (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  owner_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  brand VARCHAR(60) NOT NULL,
  model VARCHAR(80) NOT NULL,
  purchase_year SMALLINT UNSIGNED NOT NULL,
  condition_grade ENUM('neuf','tres_bon','bon','use') NOT NULL,
  description TEXT NOT NULL,
  sale_price DECIMAL(10,2) NULL,
  rental_price_day DECIMAL(8,2) NULL,
  available_for_sale TINYINT(1) NOT NULL DEFAULT 0,
  available_for_rental TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('draft','pending_verification','verified','published','reserved','archived') NOT NULL DEFAULT 'draft',
  verification_status ENUM('not_required','requested','received','approved','rejected') NOT NULL DEFAULT 'not_required',
  verified_condition ENUM('neuf','tres_bon','bon','use') NULL,
  verified_score TINYINT UNSIGNED NULL,
  verified_rental_price DECIMAL(8,2) NULL,
  verification_notes TEXT NULL,
  technical_specs JSON NULL,
  circular_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
  reuse_count INT UNSIGNED NOT NULL DEFAULT 0,
  repaired TINYINT(1) NOT NULL DEFAULT 0,
  distance_km INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_equipment_owner FOREIGN KEY (owner_id) REFERENCES users(id),
  CONSTRAINT fk_equipment_category FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_equipment_catalogue (status, category_id, brand),
  INDEX idx_equipment_sale (available_for_sale, sale_price),
  INDEX idx_equipment_rental (available_for_rental, rental_price_day),
  CHECK (available_for_sale + available_for_rental = 1),
  CHECK ((available_for_sale = 0 OR sale_price > 0) AND (available_for_rental = 0 OR rental_price_day > 0)),
  CHECK (circular_score BETWEEN 0 AND 100)
) ENGINE=InnoDB;

CREATE TABLE equipment_photos (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  equipment_id INT UNSIGNED NOT NULL,
  url VARCHAR(255) NOT NULL,
  photo_type ENUM('listing','condition_before','condition_after') NOT NULL DEFAULT 'listing',
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_photo_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE simulator_requests (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  category_id INT UNSIGNED NOT NULL,
  brand VARCHAR(60) NOT NULL,
  model VARCHAR(80) NOT NULL,
  purchase_year SMALLINT UNSIGNED NOT NULL,
  condition_grade ENUM('neuf','tres_bon','bon','use') NOT NULL,
  resale_value_estimate DECIMAL(10,2) NOT NULL,
  rental_price_suggested DECIMAL(8,2) NOT NULL,
  match_type ENUM('exact','category_average') NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_simulation_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_simulation_category FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_simulation_created (created_at),
  INDEX idx_simulation_model (category_id, brand, model)
) ENGINE=InnoDB;

CREATE TABLE newsletter_subscribers (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(160) NOT NULL UNIQUE,
  profile_type ENUM('photographe','videaste','studio_agence','proprietaire','passionne','autre') NOT NULL,
  consented_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
  source VARCHAR(60) NOT NULL DEFAULT 'homepage'
) ENGINE=InnoDB;

CREATE TABLE contact_requests (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  profile_type ENUM('acheteur','vendeur','partenaire','presse','autre') NOT NULL,
  subject VARCHAR(160) NOT NULL,
  message TEXT NOT NULL,
  consent_at DATETIME NOT NULL,
  status ENUM('new','in_progress','answered','archived') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contact_status_date (status, created_at),
  INDEX idx_contact_email (email)
) ENGINE=InnoDB;

CREATE TABLE equipment_unavailability (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  equipment_id INT UNSIGNED NOT NULL,
  owner_id INT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason VARCHAR(160) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_unavailability_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
  CONSTRAINT fk_unavailability_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_unavailability_dates (equipment_id, start_date, end_date),
  CHECK (end_date >= start_date)
) ENGINE=InnoDB;

CREATE TABLE transaction_requests (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  equipment_id INT UNSIGNED NOT NULL,
  buyer_id INT UNSIGNED NOT NULL,
  transaction_type ENUM('purchase','rental') NOT NULL,
  rental_start DATE NULL,
  rental_end DATE NULL,
  payment_method ENUM('card','bank_transfer','split_payment') NOT NULL,
  item_amount DECIMAL(10,2) NOT NULL,
  shipping_amount DECIMAL(8,2) NOT NULL DEFAULT 0,
  insurance_amount DECIMAL(8,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending','accepted','declined','cancelled') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transaction_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id),
  CONSTRAINT fk_transaction_buyer FOREIGN KEY (buyer_id) REFERENCES users(id),
  INDEX idx_transaction_equipment (equipment_id, status),
  INDEX idx_transaction_buyer (buyer_id, created_at),
  CHECK (total_amount >= 0)
) ENGINE=InnoDB;

CREATE TABLE rental_return_inspections (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  transaction_request_id BIGINT UNSIGNED NOT NULL,
  submitted_by INT UNSIGNED NOT NULL,
  photo_url VARCHAR(255) NOT NULL,
  condition_status ENUM('unchanged','minor_change','damage_reported') NOT NULL,
  notes TEXT NULL,
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_return_transaction FOREIGN KEY (transaction_request_id) REFERENCES transaction_requests(id) ON DELETE CASCADE,
  CONSTRAINT fk_return_user FOREIGN KEY (submitted_by) REFERENCES users(id),
  UNIQUE KEY uq_return_user_transaction (transaction_request_id, submitted_by)
) ENGINE=InnoDB;

INSERT INTO users (full_name, email, role, identity_verified, rating_avg, rating_count, transaction_count, created_at) VALUES
('Claire D.', 'claire.demo@focal-shift.local', 'both', 1, 4.85, 24, 12, '2025-01-15 10:00:00'),
('Nolan V.', 'nolan.demo@focal-shift.local', 'owner', 1, 4.72, 18, 9, '2025-05-09 11:30:00'),
('Studio K.', 'studio.demo@focal-shift.local', 'both', 1, 4.94, 39, 28, '2024-11-02 09:15:00'),
('Ilyass N.', 'ilyass.demo@focal-shift.local', 'renter', 1, 4.92, 7, 5, '2026-01-12 09:00:00');

INSERT INTO categories (id, name, slug, rental_yield_rate) VALUES
(1, 'Boîtiers photo', 'camera', 0.0220),
(2, 'Objectifs', 'lens', 0.0180),
(3, 'Caméras vidéo', 'video', 0.0200),
(4, 'Éclairage', 'lighting', 0.0250),
(5, 'Audio', 'audio', 0.0240),
(6, 'Accessoires', 'accessory', 0.0250);

INSERT INTO price_reference (brand, model, category_id, new_price_reference, daily_rental_reference, source_label, reference_date) VALUES
('Sony', 'A7 IV', 1, 2800.00, 65.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Canon', 'EOS R6 Mark II', 1, 2899.00, 68.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Nikon', 'Z8', 1, 4599.00, 105.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Fujifilm', 'X-T5', 1, 1999.00, 48.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Sony', 'FE 70-200mm F2.8 GM II', 2, 2999.00, 70.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Canon', 'RF 24-70mm F2.8 L', 2, 2599.00, 62.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Sigma', '24-70mm F2.8 DG DN II', 2, 1349.00, 35.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Sony', 'FX3', 3, 4699.00, 120.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Blackmagic', 'Pocket Cinema 6K Pro', 3, 2799.00, 78.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('DJI', 'RS 4 Pro', 6, 1099.00, 36.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Aputure', 'LS 600d Pro', 4, 2190.00, 65.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Rode', 'NTG5', 5, 589.00, 22.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Sennheiser', 'MKE 600', 5, 329.00, 14.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('DJI', 'Mavic 3 Pro', 6, 2099.00, 75.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21'),
('Atomos', 'Ninja V+', 6, 899.00, 28.00, 'Référentiel tarifaire interne - à actualiser régulièrement', '2026-09-21');

INSERT INTO equipment
(owner_id, category_id, brand, model, purchase_year, condition_grade, description, sale_price, rental_price_day, available_for_sale, available_for_rental, status, circular_score, reuse_count, repaired, distance_km, created_at) VALUES
(1, 1, 'Sony', 'A7 IV', 2023, 'tres_bon', 'Boîtier entretenu, capteur propre, deux batteries et chargeur inclus. Traces discrètes sous la semelle.', 1790, NULL, 1, 0, 'published', 82, 10, 1, 18, '2026-09-20 10:00:00'),
(2, 1, 'Canon', 'EOS R6 Mark II', 2024, 'neuf', 'Boîtier très peu utilisé, livré avec boîte, sangle et batterie d’origine. État cosmétique excellent.', NULL, 62, 0, 1, 'published', 65, 4, 0, 35, '2026-09-19 15:00:00'),
(3, 1, 'Nikon', 'Z8', 2023, 'tres_bon', 'Boîtier professionnel révisé, parfait pour reportage et vidéo 8K. Poignée et protection écran incluses.', NULL, 98, 0, 1, 'published', 88, 14, 1, 12, '2026-09-18 09:30:00'),
(1, 2, 'Sony', 'FE 70-200mm F2.8 GM II', 2022, 'bon', 'Optiques sans rayure, pare-soleil et collier de pied inclus. Marques d’usage sur le fût sans impact fonctionnel.', 1990, NULL, 1, 0, 'published', 91, 18, 1, 22, '2026-09-17 16:00:00'),
(2, 2, 'Canon', 'RF 24-70mm F2.8 L', 2023, 'tres_bon', 'Zoom polyvalent lumineux, lentilles impeccables. Housse et bouchons d’origine fournis.', NULL, 58, 0, 1, 'published', 74, 7, 0, 55, '2026-09-16 11:00:00'),
(3, 2, 'Sigma', '24-70mm F2.8 DG DN II', 2025, 'neuf', 'Objectif récent pour monture Sony E, complet avec boîte et accessoires. Aucune marque visible.', 1090, NULL, 1, 0, 'published', 54, 2, 0, 18, '2026-09-15 08:45:00'),
(3, 3, 'Sony', 'FX3', 2022, 'tres_bon', 'Caméra cinéma compacte avec cage, poignée XLR et deux batteries. Contrôle complet effectué.', NULL, 110, 0, 1, 'published', 94, 22, 1, 20, '2026-09-14 14:20:00'),
(1, 3, 'Blackmagic', 'Pocket Cinema 6K Pro', 2021, 'bon', 'Kit tournage complet proposé par Claire avec cage, SSD, alimentation secteur et contrôle des connectiques.', NULL, 78, 0, 1, 'reserved', 96, 27, 1, 15, '2026-09-13 12:00:00'),
(2, 6, 'DJI', 'RS 4 Pro', 2025, 'tres_bon', 'Stabilisateur complet et calibré, valise de transport incluse. Idéal pour configurations cinéma légères.', NULL, 34, 0, 1, 'published', 61, 5, 0, 42, '2026-09-12 17:15:00'),
(3, 4, 'Aputure', 'LS 600d Pro', 2022, 'bon', 'Projecteur LED puissant, contrôleur et valise à roulettes. Révision du ventilateur documentée.', NULL, 60, 0, 1, 'published', 89, 19, 1, 30, '2026-09-11 09:00:00'),
(1, 5, 'Rode', 'NTG5', 2023, 'tres_bon', 'Micro canon léger avec suspension et bonnette. Son propre, connectique XLR contrôlée.', NULL, 18, 0, 1, 'published', 77, 9, 0, 18, '2026-09-10 15:40:00'),
(2, 6, 'Atomos', 'Ninja V+', 2022, 'bon', 'Moniteur-enregistreur avec cage et deux batteries. Écran protégé, firmware à jour.', 540, NULL, 1, 0, 'published', 84, 13, 1, 65, '2026-09-09 13:30:00');

-- Les références historiques contrôlées illustrent le badge vérifié.
-- Les nouvelles annonces restent publiées avec le badge non vérifié jusqu'à la demande du propriétaire.
UPDATE equipment
SET verification_status='approved',
    verified_condition=condition_grade,
    verified_score=circular_score,
    verified_rental_price=rental_price_day,
    verification_notes='Contrôle de démonstration Focal-Shift validé'
WHERE id IN (1,2,3,4,5,7,8,10);

INSERT INTO transaction_requests
(equipment_id, buyer_id, transaction_type, rental_start, rental_end, payment_method, item_amount, shipping_amount, insurance_amount, total_amount, status, created_at) VALUES
(8, 4, 'rental', '2026-09-10', '2026-09-12', 'card', 222.30, 19.90, 17.78, 259.98, 'accepted', '2026-09-08 14:30:00');
