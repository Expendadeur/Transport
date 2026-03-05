<?php
/**
 * Dépenses Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$page_title = "Gestion des Dépenses";

// Fetch Expenses with Agence info
$query = "SELECT d.*, a.nom_agence 
          FROM depenses d 
          LEFT JOIN agence a ON d.idAgenc = a.idAg 
          ORDER BY d.date_depense DESC, d.idDep DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$depenses = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Dépenses</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Suivi des sorties de caisse et frais opérationnels.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouvelle Dépense
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Libellé / Description</th>
                    <th>Montant</th>
                    <th>Date</th>
                    <th>Agence</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($depenses) > 0): ?>
                    <?php foreach ($depenses as $d): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($d['description']); ?></div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">Dépense du <?php echo date('d/m/Y', strtotime($d['date_depense'])); ?></div>
                            </td>
                            <td style="font-weight: 700; color: #ef4444;">
                                - <?php echo number_format($d['montant'], 0, ',', ' '); ?> FBU
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($d['date_depense'])); ?></td>
                            <td><?php echo htmlspecialchars($d['nom_agence'] ?: 'Centrale'); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $d['idDep']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $d['idDep']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">Aucune dépense enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
