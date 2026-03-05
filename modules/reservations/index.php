<?php
/**
 * Réservations Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Réservations";

// Fetch Reservations with Passenger, Trajet, Depart, and Payment info
$query = "SELECT r.*, p.nomP as passager_nom, p.prenomP as passager_prenom, 
          t.ville_depart, t.ville_arrive, d.heure_depart, d.heure_arrivee, 
          a.immatriculation as vehicule, pay.montant as pay_montant, pay.statut as pay_statut 
          FROM reservation r 
          LEFT JOIN passager p ON r.id_Passager = p.idP 
          LEFT JOIN trajet t ON r.id_Trajet = t.id_Traj 
          LEFT JOIN depart d ON r.id_Depart = d.idDep
          LEFT JOIN automobile a ON d.idAuto = a.id_aut
          LEFT JOIN payment pay ON r.id_Payment = pay.idPay 
          ORDER BY r.idRes DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$reservations = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Réservations</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Suivi des billets vendus et des listes de passagers.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouvelle Réservation
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Billet #</th>
                    <th>Passager</th>
                    <th>Trajet</th>
                    <th>Date Voyage</th>
                    <th>Paiement</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reservations) > 0): ?>
                    <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td><strong style="font-family: monospace;">#RES-<?php echo $r['idRes']; ?></strong></td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($r['passager_nom'] . ' ' . $r['passager_prenom']); ?></div>
                                <div style="font-size: 0.7rem; color: #94a3b8;">Siège n° <?php echo $r['nr_place']; ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; font-weight: 500;">
                                    <?php echo htmlspecialchars($r['ville_depart'] . ' → ' . $r['ville_arrive']); ?>
                                </div>
                                <?php if ($r['heure_depart']): ?>
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        <i class="far fa-clock"></i> <?php echo substr($r['heure_depart'], 0, 5); ?> 
                                        • <span style="font-family: monospace; font-weight: bold;"><?php echo $r['vehicule']; ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($r['date_reservation'])); ?></td>
                            <td>
                                <?php if ($r['id_Payment']): ?>
                                    <div style="font-weight: 600; color: #059669;"><?php echo number_format($r['pay_montant'], 0, ',', ' '); ?> FBU</div>
                                    <span style="font-size: 0.7rem; text-transform: uppercase; color: #64748b;">(<?php echo $r['pay_statut']; ?>)</span>
                                <?php else: ?>
                                    <div style="font-weight: 600; color: #94a3b8;"><?php echo number_format($r['nr_place'] * ($r['id_Trajet'] ? ($pdo->query("SELECT prix FROM trajet WHERE id_Traj = " . (int)$r['id_Trajet'])->fetchColumn() ?: 0) : 0), 0, ',', ' '); ?> FBU</div>
                                    <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600;">Non payé</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="ticket.php?id=<?php echo $r['idRes']; ?>" class="btn" style="background: #e0f2fe; color: #0369a1; padding: 0.5rem;" title="Voir le Ticket"><i class="fas fa-print"></i></a>
                                    <?php if (!$r['id_Payment']): ?>
                                        <a href="../paiements/add.php?res_id=<?php echo $r['idRes']; ?>" class="btn" style="background: #ecfdf5; color: #059669; padding: 0.5rem;" title="Enregistrer le paiement">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="edit.php?id=<?php echo $r['idRes']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $r['idRes']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 3rem;">Aucune réservation enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
