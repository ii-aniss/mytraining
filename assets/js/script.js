/* =====================================================
   MyTraining — Validation côté client (JavaScript)
   ===================================================== */

/**
 * Affiche / cache un message d'erreur sur un champ
 */
function setError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const row = field.closest('.form-row');
    if (!row) return;
    const errEl = row.querySelector('.error');
    if (message) {
        row.classList.add('has-error');
        if (errEl) errEl.textContent = message;
    } else {
        row.classList.remove('has-error');
        if (errEl) errEl.textContent = '';
    }
}

/**
 * Fonction Verif() - validation complète du formulaire d'inscription
 */
function Verif() {
    let ok = true;

    // Récupération des champs
    const nom    = document.getElementById('nom');
    const prenom = document.getElementById('prenom');
    const cin    = document.getElementById('cin');
    const email  = document.getElementById('email');
    const niveau = document.querySelector('input[name="niveau"]:checked');
    const modules = document.querySelectorAll('input[name="modules[]"]:checked');

    // Reset
    ['nom', 'prenom', 'cin', 'email', 'niveau-group', 'modules-group'].forEach(id => setError(id, ''));

    // -- Nom : obligatoire + lettres uniquement
    if (!nom.value.trim()) {
        setError('nom', 'Le nom est obligatoire.');
        ok = false;
    } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(nom.value.trim())) {
        setError('nom', 'Le nom doit contenir uniquement des lettres.');
        ok = false;
    }

    // -- Prénom : obligatoire + lettres uniquement
    if (!prenom.value.trim()) {
        setError('prenom', 'Le prénom est obligatoire.');
        ok = false;
    } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(prenom.value.trim())) {
        setError('prenom', 'Le prénom doit contenir uniquement des lettres.');
        ok = false;
    }

    // -- CIN : 8 chiffres
    if (!cin.value.trim()) {
        setError('cin', 'Le CIN est obligatoire.');
        ok = false;
    } else if (!/^\d{8}$/.test(cin.value.trim())) {
        setError('cin', 'Le CIN doit contenir exactement 8 chiffres.');
        ok = false;
    }

    // -- Email valide
    if (!email.value.trim()) {
        setError('email', "L'email est obligatoire.");
        ok = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
        setError('email', "L'email n'est pas valide.");
        ok = false;
    }

    // -- Niveau obligatoire
    if (!niveau) {
        setError('niveau-group', 'Veuillez sélectionner un niveau.');
        ok = false;
    }

    // -- Modules : entre 1 et 2
    if (modules.length === 0) {
        setError('modules-group', 'Sélectionnez au moins un module.');
        ok = false;
    } else if (modules.length > 2) {
        setError('modules-group', 'Vous ne pouvez sélectionner que 2 modules au maximum.');
        ok = false;
    }

    return ok;
}

/* =====================================================
   Comportement dynamique des modules : limite à 2
   ===================================================== */
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[name="modules[]"]');
    const counter    = document.getElementById('modules-count');

    function updateUI() {
        const checked = document.querySelectorAll('input[name="modules[]"]:checked');
        if (counter) counter.textContent = checked.length;

        // Désactive les autres si on a déjà 2 cochés
        if (checked.length >= 2) {
            checkboxes.forEach(cb => { if (!cb.checked) cb.disabled = true; });
        } else {
            checkboxes.forEach(cb => { cb.disabled = false; });
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateUI));
    updateUI();

    /* ============== Recherche dynamique tableau ============== */
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.data-table tbody tr');
            let visible = 0;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(q)) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });
            const counterEl = document.getElementById('rowCount');
            if (counterEl) counterEl.textContent = visible;
        });
    }

    /* ============== Confirm avant suppression ============== */
    document.querySelectorAll('a[data-confirm]').forEach(link => {
        link.addEventListener('click', function (e) {
            const msg = this.getAttribute('data-confirm') || 'Confirmer cette action ?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
});
