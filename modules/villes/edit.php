<?php
/**
 * Edit Ville
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier la Ville";
$error = '';

$stmt = $pdo->prepare("SELECT * FROM ville WHERE idVil = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();
if (!$v) { header("Location: index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $station = $_POST['station'] ?? '';

    if (!empty($nom)) {
        try {
            $stmt = $pdo->prepare("UPDATE ville SET nom=?, station=? WHERE idVil=?");
            $stmt->execute([$nom, $station, $id]);
            
            $_SESSION['success'] = "La ville a été mise à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Le nom est obligatoire.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier la Ville</h1>
</div>

<div class="card" style="max-width: 500px;">
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Nom de la Ville</label>
            <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($v['nom']); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Station</label>
            <input type="text" name="station" class="form-control" value="<?php echo htmlspecialchars($v['station']); ?>">
        </div>
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
