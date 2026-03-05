<?php
/**
 * Edit Réservation
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier la Réservation";
$error = '';

$stmt = $pdo->prepare("SELECT * FROM reservation WHERE idRes = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();
if (!$r) { header("Location: index.php"); exit(); }

// Fetch Dropdowns
$passagers = $pdo->query("SELECT * FROM passager ORDER BY nomP ASC")->fetchAll();
$trajets = $pdo->query("SELECT * FROM trajet ORDER BY ville_depart ASC")->fetchAll();
$payments = $pdo->query("SELECT * FROM payment ORDER BY idPay DESC LIMIT 50")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_Passager = $_POST['id_Passager'];
    $id_Trajet = $_POST['id_Trajet'];
    $date_reservation = $_POST['date_reservation'];
    $nr_place = $_POST['nr_place'];
    $id_Payment = $_POST['id_Payment'] ?: null;

    if ($id_Passager && $id_Trajet) {
        try {
            $stmt = $pdo->prepare("UPDATE reservation SET date_reservation=?, nr_place=?, id_Passager=?, id_Trajet=?, id_Payment=? WHERE idRes=?");
            $stmt->execute([$date_reservation, $nr_place, $id_Passager, $id_Trajet, $id_Payment, $id]);
            
            $_SESSION['success'] = "Réservation mise à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Passager et Trajet requis.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier Réservation #<?php echo $id; ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Client</label>
            <select name="id_Passager" class="form-control" required>
                <?php foreach ($passagers as $p): ?>
                    <option value="<?php echo $p['idP']; ?>" <?php echo $r['id_Passager'] == $p['idP'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nomP'] . ' ' . $p['prenomP']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Trajet</label>
                <select name="id_Trajet" class="form-control" required>
                    <?php foreach ($trajets as $t): ?>
                        <option value="<?php echo $t['id_Traj']; ?>" <?php echo $r['id_Trajet'] == $t['id_Traj'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['ville_depart'] . ' - ' . $t['ville_arrive']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date du voyage</label>
                <input type="date" name="date_reservation" class="form-control" value="<?php echo $r['date_reservation']; ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Numéro de Siège</label>
                <input type="number" name="nr_place" class="form-control" value="<?php echo $r['nr_place']; ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Paiement</label>
                <select name="id_Payment" class="form-control">
                    <option value="">-- Non payé --</option>
                    <?php foreach ($payments as $pay): ?>
                        <option value="<?php echo $pay['idPay']; ?>" <?php echo $r['id_Payment'] == $pay['idPay'] ? 'selected' : ''; ?>>
                            #PAY-<?php echo $pay['idPay']; ?> (<?php echo number_format($pay['montant'], 0); ?> FBU)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
