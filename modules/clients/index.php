<?php
/**
 * Clients (Passagers) Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Clients";

// Search Logic
$search = $_GET['search'] ?? '';
$where = "";
$params = [];

if (!empty($search)) {
    $where = " WHERE p.nomP LIKE ? OR p.prenomP LIKE ? OR p.telephone LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Fetch Passagers
$query = "SELECT p.*, a.pays, a.province, a.commune 
          FROM passager p 
          LEFT JOIN adresse a ON p.id_Adres = a.idAdr 
          $where 
          ORDER BY p.idP DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$passagers = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Clients</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Consultez et gérez la base de données de vos clients et passagers.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouveau Client
    </a>
</div>

<div class="card">
    <div style="margin-bottom: 1.5rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou téléphone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Genre</th>
                    <th>Âge</th>
                    <th>Téléphone</th>
                    <th>Localisation</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($passagers) > 0): ?>
                    <?php foreach ($passagers as $p): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($p['prenomP'] . ' ' . $p['nomP']); ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;">ID: #CL-00<?php echo $p['idP']; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($p['genre']); ?></td>
                            <td><?php echo $p['age']; ?> ans</td>
                            <td><i class="fas fa-phone-alt" style="margin-right: 0.5rem; font-size: 0.8rem; opacity: 0.6;"></i> <?php echo htmlspecialchars($p['telephone']); ?></td>
                            <td>
                                <span style="font-size: 0.8rem; color: #64748b;">
                                    <?php echo htmlspecialchars(($p['commune'] ? $p['commune'].', ' : '') . ($p['province'] ?: 'N/A')); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $p['idP']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $p['idP']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">Aucun client trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
