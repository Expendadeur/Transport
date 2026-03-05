<?php
/**
 * Agences Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$page_title = "Gestion des Agences";

// Pagination Logic
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search Logic
$search = $_GET['search'] ?? '';
$where = "";
$params = [];

if (!empty($search)) {
    $where = " WHERE a.nom_agence LIKE ? OR v.nom LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Fetch Agences with their City
$query = "SELECT a.*, v.nom as ville_nom 
          FROM agence a 
          LEFT JOIN ville v ON a.id_vil = v.idVil 
          $where 
          LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$agences = $stmt->fetchAll();

// Total for pagination
$total_query = "SELECT COUNT(*) FROM agence a LEFT JOIN ville v ON a.id_vil = v.idVil $where";
$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute($params);
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Agences</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Liste de toutes les agences du réseau TRANSLOG.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouvelle Agence
    </a>
</div>

<div class="card">
    <!-- Search Bar -->
    <div style="margin-bottom: 1.5rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Rechercher une agence..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="btn" style="background: #f1f5f9;"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de l'Agence</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($agences) > 0): ?>
                    <?php foreach ($agences as $agence): ?>
                        <tr>
                            <td>#<?php echo $agence['idAg']; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($agence['nom_agence']); ?></td>
                            <td><?php echo htmlspecialchars($agence['tel_agence'] ?: 'N/A'); ?></td>
                            <td>
                                <span style="background: #eff6ff; color: #1d4ed8; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                    <i class="fas fa-map-marker-alt" style="margin-right: 0.25rem;"></i>
                                    <?php echo htmlspecialchars($agence['ville_nom'] ?: 'Non définie'); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $agence['idAg']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $agence['idAg']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: #64748b;">
                            <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                            Aucune agence trouvée.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="btn <?php echo $page == $i ? 'btn-primary' : ''; ?>" style="padding: 0.5rem 1rem; <?php echo $page != $i ? 'background: #f1f5f9; color: #0f172a;' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
