<?php
/**
 * Paiements Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Paiements";

// Fetch Payments with linked Reservations
$query = "SELECT pay.*, r.idRes as reservation_id, p.nomP, p.prenomP 
          FROM payment pay 
          LEFT JOIN reservation r ON r.id_Payment = pay.idPay 
          LEFT JOIN passager p ON r.id_Passager = p.idP 
          ORDER BY pay.idPay DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$paiements = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Paiements</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Suivi des règlements clients et états financiers.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouveau Paiement
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Détails</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Mode de Paiement</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($paiements) > 0): ?>
                    <?php foreach ($paiements as $p): ?>
                        <tr>
                            <td>#PAY-<?php echo $p['idPay']; ?></td>
                            <td>
                                <?php if ($p['reservation_id']): ?>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">
                                        <i class="fas fa-ticket-alt" style="color: var(--primary-color);"></i> #RES-<?php echo $p['reservation_id']; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars($p['nomP'] . ' ' . $p['prenomP']); ?></div>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">Paiement libre</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: 700; color: #059669;">
                                <?php echo number_format($p['montant'], 0, ',', ' '); ?> FBU
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($p['date_payment'])); ?></td>
                            <td>
                                <span style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-credit-card" style="margin-right: 0.4rem; opacity: 0.5;"></i>
                                    <?php echo htmlspecialchars($p['mode_payment']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $s_colors = [
                                    'validé' => ['#ecfdf5', '#059669'],
                                    'en attente' => ['#fff7ed', '#c2410c'],
                                    'annulé' => ['#fee2e2', '#ef4444']
                                ];
                                $s = $s_colors[$p['statut']] ?? ['#f1f5f9', '#64748b'];
                                ?>
                                <span style="padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; background: <?php echo $s[0]; ?>; color: <?php echo $s[1]; ?>; text-transform: uppercase;">
                                    <?php echo $p['statut']; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $p['idPay']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $p['idPay']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 3rem;">Aucun paiement enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
