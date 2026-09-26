<?php
require_once __DIR__ . '/includes/db.php';
session_start();
$pageTitle = "Connexion administrateur";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM comptes WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        // Authentification : on accepte le hash bcrypt OU le mot de passe seed "admin123" pour le compte par défaut
        $ok = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $ok = true;
            } elseif ($user['username'] === 'admin' && $password === 'admin123') {
                // Premier login : on régénère le hash correctement
                $newHash = password_hash('admin123', PASSWORD_DEFAULT);
                $up = $pdo->prepare("UPDATE comptes SET password = :p WHERE id = :id");
                $up->execute([':p' => $newHash, ':id' => $user['id']]);
                $ok = true;
            }
        }

        if ($ok) {
            $_SESSION['admin']    = $user['username'];
            $_SESSION['admin_id'] = (int)$user['id'];
            header('Location: liste.php');
            exit;
        }
        $error = "Identifiants incorrects.";
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="card">
        <h2 class="card-title">Espace administrateur</h2>
        <p class="card-subtitle">Connectez-vous pour gérer les inscriptions.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-grid">
                <div class="form-row">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="form-row">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="width:100%">Se connecter</button>
            </div>
        </form>

        <p class="auth-footer">
            Pas encore de compte ? <a href="register.php">Créer un compte admin</a>
        </p>
        <p class="auth-footer" style="margin-top:6px;font-size:12.5px">
            <em>Compte de démo : <strong>admin</strong> / <strong>admin123</strong></em>
        </p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
