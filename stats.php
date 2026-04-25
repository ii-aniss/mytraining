<?php
// ============================================
//  MyTraining — stats.php
//  Statistiques de la plateforme
// ============================================

require_once __DIR__ . '/php/config.php';

$pdo = getDB();

// Total users
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Total inscriptions
$totalIns = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();

// Répartition par niveau
$niveaux = $pdo->query("
    SELECT niveau, COUNT(*) as nb
    FROM users
    GROUP BY niveau
    ORDER BY FIELD(niveau,'débutant','intermédiaire','avancé')
")->fetchAll();

// Modules les plus populaires
$moduleStats = $pdo->query("
    SELECT m.nom_module, m.tag, COUNT(i.id) as nb
    FROM modules m
    LEFT JOIN inscriptions i ON i.module_id = m.id
    GROUP BY m.id
    ORDER BY nb DESC
")->fetchAll();

$maxModule = max(array_column($moduleStats, 'nb') ?: [1]);

// Nouveaux inscrits (7 derniers jours)
$nouveaux = $pdo->query("
    SELECT COUNT(*) FROM users
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyTraining — Statistiques</title>
  <link rel="stylesheet" href="css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="index.html" class="logo"><span class="logo-icon">▲</span><span>MyTraining</span></a>
    <div class="nav-links">
      <a href="index.html" class="nav-link">Inscription</a>
      <a href="liste.php" class="nav-link">Participants</a>
      <a href="stats.php" class="nav-link active">Statistiques</a>
      <a href="login.php" class="nav-link btn-nav">Connexion</a>
    </div>
  </div>
</nav>

<div class="page-header">
  <h1>Tableau de bord</h1>
  <p>Statistiques et indicateurs de la plateforme MyTraining.</p>
</div>

<div class="page-content">

  <!-- KPIs -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Participants total</div>
      <div class="stat-value stat-accent"><?= (int)$totalUsers ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Inscriptions modules</div>
      <div class="stat-value"><?= (int)$totalIns ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Nouveaux (7 jours)</div>
      <div class="stat-value stat-accent"><?= (int)$nouveaux ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Modules disponibles</div>
      <div class="stat-value"><?= count($moduleStats) ?></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;flex-wrap:wrap">

    <!-- Répartition par niveau -->
    <div class="table-card" style="padding:1.5rem">
      <h3 style="font-family:var(--font-display);font-size:1.1rem;margin-bottom:1.5rem">Répartition par niveau</h3>
      <?php foreach ($niveaux as $n):
        $pct = $totalUsers > 0 ? round($n['nb'] / $totalUsers * 100) : 0;
        $colors = ['débutant'=>'#34d399','intermédiaire'=>'#fbbf24','avancé'=>'#7c6ff7'];
        $c = $colors[$n['niveau']] ?? '#7c6ff7';
      ?>
      <div class="bar-row" style="margin-bottom:1rem">
        <div class="bar-label-row">
          <span style="text-transform:capitalize;font-size:0.9rem"><?= htmlspecialchars($n['niveau']) ?></span>
          <span style="color:var(--text3);font-size:0.85rem"><?= $n['nb'] ?> (<?= $pct ?>%)</span>
        </div>
        <div class="bar-track">
          <div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $c ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($niveaux)): ?>
        <p style="color:var(--text3);font-size:0.9rem">Aucune donnée disponible.</p>
      <?php endif; ?>
    </div>

    <!-- Popularité des modules -->
    <div class="table-card" style="padding:1.5rem">
      <h3 style="font-family:var(--font-display);font-size:1.1rem;margin-bottom:1.5rem">Modules populaires</h3>
      <?php foreach ($moduleStats as $m):
        $pct = $maxModule > 0 ? round($m['nb'] / $maxModule * 100) : 0;
      ?>
      <div class="bar-row" style="margin-bottom:1rem">
        <div class="bar-label-row">
          <span style="font-size:0.9rem"><?= htmlspecialchars($m['nom_module']) ?></span>
          <span style="color:var(--text3);font-size:0.85rem"><?= $m['nb'] ?> inscription<?= $m['nb'] != 1 ? 's' : '' ?></span>
        </div>
        <div class="bar-track">
          <div class="bar-fill" style="width:<?= $pct ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>

  <!-- Tableau détaillé modules -->
  <div class="table-card" style="margin-top:1.5rem">
    <div class="table-toolbar">
      <span style="font-weight:500;font-size:0.95rem">Détail par module</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>Module</th>
          <th>Catégorie</th>
          <th>Inscriptions</th>
          <th>Popularité</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($moduleStats as $m):
          $pct = $maxModule > 0 ? round($m['nb'] / $maxModule * 100) : 0;
        ?>
        <tr>
          <td style="font-weight:500"><?= htmlspecialchars($m['nom_module']) ?></td>
          <td><span class="module-pill"><?= htmlspecialchars($m['tag']) ?></span></td>
          <td><?= (int)$m['nb'] ?></td>
          <td style="min-width:120px">
            <div class="bar-track">
              <div class="bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<footer class="footer">
  <div class="footer-inner">
    <span class="logo"><span class="logo-icon">▲</span> MyTraining</span>
    <span>Plateforme de formation en ligne &copy; 2025</span>
  </div>
</footer>

</body>
</html>
