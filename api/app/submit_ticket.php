<?php
session_start();

// Allow POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php';

// Require login
if (!isset($_SESSION['client_id'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'You must be logged in to submit a ticket.'
    ]);
    exit;
}

try {
    if (!$conn) {
        throw new Exception('Database connection failed.');
    }

    $user_id = (int) $_SESSION['client_id'];

    // Get and validate inputs
    $category_id = (int) ($_POST['category'] ?? 0);
    $subject     = trim($_POST['subject'] ?? '');
    $message     = trim($_POST['message'] ?? '');
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');

    // Validate all required fields
    if (!$category_id) {
        throw new Exception('Please select a category.');
    }
    if (!$subject) {
        throw new Exception('Subject is required.');
    }
    if (!$message) {
        throw new Exception('Message is required.');
    }
    if (!$full_name) {
        throw new Exception('Full name is required.');
    }
    if (!$email) {
        throw new Exception('Email is required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address.');
    }

    /// File upload (optional)
    $attachment_path = null;
    $upload_dir = __DIR__ . '/../admin/uploads/tickets/';
    $allowed_ext = ['pdf','doc','docx','txt','jpg','jpeg','png','gif','xls','xlsx'];
    $max_size = 10 * 1024 * 1024;

    if (!empty($_FILES['attachment']['name'])) {

        if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed.');
        }

        if ($_FILES['attachment']['size'] > $max_size) {
            throw new Exception('File exceeds 10MB limit.');
        }

        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            throw new Exception('File type not allowed.');
        }

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
            throw new Exception('Upload directory not writable.');
        }

        $filename = 'ticket_' . time() . '_' . uniqid() . '.' . $ext;
        $target = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
            throw new Exception('Failed to save attachment.');
        }

        // Store absolute path instead of relative
        $attachment_path = $target;
    }
    // Insert ticket
    $sql = "
        INSERT INTO TICKETS
        (submitted_by, category_id, subject_, description_, client_name, client_email, status_, attachment, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $status = 'Pending';
    $stmt->bind_param(
        'iissssss',
        $user_id,
        $category_id,
        $subject,
        $message,
        $full_name,
        $email,
        $status,
        $attachment_path
    );

    if (!$stmt->execute()) {
        throw new Exception('Ticket submission failed.');
    }

    $ticket_id = $conn->insert_id;
    $ticket_number = 'TK' . str_pad($ticket_id, 6, '0', STR_PAD_LEFT);

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success'       => true,
        'ticket_id'     => $ticket_id,
        'ticket_number' => $ticket_number
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
