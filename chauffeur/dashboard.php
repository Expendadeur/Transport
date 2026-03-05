<?php
/**
 * Chauffeur Dashboard
 */
require_once '../config/database.php';
require_once '../includes/auth.php';

check_login();
// Only chauffeurs can access this page
if ($_SESSION['user_role'] !== 'chauffeur') {
    header("Location: /Gestion_agence_transport/dashboard.php");
    exit();
}

$page_title = "Tableau de Bord Chauffeur";
$driver_id = $_SESSION['user_id'];

// Determine if logged in via utilisateur table or chauffeur table
$user_type = $_SESSION['user_type'] ?? 'employe';

// Fetch driver details from chauffeur table (by matching name or user session)
if ($user_type === 'chauffeur') {
    $stmt = $pdo->prepare("SELECT c.*, aut.marque, aut.modele, aut.immatriculation, aut.etat as vehicule_etat, t.ville_depart, t.ville_arrive
                           FROM chauffeur c
                           LEFT JOIN automobile aut ON aut.id_agenc IS NOT NULL
                           LEFT JOIN trajet t ON aut.id_Trajet = t.id_Traj
                           WHERE c.id_Chauff = ? LIMIT 1");
    $stmt->execute([$driver_id]);
} else {
    // Driver logged in via utilisateur table (role = chauffeur)
    $stmt = $pdo->prepare("SELECT c.*, aut.marque, aut.modele, aut.immatriculation, aut.etat as vehicule_etat, t.ville_depart, t.ville_arrive
                           FROM chauffeur c
                           LEFT JOIN automobile aut ON aut.id_agenc IS NOT NULL
                           LEFT JOIN trajet t ON aut.id_Trajet = t.id_Traj
                           WHERE c.email = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_email']]);
}
$driver = $stmt->fetch();

// Count today's reservations on the driver's trajet
$today_res = 0;
$total_res = 0;
if ($driver && $driver['id_Trajet'] ?? false) {
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE id_Trajet = ? AND date_reservation = CURDATE()");
    $stmt2->execute([$driver['id_Trajet']]);
    $today_res = $stmt2->fetchColumn();

    $stmt3 = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE id_Trajet = ?");
    $stmt3->execute([$driver['id_Trajet']]);
    $total_res = $stmt3->fetchColumn();
}

include '../includes/header.php';
include 'sidebar.php';
?>

<!-- Welcome Banner -->
<div style="background: linear-gradient(135deg, #92400e 0%, #d97706 50%, #f59e0b 100%); border-radius: 16px; padding: 2rem; margin-bottom: 2rem; color: white; position: relative; overflow: hidden;">
    <div style="position: absolute; right: 2rem; top: 50%; transform: translateY(-50%); font-size: 6rem; opacity: 0.1;">🚗</div>
    <div>
        <p style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 0.3rem;">Bonjour,</p>
        <h1 style="font-size: 1.6rem; font-weight: 800; text-shadow: 0 2px 10px rgba(0,0,0,0.2);"><?php echo $_SESSION['user_name']; ?></h1>
        <p style="margin-top: 0.7rem; opacity: 0.85; font-size: 0.95rem;">
            <i class="fas fa-calendar-day"></i>
            <?php echo strftime('%A %d %B %Y', time()); ?> · <?php echo date('H:i'); ?>
        </p>
    </div>
</div>

<!-- Status Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

    <!-- Vehicle Card -->
    <div class="card" style="border-left: 4px solid #d97706;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef3c7; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🚌</div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">MON VÉHICULE</div>
                <div style="font-size: 1rem; font-weight: 700; color: #0f172a;">
                    <?php echo $driver ? htmlspecialchars($driver['marque'] . ' ' . $driver['modele']) : 'Non assigné'; ?>
                </div>
                <?php if ($driver && $driver['immatriculation']): ?>
                    <div style="font-size: 0.7rem; color: #64748b; font-family: monospace; background: #f1f5f9; padding: 0.1rem 0.4rem; border-radius: 4px; margin-top: 0.2rem; display: inline-block;">
                        <?php echo $driver['immatriculation']; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Current Route -->
    <div class="card" style="border-left: 4px solid #3b82f6;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🗺️</div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">TRAJET ACTUEL</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                    <?php echo $driver && $driver['ville_depart'] ? htmlspecialchars($driver['ville_depart'] . ' → ' . $driver['ville_arrive']) : 'Aucun trajet'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Passengers -->
    <div class="card" style="border-left: 4px solid #10b981;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">👥</div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">PASSAGERS AUJOURD'HUI</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #059669;"><?php echo $today_res; ?></div>
            </div>
        </div>
    </div>

    <!-- Total Trips -->
    <div class="card" style="border-left: 4px solid #8b5cf6;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #f5f3ff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🎫</div>
            <div>
                <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">TOTAL RÉSERVATIONS</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #7c3aed;"><?php echo $total_res; ?></div>
            </div>
        </div>
    </div>

</div>

<!-- Driver Permit Card -->
<?php if ($driver): ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <div class="card">
        <h3 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 700; color: var(--primary-color);">
            <i class="fas fa-id-card"></i> Informations Permis
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.85rem;">Numéro de permis</span>
                <strong style="font-family: monospace;"><?php echo $driver['num_permis'] ?: 'N/A'; ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.85rem;">Catégorie</span>
                <strong style="background: #fef3c7; color: #d97706; padding: 0.15rem 0.6rem; border-radius: 20px;"><?php echo $driver['categorie_permis'] ?: 'N/A'; ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #64748b; font-size: 0.85rem;">Téléphone</span>
                <strong><?php echo $driver['telephone'] ?: 'N/A'; ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 1rem; font-size: 1rem; font-weight: 700; color: var(--primary-color);">
            <i class="fas fa-link"></i> Liens Rapides
        </h3>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <a href="mes_trajets.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #f8fafc; border-radius: 10px; text-decoration: none; color: #0f172a; border: 1px solid #e2e8f0; transition: all 0.2s;"
               onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#93c5fd';"
               onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <i class="fas fa-route" style="color: #3b82f6;"></i>
                <span style="font-weight: 600; font-size: 0.9rem;">Voir mes trajets assignés</span>
            </a>
            <a href="passagers.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #f8fafc; border-radius: 10px; text-decoration: none; color: #0f172a; border: 1px solid #e2e8f0; transition: all 0.2s;"
               onmouseover="this.style.background='#ecfdf5'; this.style.borderColor='#6ee7b7';"
               onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <i class="fas fa-users" style="color: #10b981;"></i>
                <span style="font-weight: 600; font-size: 0.9rem;">Liste des passagers du jour</span>
            </a>
            <a href="profil.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #f8fafc; border-radius: 10px; text-decoration: none; color: #0f172a; border: 1px solid #e2e8f0; transition: all 0.2s;"
               onmouseover="this.style.background='#fef3c7'; this.style.borderColor='#fcd34d';"
               onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                <i class="fas fa-id-card" style="color: #d97706;"></i>
                <span style="font-weight: 600; font-size: 0.9rem;">Modifier mon profil</span>
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card" style="text-align: center; padding: 3rem; color: #94a3b8;">
    <i class="fas fa-user-clock" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
    <p>Votre fiche chauffeur n'est pas encore configurée. Contactez votre administrateur.</p>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
