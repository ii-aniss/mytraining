<?php
// ============================================
//  MyTraining — login.php
//  Authentification (BONUS — Sécurité)
// ============================================

require_once __DIR__ . '/php/config.php';
session_start();

$error = '';
$success = '';

// ---------- Si déjà connecté ----------
if (!empty($_SESSION['user_id'])) {
    header('Location: liste.php');
    exit;
}

// ---------- Traitement POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';

        if (!$email || !$mdp) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            $pdo  = getDB();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: liste.php');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }
    }

    if ($action === 'register') {
        $nom    = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $mdp    = $_POST['mot_de_passe'] ?? '';
        $cin    = trim($_POST['cin'] ?? '');
        $niveau = $_POST['niveau'] ?? 'débutant';

        if (!$nom || !$prenom || !$email || !$mdp || !$cin) {
            $error = "Tous les champs sont obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email invalide.";
        } elseif (!preg_match('/^\d{8}$/', $cin)) {
            $error = "CIN doit contenir 8 chiffres.";
        } elseif (strlen($mdp) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            $pdo  = getDB();
            $chk  = $pdo->prepare("SELECT id FROM users WHERE email = ? OR cin = ?");
            $chk->execute([$email, $cin]);
            if ($chk->fetch()) {
                $error = "Cet email ou CIN est déjà utilisé.";
            } else {
                $hash = password_hash($mdp, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins  = $pdo->prepare(
                    "INSERT INTO users (nom, prenom, cin, email, niveau, mot_de_passe) VALUES (?,?,?,?,?,?)"
                );
                $ins->execute([$nom, $prenom, $cin, $email, $niveau, $hash]);
                $success = "Compte créé ! Vous pouvez maintenant vous connecter.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyTraining — Connexion</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body class="auth-page">

  <div class="auth-card">
    <div class="form-header">
      <a href="index.html" class="logo" style="margin-bottom:1.5rem;display:inline-flex">
        <span class="logo-icon">▲</span> MyTraining
      </a>
      <h2 id="form-title">Connexion</h2>
      <p id="form-sub">Accédez à votre espace formation.</p>
    </div>

    <?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form id="login-form" method="POST">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label for="l-email">Email</label>
        <input type="email" id="l-email" name="email" placeholder="vous@exemple.com" required>
      </div>
      <div class="form-group">
        <label for="l-mdp">Mot de passe</label>
        <input type="password" id="l-mdp" name="mot_de_passe" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-submit btn-full">
        <span>Se connecter</span>
        <span class="btn-arrow">→</span>
      </button>
    </form>

    <!-- REGISTER FORM -->
    <form id="register-form" method="POST" style="display:none">
      <input type="hidden" name="action" value="register">
      <div class="form-row">
        <div class="form-group">
          <label for="r-nom">Nom</label>
          <input type="text" id="r-nom" name="nom" placeholder="Benali" required>
        </div>
        <div class="form-group">
          <label for="r-prenom">Prénom</label>
          <input type="text" id="r-prenom" name="prenom" placeholder="Yassine" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="r-cin">CIN</label>
          <input type="text" id="r-cin" name="cin" placeholder="12345678" maxlength="8" required>
        </div>
        <div class="form-group">
          <label for="r-niveau">Niveau</label>
          <select id="r-niveau" name="niveau" style="width:100%;padding:12px 16px;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);color:var(--text);font-family:var(--font);font-size:0.95rem">
            <option value="débutant">Débutant</option>
            <option value="intermédiaire">Intermédiaire</option>
            <option value="avancé">Avancé</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="r-email">Email</label>
        <input type="email" id="r-email" name="email" placeholder="vous@exemple.com" required>
      </div>
      <div class="form-group">
        <label for="r-mdp">Mot de passe</label>
        <input type="password" id="r-mdp" name="mot_de_passe" placeholder="Min. 6 caractères" required>
      </div>
      <button type="submit" class="btn-submit btn-full">
        <span>Créer un compte</span>
        <span class="btn-arrow">→</span>
      </button>
    </form>

    <div class="auth-divider">ou</div>
    <div class="auth-link">
      <span id="toggle-text">Pas de compte ?</span>
      <a href="#" id="toggle-btn" onclick="toggleForm(); return false;"> Créer un compte</a>
    </div>
  </div>

  <script>
  let isLogin = true;
  function toggleForm() {
    isLogin = !isLogin;
    document.getElementById('login-form').style.display    = isLogin ? '' : 'none';
    document.getElementById('register-form').style.display = isLogin ? 'none' : '';
    document.getElementById('form-title').textContent = isLogin ? 'Connexion' : 'Créer un compte';
    document.getElementById('form-sub').textContent   = isLogin ? 'Accédez à votre espace formation.' : 'Rejoignez la plateforme MyTraining.';
    document.getElementById('toggle-text').textContent = isLogin ? 'Pas de compte ?' : 'Déjà inscrit ?';
    document.getElementById('toggle-btn').textContent  = isLogin ? ' Créer un compte' : ' Se connecter';
  }
  </script>

</body>
</html>
