<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in (CLIENT side)
if (!isset($_SESSION['client_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

include 'connect.php';

// Get user's submitted tickets
$email = isset($_SESSION['client_email']) ? $conn->real_escape_string($_SESSION['client_email']) : '';
$client_id = isset($_SESSION['client_id']) ? (int)$_SESSION['client_id'] : 0;

if (empty($email) && $client_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Email or Client ID not found in session']);
    exit;
}

$sql = "SELECT 
    ticket_id,
    subject_,
    description_,
    status_,
    category_id,
    attachment,
    created_at,
    date_resolved
FROM TICKETS 
WHERE submitted_by = $client_id
ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $tickets = [];
    while ($row = $result->fetch_assoc()) {
        // Convert "To be Reviewed" to "Pending" for display
        $status = $row['status_'] === 'To be Reviewed' ? 'Pending' : $row['status_'];
        
        $tickets[] = [
            'ticket_id' => $row['ticket_id'],
            'ticket_no' => 'TK' . str_pad($row['ticket_id'], 6, '0', STR_PAD_LEFT),
            'subject' => $row['subject_'],
            'description' => $row['description_'],
            'status' => $status,
            'attachment' => $row['attachment'],
            'created_at' => date('M d, Y H:i', strtotime($row['created_at'])),
            'date_resolved' => $row['date_resolved'] ? date('M d, Y', strtotime($row['date_resolved'])) : null
        ];
    }
    echo json_encode(['success' => true, 'tickets' => $tickets]);
} else {
    echo json_encode(['success' => true, 'tickets' => []]);
}

$conn->close();
?>
