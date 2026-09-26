-- =====================================================
-- MyTraining - Mini LMS
-- Base de données : formation_db
-- =====================================================

CREATE DATABASE IF NOT EXISTS formation_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE formation_db;

-- =====================================================
-- Table: users
-- =====================================================
DROP TABLE IF EXISTS inscriptions;
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS comptes;

CREATE TABLE users (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nom       VARCHAR(100) NOT NULL,
  prenom    VARCHAR(100) NOT NULL,
  cin       VARCHAR(20)  NOT NULL UNIQUE,
  email     VARCHAR(150) NOT NULL UNIQUE,
  niveau    VARCHAR(50)  NOT NULL,
  cree_le   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: modules
-- =====================================================
CREATE TABLE modules (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nom_module   VARCHAR(100) NOT NULL,
  description  TEXT,
  icone        VARCHAR(50) DEFAULT 'book'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: inscriptions
-- =====================================================
CREATE TABLE inscriptions (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  module_id  INT NOT NULL,
  date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inscription_user
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
  CONSTRAINT fk_inscription_module
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: comptes (Bonus - Authentification admin)
-- =====================================================
CREATE TABLE comptes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  username    VARCHAR(50)  NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  role        VARCHAR(20)  DEFAULT 'admin',
  cree_le     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Données de départ - Modules
-- =====================================================
INSERT INTO modules (nom_module, description, icone) VALUES
('Développement Web',        'HTML, CSS, JavaScript, frameworks modernes (React, Vue) et bonnes pratiques du Web.',                'code'),
('Bases de Données',         'SQL, MySQL, modélisation, requêtes avancées et optimisation des performances.',                       'database'),
('Programmation PHP',        'PHP moderne, POO, sécurité, sessions, et création d''applications dynamiques.',                       'php'),
('Cybersécurité',            'Sécurité des applications, OWASP Top 10, cryptographie et tests d''intrusion.',                       'shield'),
('Intelligence Artificielle','Machine Learning, deep learning et applications pratiques de l''IA.',                                  'brain'),
('Cloud & DevOps',           'AWS, Docker, CI/CD et déploiement d''applications scalables dans le cloud.',                           'cloud'),
('Design UI/UX',             'Principes de design, prototypage Figma et expérience utilisateur.',                                    'palette'),
('Mobile (React Native)',    'Création d''applications mobiles iOS et Android avec React Native.',                                   'mobile');

-- =====================================================
-- Compte admin par défaut
-- Identifiants: admin / admin123
-- (le mot de passe est haché avec password_hash)
-- =====================================================
INSERT INTO comptes (username, password, role) VALUES
('admin', '$2y$10$E1F9hQ0xQqv6V0o2Yp1tz.uQ8OQ8JmH9Yqv6l8sJk2N9q3pZk4d9G', 'admin');
-- NOTE : le hash ci-dessus correspond à "admin123".
-- Si vous voulez régénérer un hash, exécutez en PHP : echo password_hash('admin123', PASSWORD_DEFAULT);
