<?php
header('Content-Type: application/json');
include "connect.php";

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$likeQuery = "%$query%";

$results = [];

// Search articles
$stmt = $conn->prepare("
    SELECT 
        a.article_id as id,
        a.title, 
        sc.subcategory_name as category,
        'article' as type
    FROM articles a
    LEFT JOIN subcategories sc ON a.subcategory_id = sc.subcategory_id
    WHERE (a.title LIKE ? OR a.content LIKE ?)
       AND a.status = 'Published'
    LIMIT 10
");

if ($stmt) {
    $stmt->bind_param('ss', $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = [
            'type' => 'article',
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'category' => htmlspecialchars($row['category'] ?? 'Uncategorized'),
        ];
    }
    $stmt->close();
}

// Search categories
$stmt2 = $conn->prepare("
    SELECT category_id as id, category_name, description_
    FROM categories
    WHERE LOWER(category_name) LIKE LOWER(?)
    LIMIT 5
");

if ($stmt2) {
    $stmt2->bind_param('s', $likeQuery);
    $stmt2->execute();
    $catResult = $stmt2->get_result();
    
    while ($row = $catResult->fetch_assoc()) {
        $results[] = [
            'type' => 'category',
            'id' => $row['id'],
            'title' => htmlspecialchars($row['category_name']),
            'preview' => htmlspecialchars($row['description_'] ?? '')
        ];
    }
    $stmt2->close();
}

$conn->close();

echo json_encode($results);
?>