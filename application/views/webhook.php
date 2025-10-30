<?php
session_start(); // Mulai session untuk akses $_SESSION

/**
 * Webhook handler untuk menerima status pesan dari Wablas
 * Akan menyimpan log ke file .txt dan menampilkannya dalam bentuk tabel HTML jika diakses lewat browser
 */

// Proses jika menerima POST dari Wablas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $content = json_decode($raw, true);

    if ($content && json_last_error() === JSON_ERROR_NONE) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'id'        => $content['id'] ?? '',
            'status'    => $content['status'] ?? '',
            'phone'     => $content['phone'] ?? '',
            'note'      => $content['note'] ?? '',
            'sender'    => $content['sender'] ?? '',
            'deviceId'  => $content['deviceId'] ?? '',
            'ref_id'   => $content['ref_id'] ?? '', // <- Tambahkan ini
        ];

        // Simpan ke file log
        file_put_contents('webhook-log.txt', json_encode($logData) . PHP_EOL, FILE_APPEND);

        // Response sukses
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        // Log error jika JSON tidak valid
        file_put_contents('webhook-error.txt', date('Y-m-d H:i:s') . " - INVALID JSON: $raw" . PHP_EOL, FILE_APPEND);
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    }
    exit;
}

// Jika bukan POST, berarti akses dari browser → tampilkan log
$logs = [];
$logFile = 'webhook-log.txt';
$session_ref_id = $_SESSION['ref_id'] ?? null;

if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $decoded = json_decode($line, true);
        if (is_array($decoded)) {
            // Jika ref_id tersedia di session, filter log-nya
            if ($session_ref_id) {
                if (isset($decoded['ref_id']) && $decoded['ref_id'] == $session_ref_id) {
                    $logs[] = $decoded;
                }
            } else {
                // Jika tidak login, tampilkan semua log
                $logs[] = $decoded;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Webhook Log - Wablas</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #007bff; color: #fff; }
        h2 { color: #333; }
        .log-wrapper { overflow-x: auto; }
    </style>
</head>
<body>
    <h2>📩 Log Webhook Wablas</h2>
    <?php if ($session_ref_id): ?>
        <p><strong>Menampilkan log untuk ref_id:</strong> <?= htmlspecialchars($session_ref_id) ?></p>
    <?php else: ?>
        <p><strong>Menampilkan semua log (tanpa filter session).</strong></p>
    <?php endif ?>
    <div class="log-wrapper">
        <?php if (!empty($logs)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>ID Pesan</th>
                        <th>Status</th>
                        <th>Nomor</th>
                        <th>Note</th>
                        <th>Pengirim</th>
                        <th>Device ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($logs) as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['timestamp']) ?></td>
                            <td><?= htmlspecialchars($log['id']) ?></td>
                            <td><?= htmlspecialchars($log['status']) ?></td>
                            <td><?= htmlspecialchars($log['phone']) ?></td>
                            <td><?= htmlspecialchars($log['note']) ?></td>
                            <td><?= htmlspecialchars($log['sender']) ?></td>
                            <td><?= htmlspecialchars($log['deviceId']) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada log yang tersedia untuk sesi ini.</p>
        <?php endif ?>
    </div>
</body>
</html>
