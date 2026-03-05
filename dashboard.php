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
$courrier_rev = $pdo->query("SELECT SUM(prix) FROM courrier WHERE date_expedition >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetchColumn() ?: 0;
$payment_rev = $pdo->query("SELECT SUM(montant) FROM payment WHERE date_payment >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND statut = 'validé'")->fetchColumn() ?: 0;
$total_depenses = $pdo->query("SELECT SUM(montant) FROM depenses WHERE date_depense >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetchColumn() ?: 0;

$stats = [
    'agences' => $pdo->query("SELECT COUNT(*) FROM agence")->fetchColumn(),
    'employes' => $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
    'colis' => $pdo->query("SELECT COUNT(*) FROM courrier WHERE statut = 'en transit'")->fetchColumn(),
    'revenus' => $courrier_rev + $payment_rev,
    'depenses' => $total_depenses,
    'balance' => ($courrier_rev + $payment_rev) - $total_depenses
];

// --- PREPARE CHART DATA (Last 14 Days) ---
$chart_labels = [];
$revenue_data = [];
$expense_data = [];

for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    
    // Revenue for this day
    $c_rev = $pdo->query("SELECT SUM(prix) FROM courrier WHERE DATE(date_expedition) = '$date'")->fetchColumn() ?: 0;
    $p_rev = $pdo->query("SELECT SUM(montant) FROM payment WHERE DATE(date_payment) = '$date' AND statut = 'validé'")->fetchColumn() ?: 0;
    $revenue_data[] = (float)($c_rev + $p_rev);
    
    // Expenses for this day
    $e_rev = $pdo->query("SELECT SUM(montant) FROM depenses WHERE DATE(date_depense) = '$date'")->fetchColumn() ?: 0;
    $expense_data[] = (float)$e_rev;
}

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

    <?php if (check_role('gestionnaire')): ?>
    <div class="stat-card">
        <div class="stat-icon" style="background: #f0f9ff; color: #0369a1;">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Revenus (Mois)</p>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #16a34a;">+ <?php echo number_format($stats['revenus'], 0, ',', ' '); ?> F</h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fef2f2; color: #991b1b;">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Dépenses (Mois)</p>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #ef4444;">- <?php echo number_format($stats['depenses'], 0, ',', ' '); ?> F</h3>
        </div>
    </div>

    <div class="stat-card" style="border: 2px solid <?php echo $stats['balance'] >= 0 ? '#dcfce7' : '#fee2e2'; ?>;">
        <div class="stat-icon" style="background: <?php echo $stats['balance'] >= 0 ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $stats['balance'] >= 0 ? '#166534' : '#991b1b'; ?>;">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-info">
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Balance Nette</p>
            <h3 style="font-size: 1.25rem; font-weight: 700;"><?php echo number_format($stats['balance'], 0, ',', ' '); ?> F</h3>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row" style="display: grid; grid-template-columns: <?php echo check_role('gestionnaire') ? '2fr 1fr' : '1fr'; ?>; gap: 1.5rem;">
    <?php if (check_role('gestionnaire')): ?>
    <!-- Recent Activity / Chart -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h4 style="font-weight: 700;">Aperçu des revenus vs dépenses</h4>
            <span style="font-size: 0.75rem; background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 20px;">14 derniers jours</span>
        </div>
        <div style="height: 350px;">
            <canvas id="financeChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

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

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financeChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [
                {
                    label: 'Revenus',
                    data: <?php echo json_encode($revenue_data); ?>,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#16a34a'
                },
                {
                    label: 'Dépenses',
                    data: <?php echo json_encode($expense_data); ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#ef4444'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, font: { family: 'Inter', weight: 600 } } },
                tooltip: { padding: 12, backgroundColor: '#0f172a', titleFont: { size: 14 } }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { callback: (v) => v + ' F' } },
                x: { grid: { display : false } }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
