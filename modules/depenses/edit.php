<?php
/**
 * Edit Dépense
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier la Dépense";
$error = '';

// Fetch current expense
$stmt = $pdo->prepare("SELECT * FROM depenses WHERE idDep = ?");
$stmt->execute([$id]);
$d = $stmt->fetch();
if (!$d) { header("Location: index.php"); exit(); }

// Fetch Agences
$agences = $pdo->query("SELECT * FROM agence ORDER BY nom_agence ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';
    $montant = $_POST['montant'] ?? 0;
    $date_depense = $_POST['date_depense'] ?? '';
    $idAgenc = $_POST['idAgenc'] ?: null;

    if (!empty($description) && $montant > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE depenses SET description=?, montant=?, date_depense=?, idAgenc=? WHERE idDep=?");
            $stmt->execute([$description, $montant, $date_depense, $idAgenc, $id]);
            
            $_SESSION['success'] = "La dépense a été mise à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Libellé et montant sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier Dépense #<?php echo $id; ?></h1>
</div>

<div class="card" style="max-width: 600px;">
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Description / Motif</label>
            <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($d['description']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Montant (FBU)</label>
            <input type="number" name="montant" class="form-control" value="<?php echo $d['montant']; ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="date_depense" class="form-control" value="<?php echo $d['date_depense']; ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Agence Concernée</label>
            <select name="idAgenc" class="form-control">
                <option value="">-- Non spécifiée (Centrale) --</option>
                <?php foreach ($agences as $ag): ?>
                    <option value="<?php echo $ag['idAg']; ?>" <?php echo $d['idAgenc'] == $ag['idAg'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ag['nom_agence']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mise à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
