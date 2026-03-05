<?php
/**
 * Edit Véhicule
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier le Véhicule";
$error = '';

// Fetch current vehicle
$stmt = $pdo->prepare("SELECT * FROM automobile WHERE id_aut = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();
if (!$v) { header("Location: index.php"); exit(); }

// Fetch Agences and Trajets
$agences = $pdo->query("SELECT * FROM agence ORDER BY nom_agence ASC")->fetchAll();
$trajets = $pdo->query("SELECT * FROM trajet ORDER BY ville_depart ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marque = $_POST['marque'] ?? '';
    $modele = $_POST['modele'] ?? '';
    $immatriculation = $_POST['immatriculation'] ?? '';
    $capacite = $_POST['capacite'] ?? 0;
    $etat = $_POST['etat'] ?? 'en service';
    $id_agenc = $_POST['id_agenc'] ?: null;
    $id_Trajet = $_POST['id_Trajet'] ?: null;

    if (!empty($marque) && !empty($immatriculation)) {
        try {
            $photo_path = $v['photo'];
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/vehicules/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid('veh_') . '.' . $file_ext;
                $target_file = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                    // Delete old photo if exists
                    if ($v['photo'] && file_exists('../../' . $v['photo'])) {
                        unlink('../../' . $v['photo']);
                    }
                    $photo_path = 'uploads/vehicules/' . $new_filename;
                }
            }

            $stmt = $pdo->prepare("UPDATE automobile SET marque=?, modele=?, immatriculation=?, capacite=?, etat=?, id_agenc=?, id_Trajet=?, photo=? WHERE id_aut=?");
            $stmt->execute([$marque, $modele, $immatriculation, $capacite, $etat, $id_agenc, $id_Trajet, $photo_path, $id]);
            
            $_SESSION['success'] = "Le véhicule a été mis à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Marque et immatriculation sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier : <?php echo htmlspecialchars($v['marque'].' '.$v['immatriculation']); ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group" style="margin-bottom: 2rem; text-align: center;">
            <label class="form-label" style="display: block;">Photo du Véhicule</label>
            <div style="width: 150px; height: 150px; border: 2px dashed #cbd5e1; border-radius: 12px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; position: relative;">
                <?php if ($v['photo']): ?>
                    <img id="img-preview" src="../../<?php echo $v['photo']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <i class="fas fa-bus" style="font-size: 3rem; color: #94a3b8; display: none;" id="placeholder-icon"></i>
                <?php else: ?>
                    <i class="fas fa-bus" style="font-size: 3rem; color: #94a3b8;" id="placeholder-icon"></i>
                    <img id="img-preview" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                <?php endif; ?>
            </div>
            <input type="file" name="photo" id="photo-input" style="display: none;" accept="image/*" onchange="previewImage(this)">
            <button type="button" class="btn" style="background: #f1f5f9; color: #0f172a;" onclick="document.getElementById('photo-input').click()">
                <i class="fas fa-camera"></i> Modifier la photo
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Marque</label>
                <input type="text" name="marque" class="form-control" value="<?php echo htmlspecialchars($v['marque']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Modèle</label>
                <input type="text" name="modele" class="form-control" value="<?php echo htmlspecialchars($v['modele']); ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Immatriculation</label>
                <input type="text" name="immatriculation" class="form-control" value="<?php echo htmlspecialchars($v['immatriculation']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Capacité</label>
                <input type="number" name="capacite" class="form-control" value="<?php echo $v['capacite']; ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">État</label>
                <select name="etat" class="form-control">
                    <option value="en service" <?php echo $v['etat'] == 'en service' ? 'selected' : ''; ?>>En Service</option>
                    <option value="en maintenance" <?php echo $v['etat'] == 'en maintenance' ? 'selected' : ''; ?>>En Maintenance</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Agence</label>
                <select name="id_agenc" class="form-control">
                    <option value="">-- Non affecté --</option>
                    <?php foreach ($agences as $ag): ?>
                        <option value="<?php echo $ag['idAg']; ?>" <?php echo $v['id_agenc'] == $ag['idAg'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ag['nom_agence']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Trajet</label>
                <select name="id_Trajet" class="form-control">
                    <option value="">-- Aucun trajet --</option>
                    <?php foreach ($trajets as $t): ?>
                        <option value="<?php echo $t['id_Traj']; ?>" <?php echo $v['id_Trajet'] == $t['id_Traj'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['ville_depart'] . ' - ' . $t['ville_arrive']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('img-preview');
    const icon = document.getElementById('placeholder-icon');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
