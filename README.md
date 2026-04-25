# MyTraining — Guide d'installation

## Prérequis
- PHP >= 7.4
- MySQL >= 5.7 (ou MariaDB >= 10.3)
- Serveur web : Apache (XAMPP / WAMP) ou Nginx

---

## 1. Créer la base de données

Dans phpMyAdmin ou le terminal MySQL :

```sql
SOURCE /chemin/vers/formation_db.sql;
```

Ou copiez-collez le contenu de `formation_db.sql` dans l'onglet SQL de phpMyAdmin.

---

## 2. Configurer la connexion

Ouvrez **`php/config.php`** et modifiez :

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // votre utilisateur MySQL
define('DB_PASS', '');           // votre mot de passe MySQL
define('DB_NAME', 'formation_db');
```

---

## 3. Déployer le projet

Copiez le dossier `mytraining/` dans :
- **XAMPP** : `C:/xampp/htdocs/mytraining/`
- **WAMP**  : `C:/wamp64/www/mytraining/`
- **Linux** : `/var/www/html/mytraining/`

---

## 4. Accéder à l'application

| Page | URL |
|---|---|
| Inscription | `http://localhost/mytraining/index.html` |
| Liste participants | `http://localhost/mytraining/liste.php` |
| Statistiques | `http://localhost/mytraining/stats.php` |
| Connexion | `http://localhost/mytraining/login.php` |

---

## Structure des fichiers

```
mytraining/
├── index.html          ← Formulaire d'inscription (HTML + CSS + JS)
├── liste.php           ← Liste des participants (PHP + MySQL)
├── stats.php           ← Statistiques et graphiques (PHP + MySQL)
├── login.php           ← Authentification (BONUS)
├── logout.php          ← Déconnexion
├── formation_db.sql    ← Script SQL (base + tables + données initiales)
├── css/
│   └── style.css       ← Feuille de style principale (responsive)
├── js/
│   └── validation.js   ← Validation JS côté client (fonction Verif())
└── php/
    ├── config.php      ← Connexion PDO à MySQL
    └── traitement.php  ← Traitement POST du formulaire
```

---

## Fonctionnalités implémentées

### Semaine 1 — Frontend
- [x] Formulaire complet (nom, prénom, CIN, email, niveau, modules)
- [x] Design responsive avec CSS custom (dark theme)
- [x] Validation JS `Verif()` : champs obligatoires, regex, max 2 modules
- [x] Validation inline (blur) sur chaque champ

### Semaine 2 — Backend
- [x] Base MySQL : `users`, `modules`, `inscriptions` avec clés étrangères
- [x] `php/config.php` : connexion PDO sécurisée
- [x] `php/traitement.php` : validation serveur + transaction SQL
- [x] Messages flash (succès/erreur) via session

### Semaine 3 — Affichage & Bonus
- [x] `liste.php` : tableau HTML avec jointures SQL (GROUP_CONCAT)
- [x] Recherche en temps réel (LIKE sur nom/email/CIN)
- [x] Suppression d'inscription avec confirmation JS
- [x] `stats.php` : KPIs + barres de progression par module et niveau
- [x] `login.php` : authentification sécurisée (password_hash/verify)
- [x] Système register/login dans la même page

---

## Sécurité
- Requêtes préparées PDO (protection injection SQL)
- `htmlspecialchars()` sur tous les affichages (protection XSS)
- `password_hash()` / `password_verify()` pour les mots de passe
- Validation double : client (JS) + serveur (PHP)
- Transactions SQL avec rollback en cas d'erreur
