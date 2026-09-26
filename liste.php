<?php
require_once __DIR__ . '/includes/db.php';
session_start();
$pageTitle = "Liste des inscrits";

// Récupérer tous les utilisateurs avec leurs modules (jointure SQL)
$sql = "
    SELECT
        u.id, u.nom, u.prenom, u.cin, u.email, u.niveau, u.cree_le,
        GROUP_CONCAT(m.nom_module ORDER BY m.nom_module SEPARATOR '||') AS modules
    FROM users u
    LEFT JOIN inscriptions i ON i.user_id   = u.id
    LEFT JOIN modules      m ON m.id        = i.module_id
    GROUP BY u.id
    ORDER BY u.cree_le DESC
";
$users = $pdo->query($sql)->fetchAll();

include __DIR__ . '/includes/header.php';

$isAdmin = isset($_SESSION['admin']);
?>

<section class="hero" style="text-align:left;margin-bottom:24px">
    <h1 style="font-size:28px">Liste des inscrits</h1>
    <p>Toutes les personnes inscrites sur MyTraining et les modules qu'elles suivent.</p>
</section>

<?php if (!empty($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success"><strong>Modification enregistrée.</strong></div>
<?php endif; ?>
<?php if (!empty($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success"><strong>Inscription supprimée.</strong></div>
<?php endif; ?>

<div class="table-wrapper">
    <div class="table-toolbar">
        <div class="count">
            <strong id="rowCount"><?= count($users) ?></strong> inscrit<?= count($users) > 1 ? 's' : '' ?>
        </div>
        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Rechercher (nom, email, CIN, module...)">
    </div>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Aucun inscrit pour le moment</h3>
            <p>Les inscriptions apparaîtront ici dès qu'un utilisateur s'inscrit.</p>
            <p style="margin-top:16px"><a href="index.php" class="btn btn-primary">Créer une inscription</a></p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>CIN</th>
                    <th>Niveau</th>
                    <th>Modules</th>
                    <th>Inscrit le</th>
                    <?php if ($isAdmin): ?><th style="text-align:right">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u):
                    $initials = strtoupper(mb_substr($u['prenom'], 0, 1) . mb_substr($u['nom'], 0, 1));
                    $modulesArr = $u['modules'] ? explode('||', $u['modules']) : [];
                ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="avatar"><?= htmlspecialchars($initials) ?></div>
                                <div class="user-meta">
                                    <strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong>
                                    <span><?= htmlspecialchars($u['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['cin']) ?></td>
                        <td><span class="badge badge-niveau"><?= htmlspecialchars($u['niveau']) ?></span></td>
                        <td>
                            <?php if ($modulesArr): ?>
                                <?php foreach ($modulesArr as $mod): ?>
                                    <span class="badge"><?= htmlspecialchars($mod) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge badge-empty">Aucun</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--color-muted);font-size:13.5px">
                            <?= date('d/m/Y', strtotime($u['cree_le'])) ?>
                        </td>
                        <?php if ($isAdmin): ?>
                            <td style="text-align:right;white-space:nowrap">
                                <a href="modifier.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-secondary">Modifier</a>
                                <a href="supprimer.php?id=<?= (int)$u['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   data-confirm="Voulez-vous vraiment supprimer <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?> ?">
                                   Supprimer
                                </a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!$isAdmin): ?>
            <div style="padding:14px 22px;background:var(--color-bg);font-size:13.5px;color:var(--color-muted);border-top:1px solid var(--color-border)">
                Connectez-vous en tant qu'<a href="login.php"><strong>administrateur</strong></a> pour modifier ou supprimer des inscriptions.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
