<?php
/**
 * Dashboard Page
 * TRANSLOG AGENCY MANAGER
 */
require_once 'config/database.php';
require_once 'includes/auth.php';

check_login();
$page_title = "Dashboard";

// Fetch statistics
$stats = [
    'agences' => $pdo->query("SELECT COUNT(*) FROM agence")->fetchColumn(),
    'employes' => $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
    'colis' => $pdo->query("SELECT COUNT(*) FROM courrier WHERE statut = 'en transit'")->fetchColumn(),
    'revenus' => $pdo->query("SELECT SUM(prix) FROM courrier WHERE date_expedition >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetchColumn() ?: 0,
    'depenses' => $pdo->query("SELECT SUM(montant) FROM depenses WHERE date_depense >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetchColumn() ?: 0,
];

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="dashboard-header" style="margin-bottom: 2rem;">
    <h1>Bienvenue, <?php echo $_SESSION['user_name']; ?> !</h1>
    <p style="color: #64748b;">Voici l'aperçu de votre plateforme TRANSLOG aujourd'hui.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Total Agences</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['agences']; ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #ecfdf5; color: #047857;">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Employés</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['employes']; ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fff7ed; color: #c2410c;">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Colis en cours</p>
            <h3 style="font-size: 1.5rem; font-weight: 700;"><?php echo $stats['colis']; ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #f0f9ff; color: #0369a1;">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Revenus (Mois)</p>
            <h3 style="font-size: 1.25rem; font-weight: 700;"><?php echo number_format($stats['revenus'], 0, ',', ' '); ?> FBU</h3>
        </div>
    </div>
</div>

<div class="row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Recent Activity / Chart Placeholder -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h4 style="font-weight: 700;">Aperçu des revenus vs dépenses</h4>
            <span style="font-size: 0.75rem; background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 20px;">30 derniers jours</span>
        </div>
        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 2px dashed #e2e8f0; border-radius: 12px; color: #94a3b8;">
            <div style="text-align: center;">
                <i class="fas fa-chart-area" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Graphique des transactions ici</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h4 style="font-weight: 700; margin-bottom: 1.5rem;">Actions Rapides</h4>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="modules/colis/add.php" class="btn btn-primary" style="text-decoration: none; text-align: center; display: block;">
                <i class="fas fa-plus" style="margin-right: 0.5rem;"></i> Nouveau Colis
            </a>
            <a href="modules/trajets/index.php" class="btn" style="background: #f1f5f9; color: var(--dark-color); text-decoration: none; text-align: center; display: block;">
                <i class="fas fa-route" style="margin-right: 0.5rem;"></i> Voir Trajets
            </a>
            <a href="modules/rapports/index.php" class="btn" style="background: #f1f5f9; color: var(--dark-color); text-decoration: none; text-align: center; display: block;">
                <i class="fas fa-file-invoice" style="margin-right: 0.5rem;"></i> Rapports
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
