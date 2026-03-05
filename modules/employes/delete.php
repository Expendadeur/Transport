<?php
/**
 * Delete Employé
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id && $id != $_SESSION['user_id']) {
    try {
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE idUt = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "L'employé a été supprimé.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer cet employé (il peut avoir des données liées).";
    }
}

header("Location: index.php");
exit();
?>
