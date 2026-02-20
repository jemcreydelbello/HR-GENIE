<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once 'connect.php';

try {
    $articles = [];
    
    // First, try to fetch top 4 helpful articles from article_feedback
    $sql_helpful = "
        SELECT DISTINCT 
            a.article_id, 
            a.title, 
            a.category,
            COUNT(af.feedback_id) as helpful_count
        FROM articles a
        INNER JOIN article_feedback af ON a.article_id = af.article_id
        WHERE af.is_helpful = 1 AND a.status = 'Published'
        GROUP BY a.article_id, a.title, a.category
        ORDER BY helpful_count DESC, a.created_at DESC
        LIMIT 4
    ";
    
    $result = $conn->query($sql_helpful);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $articles[] = [
                'article_id' => (int)$row['article_id'],
                'id' => (int)$row['article_id'],
                'title' => htmlspecialchars($row['title']),
                'category' => htmlspecialchars($row['category'])
            ];
        }
    }
    
    // Fallback: if no helpful articles, fetch recent 4 articles
    if (empty($articles)) {
        $sql_recent = "
            SELECT article_id, title, category 
            FROM articles 
            WHERE article_id IS NOT NULL AND status = 'Published'
            ORDER BY created_at DESC 
            LIMIT 4
        ";
        
        $result_recent = $conn->query($sql_recent);
        
        if ($result_recent && $result_recent->num_rows > 0) {
            while ($row = $result_recent->fetch_assoc()) {
                $articles[] = [
                    'article_id' => (int)$row['article_id'],
                    'id' => (int)$row['article_id'],
                    'title' => htmlspecialchars($row['title']),
                    'category' => htmlspecialchars($row['category'])
                ];
            }
        }
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'articles' => $articles,
        'count' => count($articles)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'articles' => []
    ]);
}
?>
