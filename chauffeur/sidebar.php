<!-- Dedicated Chauffeur Sidebar -->
<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user = get_user();
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon" style="background: linear-gradient(135deg, #d97706, #f59e0b);">C</div>
        <div class="logo-text">
            <h2 style="font-size: 1.1rem; font-weight: 700;">TRANSLOG</h2>
            <p style="font-size: 0.7rem; color: #94a3b8; letter-spacing: 1px;">ESPACE CHAUFFEUR</p>
        </div>
    </div>

    <ul class="sidebar-menu">
        <!-- Driver Info card -->
        <li style="padding: 0.75rem 1rem; margin-bottom: 0.5rem;">
            <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 10px; padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #d97706; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.8rem; color: #92400e;"><?php echo $user['name']; ?></div>
                    <div style="font-size: 0.65rem; color: #b45309; background: #fde68a; padding: 0.1rem 0.4rem; border-radius: 10px; display: inline-block; margin-top: 0.2rem;">🚗 CHAUFFEUR</div>
                </div>
            </div>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/chauffeur/dashboard.php" class="menu-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Mon Tableau de Bord</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/chauffeur/mes_trajets.php" class="menu-link <?php echo $current_page == 'mes_trajets.php' ? 'active' : ''; ?>">
                <i class="fas fa-route"></i>
                <span>Mes Trajets Assignés</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/chauffeur/mon_vehicule.php" class="menu-link <?php echo $current_page == 'mon_vehicule.php' ? 'active' : ''; ?>">
                <i class="fas fa-bus"></i>
                <span>Mon Véhicule</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/chauffeur/passagers.php" class="menu-link <?php echo $current_page == 'passagers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Liste des Passagers</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="/Gestion_agence_transport/chauffeur/profil.php" class="menu-link <?php echo $current_page == 'profil.php' ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i>
                <span>Mon Profil</span>
            </a>
        </li>

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
            <div style="display: flex; align-items: center; gap: 0.5rem; background: #fef3c7; padding: 0.4rem 0.9rem; border-radius: 20px; border: 1px solid #fde68a;">
                <i class="fas fa-steering-wheel" style="color: #d97706; font-size: 0.85rem;"></i>
                <span style="font-size: 0.8rem; font-weight: 600; color: #92400e;">Espace Chauffeur</span>
            </div>
            <div class="user-profile" style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="user-info" style="text-align: right;">
                    <p style="font-weight: 600; font-size: 0.9rem;"><?php echo $user['name']; ?></p>
                    <p style="font-size: 0.75rem; color: #d97706; font-weight: 600;">Chauffeur Professionnel</p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #d97706, #f59e0b); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="content-body">
