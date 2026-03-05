<?php
/**
 * Colis (Courrier) Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Colis";

// Search & Filter
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$where = " WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (c.code_suivi LIKE ? OR c.expediteur LIKE ? OR c.destinateur LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($status)) {
    $where .= " AND c.statut = ?";
    $params[] = $status;
}

// Fetch Courriers
$query = "SELECT c.*, a1.nom_agence as agence_dep, a2.nom_agence as agence_arr 
          FROM courrier c 
          LEFT JOIN agence a1 ON c.agence_depart = a1.idAg 
          LEFT JOIN agence a2 ON c.agence_arrive = a2.idAg 
          $where 
          ORDER BY c.idCourrier DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$colis = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Colis</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Suivi des expéditions, réceptions et livraisons de colis.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouvelle Expédition
    </a>
</div>

<div class="card">
    <!-- Filters -->
    <div style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1; max-width: 500px;">
            <input type="text" name="search" class="form-control" placeholder="Code suivi, expéditeur..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="form-control" style="max-width: 150px;">
                <option value="">Tous statuts</option>
                <option value="en transit" <?php echo $status == 'en transit' ? 'selected' : ''; ?>>En Transit</option>
                <option value="livré" <?php echo $status == 'livré' ? 'selected' : ''; ?>>Livré</option>
                <option value="retourné" <?php echo $status == 'retourné' ? 'selected' : ''; ?>>Retourné</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Code Suivi</th>
                    <th>Expéditeur / Dest.</th>
                    <th>Trajet</th>
                    <th>Poids / Prix</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($colis) > 0): ?>
                    <?php foreach ($colis as $c): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary-color);"><?php echo $c['code_suivi']; ?></strong>
                                <div style="font-size: 0.7rem; color: #94a3b8;">
                                    <?php echo date('d/m/Y H:i', strtotime($c['date_expedition'])); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <strong>De:</strong> <?php echo htmlspecialchars($c['expediteur']); ?><br>
                                    <strong>À:</strong> <?php echo htmlspecialchars($c['destinateur']); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <?php echo htmlspecialchars($c['agence_dep']); ?>
                                    <i class="fas fa-arrow-right" style="font-size: 0.6rem; color: #cbd5e1;"></i>
                                    <?php echo htmlspecialchars($c['agence_arr']); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <?php echo $c['poids']; ?> kg<br>
                                    <strong style="color: #059669;"><?php echo number_format($c['prix'] ?: 0, 0, ',', ' '); ?> FBU</strong>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $s_map = [
                                    'en transit' => ['#fef3c7', '#d97706', 'clock'],
                                    'livré' => ['#ecfdf5', '#059669', 'check-circle'],
                                    'retourné' => ['#fee2e2', '#ef4444', 'undo']
                                ];
                                $s = $s_map[$c['statut']] ?? ['#f1f5f9', '#64748b', 'dot-circle'];
                                ?>
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; background: <?php echo $s[0]; ?>; color: <?php echo $s[1]; ?>;">
                                    <i class="fas fa-<?php echo $s[2]; ?>"></i>
                                    <?php echo strtoupper($c['statut']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $c['idCourrier']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $c['idCourrier']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 3rem;">Aucun colis trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
