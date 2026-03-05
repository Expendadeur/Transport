<?php
/**
 * Edit Chauffeur
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier le Chauffeur";
$error = '';

$stmt = $pdo->prepare("SELECT * FROM chauffeur WHERE id_Chauff = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header("Location: index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $num_permis = $_POST['num_permis'] ?? '';
    $categorie_permis = $_POST['categorie_permis'] ?? '';

    if (!empty($nom) && !empty($telephone)) {
        try {
            $stmt = $pdo->prepare("UPDATE chauffeur SET nom=?, prenom=?, telephone=?, num_permis=?, categorie_permis=? WHERE id_Chauff=?");
            $stmt->execute([$nom, $prenom, $telephone, $num_permis, $categorie_permis, $id]);
            
            $_SESSION['success'] = "Le chauffeur a été mis à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Nom et téléphone sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier : <?php echo htmlspecialchars($c['nom'].' '.$c['prenom']); ?></h1>
</div>

<div class="card" style="max-width: 700px;">
    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($c['nom']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($c['prenom']); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($c['telephone']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Numéro de Permis</label>
                <input type="text" name="num_permis" class="form-control" value="<?php echo htmlspecialchars($c['num_permis']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Catégorie du Permis</label>
            <input type="text" name="categorie_permis" class="form-control" value="<?php echo htmlspecialchars($c['categorie_permis']); ?>">
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
