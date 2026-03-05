<?php
/**
 * Trajets Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Trajets";

// Fetch Trajets
$query = "SELECT * FROM trajet ORDER BY id_Traj DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$trajets = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Trajets</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Définissez les itinéraires, les distances et les tarifs de transport.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="../villes/index.php" class="btn" style="background: #f1f5f9; text-decoration: none; color: var(--dark-color);">
            <i class="fas fa-city"></i> Gérer Villes
        </a>
        <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
            <i class="fas fa-plus"></i> Nouveau Trajet
        </a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Itinéraire</th>
                    <th>Distance</th>
                    <th>Prix Standard</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trajets) > 0): ?>
                    <?php foreach ($trajets as $t): ?>
                        <tr>
                            <td>#TRJ-<?php echo $t['id_Traj']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($t['ville_depart']); ?></div>
                                    <i class="fas fa-long-arrow-alt-right" style="color: #94a3b8;"></i>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($t['ville_arrive']); ?></div>
                                </div>
                            </td>
                            <td><?php echo number_format($t['distance'], 1); ?> km</td>
                            <td style="font-weight: 700; color: var(--primary-color);">
                                <?php echo number_format($t['prix'], 0, ',', ' '); ?> FBU
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $t['id_Traj']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $t['id_Traj']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">Aucun trajet défini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
