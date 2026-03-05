<?php
/**
 * Employés Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$page_title = "Gestion des Employés";

// Search Logic
$search = $_GET['search'] ?? '';
$where = "";
$params = [];

if (!empty($search)) {
    $where = " WHERE u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Fetch Users with Agence and Address info
$query = "SELECT u.*, a.nom_agence 
          FROM utilisateur u 
          LEFT JOIN agence a ON u.idAgenc = a.idAg 
          $where 
          ORDER BY u.idUt DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Employés</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Administrez les comptes utilisateurs et les rôles de votre équipe.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-user-plus"></i> Nouvel Employé
    </a>
</div>

<div class="card">
    <div style="margin-bottom: 1.5rem;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Nom, prénom ou email..." value="<?php echo htmlspecialchars($search); ?>">
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
                    <th>Employé</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Agence</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; color: #64748b;">
                                        <?php echo strtoupper(substr($u['nom'], 0, 1) . substr($u['prenom'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($u['prenom'] . ' ' . $u['nom']); ?></div>
                                        <div style="font-size: 0.7rem; color: #94a3b8;"><?php echo $u['genre'] == 'M' ? 'Masculin' : 'Féminin'; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                    <?php 
                                    $role_color = [
                                        'admin' => '#7c3aed',
                                        'gestionnaire' => '#2563eb',
                                        'agent' => '#059669',
                                        'chauffeur' => '#d97706'
                                    ];
                                    ?>
                                    <span style="color: <?php echo $role_color[$u['role']] ?? '#64748b'; ?>;">
                                        <i class="fas fa-shield-alt" style="margin-right: 0.25rem;"></i>
                                        <?php echo $u['role']; ?>
                                    </span>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($u['nom_agence'] ?: 'Centrale / Multi'); ?></td>
                            <td>
                                <span style="padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; background: <?php echo $u['statut'] == 'actif' ? '#ecfdf5' : '#fee2e2'; ?>; color: <?php echo $u['statut'] == 'actif' ? '#059669' : '#ef4444'; ?>;">
                                    <?php echo ucfirst($u['statut']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $u['idUt']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <?php if ($u['idUt'] != $_SESSION['user_id']): ?>
                                        <button onclick="confirmDelete('delete.php?id=<?php echo $u['idUt']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 3rem;">Aucun employé trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
