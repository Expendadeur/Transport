<?php
/**
 * Add Colis
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Expédition de Colis";
$error = '';

// Fetch Agences
$agences = $pdo->query("SELECT * FROM agence ORDER BY nom_agence ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'] ?? '';
    $expediteur = $_POST['expediteur'] ?? '';
    $destinateur = $_POST['destinateur'] ?? '';
    $agence_depart = $_POST['agence_depart'] ?? null;
    $agence_arrive = $_POST['agence_arrive'] ?? null;
    $date_expedition = date('Y-m-d H:i:s');
    $poids = $_POST['poids'] ?? 0;
    $prix = $_POST['prix'] ?? 0;
    $type_courrier = $_POST['type_courrier'] ?? '';

    if (!empty($expediteur) && !empty($destinateur) && $agence_depart && $agence_arrive) {
        try {
            $stmt = $pdo->prepare("INSERT INTO courrier (description, expediteur, destinateur, agence_depart, agence_arrive, date_expedition, poids, prix, statut, type_courrier) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en transit', ?)");
            $stmt->execute([$description, $expediteur, $destinateur, $agence_depart, $agence_arrive, $date_expedition, $poids, $prix, $type_courrier]);
            
            $_SESSION['success'] = "Colis expédié avec succès. Code de suivi généré.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Expéditeur, Destinateur et Agences sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Nouvelle Expédition</h1>
</div>

<div class="card" style="max-width: 900px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Column 1: Parties -->
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--secondary-color);">Informations des Parties</h4>
                <div class="form-group">
                    <label class="form-label">Expéditeur <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="expediteur" class="form-control" placeholder="Nom et Prénom" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Destinateur <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="destinateur" class="form-control" placeholder="Nom et Prénom" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type de Colis</label>
                    <select name="type_courrier" class="form-control">
                        <option value="Colis Standard">Colis Standard</option>
                        <option value="Document">Document / Lettre</option>
                        <option value="Fragile">Colis Fragile</option>
                        <option value="Valeur">Objet de Valeur</option>
                    </select>
                </div>
            </div>

            <!-- Column 2: Logistique -->
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--secondary-color);">Détails Logistiques</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Agence de Départ <span style="color: #ef4444;">*</span></label>
                        <select name="agence_depart" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($agences as $ag): ?>
                                <option value="<?php echo $ag['idAg']; ?>"><?php echo htmlspecialchars($ag['nom_agence']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Agence de Destination <span style="color: #ef4444;">*</span></label>
                        <select name="agence_arrive" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach ($agences as $ag): ?>
                                <option value="<?php echo $ag['idAg']; ?>"><?php echo htmlspecialchars($ag['nom_agence']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" step="0.01" name="poids" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prix Total (FBU) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="prix" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description du contenu</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Description sommaire du contenu..."></textarea>
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Enregistrer l'Expédition
            </button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
