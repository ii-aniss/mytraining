<?php
require_once __DIR__ . '/includes/db.php';
session_start();
$pageTitle = "Modifier l'inscription";

// Sécurité : seul un admin connecté peut modifier
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: liste.php');
    exit;
}

$errors = [];

/* =====================================================
   Traitement (POST)
   ===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $cin    = trim($_POST['cin']    ?? '');
    $email  = trim($_POST['email']  ?? '');
    $niveau = trim($_POST['niveau'] ?? '');
    $selectedModules = array_values(array_unique(array_filter(array_map('intval', $_POST['modules'] ?? []))));

    if (!preg_match('/^[A-Za-zÀ-ÿ\' \-]+$/u', $nom))    $errors[] = "Nom invalide.";
    if (!preg_match('/^[A-Za-zÀ-ÿ\' \-]+$/u', $prenom)) $errors[] = "Prénom invalide.";
    if (!preg_match('/^\d{8}$/', $cin))                 $errors[] = "CIN invalide (8 chiffres).";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))     $errors[] = "Email invalide.";
    if (!in_array($niveau, ['Débutant', 'Intermédiaire', 'Avancé'], true)) $errors[] = "Niveau invalide.";
    if (count($selectedModules) === 0)  $errors[] = "Sélectionnez au moins un module.";
    if (count($selectedModules) > 2)    $errors[] = "Maximum 2 modules.";

    // Unicité (autre que soi-même)
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (cin = :c OR email = :e) AND id <> :id");
        $stmt->execute([':c' => $cin, ':e' => $email, ':id' => $id]);
        if ($stmt->fetch()) $errors[] = "Un autre utilisateur a déjà ce CIN ou cet email.";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE users SET nom=:n, prenom=:p, cin=:c, email=:e, niveau=:nv WHERE id=:id");
            $stmt->execute([':n' => $nom, ':p' => $prenom, ':c' => $cin, ':e' => $email, ':nv' => $niveau, ':id' => $id]);

            // Reset modules
            $pdo->prepare("DELETE FROM inscriptions WHERE user_id = :id")->execute([':id' => $id]);
            $stmtIns = $pdo->prepare("INSERT INTO inscriptions (user_id, module_id) VALUES (:u, :m)");
            foreach ($selectedModules as $mid) {
                $stmtIns->execute([':u' => $id, ':m' => $mid]);
            }
            $pdo->commit();
            header('Location: liste.php?msg=updated');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Erreur : " . $e->getMessage();
        }
    }

    // En cas d'erreur, on garde les valeurs saisies pour le réaffichage
    $user = [
        'id' => $id, 'nom' => $nom, 'prenom' => $prenom,
        'cin' => $cin, 'email' => $email, 'niveau' => $niveau
    ];
    $userModules = $selectedModules;
} else {
    /* =====================================================
       Chargement (GET)
       ===================================================== */
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: liste.php');
        exit;
    }
    $stmt = $pdo->prepare("SELECT module_id FROM inscriptions WHERE user_id = :id");
    $stmt->execute([':id' => $id]);
    $userModules = array_map('intval', array_column($stmt->fetchAll(), 'module_id'));
}

$modules = $pdo->query("SELECT * FROM modules ORDER BY nom_module")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero" style="text-align:left;margin-bottom:24px">
    <h1 style="font-size:28px">Modifier l'inscription</h1>
    <p>Mettez à jour les informations de <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>.</p>
</section>

<?php if ($errors): ?>
    <div class="alert alert-error">
        <strong>Erreurs :</strong>
        <ul style="margin-top:6px;padding-left:20px">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" onsubmit="return Verif();" novalidate>
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <div class="form-grid">
            <div class="form-row">
                <label for="nom">Nom <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>">
                <span class="error"></span>
            </div>
            <div class="form-row">
                <label for="prenom">Prénom <span class="required">*</span></label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>">
                <span class="error"></span>
            </div>
            <div class="form-row">
                <label for="cin">CIN <span class="required">*</span></label>
                <input type="text" id="cin" name="cin" maxlength="8" value="<?= htmlspecialchars($user['cin']) ?>">
                <span class="error"></span>
            </div>
            <div class="form-row">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                <span class="error"></span>
            </div>

            <div class="form-row full" id="niveau-group">
                <label>Niveau <span class="required">*</span></label>
                <div class="radio-group">
                    <?php foreach (['Débutant', 'Intermédiaire', 'Avancé'] as $i => $nv): ?>
                        <div class="radio-pill">
                            <input type="radio" id="nv<?= $i ?>" name="niveau" value="<?= $nv ?>" <?= $user['niveau'] === $nv ? 'checked' : '' ?>>
                            <label for="nv<?= $i ?>"><?= $nv ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <span class="error"></span>
            </div>

            <div class="form-row full" id="modules-group">
                <label>Modules de formation <span class="required">*</span></label>
                <div class="modules-grid">
                    <?php foreach ($modules as $m): ?>
                        <div class="module-card">
                            <input type="checkbox" id="mod-<?= (int)$m['id'] ?>" name="modules[]" value="<?= (int)$m['id'] ?>"
                                <?= in_array((int)$m['id'], $userModules, true) ? 'checked' : '' ?>>
                            <label for="mod-<?= (int)$m['id'] ?>">
                                <span class="check"></span>
                                <span class="name"><?= htmlspecialchars($m['nom_module']) ?></span>
                                <span class="desc"><?= htmlspecialchars($m['description']) ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="modules-info">
                    <strong><span id="modules-count">0</span></strong> / 2 modules sélectionnés.
                </p>
                <span class="error"></span>
            </div>
        </div>

        <div class="form-actions">
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
