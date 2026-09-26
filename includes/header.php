<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
$isAdmin = isset($_SESSION['admin']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — MyTraining' : 'MyTraining — Plateforme de formation en ligne' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="logo">
            <span class="logo-mark">M</span>
            <span class="logo-text">MyTraining</span>
        </a>
        <nav class="nav-links">
            <a href="index.php"        class="<?= $current === 'index.php' ? 'active' : '' ?>">Inscription</a>
            <a href="liste.php"        class="<?= $current === 'liste.php' ? 'active' : '' ?>">Inscrits</a>
            <a href="statistiques.php" class="<?= $current === 'statistiques.php' ? 'active' : '' ?>">Statistiques</a>
            <?php if ($isAdmin): ?>
                <a href="logout.php" class="btn-link">Déconnexion (<?= htmlspecialchars($_SESSION['admin']) ?>)</a>
            <?php else: ?>
                <a href="login.php" class="<?= $current === 'login.php' ? 'active' : '' ?>">Espace Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="main-container">
