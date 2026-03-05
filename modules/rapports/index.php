<?php
/**
 * Rapports & Statistiques
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$page_title = "Rapports & Statistiques";

// Filter by Month/Year
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// 1. Revenue by Payment status
$revenue_query = "SELECT SUM(montant) as total FROM payment WHERE statut = 'validé' AND MONTH(date_payment) = ? AND YEAR(date_payment) = ?";
$stmt = $pdo->prepare($revenue_query);
$stmt->execute([$month, $year]);
$total_revenue = $stmt->fetch()['total'] ?: 0;

// 2. Expenses
$expense_query = "SELECT SUM(montant) as total FROM depenses WHERE MONTH(date_depense) = ? AND YEAR(date_depense) = ?";
$stmt = $pdo->prepare($expense_query);
$stmt->execute([$month, $year]);
$total_expenses = $stmt->fetch()['total'] ?: 0;

// 3. Package Stats
$colis_query = "SELECT statut, COUNT(*) as count FROM courrier WHERE MONTH(date_expedition) = ? AND YEAR(date_expedition) = ? GROUP BY statut";
$stmt = $pdo->prepare($colis_query);
$stmt->execute([$month, $year]);
$colis_stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 4. Agency Performance (Revenue per Agency)
// Note: Payment is linked to Reservation. Reservation is linked to Trajet. Trajet is linked to Automobile. Automobile is linked to Agence.
// This is complex, but we can approximate by linking Dépenses to Agences.
$agency_expenses = $pdo->prepare("SELECT a.nom_agence, SUM(d.montant) as total 
                                 FROM depenses d 
                                 JOIN agence a ON d.idAgenc = a.idAg 
                                 WHERE MONTH(d.date_depense) = ? AND YEAR(d.date_depense) = ? 
                                 GROUP BY a.idAg");
$agency_expenses->execute([$month, $year]);
$ag_exp = $agency_expenses->fetchAll();

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700;">Rapports d'Activité</h1>
        <p style="color: #64748b; font-size: 0.875rem;">Synthèse financière et opérationnelle pour <?php echo date('F Y', strtotime("$year-$month-01")); ?>.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <form method="GET" style="display: flex; gap: 0.5rem;">
            <select name="month" class="form-control" style="width: auto;">
                <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo $month == $i ? 'selected' : ''; ?>>
                        <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-control" style="width: auto;">
                <?php for($i=date('Y'); $i>=2020; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $year == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-primary">Filtrer</button>
        </form>
        <button onclick="window.print()" class="btn" style="background: #f1f5f9; color: var(--dark-color);">
            <i class="fas fa-print"></i> Imprimer
        </button>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid #059669;">
        <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Recettes Totales</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #059669;"><?php echo number_format($total_revenue, 0, ',', ' '); ?> FBU</div>
    </div>
    <div class="card" style="border-left: 4px solid #ef4444;">
        <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Dépenses Totales</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;"><?php echo number_format($total_expenses, 0, ',', ' '); ?> FBU</div>
    </div>
    <div class="card" style="border-left: 4px solid var(--primary-color);">
        <div style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.5rem;">Résultat Net</div>
        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
            <?php echo number_format($total_revenue - $total_expenses, 0, ',', ' '); ?> FBU
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Package Distribution -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem;">Distribution des Colis</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach(['en transit', 'livré', 'retourné'] as $st): ?>
                <?php 
                $count = $colis_stats[$st] ?? 0;
                $total = array_sum($colis_stats) ?: 1;
                $percent = round(($count / $total) * 100);
                ?>
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.85rem;">
                        <span style="text-transform: capitalize; font-weight: 600;"><?php echo $st; ?></span>
                        <span><?php echo $count; ?> colis (<?php echo $percent; ?>%)</span>
                    </div>
                    <div style="height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: <?php echo $percent; ?>%; background: var(--primary-color);"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Agency Expenses Breakdown -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem;">Dépenses par Agence</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Agence</th>
                        <th style="text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ag_exp) > 0): ?>
                        <?php foreach($ag_exp as $ae): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ae['nom_agence']); ?></td>
                                <td style="text-align: right; font-weight: 600; color: #ef4444;">
                                    <?php echo number_format($ae['total'], 0, ',', ' '); ?> FBU
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2" style="text-align: center; color: #94a3b8;">Aucune donnée agence.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar-header, .btn, form { display: none !important; }
    .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .card { border: 1px solid #e2e8f0 !important; box-shadow: none !important; }
}
</style>

<?php include '../../includes/footer.php'; ?>
