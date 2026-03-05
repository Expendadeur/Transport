<?php
/**
 * Add Agence
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$page_title = "Ajouter une Agence";
$error = '';
$success = '';

// Fetch Villes for selection
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_agence = $_POST['nom_agence'] ?? '';
    $tel_agence = $_POST['tel_agence'] ?? '';
    $id_vil = $_POST['id_vil'] ?? null;

    if (!empty($nom_agence)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO agence (nom_agence, tel_agence, id_vil) VALUES (?, ?, ?)");
            $stmt->execute([$nom_agence, $tel_agence, $id_vil]);
            
            $_SESSION['success'] = "L'agence a été ajoutée avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout: " . $e->getMessage();
        }
    } else {
        $error = "Le nom de l'agence est obligatoire.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour à la liste
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Ajouter une Nouvelle Agence</h1>
</div>

<div class="card" style="max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Nom de l'Agence <span style="color: #ef4444;">*</span></label>
            <input type="text" name="nom_agence" class="form-control" placeholder="Ex: Agence Gitega Centre" required>
        </div>

        <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="text" name="tel_agence" class="form-control" placeholder="Ex: +257 22 22 00 00">
        </div>

        <div class="form-group">
            <label class="form-label">Ville de Localisation</label>
            <select name="id_vil" class="form-control">
                <option value="">-- Sélectionner une ville --</option>
                <?php foreach ($villes as $ville): ?>
                    <option value="<?php echo $ville['idVil']; ?>"><?php echo htmlspecialchars($ville['nom']); ?></option>
                <?php endforeach; ?>
            </select>
            <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem;">
                Si la ville n'existe pas, elle doit être ajoutée dans le module Villes.
            </p>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer l'Agence</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
