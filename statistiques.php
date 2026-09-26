<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = "Statistiques";

// Stats globales
$totalUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalModules = (int)$pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();
$totalInsc    = (int)$pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();

// Nb d'utilisateurs par module
$sql = "
    SELECT m.nom_module, COUNT(i.id) AS nb
    FROM modules m
    LEFT JOIN inscriptions i ON i.module_id = m.id
    GROUP BY m.id
    ORDER BY nb DESC, m.nom_module
";
$stats = $pdo->query($sql)->fetchAll();
$max = 0;
foreach ($stats as $s) { if ((int)$s['nb'] > $max) $max = (int)$s['nb']; }

// Stats par niveau
$niveaux = $pdo->query("SELECT niveau, COUNT(*) AS nb FROM users GROUP BY niveau ORDER BY nb DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero" style="text-align:left;margin-bottom:24px">
    <h1 style="font-size:28px">Statistiques de la plateforme</h1>
    <p>Vue d'ensemble des inscriptions et de la popularité des modules.</p>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Étudiants inscrits</div>
        <div class="value"><?= $totalUsers ?></div>
        <div class="delta">Tous niveaux confondus</div>
    </div>
    <div class="stat-card">
        <div class="label">Modules disponibles</div>
        <div class="value"><?= $totalModules ?></div>
        <div class="delta">Catalogue MyTraining</div>
    </div>
    <div class="stat-card">
        <div class="label">Inscriptions totales</div>
        <div class="value"><?= $totalInsc ?></div>
        <div class="delta">Toutes formations</div>
    </div>
    <div class="stat-card">
        <div class="label">Moyenne / étudiant</div>
        <div class="value"><?= $totalUsers > 0 ? number_format($totalInsc / $totalUsers, 1, ',', ' ') : '0' ?></div>
        <div class="delta">Modules par personne</div>
    </div>
</div>

<div class="card" style="margin-bottom:24px">
    <h2 class="card-title">Nombre d'étudiants par module</h2>
    <p class="card-subtitle">Popularité de chaque formation au sein de la plateforme.</p>

    <?php if ($max === 0): ?>
        <div class="empty-state" style="padding:30px">
            <p>Aucune inscription pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="bar-chart">
            <?php foreach ($stats as $s):
                $pct = $max > 0 ? round(((int)$s['nb'] / $max) * 100) : 0;
            ?>
                <div class="bar-row">
                    <div class="bar-label"><?= htmlspecialchars($s['nom_module']) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?= $pct ?>%"></div>
                    </div>
                    <div class="bar-value"><?= (int)$s['nb'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2 class="card-title">Répartition par niveau</h2>
    <p class="card-subtitle">Niveau déclaré par les étudiants à l'inscription.</p>

    <?php if (empty($niveaux)): ?>
        <div class="empty-state" style="padding:30px"><p>Aucune donnée.</p></div>
    <?php else: ?>
        <div class="bar-chart">
            <?php
            $maxN = 0;
            foreach ($niveaux as $n) if ((int)$n['nb'] > $maxN) $maxN = (int)$n['nb'];
            foreach ($niveaux as $n):
                $pct = $maxN > 0 ? round(((int)$n['nb'] / $maxN) * 100) : 0;
            ?>
                <div class="bar-row">
                    <div class="bar-label"><?= htmlspecialchars($n['niveau']) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: <?= $pct ?>%"></div>
                    </div>
                    <div class="bar-value"><?= (int)$n['nb'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
