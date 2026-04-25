<?php
// ============================================
//  MyTraining — liste.php
//  Liste des participants et leurs modules
// ============================================

require_once __DIR__ . '/php/config.php';
session_start();

$pdo = getDB();

// ---------- Suppression (BONUS) ----------
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $_SESSION['flash_msg']  = "Inscription supprimée.";
    $_SESSION['flash_type'] = 'success';
    header('Location: liste.php');
    exit;
}

// ---------- Recherche ----------
$search = htmlspecialchars(trim($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8');

// ---------- Récupérer les utilisateurs + modules ----------
$sql = "
    SELECT
        u.id,
        u.nom,
        u.prenom,
        u.cin,
        u.email,
        u.niveau,
        u.created_at,
        GROUP_CONCAT(m.nom_module ORDER BY m.id SEPARATOR '||') AS modules_list
    FROM users u
    LEFT JOIN inscriptions i ON i.user_id = u.id
    LEFT JOIN modules m ON m.id = i.module_id
";
$params = [];
if ($search) {
    $sql .= " WHERE u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.cin LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}
$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$totalUsers = count($users);

// Flash message
$flash     = $_SESSION['flash_msg']  ?? '';
$flashType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyTraining — Participants</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="index.html" class="logo"><span class="logo-icon">▲</span><span>MyTraining</span></a>
    <div class="nav-links">
      <a href="index.html" class="nav-link">Inscription</a>
      <a href="liste.php" class="nav-link active">Participants</a>
      <a href="stats.php" class="nav-link">Statistiques</a>
      <a href="login.php" class="nav-link btn-nav">Connexion</a>
    </div>
  </div>
</nav>

<div class="page-header">
  <h1>Participants inscrits</h1>
  <p>Liste complète des utilisateurs et leurs modules de formation.</p>
</div>

<div class="page-content">

  <?php if ($flash): ?>
  <div class="alert <?= htmlspecialchars($flashType) ?>" style="margin-bottom:1.5rem">
    <?= htmlspecialchars($flash) ?>
  </div>
  <?php endif; ?>

  <div class="table-card">
    <div class="table-toolbar">
      <form method="GET" action="liste.php" style="display:flex;gap:10px;flex:1;max-width:400px">
        <div class="search-box" style="flex:1">
          <span class="search-icon">🔍</span>
          <input type="search" name="q" placeholder="Rechercher nom, email, CIN…"
                 value="<?= htmlspecialchars($search) ?>"
                 onchange="this.form.submit()">
        </div>
        <?php if ($search): ?>
          <a href="liste.php" style="padding:8px 14px;border:1px solid var(--border);border-radius:var(--radius);color:var(--text2);text-decoration:none;font-size:0.85rem;display:flex;align-items:center">✕</a>
        <?php endif; ?>
      </form>
      <span class="badge-count"><?= $totalUsers ?> participant<?= $totalUsers !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($users)): ?>
      <div class="empty-state">
        <div class="empty-icon">📋</div>
        <p><?= $search ? "Aucun résultat pour « $search »." : "Aucun participant inscrit pour l'instant." ?></p>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Participant</th>
          <th>CIN</th>
          <th>Niveau</th>
          <th>Modules</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $i => $user):
            $initials = strtoupper(mb_substr($user['nom'], 0, 1) . mb_substr($user['prenom'], 0, 1));
            $mods = $user['modules_list'] ? explode('||', $user['modules_list']) : [];
            $niveauClass = 'niveau-' . strtolower($user['niveau']);
            $date = date('d/m/Y', strtotime($user['created_at']));
        ?>
        <tr>
          <td style="color:var(--text3);font-size:0.8rem"><?= $i + 1 ?></td>
          <td>
            <div class="user-cell">
              <div class="avatar"><?= htmlspecialchars($initials) ?></div>
              <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
              </div>
            </div>
          </td>
          <td style="font-family:monospace;letter-spacing:0.05em"><?= htmlspecialchars($user['cin']) ?></td>
          <td><span class="niveau-badge <?= $niveauClass ?>"><?= htmlspecialchars($user['niveau']) ?></span></td>
          <td>
            <?php if (empty($mods)): ?>
              <span style="color:var(--text3);font-size:0.82rem">Aucun</span>
            <?php else: ?>
              <?php foreach ($mods as $m): ?>
                <span class="module-pill"><?= htmlspecialchars($m) ?></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </td>
          <td style="color:var(--text3);font-size:0.85rem"><?= $date ?></td>
          <td>
            <button class="btn-delete"
              onclick="confirmDelete(<?= (int)$user['id'] ?>, '<?= htmlspecialchars($user['prenom'].' '.$user['nom'], ENT_QUOTES) ?>')">
              Supprimer
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>

<footer class="footer">
  <div class="footer-inner">
    <span class="logo"><span class="logo-icon">▲</span> MyTraining</span>
    <span>Plateforme de formation en ligne &copy; 2025</span>
  </div>
</footer>

<script>
function confirmDelete(id, name) {
  if (confirm('Supprimer l\'inscription de ' + name + ' ?\nCette action est irréversible.')) {
    window.location.href = 'liste.php?delete=' + id;
  }
}

// Recherche en temps réel
const searchInput = document.querySelector('input[name="q"]');
if (searchInput) {
  let timeout;
  searchInput.removeAttribute('onchange');
  searchInput.addEventListener('input', function() {
    clearTimeout(timeout);
    timeout = setTimeout(() => this.form.submit(), 400);
  });
}
</script>

</body>
</html>
