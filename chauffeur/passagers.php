<?php
/**
 * Chauffeur - Liste des Passagers (Today)
 */
require_once '../config/database.php';
require_once '../includes/auth.php';

check_login();
if ($_SESSION['user_role'] !== 'chauffeur') {
    header("Location: /Gestion_agence_transport/dashboard.php"); exit();
}

$page_title = "Liste des Passagers";
$date = $_GET['date'] ?? date('Y-m-d');

// Find driver's assigned trajet
$stmt = $pdo->prepare("SELECT c.*, aut.id_Trajet FROM chauffeur c LEFT JOIN automobile aut ON aut.id_agenc IS NOT NULL WHERE c.email = ? LIMIT 1");
$stmt->execute([$_SESSION['user_email']]);
$driver = $stmt->fetch();

$passengers = [];
if ($driver && $driver['id_Trajet']) {
    $stmt2 = $pdo->prepare("
        SELECT r.*, p.nomP, p.prenomP, p.num_piece_id, adresse.tel, pay.montant, pay.statut as pay_statut, pay.mode_payment
        FROM reservation r
        JOIN passager p ON r.id_Passager = p.idP
        LEFT JOIN adresse ON p.id_Adres = adresse.idAdr
        LEFT JOIN payment pay ON r.id_Payment = pay.idPay
        WHERE r.id_Trajet = ? AND r.date_reservation = ?
        ORDER BY r.nr_place ASC
    ");
    $stmt2->execute([$driver['id_Trajet'], $date]);
    $passengers = $stmt2->fetchAll();
}

include '../includes/header.php';
include 'sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Liste des Passagers</h1>
        <p style="color: #64748b;">Consultez les passagers réservés pour votre trajet.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <form method="GET">
            <input type="date" name="date" class="form-control" value="<?php echo $date; ?>" onchange="this.form.submit()">
        </form>
        <button onclick="window.print()" class="btn" style="background: #f1f5f9; color: #0f172a;">
            <i class="fas fa-print"></i> Imprimer
        </button>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Siège</th>
                    <th>Passager</th>
                    <th>CIN / Pièce d'ID</th>
                    <th>Téléphone</th>
                    <th>Mode Paiement</th>
                    <th>Montant</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($passengers) > 0): ?>
                    <?php foreach ($passengers as $p): ?>
                        <tr>
                            <td>
                                <span style="background: #fef3c7; color: #d97706; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.9rem;">
                                    <?php echo $p['nr_place']; ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($p['nomP'] . ' ' . $p['prenomP']); ?></td>
                            <td style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($p['num_piece_id']); ?></td>
                            <td><?php echo htmlspecialchars($p['tel'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($p['mode_payment'] ?? '—'); ?></td>
                            <td style="font-weight: 700; color: #059669;"><?php echo number_format($p['montant'] ?? 0, 0, ',', ' '); ?> FBU</td>
                            <td>
                                <?php $paid = !empty($p['montant']); ?>
                                <span style="padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: <?php echo $paid ? '#ecfdf5' : '#fff7ed'; ?>; color: <?php echo $paid ? '#059669' : '#c2410c'; ?>;">
                                    <?php echo $paid ? '✓ PAYÉ' : '⚠ IMPAYÉ'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        Aucun passager pour le <?php echo date('d/m/Y', strtotime($date)); ?>.
                    </td></tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($passengers) > 0): ?>
                <tfoot style="background: #f8fafc; font-weight: 700;">
                    <tr>
                        <td colspan="5" style="text-align: right; padding: 0.75rem 1rem; color: #64748b;">Total Passagers : <?php echo count($passengers); ?></td>
                        <td style="padding: 0.75rem 1rem; color: #059669;"><?php echo number_format(array_sum(array_column($passengers, 'montant')), 0, ',', ' '); ?> FBU</td>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<style>
@media print {
    .sidebar, #sidebar-toggle, form, button, .top-navbar .nav-right { display: none !important; }
    .main-content { margin-left: 0 !important; }
}
</style>

<?php include '../includes/footer.php'; ?>
