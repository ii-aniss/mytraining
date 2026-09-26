<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = "Inscription";

// Charger les modules depuis la BD pour les afficher dynamiquement
$modules = $pdo->query("SELECT * FROM modules ORDER BY nom_module")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <span class="hero-eyebrow">Formation en ligne</span>
    <h1>Inscrivez-vous à vos modules</h1>
    <p>Créez votre compte étudiant, choisissez jusqu'à 2 modules de formation et démarrez votre apprentissage avec MyTraining.</p>
</section>

<?php if (!empty($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success"><strong>Inscription supprimée.</strong> Les données ont été retirées de la base.</div>
<?php endif; ?>

<div class="card">
    <h2 class="card-title">Formulaire d'inscription</h2>
    <p class="card-subtitle">Tous les champs marqués d'une <span style="color:#ef4444">*</span> sont obligatoires.</p>

    <form action="traitement.php" method="POST" onsubmit="return Verif();" novalidate>

        <div class="form-grid">

            <div class="form-row">
                <label for="nom">Nom <span class="required">*</span></label>
                <input type="text" id="nom" name="nom" placeholder="Ex : Laraba" autocomplete="family-name">
                <span class="error"></span>
            </div>

            <div class="form-row">
                <label for="prenom">Prénom <span class="required">*</span></label>
                <input type="text" id="prenom" name="prenom" placeholder="Ex : Anis" autocomplete="given-name">
                <span class="error"></span>
            </div>

            <div class="form-row">
                <label for="cin">CIN <span class="required">*</span></label>
                <input type="text" id="cin" name="cin" placeholder="8 chiffres" maxlength="8" inputmode="numeric">
                <span class="hint">Exactement 8 chiffres.</span>
                <span class="error"></span>
            </div>

            <div class="form-row">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" placeholder="exemple@domaine.com" autocomplete="email">
                <span class="error"></span>
            </div>

            <div class="form-row full" id="niveau-group">
                <label>Niveau <span class="required">*</span></label>
                <div class="radio-group">
                    <div class="radio-pill">
                        <input type="radio" id="n1" name="niveau" value="Débutant">
                        <label for="n1">Débutant</label>
                    </div>
                    <div class="radio-pill">
                        <input type="radio" id="n2" name="niveau" value="Intermédiaire">
                        <label for="n2">Intermédiaire</label>
                    </div>
                    <div class="radio-pill">
                        <input type="radio" id="n3" name="niveau" value="Avancé">
                        <label for="n3">Avancé</label>
                    </div>
                </div>
                <span class="error"></span>
            </div>

            <div class="form-row full" id="modules-group">
                <label>Modules de formation <span class="required">*</span></label>
                <div class="modules-grid">
                    <?php foreach ($modules as $m): ?>
                        <div class="module-card">
                            <input type="checkbox" id="mod-<?= (int)$m['id'] ?>" name="modules[]" value="<?= (int)$m['id'] ?>">
                            <label for="mod-<?= (int)$m['id'] ?>">
                                <span class="check"></span>
                                <span class="name"><?= htmlspecialchars($m['nom_module']) ?></span>
                                <span class="desc"><?= htmlspecialchars($m['description']) ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="modules-info">
                    <strong><span id="modules-count">0</span></strong> / 2 modules sélectionnés (maximum 2).
                </p>
                <span class="error"></span>
            </div>

        </div>

        <div class="form-actions">
            <button type="reset"  class="btn btn-secondary">Réinitialiser</button>
            <button type="submit" class="btn btn-primary">Valider mon inscription</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
