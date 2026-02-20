<?php
header('Content-Type: application/json');

try {
    require_once 'connect.php';

    // get cat from categories table
    $sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id'   => $row['category_id'],
            'name' => $row['category_name']
        ];
    }

    $conn->close();

    echo json_encode([
        'success'    => true,
        'categories' => $categories,
        'count'      => count($categories)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success'    => false,
        'error'      => $e->getMessage(),
        'categories' => []
    ]);
}
?>
