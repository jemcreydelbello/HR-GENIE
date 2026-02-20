<?php
session_start();
header('Content-Type: application/json');

// Verify client is logged in
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once '../admin/db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

$ticket_id = isset($data['ticket_id']) ? intval($data['ticket_id']) : null;
$subject = isset($data['subject']) ? trim($data['subject']) : null;
$description = isset($data['description']) ? trim($data['description']) : null;

// Validate input
if (!$ticket_id || !$subject || !$description) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Verify ticket belongs to current client
$stmt = $connection->prepare("SELECT ticket_id FROM TICKETS WHERE ticket_id = ? AND submitted_by = ?");
$stmt->bind_param("ii", $ticket_id, $_SESSION['client_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized to edit this ticket']);
    exit;
}

// Update ticket
$stmt = $connection->prepare("UPDATE TICKETS SET subject = ?, description = ? WHERE ticket_id = ? AND submitted_by = ?");
$stmt->bind_param("ssii", $subject, $description, $ticket_id, $_SESSION['client_id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Ticket updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
}

$stmt->close();
$connection->close();
?>
