<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = "Confirmation";

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/* =====================================================
   Récupération + nettoyage des données
   ===================================================== */
$nom    = trim($_POST['nom']    ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$cin    = trim($_POST['cin']    ?? '');
$email  = trim($_POST['email']  ?? '');
$niveau = trim($_POST['niveau'] ?? '');
$selectedModules = $_POST['modules'] ?? [];

if (!is_array($selectedModules)) {
    $selectedModules = [];
}
$selectedModules = array_values(array_unique(array_filter(array_map('intval', $selectedModules))));

/* =====================================================
   Validation côté serveur (sécurité)
   ===================================================== */
$errors = [];

if ($nom === '' || !preg_match('/^[A-Za-zÀ-ÿ\' \-]+$/u', $nom)) {
    $errors[] = "Nom invalide (lettres uniquement).";
}
if ($prenom === '' || !preg_match('/^[A-Za-zÀ-ÿ\' \-]+$/u', $prenom)) {
    $errors[] = "Prénom invalide (lettres uniquement).";
}
if (!preg_match('/^\d{8}$/', $cin)) {
    $errors[] = "Le CIN doit contenir exactement 8 chiffres.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email invalide.";
}
if (!in_array($niveau, ['Débutant', 'Intermédiaire', 'Avancé'], true)) {
    $errors[] = "Niveau invalide.";
}
if (count($selectedModules) === 0) {
    $errors[] = "Sélectionnez au moins un module.";
} elseif (count($selectedModules) > 2) {
    $errors[] = "Vous ne pouvez sélectionner que 2 modules maximum.";
}

include __DIR__ . '/includes/header.php';

if ($errors) {
    echo '<div class="alert alert-error"><strong>Le formulaire contient des erreurs :</strong>';
    echo '<ul style="margin-top:8px;padding-left:20px">';
    foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>';
    echo '</ul></div>';
    echo '<p><a href="index.php" class="btn btn-secondary">← Retour au formulaire</a></p>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* =====================================================
   Vérifier l'unicité (CIN + email)
   ===================================================== */
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE cin = :cin OR email = :email LIMIT 1");
    $stmt->execute([':cin' => $cin, ':email' => $email]);
    if ($stmt->fetch()) {
        echo '<div class="alert alert-warning"><strong>Utilisateur déjà inscrit.</strong> Un compte existe déjà avec ce CIN ou cet email.</div>';
        echo '<p><a href="index.php" class="btn btn-secondary">← Retour</a> &nbsp; <a href="liste.php" class="btn btn-primary">Voir la liste des inscrits</a></p>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-error"><strong>Erreur :</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* =====================================================
   Vérifier que les modules existent vraiment
   ===================================================== */
$placeholders = implode(',', array_fill(0, count($selectedModules), '?'));
$stmt = $pdo->prepare("SELECT id, nom_module FROM modules WHERE id IN ($placeholders)");
$stmt->execute($selectedModules);
$validModules = $stmt->fetchAll();

if (count($validModules) !== count($selectedModules)) {
    echo '<div class="alert alert-error"><strong>Erreur :</strong> certains modules sélectionnés n\'existent pas.</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* =====================================================
   Insertion en base (transaction)
   ===================================================== */
try {
    $pdo->beginTransaction();

    // 1. Insertion utilisateur
    $stmt = $pdo->prepare("
        INSERT INTO users (nom, prenom, cin, email, niveau)
        VALUES (:nom, :prenom, :cin, :email, :niveau)
    ");
    $stmt->execute([
        ':nom'    => $nom,
        ':prenom' => $prenom,
        ':cin'    => $cin,
        ':email'  => $email,
        ':niveau' => $niveau,
    ]);
    $userId = (int)$pdo->lastInsertId();

    // 2. Insertion des inscriptions
    $stmt = $pdo->prepare("INSERT INTO inscriptions (user_id, module_id) VALUES (:uid, :mid)");
    foreach ($validModules as $m) {
        $stmt->execute([':uid' => $userId, ':mid' => $m['id']]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    echo '<div class="alert alert-error"><strong>Erreur lors de l\'enregistrement :</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* =====================================================
   Affichage de la confirmation
   ===================================================== */
?>

<div class="alert alert-success">
    <strong>Inscription enregistrée avec succès !</strong>
    Bienvenue sur MyTraining, <?= htmlspecialchars($prenom . ' ' . $nom) ?>. Vos modules ont bien été enregistrés.
</div>

<div class="card">
    <h2 class="card-title">Récapitulatif</h2>
    <p class="card-subtitle">Voici les informations que nous avons enregistrées :</p>

    <table class="data-table" style="border:1px solid var(--color-border);border-radius:12px;overflow:hidden">
        <tbody>
            <tr><th style="width:200px">Nom complet</th><td><?= htmlspecialchars($prenom . ' ' . $nom) ?></td></tr>
            <tr><th>CIN</th><td><?= htmlspecialchars($cin) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($email) ?></td></tr>
            <tr><th>Niveau</th><td><span class="badge badge-niveau"><?= htmlspecialchars($niveau) ?></span></td></tr>
            <tr>
                <th>Modules choisis</th>
                <td>
                    <?php foreach ($validModules as $m): ?>
                        <span class="badge"><?= htmlspecialchars($m['nom_module']) ?></span>
                    <?php endforeach; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="form-actions">
        <a href="index.php"  class="btn btn-secondary">Nouvelle inscription</a>
        <a href="liste.php"  class="btn btn-primary">Voir tous les inscrits</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
