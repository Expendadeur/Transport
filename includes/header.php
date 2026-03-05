<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - TRANSLOG AGENCY MANAGER</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Style -->
    <link rel="stylesheet" href="/Gestion_agence_transport/assets/css/style.css">
    <!-- Notification System -->
    <link rel="stylesheet" href="/Gestion_agence_transport/assets/css/notifications.css">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Top Progress Bar -->
    <div id="nprogress-bar"></div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Delete Confirm Modal -->
    <div id="confirm-modal-overlay">
        <div id="confirm-modal">
            <div class="modal-icon"><i class="fas fa-trash-alt"></i></div>
            <h3>Confirmer la suppression</h3>
            <p>Cette action est définitive et ne peut pas être annulée.</p>
            <div class="modal-btns">
                <button id="modal-cancel-btn" class="btn" style="background: #f1f5f9; color: #0f172a;">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button id="modal-confirm-btn" class="btn" style="background: #ef4444; color: white;">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>
    </div>

    <div class="app-container">

<?php
// Flash message data to JavaScript (rendered in footer.php)
$flash_success = $_SESSION['success'] ?? null;
$flash_error   = $_SESSION['error']   ?? null;
$flash_warning = $_SESSION['warning'] ?? null;
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['warning']);
?>
