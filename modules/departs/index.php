<?php
/**
 * Listing des Départs (Voyages programmés)
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$page_title = "Gestion des Départs";

// Fetch Departs with Trajet and Automobile info
$query = "SELECT d.*, t.ville_depart, t.ville_arrive, a.marque, a.immatriculation, a.capacite 
          FROM depart d
          LEFT JOIN trajet t ON d.id_Trajet = t.id_Traj
          LEFT JOIN automobile a ON d.idAuto = a.id_aut
          ORDER BY d.date_depart DESC, d.heure_depart DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$departs = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Voyages Programmés</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Planifiez les départs et gérez le remplissage des véhicules.</p>
    </div>
    <a href="add.php" class="btn btn-primary" style="text-decoration: none;">
        <i class="fas fa-calendar-plus"></i> Programmer un Départ
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Détails Voyage</th>
                    <th>Date & Heures</th>
                    <th>Véhicule</th>
                    <th>Places Restantes</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($departs) > 0): ?>
                    <?php foreach ($departs as $d): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($d['ville_depart'] . ' → ' . $d['ville_arrive']); ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;">ID: #DEP-<?php echo $d['idDep']; ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y', strtotime($d['date_depart'])); ?></div>
                                <div style="font-size: 0.85rem; color: #64748b;">
                                    <i class="far fa-clock"></i> <?php echo substr($d['heure_depart'], 0, 5); ?> 
                                    <i class="fas fa-long-arrow-alt-right"></i> <?php echo substr($d['heure_arrivee'], 0, 5); ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($d['marque']); ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars($d['immatriculation']); ?></div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="font-weight: 700; font-size: 1.1rem; <?php echo $d['places_disponibles'] <= 5 ? 'color: #ef4444;' : 'color: #059669;'; ?>">
                                        <?php echo $d['places_disponibles']; ?>
                                    </div>
                                    <span style="font-size: 0.7rem; color: #94a3b8;">/ <?php echo $d['capacite']; ?></span>
                                </div>
                                <div style="width: 100px; height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 0.3rem;">
                                    <?php 
                                    $percent = ($d['capacite'] > 0) ? (($d['capacite'] - $d['places_disponibles']) / $d['capacite']) * 100 : 0;
                                    ?>
                                    <div style="width: <?php echo $percent; ?>%; height: 100%; background: var(--primary-color); border-radius: 2px;"></div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $colors = ['ouvert' => ['#ecfdf5', '#059669'], 'fermé' => ['#fff7ed', '#c2410c'], 'terminé' => ['#f1f5f9', '#64748b']];
                                $c = $colors[$d['statut']] ?? $colors['terminé'];
                                ?>
                                <span style="padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; background: <?php echo $c[0]; ?>; color: <?php echo $c[1]; ?>; text-transform: uppercase;">
                                    <?php echo $d['statut']; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="edit.php?id=<?php echo $d['idDep']; ?>" class="btn" style="background: #f1f5f9; color: #0f172a; padding: 0.5rem;"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('delete.php?id=<?php echo $d['idDep']; ?>')" class="btn" style="background: #fee2e2; color: #ef4444; padding: 0.5rem;"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 4rem; color: #94a3b8;">Aucun voyage programmé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
