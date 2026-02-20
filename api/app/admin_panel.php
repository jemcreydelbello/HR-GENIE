<?php
/**
 * Enhanced FAQ Admin Panel with Semantic Search Support
 * Manage articles with embedding generation and analytics
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequests();
}

if ($action === 'generate_embeddings') {
    generateEmbeddings();
}

// Page content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Admin - Semantic Search Management</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            margin: 20px;
        }
        
        .admin-sidebar {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
        }
        
        .admin-sidebar a {
            display: block;
            padding: 10px 15px;
            margin: 5px 0;
            background: white;
            border-left: 4px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: #007bff;
            color: white;
            border-left-color: #0056b3;
        }
        
        .admin-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card.high {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.medium {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card.low {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .embedding-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .embedding-status.generated {
            background: #d4edda;
            color: #155724;
        }
        
        .embedding-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .embedding-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .confidence-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .confidence-high {
            background: #d4edda;
            color: #155724;
        }
        
        .confidence-medium {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .confidence-low {
            background: #fff3cd;
            color: #856404;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .modal-footer {
            margin-top: 20px;
            text-align: right;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <div class="admin-sidebar">
            <h3>FAQ Admin</h3>
            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="?page=articles" class="<?= $page === 'articles' ? 'active' : '' ?>">📝 Articles</a>
            <a href="?page=embeddings" class="<?= $page === 'embeddings' ? 'active' : '' ?>">🤖 Embeddings</a>
            <a href="?page=analytics" class="<?= $page === 'analytics' ? 'active' : '' ?>">📈 Analytics</a>
            <a href="?page=settings" class="<?= $page === 'settings' ? 'active' : '' ?>">⚙️ Settings</a>
            <hr>
            <a href="../logout.php">🚪 Logout</a>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <?php
            switch ($page) {
                case 'dashboard':
                    include 'pages/admin_dashboard.php';
                    break;
                case 'articles':
                    include 'pages/admin_articles.php';
                    break;
                case 'embeddings':
                    include 'pages/admin_embeddings.php';
                    break;
                case 'analytics':
                    include 'pages/admin_analytics.php';
                    break;
                case 'settings':
                    include 'pages/admin_settings.php';
                    break;
                default:
                    include 'pages/admin_dashboard.php';
            }
            ?>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openModal(id) {
            document.getElementById(id).classList.add('show');
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('show');
        }
        
        function generateEmbeddings() {
            if (confirm('Generate embeddings for all articles without embeddings?\nThis may take a few minutes.')) {
                fetch('semantic_actions.php?action=generate_embeddings', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Embeddings generated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
    </script>
</body>
</html>

<?php
function handlePostRequests() {
    // Handle AJAX requests
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_article':
                addArticle();
                break;
            case 'update_article':
                updateArticle();
                break;
            case 'delete_article':
                deleteArticle();
                break;
        }
    }
}

function addArticle() {
    global $conn;
    
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 1;
    
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'error' => 'Title and content are required']);
        exit;
    }
    
    $sql = "INSERT INTO articles (title, content, category_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $title, $content, $category_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

function generateEmbeddings() {
    // Call Python API to generate embeddings
    $api_url = 'http://localhost:5001/api/generate-embeddings';
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo json_encode(['success' => true, 'data' => json_decode($response, true)]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to generate embeddings']);
    }
    exit;
}
?>
