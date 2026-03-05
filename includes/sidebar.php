<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user = get_user();
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">T</div>
        <div class="logo-text">
            <h2 style="font-size: 1.1rem; font-weight: 700;">TRANSLOG</h2>
            <p style="font-size: 0.7rem; color: #94a3b8; letter-spacing: 1px;">AGENCY MANAGER</p>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="/Gestion_agence_transport/dashboard.php" class="menu-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <?php if (check_role('gestionnaire')): ?>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/agences/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/agences/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-building"></i>
                <span>Agences</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/colis/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/colis/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Gestion Colis</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/reservations/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/reservations/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i>
                <span>Réservations</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/departs/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/departs/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-bus-alt"></i>
                <span>Départs</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/trajets/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/trajets/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-route"></i>
                <span>Trajets</span>
            </a>
        </li>

        <?php if (check_role('gestionnaire')): ?>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/vehicules/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/vehicules/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-bus"></i>
                <span>Véhicules</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (check_role('gestionnaire')): ?>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/chauffeurs/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/chauffeurs/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i>
                <span>Chauffeurs</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/clients/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/clients/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </a>
        </li>

        <?php if (check_role('admin')): ?>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/employes/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/employes/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i>
                <span>Employés</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/villes/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/villes/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-city"></i>
                <span>Villes</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (check_role('gestionnaire')): ?>
        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/paiements/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/paiements/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Paiements</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/depenses/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/depenses/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-wallet"></i>
                <span>Dépenses</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/modules/rapports/index.php" class="menu-link <?php echo strpos($_SERVER['PHP_SELF'], '/rapports/') !== false ? 'active' : ''; ?>">
                <i class="fas fa-file-contract"></i>
                <span>Rapports</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-item" style="margin-top: 2rem;">
            <a href="/Gestion_agence_transport/logout.php" class="menu-link" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</aside>

<main class="main-content">
    <nav class="top-navbar">
        <div class="nav-left">
            <button id="sidebar-toggle" style="background: none; border: none; font-size: 1.25rem; cursor: pointer;">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="nav-right">
            <div class="user-profile" style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="user-info" style="text-align: right;">
                    <p style="font-weight: 600; font-size: 0.9rem;"><?php echo $user['name']; ?></p>
                    <p style="font-size: 0.75rem; color: #64748b; text-transform: capitalize;"><?php echo $user['role']; ?></p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user" style="color: #94a3b8;"></i>
                </div>
            </div>
        </div>
    </nav>
    <div class="content-body">
