<?php
/**
 * Supprimer un Départ
 */
require_once '../../config/database.php';
require_once '../../includes/auth.php';

check_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id) {
    try {
        // Check if there are reservations for this trip
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservation WHERE id_Depart = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Impossible de supprimer ce voyage car il contient des réservations.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM depart WHERE idDep = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Le voyage a été supprimé.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();
