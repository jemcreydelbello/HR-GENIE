<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php';

try {
    if (!$conn) {
        throw new Exception('Database connection failed.');
    }

    $sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'category_id' => intval($row['category_id']),
            'category_name' => $row['category_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
