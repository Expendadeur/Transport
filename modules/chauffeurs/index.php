<?php
/**
 * Chauffeurs Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Chauffeurs";

// Fetch Chauffeurs
$query = "SELECT * FROM chauffeur ORDER BY id_Chauff DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$chauffeurs = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Chauffeurs</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Gérez votre personnel de conduite et leurs permis.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouveau Chauffeur
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom Complet</th>
                    <th>Téléphone</th>
                    <th>N° Permis</th>
                    <th>Catégorie</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($chauffeurs) > 0): ?>
                    <?php foreach ($chauffeurs as $c): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($c['nom'] . ' ' . $c['prenom']); ?></div>
                                <div style="font-size: 0.7rem; color: #94a3b8;">ID: #CHF-<?php echo $c['id_Chauff']; ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($c['telephone']); ?></td>
                            <td><span style="font-family: monospace; font-weight: 600;"><?php echo htmlspecialchars($c['num_permis']); ?></span></td>
                            <td><span style="padding: 0.2rem 0.5rem; background: #f1f5f9; border-radius: 4px; font-weight: 700; font-size: 0.8rem;"><?php echo htmlspecialchars($c['categorie_permis']); ?></span></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $c['id_Chauff']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $c['id_Chauff']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 3rem;">Aucun chauffeur enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
