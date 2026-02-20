<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "connect.php";
// Check if user is logged in
$is_logged_in = isset($_SESSION['client_id']) && !empty($_SESSION['client_id']);

// Check if this is admin preview mode (allows viewing unpublished articles)
$is_admin_preview = isset($_GET['preview_mode']) && $_GET['preview_mode'] === 'admin' && isset($_SESSION['admin_id']);

$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$article = [];
$steps = [];
$article_image = null;
$related_articles = [];
$category_name = '';
$subcategory_name = '';

if ($article_id > 0) {
    $sql = "
        SELECT a.article_id, a.title, a.article_type, a.created_at, a.article_image, a.admin_id, a.introduction, a.subcategory_id, a.status
        FROM articles a
        WHERE a.article_id = ?";
    
    // Only filter by published status if NOT in admin preview mode
    if (!$is_admin_preview) {
        $sql .= " AND a.status = 'Published'";
    }
    $sql .= "";

    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param('i', $article_id);
    if (!$stmt->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $article = $result->fetch_assoc();
        
        // Set article image - only if it's a valid single filename (not JSON)
        $article_image = null;
        if (!empty($article['article_image'])) {
            // Check if it's JSON (contains curly braces) - if so, ignore it
            if (strpos($article['article_image'], '{') === false && strpos($article['article_image'], '}') === false) {
                $article_image = '../admin/uploads/' . htmlspecialchars($article['article_image']);
            }
        } 
        
        // For standard articles, fetch description from article_standard table
        $standard_description = null;
        $standard_image = null;
        if ($article['article_type'] === 'standard') {
            $standard_stmt = $conn->prepare("SELECT description, standard_image FROM article_standard WHERE article_id = ?");
            if ($standard_stmt) {
                $standard_stmt->bind_param('i', $article_id);
                $standard_stmt->execute();
                $standard_result = $standard_stmt->get_result();
                if ($standard_result->num_rows > 0) {
                    $standard_row = $standard_result->fetch_assoc();
                    $standard_description = $standard_row['description'];
                    $standard_image = !empty($standard_row['standard_image']) 
                        ? '../admin/uploads/articles/' . htmlspecialchars($standard_row['standard_image'])
                        : null;
                }
                $standard_stmt->close();
            }
        }
        
        // Get subcategory and category names for breadcrumb
        $subcat_stmt = $conn->prepare("
            SELECT s.subcategory_name, s.category_id, c.category_name 
            FROM subcategories s
            LEFT JOIN categories c ON s.category_id = c.category_id
            WHERE s.subcategory_id = ?
        ");
        if ($subcat_stmt) {
            $subcat_stmt->bind_param('i', $article['subcategory_id']);
            $subcat_stmt->execute();
            $subcat_result = $subcat_stmt->get_result();
            if ($subcat_result->num_rows > 0) {
                $subcat_row = $subcat_result->fetch_assoc();
                $article['category'] = $subcat_row['subcategory_name'];
                $article['category_id'] = $subcat_row['category_id'];
                $category_name = $subcat_row['category_name'];
                $subcategory_name = $subcat_row['subcategory_name'];
            }
            $subcat_stmt->close();
        }
        
        if ($article['article_type'] === 'standard') {
            // Content is already fetched from article_standard table
     
         } elseif ($article['article_type'] === 'step_by_step') {

            $steps_stmt = $conn->prepare("SELECT step_id, step_number, step_title, step_description, step_image FROM article_steps WHERE article_id = ? ORDER BY step_number ASC");
            if ($steps_stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $steps_stmt->bind_param('i', $article_id);
            if (!$steps_stmt->execute()) {
                die('Execute failed: ' . htmlspecialchars($steps_stmt->error));
            }
            $steps_result = $steps_stmt->get_result();
            if ($steps_result === false) {
                die('Get result failed: ' . htmlspecialchars($steps_stmt->error));
            }
            
            // Debug: Check how many rows
            error_log('Article ID: ' . $article_id . ', Article Type: ' . $article['article_type'] . ', Rows found: ' . $steps_result->num_rows);
            
            while ($step_row = $steps_result->fetch_assoc()) {
                $steps[] = $step_row;
            }
            $steps_stmt->close();
        } elseif ($article['article_type'] === 'simple_question') {
            $qa_stmt = $conn->prepare("SELECT question, answer FROM article_qa WHERE article_id = ?");
            if ($qa_stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $qa_stmt->bind_param('i', $article_id);
            if (!$qa_stmt->execute()) {
                die('Execute failed: ' . htmlspecialchars($qa_stmt->error));
            }
            $qa_result = $qa_stmt->get_result();
            if ($qa_result === false) {
                die('Get result failed: ' . htmlspecialchars($qa_stmt->error));
            }
            
            while ($qa_row = $qa_result->fetch_assoc()) {
                $steps[] = $qa_row;
            }
            $qa_stmt->close();
        }
        
       // Fetch related articles based on the same subcategory
        if (!empty($article['subcategory_id'])) {
            $related_sql = "
                SELECT a.article_id, a.title FROM articles a
                WHERE a.subcategory_id = ?
                AND a.article_id != ?";
            
            // Only filter by published status if NOT in admin preview mode
            if (!$is_admin_preview) {
                $related_sql .= " AND a.status = 'Published'";
            }
            $related_sql .= " LIMIT 5";
            
            $related_stmt = $conn->prepare($related_sql);
            if ($related_stmt !== false) {
                $related_stmt->bind_param('ii', $article['subcategory_id'], $article_id);
                $related_stmt->execute();
                $related_result = $related_stmt->get_result();
                while ($related_row = $related_result->fetch_assoc()) {
                    $related_articles[] = $related_row;
                }
                $related_stmt->close();
            }
        }

    }
    $stmt->close();
}


if (empty($article)) {
    // In preview mode, show error; in normal mode, redirect
    if ($is_admin_preview) {
        echo '<!DOCTYPE html><html><body style="padding: 20px; font-family: Arial;">';
        echo '<h2>Article not found</h2>';
        echo '<p>Article ID: ' . htmlspecialchars($article_id) . '</p>';
        echo '<p>Preview Mode: ' . ($is_admin_preview ? 'Yes (Admin)' : 'No (Public)') . '</p>';
        if (!isset($_SESSION['admin_id'])) {
            echo '<p style="color: red;"><strong>Warning:</strong> Admin session not set. Session admin_id: ' . (isset($_SESSION['admin_id']) ? 'SET' : 'NOT SET') . '</p>';
        }
        echo '</body></html>';
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}
?>
<?php
// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['is_helpful'])) {
    $is_helpful = intval($_POST['is_helpful']);
    
    if ($is_helpful === 0 || $is_helpful === 1) {
        $feedback_sql = "INSERT INTO article_feedback (article_id, is_helpful) 
                        VALUES (?, ?)";
        $feedback_stmt = $conn->prepare($feedback_sql);
        if ($feedback_stmt) {
            $feedback_stmt->bind_param('ii', $article_id, $is_helpful);
            $feedback_stmt->execute();
            $feedback_stmt->close();
            
            // Return JSON response for AJAX
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => true, 'message' => 'Feedback saved']);
                exit;
            }
        }
    }
}

// Fetch current user's feedback if logged in
$user_feedback = null;
if ($is_logged_in && $article_id > 0) {
    // Note: Since article_feedback doesn't track individual user feedback,
    // we can't retrieve user-specific feedback. This would require a dedicated feedback table.
    // For now, simply check if article has any feedback.
    $feedback_stats_sql = "SELECT COUNT(*) as total, SUM(is_helpful) as helpful_count FROM article_feedback WHERE article_id = ?";
    $feedback_stats_stmt = $conn->prepare($feedback_stats_sql);
    if ($feedback_stats_stmt) {
        $feedback_stats_stmt->bind_param('i', $article_id);
        $feedback_stats_stmt->execute();
        $feedback_stats_result = $feedback_stats_stmt->get_result();
        $feedback_stats_stmt->close();
    }
}

// Fetch feedback counts
$feedback_sql = "SELECT 
    SUM(CASE WHEN is_helpful = 1 THEN 1 ELSE 0 END) as helpful_count,
    SUM(CASE WHEN is_helpful = 0 THEN 1 ELSE 0 END) as unhelpful_count,
    COUNT(*) as total_feedback
    FROM article_feedback WHERE article_id = ?";
$feedback_stmt = $conn->prepare($feedback_sql);
$helpful_count = 0;
$unhelpful_count = 0;
$total_feedback = 0;
if ($feedback_stmt) {
    $feedback_stmt->bind_param('i', $article_id);
    $feedback_stmt->execute();
    $feedback_result = $feedback_stmt->get_result();
    if ($feedback_result->num_rows > 0) {
        $feedback_data = $feedback_result->fetch_assoc();
        $helpful_count = intval($feedback_data['helpful_count'] ?? 0);
        $unhelpful_count = intval($feedback_data['unhelpful_count'] ?? 0);
        $total_feedback = intval($feedback_data['total_feedback'] ?? 0);
    }
    $feedback_stmt->close();
}

// Return JSON if feedback counts are requested via AJAX
if (isset($_GET['get_feedback']) && $_GET['get_feedback'] === 'true') {
    header('Content-Type: application/json');
    echo json_encode([
        'helpful_count' => $helpful_count,
        'unhelpful_count' => $unhelpful_count,
        'total_feedback' => $total_feedback
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> - HR Genie</title>
    <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css">
    <style>
        /* Quill Editor Display Styles */
        .ql-editor {
            padding: 0 !important;
            font-size: 1rem;
            margin: 0 !important;
            margin-top: 0 !important;
            margin-block: 0 !important;
            margin-block-start: 0 !important;
            line-height: 1.6 !important;
            overflow: visible !important;
        }
        
        .ql-editor * {
            line-height: 1.6;
        }
        
        .ql-editor.ql-blank::before {
            display: none !important;
        }
        
        .ql-editor p {
            margin: 0 0 1rem 0 !important;
            line-height: 1.6 !important;
        }
        
        .ql-editor p:first-child {
            margin: 0 !important;
            margin-top: 0 !important;
            margin-block-start: 0 !important;
        }
        
        .ql-editor p:last-child {
            margin-bottom: 0 !important;
        }
        
        /* Remove top margin from first element in ql-editor */
        .ql-editor > :first-child {
            margin-top: 0 !important;
            margin-block-start: 0 !important;
            padding-top: 0 !important;
        }
        
        .ql-editor h1, .ql-editor h2, .ql-editor h3, .ql-editor h4, .ql-editor h5, .ql-editor h6 {
            margin: 1.5rem 0 0.5rem 0 !important;
            font-weight: bold;
            line-height: 1.4 !important;
        }
        
        .ql-editor > h1:first-child, .ql-editor > h2:first-child, .ql-editor > h3:first-child,
        .ql-editor > h4:first-child, .ql-editor > h5:first-child, .ql-editor > h6:first-child {
            margin-top: 0 !important;
        }
        
        .ql-editor ul, .ql-editor ol {
            margin: 0 0 1rem 1.5rem !important;
            line-height: 1.6 !important;
            padding-left: 1.5rem !important;
        }
        
        .ql-editor ul {
            list-style-type: disc !important;
        }
        
        .ql-editor ol {
            list-style-type: decimal !important;
        }
        
        .ql-editor li {
            line-height: 1.6 !important;
            margin-bottom: 0.5rem !important;
            list-style: inherit !important;
            display: list-item !important;
        }
        
        .ql-editor strong, .ql-editor b {
            font-weight: 700 !important;
            line-height: 1.6 !important;
        }
        
        .ql-editor em, .ql-editor i {
            font-style: italic !important;
            line-height: 1.6 !important;
        }
        
        .ql-editor blockquote {
            border-left: 4px solid #ccc;
            margin: 0 0 1rem 0 !important;
            padding-left: 1rem;
            line-height: 1.6 !important;
        }
        
        .ql-editor code {
            background-color: #f5f5f5;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: monospace;
            line-height: 1.6 !important;
        }
        
        .ql-editor pre {
            background-color: #f5f5f5;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            margin-bottom: 1rem !important;
            max-width: 100%;
            word-wrap: break-word;
            line-height: 1.6 !important;
        }
        
        .ql-editor img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .ql-editor a {
            color: #0066cc;
            text-decoration: underline;
            line-height: 1.6 !important;
        }
        
        .ql-editor a:hover {
            color: #0052a3;
        }
        
        /* Remove space before step descriptions */
        h3.text-lg.sm\:text-xl {
            margin-bottom: 0 !important;
        }
        
        .space-y-4 .ql-editor {
            margin-top: 0 !important;
            margin-block-start: 0 !important;
            padding-top: 0 !important;
            margin-top: -2.5rem !important;
        }
        
        .space-y-4 .ql-editor.pt-0.mt-0 {
            margin-top: 0 !important;
            padding-top: 0 !important;
            margin-top: -2.25rem !important;
        }
        
        .space-y-4 .ql-editor > :first-child {
            margin-top: 0 !important;
        }
        
        /* Override space-y padding */
        .space-y-4 > .ql-editor {
            margin-top: -2.5rem !important;
            padding-top: 0 !important;
        }
        
        /* Remove h3 margin completely */
        h3.mb-3.sm\:mb-4 {
            margin-bottom: 0 !important;
        }
        
        /* Remove space at top of standard article description */
        .text-gray-700.leading-relaxed.ql-editor {
            margin-top: 0 !important;
            margin-block-start: 0 !important;
            padding-top: 0 !important;
        }
        
        .text-gray-700.leading-relaxed.ql-editor > :first-child {
            margin-top: 0 !important;
            margin-block-start: 0 !important;
        }
        
        .text-gray-700.leading-relaxed.ql-editor > p:first-of-type {
            margin-top: 0 !important;
            margin-block-start: 0 !important;
            margin: 0 0 1rem 0 !important;
        }
        
        .text-gray-700.leading-relaxed.ql-editor p:first-child {
            margin-top: 0 !important;
            margin-block-start: 0 !important;
            margin: 0 0 1rem 0 !important;
        }
        
        /* Active TOC indicator */
        .toc-link { position: relative; padding-right: 12px; display: block; transition: all 0.3s ease; }
        .toc-link::before { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 3px; background-color: transparent; transition: background-color 0.3s ease; border-radius: 2px; }
        .toc-link.active::before { background-color: #2563eb; }
        .toc-link.active { color: #1e40af; font-weight: 600; }
        
        /* Article hero image responsive */
        .article-hero-image { height: auto !important; max-height: 60vh; }
    </style>
    <script>
    function refreshArticleContent() {
            const articleId = <?= $article_id ?>;
            
            fetch(`get_article_content.php?id=${articleId}&type=<?= $article['article_type'] ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update content based on article type
                        const contentDiv = document.querySelector('.prose');
                        if (contentDiv) {
                            contentDiv.innerHTML = data.html;
                        }
                        
                        // If it's a step article, update table of contents
                        if (<?= $article['article_type'] === 'step_by_step' ? 'true' : 'false' ?>) {
                            updateTableOfContents(data.steps);
                        }
                    }
                })
                .catch(error => console.log('Auto-refresh check completed'));
        }
        
    //Refresh every 30 seconds
        
        function updateTableOfContents(steps) {
            const tocNav = document.querySelector('nav');
            if (tocNav) {
                tocNav.innerHTML = steps.map(step => 
                    `<a href="#step-${step.step_id}" class="block text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                        ${step.step_title}
                    </a>`
                ).join('');
            }
        }
</script>
</head>
<body class="bg-[#f5f7fb] flex flex-col min-h-screen">

    <!-- Navbar -->
    <?php include "navbar.php"; ?>

    <!-- Main Content -->
    <div class="flex-1 w-full">
        <!-- Header Section -->
        <div class="bg-gray-50 border-b border-gray-200">
            <div class="w-full md:w-11/12 mx-auto px-3 sm:px-5 md:px-10 py-4 md:py-6">
              <!-- Breadcrumb Navigation -->
            <?php
                $breadcrumbs = [
                    ['label' => 'Home', 'url' => 'index.php'],
                    ['label' => htmlspecialchars($category_name), 'url' => 'category_page.php?cat_id=' . ($article['category_id'] ?? '')],
                    ['label' => htmlspecialchars($subcategory_name), 'url' => 'subcategory_page.php?subcat_id=' . ($article['subcategory_id'] ?? '')]
                ];
                include 'breadcrumb.php';
                ?>

                <!-- Title -->
                <div class="flex items-center gap-2 md:gap-3">
                    <h1 class="text-[28px] sm:text-[28px] md:text-3xl lg:text-4xl font-bold text-[#2c3e50] mb-3 md:mb-4"><?= htmlspecialchars($article['title']) ?></h1>
                    <?php if ($is_admin_preview && $article['status'] !== 'Published'): ?>
                        <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                            <i class="bi bi-exclamation-circle mr-1"></i>DRAFT
                        </span>
                    <?php endif; ?>
                </div>

               <!-- Meta Info: Category and Date -->
                <div class="flex items-center gap-2 sm:gap-4 flex-wrap">
                    <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs sm:text-sm font-medium">
                        <i class="bi bi-folder mr-1 sm:mr-2"></i><?= htmlspecialchars($article['category'] ?? 'General') ?>
                    </span>
                    <span class="text-xs sm:text-sm text-gray-600">
                        <i class="bi bi-calendar mr-2"></i><?= ($is_admin_preview && $article['status'] !== 'Published') ? 'Last updated' : 'Published' ?> on <?= date('F d, Y', strtotime($article['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>

     
    <!-- Article Layout Grid -->
    <div class="w-full">
          <!-- ROW 1: Article Image (Full Width) -->
         <?php if ($article_image): ?>
            <div class="w-full md:w-11/12 mx-auto px-3 sm:px-5 md:px-10">
                <img src="<?= htmlspecialchars($article_image) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-hero-image w-full h-screen object-cover hover:opacity-90 transition-opacity rounded-lg">
            </div>
        <?php endif; ?>

         <!-- ROW 2: Introduction (Left Column) - Only for Standard and Simple Question Articles -->
        <?php if (!empty($article['introduction']) && ($article['article_type'] === 'standard' || $article['article_type'] === 'simple_question')): ?>
            <div class="w-full md:w-11/12 mx-auto px-3 sm:px-5 md:px-10 py-4 sm:py-6 md:py-8">
            <div class="flex gap-8 items-start lg:items-start flex-col lg:flex-row">
                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                        <div class="text-gray-700 leading-relaxed text-base sm:text-base md:text-base" style="line-height: 1.8; color: #333;">
                            <?= $article['introduction'] ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

       <!-- ROW 3: Article Content -->
        <div class="w-full md:w-11/12 mx-auto px-3 sm:px-5 md:px-10 py-4 sm:py-6 md:py-8">
            <div class="flex gap-8 items-start lg:items-start flex-col lg:flex-row">
                <!-- Main Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose prose-sm max-w-none">
                        <?php if ($article['article_type'] === 'standard'): ?>
    <!-- Standard Article -->
    <div class="space-y-6">
        <!-- Standard Article Image -->
        <?php if ($standard_image): ?>
            <div class="border-2 border-gray-300 rounded-lg overflow-hidden mb-8">
                <img src="<?= htmlspecialchars($standard_image) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="w-full h-auto object-cover cursor-pointer hover:opacity-90 transition-opacity" onclick="openImageModal(this.src, this.alt)">
            </div>
        <?php endif; ?>
        
       <!-- Standard Article Description -->
        <div class="text-gray-700 leading-relaxed ql-editor text-base sm:text-base">
            <?= $standard_description ?? '' ?>
        </div>
    </div>

                           <?php elseif ($article['article_type'] === 'simple_question'): ?>
                            <!-- Q&A Article -->
                            <div class="space-y-6">
                                <?php foreach ($steps as $qa): ?>
                                        <div class="space-y-4">
                                        <!-- Question Table -->
                                        <table class="w-full border border-blue-200 rounded-lg overflow-hidden shadow-sm bg-blue-50">
                                            <tbody>
                                                <tr class="bg-gradient-to-r from-blue-50 via-blue-50 to-blue-100 border-b border-blue-200">
                                                    <td class="px-4 sm:px-6 md:px-8 py-3 md:py-4">
                                                        <div class="flex items-center gap-2 sm:gap-3">
                                                            <i class="bi bi-info-circle text-blue-600 text-xl sm:text-2xl flex-shrink-0"></i>
                                                            <h3 class="text-sm sm:text-base font-bold text-blue-700 uppercase tracking-wide">Question</h3>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 sm:px-6 md:px-8 py-4 sm:py-5 md:py-6 bg-blue-50">
                                                        <div class="text-gray-800 font-semibold text-base sm:text-base leading-relaxed ql-editor">
                                                            <?= $qa['question'] ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- Answer Table -->
                                        <table class="w-full border border-green-200 rounded-lg overflow-hidden shadow-sm bg-green-50">
                                            <tbody>
                                                <tr class="bg-gradient-to-r from-green-50 via-green-50 to-emerald-100 border-b border-green-200">
                                                    <td class="px-4 sm:px-6 md:px-8 py-3 md:py-4">
                                                        <div class="flex items-center gap-2 sm:gap-3">
                                                            <i class="bi bi-check-circle text-green-600 text-xl sm:text-2xl flex-shrink-0"></i>
                                                            <h3 class="text-sm sm:text-base font-bold text-green-700 uppercase tracking-wide">Answer</h3>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 sm:px-6 md:px-8 py-4 sm:py-5 md:py-6 bg-green-50">
                                                        <div class="text-gray-800 leading-relaxed ql-editor text-base sm:text-base">
                                                            <?= $qa['answer'] ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        </div>
                                <?php endforeach; ?>
                            </div>

                            <?php elseif ($article['article_type'] === 'step_by_step'): ?>
                             <!-- Step-by-Step Article -->
                            <div class="space-y-8">
                                <!-- Introduction for Step-by-Step (First Row) -->
                                <?php if (!empty($article['introduction'])): ?>
                                    <div id="article-introduction" class="mb-6 sm:mb-8 pb-6 sm:pb-8 border-b border-gray-200">
                                        <div class="text-gray-700 leading-relaxed text-base sm:text-base" style="line-height: 1.8; color: #333;">
                                            <?= $article['introduction'] ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                  <?php foreach ($steps as $step): ?>
                                    <!-- Step Title -->
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-block px-2 sm:px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs sm:text-sm font-bold">
                                                Step <?= intval($step['step_number']) ?>
                                            </span>
                                        </div>
                                        <h3 class="text-lg sm:text-xl font-bold text-[#2c3e50] mb-3 sm:mb-4" id="step-<?= $step['step_id'] ?>">
                                            <?= htmlspecialchars($step['step_title']) ?>
                                        </h3>

                                        <!-- Step Content - Single Column Layout -->
                                        <div class="space-y-4">
                                            <!-- Step Description -->
                                            <div class="ql-editor pt-0 mt-0 text-base sm:text-base">
                                                <?= $step['step_description'] ?>
                                            </div>
                                                                                    
                                            <!-- Step Image -->
                                            <?php if (!empty($step['step_image'])): ?>
                                                <div class="border-2 border-gray-300 rounded-lg p-2 sm:p-3 md:p-4 bg-gray-50">
                                                    <img src="<?= htmlspecialchars('/FAQ/admin/uploads/articles/' . $step['step_image']) ?>" alt="<?= htmlspecialchars($step['step_title']) ?>" class="w-full rounded-md object-cover cursor-pointer hover:opacity-90 transition-opacity" onclick="openImageModal(this.src, this.alt)" onerror="this.src='/FAQ/assets/img/placeholder.png">
                                                </div>
                                            <?php else: ?>
                                                <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-100 flex items-center justify-center h-64">
                                                    <span class="text-gray-500 text-center">No image available</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Divider between steps -->
                                    <?php if ($step !== end($steps)): ?>
                                        <hr class="border-gray-200 my-8">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                  <!-- Sticky Sidebar -->
                <aside id="sidebarArticleInfo" class="hidden lg:block w-72 flex-shrink-0 sticky top-[100px] h-fit">
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">            
                        <!-- Table of Contents for Step Articles -->
                        <?php if ($article['article_type'] === 'step_by_step'): ?>
                            <h3 class="font-bold text-base lg:text-lg text-gray-800 mb-3 lg:mb-4">
                                <i class="bi bi-list-check mr-2 text-blue-500"></i>Table of Contents
                            </h3>
                             <nav class="space-y-2 lg:space-y-3 mb-6 pb-6 border-b border-gray-200">
                                <!-- Introduction Link for Step-by-Step -->
                                <?php if (!empty($article['introduction'])): ?>
                                    <a href="#article-introduction" class="toc-link text-sm text-blue-600 hover:text-blue-800 transition-colors">
                                        Introduction
                                    </a>
                                <?php endif; ?>
                                
                                <?php foreach ($steps as $step): ?>
                                    <a href="#step-<?= $step['step_id'] ?>" class="toc-link text-sm text-blue-600 hover:text-blue-800 transition-colors">
                                        <?= htmlspecialchars($step['step_title']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>

                                                <!-- Related Articles -->
                        <?php if (!empty($related_articles)): ?>
                            <div class="mb-6 pb-6 border-b border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-2 lg:mb-3 text-xs lg:text-sm">
                                    <i class="bi bi-link-45deg mr-2 text-blue-500"></i>Related Articles
                                </h4>
                                <div class="space-y-2">
                                    <?php foreach ($related_articles as $related): ?>
                                        <a href="view_article.php?id=<?= $related['article_id'] ?>" class="block text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors line-clamp-2">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                  

                        <!-- Share Section -->
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-2 lg:mb-3 text-xs lg:text-sm">Share</h4>
                            <div class="flex gap-3">
                                <button onclick="shareArticle('twitter')" class="flex-1 px-3 py-2 bg-gray-100 hover:bg-blue-50 text-gray-700 rounded text-sm transition-colors" title="Share on Twitter">
                                    <i class="bi bi-twitter"></i>
                                </button>
                                <button onclick="shareArticle('linkedin')" class="flex-1 px-3 py-2 bg-gray-100 hover:bg-blue-50 text-gray-700 rounded text-sm transition-colors" title="Share on LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </button>
                                <button onclick="shareArticle('copy')" class="flex-1 px-3 py-2 bg-gray-100 hover:bg-blue-50 text-gray-700 rounded text-sm transition-colors" title="Copy Link">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

        </div>

        <!-- Feedback Section - Two Column Layout -->
        <div class="w-full md:w-11/12 mx-auto px-3 sm:px-5 md:px-10 py-4 sm:py-6 md:py-8 pb-6 md:pb-8">
            <div class="flex gap-4 md:gap-8 items-start flex-col lg:flex-row">
                <!-- Feedback Column -->
                <div class="flex-1 w-full min-w-0 bg-white rounded-lg p-4 sm:p-5 md:p-6">
                    <div class="flex items-center gap-2 sm:gap-3 md:gap-4 flex-wrap">
                        <span class="text-sm sm:text-base text-gray-700 font-medium w-full sm:w-auto mb-2 sm:mb-0 pl-0 lg:pl-8">Was this article helpful?</span>
                        <button id="feedbackHelpful" class="feedback-btn text-xl sm:text-2xl transition-all border-2 border-gray-300 rounded-lg p-2 bg-gray-50 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center <?= $user_feedback === 1 ? 'text-green-500' : 'text-gray-400 hover:text-green-400' ?>" data-feedback="1" title="This was helpful">
                            <i class="bi bi-hand-thumbs-up"></i>
                        </button>
                        <button id="feedbackUnhelpful" class="feedback-btn text-xl sm:text-2xl transition-all border-2 border-gray-300 rounded-lg p-2 bg-gray-50 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center <?= $user_feedback === 0 ? 'text-red-500' : 'text-gray-400 hover:text-red-400' ?>" data-feedback="0" title="This was not helpful">
                            <i class="bi bi-hand-thumbs-down"></i>
                        </button>
                        <span class="text-xs sm:text-sm text-gray-600 w-full sm:w-auto sm:ml-auto mt-2 sm:mt-0" id="feedbackText">
                            <?php if ($total_feedback > 0): ?>
                                <?= htmlspecialchars($helpful_count) ?> out of <?= htmlspecialchars($total_feedback) ?> found this helpful
                            <?php else: ?>
                                0 out of 0 found this helpful
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- Empty Column (matching sidebar width) -->
                <div class="hidden lg:block w-72 flex-shrink-0"></div>
            </div>
        </div>

        <!-- Related Articles Section - Mobile/Tablet View -->
        <?php if (!empty($related_articles)): ?>
            <div class="w-full md:w-11/12 mx-auto px-3 sm:px-5 md:px-10 py-4 sm:py-6 md:py-8 lg:hidden">
                <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-5 md:p-6 shadow-sm">
                    <h4 class="font-semibold text-gray-800 mb-3 text-sm">
                        <i class="bi bi-link-45deg mr-2 text-blue-500"></i>Related Articles
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($related_articles as $related): ?>
                            <a href="view_article.php?id=<?= $related['article_id'] ?>" class="block text-sm text-blue-600 hover:text-blue-800 hover:underline transition-colors line-clamp-2">
                                <?= htmlspecialchars($related['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
                     <!-- Image Modal -->
    <div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4" style="z-index: 9999;" onclick="closeImageModal(event)">
                <div id="modalContainer" class="bg-white rounded-lg shadow-xl w-auto max-w-4xl max-h-[90vh] overflow-hidden transition-all duration-300 flex flex-col" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 flex-shrink-0">
                <h3 id="modalImageAlt" class="text-lg font-semibold text-gray-800 flex-1 truncate"></h3>
                <div class="flex gap-2 ml-4">
                    
                    <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700 text-2xl p-2 hover:bg-gray-100 rounded transition-colors flex-shrink-0" title="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
                        <div id="modalContent" class="flex items-center justify-center bg-gray-100 flex-1">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
            </div>
        </div>
    </div>   

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <script>
        function shareArticle(platform) {
            const title = "<?= htmlspecialchars(addslashes($article['title'])) ?>";
            const url = window.location.href;
            
            if (platform === 'twitter') {
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
            } else if (platform === 'linkedin') {
                window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
            } else if (platform === 'copy') {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copied to clipboard!');
                }).catch(() => {
                    alert('Failed to copy link');
                });
            }
        }

        // Smart sticky sidebar with active section indicator
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebarArticleInfo');
            const navbar = document.querySelector('nav.navbar');
            const navbarHeight = navbar ? navbar.offsetHeight : 70;

            window.addEventListener('scroll', updateActiveTocLink);
            
            // Initial call on page load
            updateActiveTocLink();
        });

            function updateActiveTocLink() {
            const links = document.querySelectorAll('.toc-link');
            const navbar = document.querySelector('nav.navbar');
            const navbarHeight = navbar ? navbar.offsetHeight : 70;
            const offset = navbarHeight + 150;

            let currentActive = null;
            let closestDistance = Infinity;

            links.forEach(link => {
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const rect = targetElement.getBoundingClientRect();
                    const distance = Math.abs(rect.top - offset);
                    
                    // Find the section closest to the viewport top
                    // Changed condition to also include sections slightly below the offset
                    if (rect.top <= offset + 100 && rect.top > -500 && distance < closestDistance) {
                        closestDistance = distance;
                        currentActive = link;
                    }
                }
            });

            // Remove active class from all links
            links.forEach(link => link.classList.remove('active'));

            // Add active class to current section
            if (currentActive) {
                currentActive.classList.add('active');
            }
        }
    
         // Enable smooth scrolling for anchor links
        document.documentElement.style.scrollBehavior = 'smooth';
        document.documentElement.style.scrollPaddingTop = '120px';

        function openImageModal(src, alt) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const modalImageAlt = document.getElementById('modalImageAlt');
            
            modalImage.src = src;
            modalImage.alt = alt;
            modalImageAlt.textContent = alt;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal(event) {
            if (event && event.target.id !== 'imageModal') return;
            
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
        const feedbackBtns = document.querySelectorAll('.feedback-btn');
        const isLoggedIn = <?= isset($_SESSION['client_id']) && !empty($_SESSION['client_id']) ? 'true' : 'false' ?>;
        
        feedbackBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (!isLoggedIn) {
                    alert('Please login to provide feedback');
                    window.location.href = 'login.php';
                    return;
                }
                
                const feedback = this.getAttribute('data-feedback');
                submitFeedback(feedback);
            });
        });
    });
    
    function submitFeedback(feedback) {
        const formData = new FormData();
        formData.append('is_helpful', feedback);
        formData.append('ajax', true);
        
        fetch('<?= $_SERVER['REQUEST_URI'] ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button states
                const helpfulBtn = document.getElementById('feedbackHelpful');
                const unhelpfulBtn = document.getElementById('feedbackUnhelpful');
                
                // Remove all color classes and reset hover states
                helpfulBtn.classList.remove('text-green-500', 'text-gray-400');
                unhelpfulBtn.classList.remove('text-red-500', 'text-gray-400');
                
                // Add appropriate classes based on feedback
                if (feedback == 1) {
                    helpfulBtn.classList.add('text-green-500');
                    helpfulBtn.classList.remove('hover:text-green-400');
                    unhelpfulBtn.classList.add('text-gray-400');
                    unhelpfulBtn.classList.add('hover:text-red-400');
                } else {
                    unhelpfulBtn.classList.add('text-red-500');
                    unhelpfulBtn.classList.remove('hover:text-red-400');
                    helpfulBtn.classList.add('text-gray-400');
                    helpfulBtn.classList.add('hover:text-green-400');
                }
                
                // Optionally fetch updated feedback counts from server
                fetchUpdatedFeedbackCounts();
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    function fetchUpdatedFeedbackCounts() {
        const articleId = <?= $article_id ?>;
        fetch(`view_article.php?id=${articleId}&get_feedback=true`)
            .then(response => response.json())
            .then(data => {
                const feedbackText = document.getElementById('feedbackText');
                if (data.helpful_count !== undefined && data.total_feedback !== undefined) {
                    feedbackText.textContent = `${data.helpful_count} out of ${data.total_feedback} found this helpful`;
                }
            })
            .catch(error => console.log('Could not update feedback counts'));
    }
    </script>

    <!-- Include Chatbot Widget -->
    <?php include 'chatbot_widget.php'; ?>

</body>
</html>