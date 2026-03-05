<?php
/**
 * Public Landing Page - TRANSLOG
 */
require_once 'config/database.php';
session_start();

// Fetch some trajet data for the "Nos Destinations" section
$trajets = $pdo->query("SELECT * FROM trajet ORDER BY id_Traj DESC LIMIT 6")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSLOG - Voyagez en toute sérénité</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            height: 90vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/img/hero.png');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 1rem;
        }
        .hero-content h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
        }
        .hero-content p {
            font-size: 1.5rem;
            max-width: 800px;
            margin: 0 auto 2.5rem;
            opacity: 0.9;
        }
        .destinations {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--dark-color);
        }
        .section-title p {
            color: var(--secondary-color);
            margin-top: 1rem;
        }
        .dest-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .dest-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .dest-card:hover {
            transform: translateY(-10px);
        }
        .dest-info {
            padding: 2rem;
        }
        .dest-info h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .dest-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }
        .cta-btn {
            background: var(--primary-color);
            color: white;
            padding: 1.25rem 3rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            transition: var(--transition);
            display: inline-block;
        }
        .cta-btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        nav {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 1.5rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }
        .logo-text {
            color: white;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 2px;
        }
        .login-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border: 2px solid white;
            border-radius: 50px;
            transition: var(--transition);
        }
        .login-link:hover {
            background: white;
            color: var(--dark-color);
        }
        footer {
            background: var(--dark-color);
            color: #94a3b8;
            padding: 3rem 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo-text">TRANSLOG</div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="login-link">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="login-link"><i class="fas fa-user-lock"></i> Staff Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>Voyagez Partout, <br>Vivez l'Instant.</h1>
            <p>Réservez vos billets de transport en quelques clics et profitez d'un confort exceptionnel sur toutes nos lignes.</p>
            <a href="reservation_publique.php" class="cta-btn">Réserver mon Billet</a>
        </div>
    </header>

    <section class="destinations">
        <div class="section-title">
            <h2>Nos Destinations Populaires</h2>
            <p>Découvrez les villes du Burundi avec nos trajets réguliers et sécurisés.</p>
        </div>

        <div class="dest-grid">
            <?php foreach ($trajets as $t): ?>
                <div class="dest-card">
                    <div class="dest-info">
                        <div style="color: var(--secondary-color); font-size: 0.8rem; text-transform: uppercase; margin-bottom: 0.5rem;">Départ: <?php echo htmlspecialchars($t['ville_depart']); ?></div>
                        <h3><i class="fas fa-route" style="color: var(--primary-color);"></i> Vers <?php echo htmlspecialchars($t['ville_arrive']); ?></h3>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                            <span class="dest-price"><?php echo number_format($t['prix'], 0); ?> FBU</span>
                            <a href="reservation_publique.php?trajet=<?php echo $t['id_Traj']; ?>" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Choisir <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> TRANSLOG Burundi. Tous droits réservés.</p>
        <div style="margin-top: 1rem;">
            <i class="fab fa-facebook mx-2" style="cursor: pointer;"></i>
            <i class="fab fa-twitter mx-2" style="cursor: pointer;"></i>
            <i class="fab fa-instagram mx-2" style="cursor: pointer;"></i>
        </div>
    </footer>
</body>
</html>
