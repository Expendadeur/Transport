<?php
/**
 * Edit Employé
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$page_title = "Modifier l'Employé";
$error = '';

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE idUt = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) { header("Location: index.php"); exit(); }

// Fetch Agences
$agences = $pdo->query("SELECT * FROM agence ORDER BY nom_agence ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $role = $_POST['role'] ?? 'agent';
    $idAgenc = $_POST['idAgenc'] ?: null;
    $statut = $_POST['statut'] ?? 'actif';
    $password = $_POST['password'] ?? '';

    if (!empty($nom) && !empty($prenom) && !empty($email)) {
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, email=?, genre=?, role=?, idAgenc=?, statut=?, password=? WHERE idUt=?");
                $stmt->execute([$nom, $prenom, $email, $genre, $role, $idAgenc, $statut, $hashed_password, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, email=?, genre=?, role=?, idAgenc=?, statut=? WHERE idUt=?");
                $stmt->execute([$nom, $prenom, $email, $genre, $role, $idAgenc, $statut, $id]);
            }
            $_SESSION['success'] = "L'employé a été mis à jour.";
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    } else {
        $error = "Nom, prénom et email sont obligatoires.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div style="margin-bottom: 2rem;">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; margin-top: 1rem;">Modifier : <?php echo htmlspecialchars($u['prenom'].' '.$u['nom']); ?></h1>
</div>

<div class="card" style="max-width: 800px;">
    <form action="" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($u['nom']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($u['prenom']); ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u['email']); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Genre</label>
                <select name="genre" class="form-control">
                    <option value="M" <?php echo $u['genre'] == 'M' ? 'selected' : ''; ?>>Masculin</option>
                    <option value="F" <?php echo $u['genre'] == 'F' ? 'selected' : ''; ?>>Féminin</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Nouveau Mot de Passe (laisser vide pour ne pas changer)</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••">
            </div>
            <div class="form-group">
                <label class="form-label">Rôle</label>
                <select name="role" class="form-control">
                    <option value="agent" <?php echo $u['role'] == 'agent' ? 'selected' : ''; ?>>Agent</option>
                    <option value="gestionnaire" <?php echo $u['role'] == 'gestionnaire' ? 'selected' : ''; ?>>Gestionnaire</option>
                    <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="chauffeur" <?php echo $u['role'] == 'chauffeur' ? 'selected' : ''; ?>>Chauffeur</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Agence</label>
                <select name="idAgenc" class="form-control">
                    <option value="">-- Siège --</option>
                    <?php foreach ($agences as $agence): ?>
                        <option value="<?php echo $agence['idAg']; ?>" <?php echo $u['idAgenc'] == $agence['idAg'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($agence['nom_agence']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-control">
                    <option value="actif" <?php echo $u['statut'] == 'actif' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactif" <?php echo $u['statut'] == 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Enregistrer les changements</button>
            <a href="index.php" class="btn" style="background: #f1f5f9; color: #0f172a; text-decoration: none;">Annuler</a>
        </div>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
