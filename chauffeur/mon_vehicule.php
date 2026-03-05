<?php
/**
 * Chauffeur - Mon Véhicule
 */
require_once '../config/database.php';
require_once '../includes/auth.php';

check_login();
if ($_SESSION['user_role'] !== 'chauffeur') {
    header("Location: /Gestion_agence_transport/dashboard.php"); exit();
}

$page_title = "Mon Véhicule";

// Find the vehicle linked to this driver's email/id
$stmt = $pdo->prepare("
    SELECT aut.*, ag.nom_agence, t.ville_depart, t.ville_arrive, t.prix, t.distance
    FROM chauffeur c
    JOIN automobile aut ON aut.id_agenc IS NOT NULL
    LEFT JOIN agence ag ON aut.id_agenc = ag.idAg
    LEFT JOIN trajet t ON aut.id_Trajet = t.id_Traj
    WHERE c.email = ?
    LIMIT 1
");
$stmt->execute([$_SESSION['user_email']]);
$vehicule = $stmt->fetch();

include '../includes/header.php';
include 'sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">Mon Véhicule</h1>
    <p style="color: #64748b;">Détails du véhicule qui vous est assigné.</p>
</div>

<?php if ($vehicule): ?>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

    <!-- Vehicle Card -->
    <div class="card" style="background: linear-gradient(160deg, #1e293b, #334155); color: white; border: none;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 5rem; margin-bottom: 0.5rem;">🚌</div>
            <h2 style="font-size: 1.3rem;"><?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?></h2>
            <div style="font-family: monospace; font-size: 1.2rem; font-weight: 800; background: #fef3c7; color: #92400e; padding: 0.5rem 1.5rem; border-radius: 8px; margin-top: 0.75rem; display: inline-block; letter-spacing: 3px;">
                <?php echo htmlspecialchars($vehicule['immatriculation']); ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
            <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 0.75rem;">
                <div style="font-size: 0.65rem; opacity: 0.7; margin-bottom: 0.25rem;">CAPACITÉ</div>
                <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $vehicule['capacite']; ?></div>
                <div style="font-size: 0.65rem; opacity: 0.7;">places</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 0.75rem;">
                <div style="font-size: 0.65rem; opacity: 0.7; margin-bottom: 0.25rem;">ÉTAT</div>
                <div style="font-size: 0.9rem; font-weight: 700; color: <?php echo $vehicule['etat'] === 'en service' ? '#4ade80' : '#fca5a5'; ?>;">
                    <?php echo strtoupper($vehicule['etat']); ?>
                </div>
            </div>
        </div>

        <div style="margin-top: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 8px; padding: 0.75rem;">
            <div style="font-size: 0.7rem; opacity: 0.7; margin-bottom: 0.25rem;">AGENCE D'AFFECTATION</div>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($vehicule['nom_agence'] ?? 'Non spécifiée'); ?></div>
        </div>
    </div>

    <!-- Trajet Info -->
    <div class="card">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--primary-color);">
            <i class="fas fa-route"></i> Trajet Assigné
        </h3>
        <?php if ($vehicule['ville_depart']): ?>
        <div style="display: flex; align-items: center; justify-content: space-around; padding: 1.5rem 0; border: 2px dashed #e2e8f0; border-radius: 12px; margin-bottom: 1.5rem;">
            <div style="text-align: center;">
                <div style="font-size: 1.3rem; font-weight: 900;"><?php echo htmlspecialchars($vehicule['ville_depart']); ?></div>
                <div style="font-size: 0.7rem; color: #64748b;">DÉPART</div>
            </div>
            <div style="text-align: center;">
                <div style="color: #94a3b8; font-size: 1.5rem;">→</div>
                <div style="font-size: 0.7rem; color: #94a3b8;"><?php echo number_format($vehicule['distance'], 0); ?> km</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.3rem; font-weight: 900;"><?php echo htmlspecialchars($vehicule['ville_arrive']); ?></div>
                <div style="font-size: 0.7rem; color: #64748b;">ARRIVÉE</div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: center;">
            <div style="background: #f8fafc; border-radius: 10px; padding: 1rem;">
                <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.3rem;">TARIF</div>
                <div style="font-size: 1.2rem; font-weight: 800; color: #059669;"><?php echo number_format($vehicule['prix'], 0, ',', ' '); ?> <span style="font-size: 0.75rem;">FBU</span></div>
            </div>
            <div style="background: #f8fafc; border-radius: 10px; padding: 1rem;">
                <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 0.3rem;">DISTANCE</div>
                <div style="font-size: 1.2rem; font-weight: 800; color: var(--primary-color);"><?php echo number_format($vehicule['distance'], 0); ?> <span style="font-size: 0.75rem;">km</span></div>
            </div>
        </div>
        <?php else: ?>
        <div style="text-align: center; color: #94a3b8; padding: 2rem;">Aucun trajet assigné à ce véhicule.</div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="card" style="text-align: center; padding: 3rem; color: #94a3b8;">
    <i class="fas fa-bus" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
    Aucun véhicule ne vous est assigné. Contactez votre responsable.
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
