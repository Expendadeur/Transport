<?php
/**
 * Edit Client
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier le Client";
$error = '';

// Fetch client and address
$stmt = $pdo->prepare("SELECT p.*, a.pays, a.province, a.commune FROM passager p LEFT JOIN adresse a ON p.id_Adres = a.idAdr WHERE p.idP = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header("Location: index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomP = $_POST['nomP'] ?? '';
    $prenomP = $_POST['prenomP'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $age = $_POST['age'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    
    $pays = $_POST['pays'] ?? '';
    $province = $_POST['province'] ?? '';
    $commune = $_POST['commune'] ?? '';

    if (!empty($nomP) && !empty($prenomP) && !empty($telephone)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Update/Create Address
            if ($p['id_Adres']) {
                $stmt_adr = $pdo->prepare("UPDATE adresse SET pays=?, province=?, commune=? WHERE idAdr=?");
                $stmt_adr->execute([$pays, $province, $commune, $p['id_Adres']]);
                $id_adr = $p['id_Adres'];
            } else {
                $stmt_adr = $pdo->prepare("INSERT INTO adresse (pays, province, commune) VALUES (?, ?, ?)");
                $stmt_adr->execute([$pays, $province, $commune]);
                $id_adr = $pdo->lastInsertId();
            }
            
            // 2. Update Passager
            $stmt = $pdo->prepare("UPDATE passager SET nomP=?, prenomP=?, genre=?, age=?, telephone=?, id_Adres=? WHERE idP=?");
            $stmt->execute([$nomP, $prenomP, $genre, $age, $telephone, $id_adr, $id]);
            
            $pdo->commit();
            $_SESSION['success'] = "Les informations du client ont été mises à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
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
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier Client : <?php echo htmlspecialchars($p['prenomP'].' '.$p['nomP']); ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="" method="POST">
        <h4 style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">Informations Personnelles</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input type="text" name="nomP" class="form-control" value="<?php echo htmlspecialchars($p['nomP']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenomP" class="form-control" value="<?php echo htmlspecialchars($p['prenomP']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Genre</label>
                <select name="genre" class="form-control">
                    <option value="Homme" <?php echo $p['genre'] == 'Homme' ? 'selected' : ''; ?>>Homme</option>
                    <option value="Femme" <?php echo $p['genre'] == 'Femme' ? 'selected' : ''; ?>>Femme</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Âge</label>
                <input type="number" name="age" class="form-control" value="<?php echo $p['age']; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($p['telephone']); ?>" required>
            </div>
        </div>

        <h4 style="margin: 2rem 0 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">Localisation</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Pays</label>
                <input type="text" name="pays" class="form-control" value="<?php echo htmlspecialchars($p['pays']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Province</label>
                <input type="text" name="province" class="form-control" value="<?php echo htmlspecialchars($p['province']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Commune</label>
                <input type="text" name="commune" class="form-control" value="<?php echo htmlspecialchars($p['commune']); ?>">
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
