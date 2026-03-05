<?php
/**
 * Edit Colis
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Mettre à jour le Colis";
$error = '';

// Fetch current package
$stmt = $pdo->prepare("SELECT * FROM courrier WHERE idCourrier = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header("Location: index.php"); exit(); }

// Fetch Agences
$agences = $pdo->query("SELECT * FROM agence ORDER BY nom_agence ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';
    $expediteur = $_POST['expediteur'] ?? '';
    $destinateur = $_POST['destinateur'] ?? '';
    $agence_depart = $_POST['agence_depart'] ?? null;
    $agence_arrive = $_POST['agence_arrive'] ?? null;
    $statut = $_POST['statut'] ?? 'en transit';
    $poids = $_POST['poids'] ?? 0;
    $prix = $_POST['prix'] ?? 0;
    
    // Delivery date logic
    $date_reception = $c['date_reception'];
    if ($statut == 'livré' && empty($date_reception)) {
        $date_reception = date('Y-m-d H:i:s');
    } elseif ($statut != 'livré') {
        $date_reception = null;
    }

    if (!empty($expediteur) && !empty($destinateur)) {
        try {
            $stmt = $pdo->prepare("UPDATE courrier SET description=?, expediteur=?, destinateur=?, agence_depart=?, agence_arrive=?, statut=?, poids=?, prix=?, date_reception=? WHERE idCourrier=?");
            $stmt->execute([$description, $expediteur, $destinateur, $agence_depart, $agence_arrive, $statut, $poids, $prix, $date_reception, $id]);
            
            $_SESSION['success'] = "Le statut du colis a été mis à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Expéditeur et Destinateur sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Mise à jour Colis : <?php echo $c['code_suivi']; ?></h1>
</div>

<div class="card" style="max-width: 900px;">
    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--secondary-color);">Suivi & Statut</h4>
                <div class="form-group">
                    <label class="form-label">Statut Actuel</label>
                    <select name="statut" class="form-control" style="border: 2px solid var(--primary-color);">
                        <option value="en transit" <?php echo $c['statut'] == 'en transit' ? 'selected' : ''; ?>>En Transit</option>
                        <option value="livré" <?php echo $c['statut'] == 'livré' ? 'selected' : ''; ?>>Livré au Destinataire</option>
                        <option value="retourné" <?php echo $c['statut'] == 'retourné' ? 'selected' : ''; ?>>Retourné à l'envoyeur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Description du contenu</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($c['description']); ?></textarea>
                </div>
            </div>

            <div>
                <h4 style="margin-bottom: 1rem; color: var(--secondary-color);">Parties & Logistique</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Expéditeur</label>
                        <input type="text" name="expediteur" class="form-control" value="<?php echo htmlspecialchars($c['expediteur']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Destinateur</label>
                        <input type="text" name="destinateur" class="form-control" value="<?php echo htmlspecialchars($c['destinateur']); ?>" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Départ</label>
                        <select name="agence_depart" class="form-control">
                            <?php foreach ($agences as $ag): ?>
                                <option value="<?php echo $ag['idAg']; ?>" <?php echo $c['agence_depart'] == $ag['idAg'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ag['nom_agence']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Arrivée</label>
                        <select name="agence_arrive" class="form-control">
                            <?php foreach ($agences as $ag): ?>
                                <option value="<?php echo $ag['idAg']; ?>" <?php echo $c['agence_arrive'] == $ag['idAg'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($ag['nom_agence']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" step="0.01" name="poids" class="form-control" value="<?php echo $c['poids']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prix (FBU)</label>
                        <input type="number" name="prix" class="form-control" value="<?php echo $c['prix']; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mettre à jour l'expédition</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
