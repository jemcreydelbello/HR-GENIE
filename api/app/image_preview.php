
<?php
session_start();

if (!isset($_SESSION['client_id'])) {
    http_response_code(401);
    exit;
}

include 'connect.php';

$ticket_id = intval($_GET['ticket_id'] ?? 0);
$client_id = $_SESSION['client_id'];

if ($ticket_id <= 0) {
    http_response_code(400);
    exit;
}

$stmt = $mysqli->prepare("SELECT attachment FROM tickets WHERE ticket_id = ? AND submitted_by = ?");
$stmt->bind_param("ii", $ticket_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();

if (!$ticket || empty($ticket['attachment'])) {
    http_response_code(404);
    exit;
}

$file_path = trim($ticket['attachment']);

// Convert relative path to absolute
if (strpos($file_path, '..') === 0) {
    $file_path = __DIR__ . '/' . $file_path;
}

$file_path = realpath($file_path);

if (!$file_path || !file_exists($file_path) || !is_readable($file_path)) {
    http_response_code(404);
    exit;
}

$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($ext, $allowed)) {
    http_response_code(403);
    exit;
}

$mime_types = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
];

if (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mime_types[$ext]);
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: public, max-age=3600');

readfile($file_path);
exit;
?>