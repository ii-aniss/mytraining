<?php
require_once __DIR__ . '/includes/db.php';
session_start();
$pageTitle = "Créer un compte admin";

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if ($username === '' || $password === '') {
        $error = "Tous les champs sont obligatoires.";
    } elseif (strlen($username) < 3) {
        $error = "Le nom d'utilisateur doit faire au moins 3 caractères.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les deux mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM comptes WHERE username = :u");
        $stmt->execute([':u' => $username]);
        if ($stmt->fetch()) {
            $error = "Ce nom d'utilisateur est déjà pris.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO comptes (username, password, role) VALUES (:u, :p, 'admin')");
            $stmt->execute([':u' => $username, ':p' => $hash]);
            $success = true;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="card">
        <h2 class="card-title">Créer un compte administrateur</h2>
        <p class="card-subtitle">Inscrivez-vous pour pouvoir gérer la plateforme.</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><strong>Compte créé !</strong> Vous pouvez maintenant <a href="login.php">vous connecter</a>.</div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" novalidate>
            <div class="form-grid">
                <div class="form-row">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-row">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    <span class="hint">Minimum 6 caractères.</span>
                </div>
                <div class="form-row">
                    <label for="confirm">Confirmation</label>
                    <input type="password" id="confirm" name="confirm" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="width:100%">Créer mon compte</button>
            </div>
        </form>
        <?php endif; ?>

        <p class="auth-footer">
            Déjà un compte ? <a href="login.php">Se connecter</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
