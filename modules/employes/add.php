<?php
/**
 * Add Employé
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$page_title = "Ajouter un Employé";
$error = '';

// Fetch Agences for selection
$agences = $pdo->query("SELECT * FROM agence ORDER BY nom_agence ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'agent';
    $idAgenc = $_POST['idAgenc'] ?: null;
    $statut = $_POST['statut'] ?? 'actif';

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($password)) {
        try {
            // Check if email exists
            $check = $pdo->prepare("SELECT idUt FROM utilisateur WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, genre, password, role, idAgenc, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $genre, $hashed_password, $role, $idAgenc, $statut]);
                
                $_SESSION['success'] = "L'employé a été ajouté avec succès.";
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout: " . $e->getMessage();
        }
    } else {
        $error = "Tous les champs marqués d'une étoile (*) sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour à la liste
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Ajouter un Nouvel Employé</h1>
</div>

<div class="card" style="max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nom <span style="color: #ef4444;">*</span></label>
                <input type="text" name="nom" class="form-control" placeholder="Nom de famille" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom <span style="color: #ef4444;">*</span></label>
                <input type="text" name="prenom" class="form-control" placeholder="Prénom" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Email <span style="color: #ef4444;">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="email@exemple.com" required>
            </div>
            <div class="form-group">
                <label class="form-label">Genre</label>
                <select name="genre" class="form-control">
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Mot de Passe <span style="color: #ef4444;">*</span></label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label class="form-label">Rôle</label>
                <select name="role" class="form-control">
                    <option value="agent">Agent (Guichet)</option>
                    <option value="gestionnaire">Gestionnaire</option>
                    <option value="admin">Administrateur</option>
                    <option value="chauffeur">Chauffeur</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Agence d'Affectation</label>
                <select name="idAgenc" class="form-control">
                    <option value="">-- Pas d'agence spécifique (Siège) --</option>
                    <?php foreach ($agences as $agence): ?>
                        <option value="<?php echo $agence['idAg']; ?>"><?php echo htmlspecialchars($agence['nom_agence']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Statut du Compte</label>
                <select name="statut" class="form-control">
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Créer le compte</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
