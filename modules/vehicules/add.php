<?php
/**
 * Add Véhicule
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Ajouter un Véhicule";
$error = '';

// Fetch Agences and Trajets for selection
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
            $stmt = $pdo->prepare("INSERT INTO automobile (marque, modele, immatriculation, capacite, etat, id_agenc, id_Trajet) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$marque, $modele, $immatriculation, $capacite, $etat, $id_agenc, $id_Trajet]);
            
            $_SESSION['success'] = "Le véhicule a été ajouté avec succès.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout: " . $e->getMessage();
        }
    } else {
        $error = "La marque et l'immatriculation sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouveau Véhicule</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Marque <span style="color: #ef4444;">*</span></label>
                <input type="text" name="marque" class="form-control" placeholder="Ex: Toyota" required>
            </div>
            <div class="form-group">
                <label class="form-label">Modèle</label>
                <input type="text" name="modele" class="form-control" placeholder="Ex: Coaster">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Immatriculation <span style="color: #ef4444;">*</span></label>
                <input type="text" name="immatriculation" class="form-control" placeholder="Ex: D 1234 A" required>
            </div>
            <div class="form-group">
                <label class="form-label">Capacité (Places)</label>
                <input type="number" name="capacite" class="form-control" placeholder="Ex: 30">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">État</label>
                <select name="etat" class="form-control">
                    <option value="en service">En Service</option>
                    <option value="en maintenance">En Maintenance</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Agence d'Affectation</label>
                <select name="id_agenc" class="form-control">
                    <option value="">-- Non affecté --</option>
                    <?php foreach ($agences as $ag): ?>
                        <option value="<?php echo $ag['idAg']; ?>"><?php echo htmlspecialchars($ag['nom_agence']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Trajet Affecté</label>
                <select name="id_Trajet" class="form-control">
                    <option value="">-- Aucun trajet --</option>
                    <?php foreach ($trajets as $t): ?>
                        <option value="<?php echo $t['id_Traj']; ?>"><?php echo htmlspecialchars($t['ville_depart'] . ' - ' . $t['ville_arrive']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer le Véhicule</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
