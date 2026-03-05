<?php
/**
 * Villes Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Villes";

// Fetch Villes
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom ASC")->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Villes</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Administrez les villes desservies par le réseau TRANSLOG.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouvelle Ville
    </a>
</div>

<div class="card" style="max-width: 800px;">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de la Ville</th>
                    <th>Station / Terminal</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($villes) > 0): ?>
                    <?php foreach ($villes as $v): ?>
                        <tr>
                            <td>#VIL-<?php echo $v['idVil']; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($v['nom']); ?></td>
                            <td><?php echo htmlspecialchars($v['station'] ?: 'N/A'); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $v['idVil']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $v['idVil']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; padding: 3rem;">Aucune ville enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
