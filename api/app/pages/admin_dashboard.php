<?php
/**
 * Admin Dashboard - Overview of FAQ system health and analytics
 */

require_once '../connect.php';

// Get statistics
$stats = getSystemStatistics();

function getSystemStatistics() {
    global $conn;
    
    $stats = [
        'total_articles' => 0,
        'articles_with_embeddings' => 0,
        'embeddings_pending' => 0,
        'total_queries' => 0,
        'avg_confidence' => 0,
        'high_confidence_count' => 0,
        'medium_confidence_count' => 0,
        'low_confidence_count' => 0,
        'no_match_count' => 0,
        'api_status' => checkAPIStatus()
    ];
    
    // Total articles
    $result = $conn->query("SELECT COUNT(*) as count FROM articles");
    $stats['total_articles'] = $result->fetch_assoc()['count'];
    
    // Articles with embeddings
    $result = $conn->query("SELECT COUNT(*) as count FROM articles WHERE has_embedding = 1");
    $stats['articles_with_embeddings'] = $result->fetch_assoc()['count'];
    
    // Pending embeddings
    $stats['embeddings_pending'] = $stats['total_articles'] - $stats['articles_with_embeddings'];
    
    // Search analytics
    $result = $conn->query("SELECT COUNT(*) as count FROM search_analytics");
    $stats['total_queries'] = $result->fetch_assoc()['count'];
    
    // Average confidence
    $result = $conn->query("SELECT AVG(confidence_score) as avg FROM search_analytics WHERE confidence_score IS NOT NULL");
    $avg = $result->fetch_assoc()['avg'];
    $stats['avg_confidence'] = $avg ? round($avg, 2) : 0;
    
    // Confidence distribution
    $result = $conn->query("SELECT response_strategy, COUNT(*) as count FROM search_analytics GROUP BY response_strategy");
    while ($row = $result->fetch_assoc()) {
        $strategy = $row['response_strategy'];
        if ($strategy === 'direct_answer') {
            $stats['high_confidence_count'] = $row['count'];
        } elseif ($strategy === 'clarify_and_answer') {
            $stats['medium_confidence_count'] = $row['count'];
        } elseif ($strategy === 'clarify_question') {
            $stats['low_confidence_count'] = $row['count'];
        } elseif ($strategy === 'no_match') {
            $stats['no_match_count'] = $row['count'];
        }
    }
    
    return $stats;
}

function checkAPIStatus() {
    $api_url = 'http://localhost:5001/api/health';
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code === 200;
}
?>

<h1>📊 FAQ Admin Dashboard</h1>

<div style="margin-top: 20px;">
    <!-- System Status -->
    <div class="alert alert-info">
        <strong>System Status:</strong>
        <?php if ($stats['api_status']): ?>
            ✓ Semantic API is <strong style="color: green;">running</strong>
        <?php else: ?>
            ✗ Semantic API is <strong style="color: red;">offline</strong> - Start with: <code>python semantic_api/app.py</code>
        <?php endif; ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Articles</div>
        <div class="stat-number"><?= $stats['total_articles'] ?></div>
        <div class="stat-label">in Knowledge Base</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">Embeddings Generated</div>
        <div class="stat-number"><?= $stats['articles_with_embeddings'] ?></div>
        <div class="stat-label"><?= $stats['articles_with_embeddings'] ?>/<?= $stats['total_articles'] ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">Total Queries</div>
        <div class="stat-number"><?= $stats['total_queries'] ?></div>
        <div class="stat-label">this session</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-label">Avg Confidence</div>
        <div class="stat-number"><?= ($stats['avg_confidence'] * 100) . '%' ?></div>
        <div class="stat-label">Match quality</div>
    </div>
</div>

<!-- Confidence Distribution -->
<div style="margin-top: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
    <h3>Query Distribution by Confidence Level</h3>
    
    <div class="stats-grid" style="margin-top: 15px;">
        <div class="stat-card high">
            <div class="stat-label">High Confidence</div>
            <div class="stat-number"><?= $stats['high_confidence_count'] ?></div>
            <div class="stat-label">Direct Answers</div>
        </div>
        
        <div class="stat-card medium">
            <div class="stat-label">Medium Confidence</div>
            <div class="stat-number"><?= $stats['medium_confidence_count'] ?></div>
            <div class="stat-label">Clarify & Answer</div>
        </div>
        
        <div class="stat-card low">
            <div class="stat-label">Low Confidence</div>
            <div class="stat-number"><?= $stats['low_confidence_count'] ?></div>
            <div class="stat-label">Ask Clarification</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">No Match</div>
            <div class="stat-number"><?= $stats['no_match_count'] ?></div>
            <div class="stat-label">Fallback Response</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div style="margin-top: 30px;">
    <h3>Quick Actions</h3>
    
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php if ($stats['embeddings_pending'] > 0): ?>
            <button class="btn btn-success" onclick="generateEmbeddings()">
                🤖 Generate Embeddings (<?= $stats['embeddings_pending'] ?> pending)
            </button>
        <?php else: ?>
            <button class="btn btn-success" disabled>
                ✓ All embeddings generated
            </button>
        <?php endif; ?>
        
        <a href="?page=articles" class="btn btn-primary">📝 Manage Articles</a>
        <a href="?page=analytics" class="btn btn-primary">📈 View Analytics</a>
        <a href="../BOTPRESS_SETUP_GUIDE.md" class="btn btn-primary" target="_blank">📚 Setup Guide</a>
    </div>
</div>

<!-- System Health Check -->
<div style="margin-top: 30px;">
    <h3>System Health</h3>
    
    <div style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
        <table style="width: 100%;">
            <tr>
                <td><strong>Semantic API</strong></td>
                <td><?= $stats['api_status'] ? '<span style="color: green;">✓ Running</span>' : '<span style="color: red;">✗ Offline</span>' ?></td>
            </tr>
            <tr>
                <td><strong>Database Connection</strong></td>
                <td><span style="color: green;">✓ Connected</span></td>
            </tr>
            <tr>
                <td><strong>Articles Table</strong></td>
                <td><span style="color: green;">✓ Ready</span></td>
            </tr>
            <tr>
                <td><strong>Analytics Tables</strong></td>
                <td><span style="color: green;">✓ Ready</span></td>
            </tr>
            <tr>
                <td><strong>Embedding Model</strong></td>
                <td>all-MiniLM-L6-v2 (22M parameters)</td>
            </tr>
        </table>
    </div>
</div>

<!-- Recent Queries -->
<div style="margin-top: 30px;">
    <h3>Recent User Queries</h3>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Query</th>
                    <th>Matched Article</th>
                    <th>Confidence</th>
                    <th>Strategy</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("
                    SELECT 
                        sa.query,
                        sa.confidence_score,
                        sa.response_strategy,
                        sa.created_at,
                        a.title
                    FROM search_analytics sa
                    LEFT JOIN articles a ON sa.best_match_id = a.id
                    ORDER BY sa.created_at DESC
                    LIMIT 10
                ");
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars(substr($row['query'], 0, 40)) . '...</td>';
                        echo '<td>' . htmlspecialchars($row['title'] ?? 'N/A') . '</td>';
                        echo '<td><span class="confidence-' . 
                             (($row['confidence_score'] >= 0.75) ? 'high' : 
                              (($row['confidence_score'] >= 0.55) ? 'medium' : 'low')) . 
                             ' confidence-badge">' . round($row['confidence_score'], 2) . '</span></td>';
                        echo '<td>' . htmlspecialchars($row['response_strategy']) . '</td>';
                        echo '<td>' . date('M d, H:i', strtotime($row['created_at'])) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5" style="text-align: center; color: #666;">No queries yet</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function generateEmbeddings() {
        if (confirm('Generate embeddings for all articles without embeddings?\nThis may take a few minutes.')) {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div> Generating...';
            
            fetch('semantic_actions.php?action=generate_embeddings', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Embeddings generated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                    btn.disabled = false;
                    btn.innerHTML = '🤖 Generate Embeddings';
                }
            })
            .catch(err => {
                alert('Error: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = '🤖 Generate Embeddings';
            });
        }
    }
</script>
