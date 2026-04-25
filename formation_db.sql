-- ============================================
--  MyTraining — formation_db.sql
--  Script de création de la base de données
-- ============================================

CREATE DATABASE IF NOT EXISTS formation_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE formation_db;

-- ---- Table: users ----
CREATE TABLE IF NOT EXISTS users (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom      VARCHAR(60)  NOT NULL,
  prenom   VARCHAR(60)  NOT NULL,
  cin      CHAR(8)      NOT NULL UNIQUE,
  email    VARCHAR(120) NOT NULL UNIQUE,
  niveau   ENUM('débutant','intermédiaire','avancé') NOT NULL,
  mot_de_passe VARCHAR(255) DEFAULT NULL,
  role     ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- Table: modules ----
CREATE TABLE IF NOT EXISTS modules (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom_module   VARCHAR(100) NOT NULL,
  description  TEXT,
  tag          VARCHAR(40)
) ENGINE=InnoDB;

-- ---- Table: inscriptions ----
CREATE TABLE IF NOT EXISTS inscriptions (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id   INT UNSIGNED NOT NULL,
  module_id INT UNSIGNED NOT NULL,
  date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
  UNIQUE KEY unique_inscription (user_id, module_id)
) ENGINE=InnoDB;

-- ---- Données initiales : modules ----
INSERT INTO modules (nom_module, description, tag) VALUES
  ('HTML / CSS',      'Structurez et stylisez vos pages web modernes.',              'Dev Web'),
  ('JavaScript',      'Interactivité, DOM et logique applicative.',                  'Dev Web'),
  ('PHP & MySQL',     'Développez des applications serveur robustes.',               'Backend'),
  ('Python Data',     'Analyse de données avec Pandas et NumPy.',                   'Data'),
  ('Machine Learning','Modèles prédictifs et algorithmes d\'apprentissage.',         'IA'),
  ('Cybersécurité',   'Sécurité des systèmes et réseaux informatiques.',             'Réseau');

-- ---- Compte admin de démonstration ----
-- Mot de passe : Admin@1234  (haché avec password_hash PHP)
INSERT INTO users (nom, prenom, cin, email, niveau, mot_de_passe, role) VALUES
  ('Admin', 'System', '00000000', 'admin@mytraining.dz',
   'avancé',
   '$2y$12$examplehashplaceholder00000000000000000000000000000000.',
   'admin');
