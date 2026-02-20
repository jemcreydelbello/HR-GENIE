<?php
header('Content-Type: application/json; charset=utf-8');

include 'connect.php';

try {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
    
    // Get published articles with highest helpful feedback counts
    $sql = "
        SELECT 
            a.article_id,
            a.title,
            sc.subcategory_name as category,
            COUNT(CASE WHEN af.is_helpful = 1 THEN 1 END) as helpful_count
        FROM articles a
        LEFT JOIN article_feedback af ON a.article_id = af.article_id
        LEFT JOIN subcategories sc ON a.subcategory_id = sc.subcategory_id
        WHERE a.status = 'Published'
        GROUP BY a.article_id, a.title, sc.subcategory_name
        ORDER BY helpful_count DESC, a.created_at DESC
        LIMIT ?
    ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $limit);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $articles = [];
    
    while ($row = $result->fetch_assoc()) {
        $articles[] = [
            'article_id' => intval($row['article_id']),
            'title' => htmlspecialchars($row['title']),
            'category' => htmlspecialchars($row['category'] ?? 'General'),
            'helpful_count' => intval($row['helpful_count'])
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'articles' => $articles,
        'count' => count($articles)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
