<?php
require_once __DIR__ . '/includes/db.php';
session_start();

// Sécurité : seul un admin connecté peut supprimer
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: liste.php');
    exit;
}

try {
    // La FK ON DELETE CASCADE supprime automatiquement les inscriptions liées
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
} catch (PDOException $e) {
    die("Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()));
}

header('Location: liste.php?msg=deleted');
exit;
