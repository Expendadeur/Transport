<?php
/**
 * Add Client
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Ajouter un Client";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomP = $_POST['nomP'] ?? '';
    $prenomP = $_POST['prenomP'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $age = $_POST['age'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    
    // Address info
    $pays = $_POST['pays'] ?? 'Burundi';
    $province = $_POST['province'] ?? '';
    $commune = $_POST['commune'] ?? '';

    if (!empty($nomP) && !empty($prenomP) && !empty($telephone)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Create Address
            $stmt_adr = $pdo->prepare("INSERT INTO adresse (pays, province, commune) VALUES (?, ?, ?)");
            $stmt_adr->execute([$pays, $province, $commune]);
            $id_adr = $pdo->lastInsertId();
            
            // 2. Create Passager
            $stmt = $pdo->prepare("INSERT INTO passager (nomP, prenomP, genre, age, telephone, id_Adres) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nomP, $prenomP, $genre, $age, $telephone, $id_adr]);
            
            $pdo->commit();
            
            $_SESSION['success'] = "Le client a été ajouté avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur lors de l'ajout: " . $e->getMessage();
        }
    } else {
        $error = "Nom, Prénom et Téléphone sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouveau Client</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <h4 style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">Informations Personnelles</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nom <span style="color: #ef4444;">*</span></label>
                <input type="text" name="nomP" class="form-control" placeholder="Nom du client" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom <span style="color: #ef4444;">*</span></label>
                <input type="text" name="prenomP" class="form-control" placeholder="Prénom du client" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Genre</label>
                <select name="genre" class="form-control">
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Âge</label>
                <input type="number" name="age" class="form-control" placeholder="Ex: 25">
            </div>
            <div class="form-group">
                <label class="form-label">Téléphone <span style="color: #ef4444;">*</span></label>
                <input type="text" name="telephone" class="form-control" placeholder="Ex: 68123456" required>
            </div>
        </div>

        <h4 style="margin: 2rem 0 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">Localisation</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Pays</label>
                <input type="text" name="pays" class="form-control" value="Burundi">
            </div>
            <div class="form-group">
                <label class="form-label">Province</label>
                <input type="text" name="province" class="form-control" placeholder="Ex: Bujumbura">
            </div>
            <div class="form-group">
                <label class="form-label">Commune</label>
                <input type="text" name="commune" class="form-control" placeholder="Ex: Mukaza">
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer le Client</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
