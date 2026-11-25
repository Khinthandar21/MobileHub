<?php
require_once __DIR__ . '/includes/auth.php';
require_role('admin', 'editor');

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT order_number, payment_method FROM orders WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$order = $stmt->fetch();

if (!$order || $order['payment_method'] !== 'bank_transfer') {
    http_response_code(404);
    exit('Payment proof not found.');
}

$orderNumber = preg_replace('/[^A-Za-z0-9-]/', '', (string)$order['order_number']);
$proofPath = null;
$proofMime = null;
foreach (['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'] as $extension => $mime) {
    $candidate = UPLOADS_PATH . '/payment-proofs/' . $orderNumber . '.' . $extension;
    if (is_file($candidate)) {
        $proofPath = $candidate;
        $proofMime = $mime;
        break;
    }
}

if (!$proofPath) {
    http_response_code(404);
    exit('Payment proof not found.');
}

header('Content-Type: ' . $proofMime);
header('Content-Length: ' . (string)filesize($proofPath));
header('Content-Disposition: inline; filename="payment-proof-' . $orderNumber . '.' . pathinfo($proofPath, PATHINFO_EXTENSION) . '"');
header('X-Content-Type-Options: nosniff');
readfile($proofPath);
exit;
