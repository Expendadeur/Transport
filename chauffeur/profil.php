<?php
/**
 * Chauffeur - Modifier mon Profil
 */
require_once '../config/database.php';
require_once '../includes/auth.php';

check_login();
if ($_SESSION['user_role'] !== 'chauffeur') {
    header("Location: /Gestion_agence_transport/dashboard.php"); exit();
}

$page_title = "Mon Profil";
$error = '';
$user_type = $_SESSION['user_type'] ?? 'employe';

// Get current driver data
if ($user_type === 'chauffeur') {
    $stmt = $pdo->prepare("SELECT * FROM chauffeur WHERE id_Chauff = ?");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM chauffeur WHERE email = ?");
    $stmt->execute([$_SESSION['user_email']]);
}
$driver = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $driver) {
    $telephone = $_POST['telephone'] ?? '';
    $email     = $_POST['email'] ?? '';
    $new_pass  = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    try {
        if (!empty($new_pass)) {
            if ($new_pass !== $conf_pass) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE chauffeur SET telephone=?, email=?, password=? WHERE id_Chauff=?")
                    ->execute([$telephone, $email, $hash, $driver['id_Chauff']]);
                $_SESSION['success'] = "Profil mis à jour avec succès.";
                header("Location: profil.php"); exit();
            }
        } else {
            $pdo->prepare("UPDATE chauffeur SET telephone=?, email=? WHERE id_Chauff=?")
                ->execute([$telephone, $email, $driver['id_Chauff']]);
            $_SESSION['success'] = "Profil mis à jour.";
            header("Location: profil.php"); exit();
        }
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

include '../includes/header.php';
include 'sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700;">Mon Profil</h1>
    <p style="color: #64748b;">Gérez vos informations personnelles et votre mot de passe.</p>
</div>

<?php if ($driver): ?>
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- ID Card -->
    <div class="card" style="text-align: center; background: linear-gradient(160deg, #92400e, #d97706); color: white; border: none;">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; font-weight: 800; border: 3px solid rgba(255,255,255,0.4);">
            <?php echo strtoupper(substr($driver['nom'], 0, 1)); ?>
        </div>
        <h2 style="font-size: 1.2rem; font-weight: 700;"><?php echo htmlspecialchars($driver['prenom'] . ' ' . $driver['nom']); ?></h2>
        <p style="opacity: 0.8; font-size: 0.85rem; margin-top: 0.3rem;">Chauffeur Professionnel</p>
        <div style="margin-top: 1.5rem; background: rgba(0,0,0,0.2); border-radius: 8px; padding: 0.75rem;">
            <div style="font-size: 0.65rem; opacity: 0.7; margin-bottom: 0.25rem;">PERMIS N°</div>
            <div style="font-family: monospace; font-size: 1rem; font-weight: 700; letter-spacing: 2px;"><?php echo $driver['num_permis'] ?: '--'; ?></div>
        </div>
        <div style="margin-top: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 8px; padding: 0.5rem;">
            <span style="background: #fef3c7; color: #92400e; font-weight: 800; padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                Catégorie <?php echo $driver['categorie_permis'] ?: '—'; ?>
            </span>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <h3 style="margin-bottom: 1.5rem; font-size: 1rem; font-weight: 700;">Modifier mes informations</h3>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Nom (lecture seule)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($driver['nom']); ?>" disabled style="background: #f8fafc;">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom (lecture seule)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($driver['prenom']); ?>" disabled style="background: #f8fafc;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="<?php echo htmlspecialchars($driver['telephone']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email de connexion</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($driver['email'] ?? ''); ?>">
                </div>
            </div>

            <hr style="margin: 1.5rem 0; border-color: #f1f5f9;">
            <h4 style="margin-bottom: 1rem; font-size: 0.9rem; color: #64748b;">Changer de mot de passe (optionnel)</h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Laisser vide pour ne pas changer">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirmer le nouveau mot de passe">
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="dashboard.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="card" style="text-align: center; padding: 2rem; color: #94a3b8;">
    Votre fiche chauffeur n'est pas encore créée. Contactez l'administrateur.
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
