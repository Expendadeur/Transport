<?php
/**
 * Delete Chauffeur
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('gestionnaire');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM chauffeur WHERE id_Chauff = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Le chauffeur a été supprimé.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer ce chauffeur.";
    }
}

header("Location: index.php");
exit();
?>
