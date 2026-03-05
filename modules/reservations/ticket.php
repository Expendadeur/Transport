<?php
/**
 * Ticket de Réservation avec QRCode
 */
require_once '../../config/database.php';
session_start();

$resId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_public = isset($_GET['public']);

if (!$resId) {
    die("ID de réservation manquant.");
}

// Fetch detailed reservation info
$query = "SELECT r.*, p.nomP, p.prenomP, p.telephone, 
          t.ville_depart, t.ville_arrive, d.heure_depart, d.date_depart, d.heure_arrivee, 
          a.marque, a.immatriculation, a.photo, pay.montant as pay_montant, pay.statut as pay_statut 
          FROM reservation r 
          LEFT JOIN passager p ON r.id_Passager = p.idP 
          LEFT JOIN trajet t ON r.id_Trajet = t.id_Traj 
          LEFT JOIN depart d ON r.id_Depart = d.idDep
          LEFT JOIN automobile a ON d.idAuto = a.id_aut
          LEFT JOIN payment pay ON r.id_Payment = pay.idPay 
          WHERE r.idRes = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$resId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Réservation introuvable.");
}

// Prepare Comprehensive QRCode Data
$qrData = "TRANSLOG\n";
$qrData .= "ID: #RES-{$resId}\n";
$qrData .= "Passager: {$ticket['nomP']} {$ticket['prenomP']}\n";
$qrData .= "Trajet: {$ticket['ville_depart']} -> {$ticket['ville_arrive']}\n";
$qrData .= "Date: " . date('d/m/Y', strtotime($ticket['date_depart'])) . " à " . substr($ticket['heure_depart'], 0, 5) . "\n";
$qrData .= "Véhicule: {$ticket['immatriculation']} ({$ticket['marque']})\n";
$qrData .= "Places: {$ticket['nr_place']}\n";
$qrData .= "Total: " . number_format($ticket['prix_total'], 0) . " FBU\n";
$qrData .= "Paiement: " . ($ticket['id_Payment'] ? "PAYÉ (" . $ticket['pay_statut'] . ")" : "À PAYER");

$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);

$status_label = $ticket['id_Payment'] ? "PAYÉ" : "À PAYER";
$status_color = $ticket['id_Payment'] ? "#059669" : "#ef4444";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #RES-<?php echo $resId; ?> - TRANSLOG</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }

        .ticket-box {
            background: white;
            width: 100%;
            max-width: 500px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
        }

        .ticket-header {
            background: #0f172a;
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 1.2rem;
        }

        .res-id {
            background: rgba(255,255,255,0.1);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .ticket-body {
            padding: 2rem;
        }

        .passenger-info {
            margin-bottom: 2rem;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 1.5rem;
        }

        .label {
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
        }

        .value {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .trip-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .city-box {
            text-align: center;
        }

        .arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
        }

        .footer-qr {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #e2e8f0;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-weight: 800;
            font-size: 0.75rem;
            background: <?php echo $status_color; ?>15;
            color: <?php echo $status_color; ?>;
            border: 1px solid <?php echo $status_color; ?>30;
        }

        .print-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #0f172a;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        @media print {
            body { background: white; padding: 0; }
            .ticket-box { box-shadow: none; border: 1px solid #e2e8f0; border-radius: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="ticket-box">
        <div class="ticket-header">
            <div class="logo">TRANSLOG</div>
            <div class="res-id">#RES-<?php echo $resId; ?></div>
        </div>

        <div class="ticket-body">
            <div class="passenger-info">
                <div class="label">Passager</div>
                <div class="value"><?php echo htmlspecialchars($ticket['nomP'] . ' ' . $ticket['prenomP']); ?></div>
                <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;">
                    <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($ticket['telephone']); ?>
                </div>
            </div>

            <div class="trip-grid">
                <div class="city-box" style="text-align: left;">
                    <div class="label">Départ</div>
                    <div class="value"><?php echo htmlspecialchars($ticket['ville_depart']); ?></div>
                    <div style="font-size: 0.85rem; color: #64748b;"><?php echo substr($ticket['heure_depart'], 0, 5); ?></div>
                </div>
                <div class="city-box" style="text-align: right;">
                    <div class="label">Arrivée</div>
                    <div class="value"><?php echo htmlspecialchars($ticket['ville_arrive']); ?></div>
                    <div style="font-size: 0.85rem; color: #64748b;"><?php echo substr($ticket['heure_arrivee'], 0, 5); ?></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div>
                    <div class="label">Date</div>
                    <div class="value"><?php echo date('d M Y', strtotime($ticket['date_depart'])); ?></div>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden; background: #f1f5f9; border: 1px solid #e2e8f0; flex-shrink: 0;">
                        <?php if ($ticket['photo']): ?>
                            <img src="../../<?php echo $ticket['photo']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                <i class="fas fa-bus"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="label">Véhicule</div>
                        <div class="value"><?php echo htmlspecialchars($ticket['immatriculation']); ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars($ticket['marque']); ?></div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                <div>
                    <div class="label">Siège</div>
                    <div class="value" style="font-size: 1.5rem;">#<?php echo $ticket['nr_place']; ?></div>
                </div>
                <div style="text-align: right;">
                    <div class="label">Statut</div>
                    <div class="status-badge"><?php echo $status_label; ?></div>
                </div>
            </div>
        </div>

        <div class="footer-qr">
            <div style="font-size: 0.7rem; color: #94a3b8; max-width: 200px;">
                Veuillez présenter ce ticket à l'embarquement. <br>
                Merci de voyager avec <strong>TRANSLOG</strong>.
            </div>
            <!-- Improved QRCode Provider -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($qrData); ?>" alt="QRCode" style="border: 4px solid white; border-radius: 8px; width: 100px; height: 100px;">
        </div>
    </div>

    <!-- PDF Generation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <button onclick="downloadTicket()" class="print-btn" id="dl-btn">
        <i class="fas fa-file-pdf"></i> Télécharger le PDF
    </button>

    <script>
        function downloadTicket() {
            const btn = document.getElementById('dl-btn');
            btn.style.display = 'none'; // Hide button in PDF
            
            const element = document.querySelector('.ticket-box');
            const opt = {
                margin:       [0.5, 0.5, 0.5, 0.5],
                filename:     'Ticket_TRANSLOG_RES-<?php echo $resId; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            // PDF Generation
            html2pdf().set(opt).from(element).save().then(() => {
                btn.style.display = 'flex'; // Show button back
                
                // Auto-redirect to home for public users after download
                <?php if ($is_public): ?>
                setTimeout(() => {
                    window.location.href = '../../index.php';
                }, 2000); // 2 seconds delay to let the user see the "Download started" feedback
                <?php endif; ?>
            });
        }
    </script>
</body>
</html>
