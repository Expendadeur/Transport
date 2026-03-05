<?php
/**
 * Edit Agence
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$GET['id'] : 0;
if (!$id) {
    header("Location: index.php");
    exit();
}

$page_title = "Modifier l'Agence";
$error = '';

// Fetch current agence
$stmt = $pdo->prepare("SELECT * FROM agence WHERE idAg = ?");
$stmt->execute([$id]);
$agence = $stmt->fetch();

if (!$agence) {
    header("Location: index.php");
    exit();
}

// Fetch Villes
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_agence = $_POST['nom_agence'] ?? '';
    $tel_agence = $_POST['tel_agence'] ?? '';
    $id_vil = $_POST['id_vil'] ?? null;

    if (!empty($nom_agence)) {
        try {
            $stmt = $pdo->prepare("UPDATE agence SET nom_agence = ?, tel_agence = ?, id_vil = ? WHERE idAg = ?");
            $stmt->execute([$nom_agence, $tel_agence, $id_vil, $id]);
            
            $_SESSION['success'] = "L'agence a été modifiée avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification: " . $e->getMessage();
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
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier l'Agence : <?php echo htmlspecialchars($agence['nom_agence']); ?></h1>
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
            <input type="text" name="nom_agence" class="form-control" value="<?php echo htmlspecialchars($agence['nom_agence']); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="text" name="tel_agence" class="form-control" value="<?php echo htmlspecialchars($agence['tel_agence']); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Ville de Localisation</label>
            <select name="id_vil" class="form-control">
                <option value="">-- Sélectionner une ville --</option>
                <?php foreach ($villes as $ville): ?>
                    <option value="<?php echo $ville['idVil']; ?>" <?php echo $agence['id_vil'] == $ville['idVil'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ville['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer les Modifications</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
