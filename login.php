<?php
/**
 * Login Page
 * TRANSLOG AGENCY MANAGER
 */
require_once 'config/database.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // 1. Check utilisateur table first (admin, gestionnaire, agent)
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ? AND statut = 'actif'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['idUt'];
            $_SESSION['user_name']  = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role']  = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type']  = 'employe';

            // Route chauffeur role to driver dashboard
            if ($user['role'] === 'chauffeur') {
                header("Location: /Gestion_agence_transport/chauffeur/dashboard.php");
            } else {
                header("Location: /Gestion_agence_transport/dashboard.php");
            }
            exit();

        } else {
            // 2. Check chauffeur table (drivers with their own login)
            $stmt2 = $pdo->prepare("SELECT * FROM chauffeur WHERE email = ? AND statut = 'actif'");
            $stmt2->execute([$email]);
            $chauffeur = $stmt2->fetch();

            if ($chauffeur && password_verify($password, $chauffeur['password'])) {
                $_SESSION['user_id']    = $chauffeur['id_Chauff'];
                $_SESSION['user_name']  = $chauffeur['prenom'] . ' ' . $chauffeur['nom'];
                $_SESSION['user_role']  = 'chauffeur';
                $_SESSION['user_email'] = $chauffeur['email'];
                $_SESSION['user_type']  = 'chauffeur';

                header("Location: /Gestion_agence_transport/chauffeur/dashboard.php");
                exit();
            } else {
                $error = "Email ou mot de passe incorrect, ou compte inactif.";
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TRANSLOG AGENCY MANAGER</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-top: 1rem;
        }
        .error-msg {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div id="nprogress-bar"></div>
    <div id="toast-container"></div>
    <div class="card login-card">
        <div class="login-header">
            <div class="logo-icon" style="margin: 0 auto;">T</div>
            <h1>TRANSLOG MANAGER</h1>
            <p style="color: #64748b; margin-top: 0.5rem;">Connectez-vous à votre espace</p>
        </div>

        <?php /* Errors shown via toast below */ ?>

        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Adresse Email</label>
                <input type="email" name="email" class="form-control" required placeholder="admin@translog.com">
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>
    </div>

<script src="assets/js/notifications.js"></script>
<script>
    document.querySelector('form').addEventListener('submit', () => Notify.startProgress());
    window.addEventListener('load', () => Notify.doneProgress());
    <?php if (!empty($error)): ?>
    document.addEventListener('DOMContentLoaded', () => {
        Notify.toast('error', <?php echo json_encode($error); ?>, 5000);
    });
    <?php endif; ?>
</script>
</body>
</html>
