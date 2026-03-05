<?php
/**
 * Véhicules (Automobile) Listing
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Véhicules";

// Fetch Automobiles with Agence and Trajet info
$query = "SELECT aut.*, ag.nom_agence, t.ville_depart, t.ville_arrive 
          FROM automobile aut 
          LEFT JOIN agence ag ON aut.id_agenc = ag.idAg 
          LEFT JOIN trajet t ON aut.id_Trajet = t.id_Traj 
          ORDER BY aut.id_aut DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$vehicules = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Gestion des Véhicules</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Gérez votre flotte automobile, la maintenance et l'affectation aux trajets.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-plus"></i> Nouveau Véhicule
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Véhicule</th>
                    <th>Immatriculation</th>
                    <th>Capacité</th>
                    <th>Agence</th>
                    <th>Trajet Actuel</th>
                    <th>État</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($vehicules) > 0): ?>
                    <?php foreach ($vehicules as $v): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($v['marque'] . ' ' . $v['modele']); ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;">ID: #VEH-<?php echo $v['id_aut']; ?></div>
                            </td>
                            <td><span style="background: #f1f5f9; padding: 0.2rem 0.6rem; border-radius: 4px; font-family: monospace; font-weight: bold; border: 1px solid #e2e8f0;"><?php echo htmlspecialchars($v['immatriculation']); ?></span></td>
                            <td><?php echo $v['capacite']; ?> places</td>
                            <td><?php echo htmlspecialchars($v['nom_agence'] ?: 'Non affecté'); ?></td>
                            <td>
                                <?php if ($v['id_Trajet']): ?>
                                    <span style="font-size: 0.85rem; color: #64748b;">
                                        <?php echo htmlspecialchars($v['ville_depart'] . ' → ' . $v['ville_arrive']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #cbd5e1; font-size: 0.85rem;">Aucun trajet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status_bg = $v['etat'] == 'en service' ? '#ecfdf5' : '#fff7ed';
                                $status_color = $v['etat'] == 'en service' ? '#059669' : '#c2410c';
                                ?>
                                <span style="padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; text-transform: capitalize;">
                                    <?php echo $v['etat']; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $v['id_aut']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $v['id_aut']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">Aucun véhicule trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
