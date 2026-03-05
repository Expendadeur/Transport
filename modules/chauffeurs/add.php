<?php
/**
 * Add Chauffeur
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Ajouter un Chauffeur";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $num_permis = $_POST['num_permis'] ?? '';
    $categorie_permis = $_POST['categorie_permis'] ?? '';

    if (!empty($nom) && !empty($telephone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO chauffeur (nom, prenom, telephone, num_permis, categorie_permis) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $telephone, $num_permis, $categorie_permis]);
            
            $_SESSION['success'] = "Le chauffeur a été enregistré avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de l'enregistrement: " . $e->getMessage();
        }
    } else {
        $error = "Le nom et le téléphone sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouveau Chauffeur</h1>
</div>

<div class="card" style="max-width: 700px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nom <span style="color: #ef4444;">*</span></label>
                <input type="text" name="nom" class="form-control" placeholder="Nom de famille" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control" placeholder="Prénom">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Téléphone <span style="color: #ef4444;">*</span></label>
                <input type="text" name="telephone" class="form-control" placeholder="Ex: +257 69..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Numéro de Permis</label>
                <input type="text" name="num_permis" class="form-control" placeholder="Ex: A12345/BUJ">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Catégorie du Permis</label>
            <input type="text" name="categorie_permis" class="form-control" placeholder="Ex: B, C, D, E">
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer le chauffeur</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
