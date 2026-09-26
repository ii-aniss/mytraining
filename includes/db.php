<?php
/**
 * Connexion PDO à la base de données formation_db (XAMPP / MySQL)
 */

$DB_HOST = 'localhost';
$DB_NAME = 'formation_db';
$DB_USER = 'root';
$DB_PASS = '';      // Par défaut, XAMPP utilise un mot de passe vide pour root
$DB_CHAR = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHAR";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die(
        "<div style='font-family:sans-serif;padding:30px;max-width:600px;margin:80px auto;background:#fee2e2;border:1px solid #ef4444;border-radius:12px;color:#7f1d1d;'>
            <h2 style='margin-top:0'>Erreur de connexion à la base de données</h2>
            <p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p>Vérifiez que <strong>XAMPP</strong> est démarré (Apache + MySQL) et que la base <code>formation_db</code> a bien été importée depuis <code>sql/formation_db.sql</code>.</p>
         </div>"
    );
}
