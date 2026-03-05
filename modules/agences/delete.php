<?php
/**
 * Delete Agence
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();
authorize('admin'); // Only admins can delete agencies

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM agence WHERE idAg = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "L'agence a été supprimée.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Impossible de supprimer cette agence (elle peut être liée à d'autres données).";
    }
}

header("Location: index.php");
exit();
?>
