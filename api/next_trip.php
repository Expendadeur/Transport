<?php
/**
 * API: Fetch Next Available Trip for a route
 */
require_once '../config/database.php';
header('Content-Type: application/json');

$trajetId = isset($_GET['trajet']) ? (int)$_GET['trajet'] : 0;
$excludeId = isset($_GET['exclude']) ? (int)$_GET['exclude'] : 0;

if (!$trajetId) {
    echo json_encode(['success' => false, 'message' => 'Trajet manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT idDep, date_depart, heure_depart, places_disponibles 
        FROM depart 
        WHERE id_Trajet = ? AND idDep != ? AND statut = 'ouvert' AND places_disponibles > 0 AND date_depart >= CURDATE()
        ORDER BY date_depart ASC, heure_depart ASC 
        LIMIT 1
    ");
    $stmt->execute([$trajetId, $excludeId]);
    $trip = $stmt->fetch();

    if ($trip) {
        echo json_encode([
            'success' => true,
            'trip' => [
                'id' => $trip['idDep'],
                'date' => date('d/m/Y', strtotime($trip['date_depart'])),
                'heure' => substr($trip['heure_depart'], 0, 5),
                'seats' => $trip['places_disponibles']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aucun voyage trouvé']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
