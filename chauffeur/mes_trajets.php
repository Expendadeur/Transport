<?php
/**
 * Chauffeur - Mes Trajets Assignés
 */
require_once '../config/database.php';
require_once '../includes/auth.php';

check_login();
if ($_SESSION['user_role'] !== 'chauffeur') {
    header("Location: /Gestion_agence_transport/dashboard.php"); exit();
}

$page_title = "Mes Trajets";

// Find the driver's vehicle → trajet
$chauffeur_email = $_SESSION['user_email'];
$stmt = $pdo->prepare("
    SELECT t.*, aut.marque, aut.modele, aut.immatriculation, aut.capacite, ag.nom_agence
    FROM chauffeur c
    JOIN automobile aut ON aut.id_agenc IS NOT NULL
    JOIN trajet t ON aut.id_Trajet = t.id_Traj
    LEFT JOIN agence ag ON aut.id_agenc = ag.idAg
    WHERE c.email = ?
    LIMIT 1
");
$stmt->execute([$chauffeur_email]);
$trajet = $stmt->fetch();

// Get today's reservations for this trajet
$today_passengers = [];
if ($trajet) {
    $stmt2 = $pdo->prepare("
        SELECT r.*, p.nomP as nom, p.prenomP as prenom, p.num_piece_id, pay.montant, pay.statut as pay_statut
        FROM reservation r
        JOIN passager p ON r.id_Passager = p.idP
        LEFT JOIN payment pay ON r.id_Payment = pay.idPay
        WHERE r.id_Trajet = ? AND r.date_reservation = CURDATE()
        ORDER BY r.nr_place ASC
    ");
    $stmt2->execute([$trajet['id_Traj']]);
    $today_passengers = $stmt2->fetchAll();
}

include '../includes/header.php';
include 'sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">Mes Trajets Assignés</h1>
    <p style="color: #64748b;">Vue de votre itinéraire actuel et des passagers du jour.</p>
</div>

<?php if ($trajet): ?>
<!-- Trajet Card -->
<div class="card" style="margin-bottom: 2rem; border: 2px solid #fde68a; background: linear-gradient(135deg, #fffbeb, #fff);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1.5rem;">
            <div style="text-align: center;">
                <div style="font-size: 1.2rem; font-weight: 800; color: #0f172a;"><?php echo htmlspecialchars($trajet['ville_depart']); ?></div>
                <div style="font-size: 0.7rem; color: #64748b;">DÉPART</div>
            </div>
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.2rem;">
                <div style="font-size: 0.7rem; color: #94a3b8;"><?php echo number_format($trajet['distance'], 0); ?> km</div>
                <div style="width: 80px; height: 2px; background: linear-gradient(90deg, #d97706, #f59e0b); border-radius: 2px; position: relative;">
                    <i class="fas fa-bus" style="position: absolute; top: -8px; right: -8px; color: #d97706; font-size: 0.75rem;"></i>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.2rem; font-weight: 800; color: #0f172a;"><?php echo htmlspecialchars($trajet['ville_arrive']); ?></div>
                <div style="font-size: 0.7rem; color: #64748b;">ARRIVÉE</div>
            </div>
        </div>
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
            <div style="text-align: center;">
                <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.2rem;">VÉHICULE</div>
                <div style="font-weight: 700;"><?php echo htmlspecialchars($trajet['marque'] . ' ' . $trajet['modele']); ?></div>
                <div style="font-size: 0.7rem; font-family: monospace; color: #64748b;"><?php echo $trajet['immatriculation']; ?></div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.2rem;">CAPACITÉ</div>
                <div style="font-weight: 700;"><?php echo $trajet['capacite']; ?> places</div>
                <div style="font-size: 0.7rem; color: #10b981;"><?php echo count($today_passengers); ?> occupées</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.2rem;">TARIF</div>
                <div style="font-weight: 700; color: #059669;"><?php echo number_format($trajet['prix'], 0, ',', ' '); ?> FBU</div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Passengers -->
<div class="card">
    <h3 style="margin-bottom: 1.5rem; font-size: 1rem; font-weight: 700;">
        <i class="fas fa-users" style="color: #10b981;"></i>
        Passagers du Jour — <?php echo date('d/m/Y'); ?>
        <span style="background: #ecfdf5; color: #059669; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; margin-left: 0.5rem;"><?php echo count($today_passengers); ?> person(s)</span>
    </h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Siège #</th>
                    <th>Passager</th>
                    <th>Pièce d'Identité</th>
                    <th>Paiement</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($today_passengers) > 0): ?>
                    <?php foreach ($today_passengers as $p): ?>
                        <tr>
                            <td><strong style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px;"><?php echo $p['nr_place']; ?></strong></td>
                            <td><strong><?php echo htmlspecialchars($p['nom'] . ' ' . $p['prenom']); ?></strong></td>
                            <td style="font-family: monospace; font-size: 0.85rem;"><?php echo $p['num_piece_id']; ?></td>
                            <td style="font-weight: 600; color: #059669;"><?php echo number_format($p['montant'] ?? 0, 0, ',', ' '); ?> FBU</td>
                            <td>
                                <?php $paid = !empty($p['montant']); ?>
                                <span style="padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: <?php echo $paid ? '#ecfdf5' : '#fff7ed'; ?>; color: <?php echo $paid ? '#059669' : '#c2410c'; ?>;">
                                    <?php echo $paid ? 'PAYÉ' : 'EN ATTENTE'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">Aucune réservation pour aujourd'hui.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<div class="card" style="text-align: center; padding: 3rem; color: #94a3b8;">
    <i class="fas fa-route" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
    Aucun trajet ne vous est encore assigné. Contactez votre responsable.
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
