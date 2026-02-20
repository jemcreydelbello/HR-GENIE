<?php
include "connect.php";

$subcat_id = isset($_GET['subcat_id']) ? intval($_GET['subcat_id']) : 0;
if ($subcat_id === 0) {
    die("Invalid subcategory.");
}

$subcat_stmt = $conn->prepare("SELECT subcategory_id, category_id, subcategory_name, description_ FROM subcategories WHERE subcategory_id = ?");
if (!$subcat_stmt) {
    die("Database error: " . $conn->error);
}
$subcat_stmt->bind_param('i', $subcat_id);
$subcat_stmt->execute();
$subcat_result = $subcat_stmt->get_result();

if ($subcat_result->num_rows === 0) {
    die("Subcategory not found.");
}

$subcategory = $subcat_result->fetch_assoc();
$subcat_stmt->close();

// Get category name for breadcrumb
$cat_stmt = $conn->prepare("SELECT category_id, category_name FROM categories WHERE category_id = ?");
if (!$cat_stmt) {
    die("Database error: " . $conn->error);
}
$cat_stmt->bind_param('i', $subcategory['category_id']);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$category = $cat_result->fetch_assoc();
$cat_stmt->close();

// Get articles for this subcategory
$test_stmt = $conn->prepare("SHOW COLUMNS FROM articles LIKE 'subcategory_id'");
if ($test_stmt) {
    $test_stmt->execute();
    $test_result = $test_stmt->get_result();
    $has_subcategory_column = $test_result->num_rows > 0;
    $test_stmt->close();
} else {
    $has_subcategory_column = false;
}

// Check if sort_order column exists
$sort_order_exists = false;
$sort_test = $conn->prepare("SHOW COLUMNS FROM articles LIKE 'sort_order'");
if ($sort_test) {
    $sort_test->execute();
    $sort_result = $sort_test->get_result();
    $sort_order_exists = $sort_result->num_rows > 0;
    $sort_test->close();
}

if ($has_subcategory_column) {
    // Use subcategory_id if column exists
    // Order by sort_order if it exists and has values, otherwise by created_at DESC
    if ($sort_order_exists) {
        $article_stmt = $conn->prepare("
            SELECT article_id, subcategory_id, title, created_at, article_type
            FROM articles 
            WHERE subcategory_id = ? AND status = 'Published'
            ORDER BY COALESCE(sort_order, 999999), created_at DESC
        ");
    } else {
        $article_stmt = $conn->prepare("
            SELECT article_id, subcategory_id, title, created_at, article_type
            FROM articles 
            WHERE subcategory_id = ? AND status = 'Published'
            ORDER BY created_at DESC
        ");
    }
} else {
    // Fallback: use category name from subcategory
    if ($sort_order_exists) {
        $article_stmt = $conn->prepare("
            SELECT article_id, NULL as subcategory_id, title, created_at, article_type
            FROM articles 
            WHERE category = ? AND status = 'Published'
            ORDER BY COALESCE(sort_order, 999999), created_at DESC
        ");
    } else {
        $article_stmt = $conn->prepare("
            SELECT article_id, NULL as subcategory_id, title, created_at, article_type
            FROM articles 
            WHERE category = ? AND status = 'Published'
            ORDER BY created_at DESC
        ");
    }
}

if (!$article_stmt) {
    die("Database error: " . $conn->error);
}

if ($has_subcategory_column) {
    $article_stmt->bind_param('i', $subcat_id);
} else {
    $article_stmt->bind_param('s', $subcategory['subcategory_name']);
}

$article_stmt->execute();
$article_result = $article_stmt->get_result();

$articles = [];
while ($row = $article_result->fetch_assoc()) {
    $article_id = $row['article_id'];
    $row['preview'] = '';
    
    // Auto-detect article type if NULL
    if (empty($row['article_type'])) {
        $check_qa = $conn->prepare("SELECT article_id FROM article_qa WHERE article_id = ? LIMIT 1");
        if ($check_qa !== false) {
            $check_qa->bind_param('i', $article_id);
            $check_qa->execute();
            if ($check_qa->get_result()->num_rows > 0) {
                $row['article_type'] = 'simple_question';
            }
            $check_qa->close();
        }
        
        if (empty($row['article_type'])) {
            $check_steps = $conn->prepare("SELECT article_id FROM article_steps WHERE article_id = ? LIMIT 1");
            if ($check_steps !== false) {
                $check_steps->bind_param('i', $article_id);
                $check_steps->execute();
                if ($check_steps->get_result()->num_rows > 0) {
                    $row['article_type'] = 'step_by_step';
                }
                $check_steps->close();
            }
        }
        
        if (empty($row['article_type'])) {
            $check_standard = $conn->prepare("SELECT article_id FROM article_standard WHERE article_id = ? LIMIT 1");
            if ($check_standard !== false) {
                $check_standard->bind_param('i', $article_id);
                $check_standard->execute();
                if ($check_standard->get_result()->num_rows > 0) {
                    $row['article_type'] = 'standard';
                }
                $check_standard->close();
            }
        }
        
        // Default to 'standard' if no type-specific data found
        if (empty($row['article_type'])) {
            $row['article_type'] = 'standard';
        }
    }
    
    // Fetch preview based on article type
    $preview_limit = 140; // Character limit for preview
    if ($row['article_type'] === 'standard') {
        $preview_stmt = $conn->prepare("SELECT description FROM article_standard WHERE article_id = ? LIMIT 1");
        if ($preview_stmt !== false) {
            $preview_stmt->bind_param('i', $article_id);
            $preview_stmt->execute();
            $preview_result = $preview_stmt->get_result();
            if ($preview_result->num_rows > 0) {
                $preview_row = $preview_result->fetch_assoc();
                // Strip HTML tags, decode entities, and clean up whitespace
                $clean_text = strip_tags($preview_row['description']);
                $clean_text = html_entity_decode($clean_text, ENT_QUOTES, 'UTF-8');
                $clean_text = preg_replace('/\s+/', ' ', trim($clean_text)); // Replace multiple spaces with single space
                $row['preview'] = substr($clean_text, 0, $preview_limit);
                $row['preview_full_length'] = strlen($clean_text);
            }
            $preview_stmt->close();
        }
    } elseif ($row['article_type'] === 'step_by_step') {
        $preview_stmt = $conn->prepare("SELECT introduction FROM articles WHERE article_id = ? LIMIT 1");
        if ($preview_stmt !== false) {
            $preview_stmt->bind_param('i', $article_id);
            $preview_stmt->execute();
            $preview_result = $preview_stmt->get_result();
            if ($preview_result->num_rows > 0) {
                $preview_row = $preview_result->fetch_assoc();
                $clean_text = strip_tags($preview_row['introduction']);
                $clean_text = html_entity_decode($clean_text, ENT_QUOTES, 'UTF-8');
                $clean_text = preg_replace('/\s+/', ' ', trim($clean_text));
                $row['preview'] = substr($clean_text, 0, $preview_limit);
                $row['preview_full_length'] = strlen($clean_text);
            }
            $preview_stmt->close();
        }
    } elseif ($row['article_type'] === 'simple_question') {
        $preview_stmt = $conn->prepare("SELECT question FROM article_qa WHERE article_id = ? LIMIT 1");
        if ($preview_stmt !== false) {
            $preview_stmt->bind_param('i', $article_id);
            $preview_stmt->execute();
            $preview_result = $preview_stmt->get_result();
            if ($preview_result->num_rows > 0) {
                $preview_row = $preview_result->fetch_assoc();
                $clean_text = strip_tags($preview_row['question']);
                $clean_text = html_entity_decode($clean_text, ENT_QUOTES, 'UTF-8');
                $clean_text = preg_replace('/\s+/', ' ', trim($clean_text));
                $row['preview'] = substr($clean_text, 0, $preview_limit);
                $row['preview_full_length'] = strlen($clean_text);
            }
            $preview_stmt->close();
        }
    }
    
    $articles[] = $row;
}
$article_stmt->close();

// Fetch tags for all articles
$articles_with_tags = [];
foreach ($articles as $article) {
    $article_id = $article['article_id'];
    $article['tags'] = [];
    
    // Fetch tags for this article
    $tags_stmt = $conn->prepare("
        SELECT t.tag_id, t.tag_name 
        FROM tags t 
        INNER JOIN article_tags at ON t.tag_id = at.tag_id 
        WHERE at.article_id = ?
    ");
    if ($tags_stmt !== false) {
        $tags_stmt->bind_param('i', $article_id);
        $tags_stmt->execute();
        $tags_result = $tags_stmt->get_result();
        while ($tag_row = $tags_result->fetch_assoc()) {
            $article['tags'][] = $tag_row['tag_name'];
        }
        $tags_stmt->close();
    }
    
    $articles_with_tags[] = $article;
}

$articles = $articles_with_tags;

// Define article type display names and colors
$article_type_display = [
    'step_by_step' => [
        'name' => 'Step by Step Article',
        'bg_color' => 'bg-emerald-100',
        'text_color' => 'text-emerald-800'
    ],
    'simple_question' => [
        'name' => 'Simple Question',
        'bg_color' => 'bg-purple-100',
        'text_color' => 'text-purple-800'
    ],
    'standard' => [
        'name' => 'Standard Article',
        'bg_color' => 'bg-blue-100',
        'text_color' => 'text-blue-800'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subcategory['subcategory_name']) ?> - HR Genie</title>
     <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-[#f5f7fb]">
    <!-- Navbar -->
    <?php include "navbar.php"; ?>
    
    <div class="max-w-7xl mx-auto min-h-[calc(100vh-200px)]">

        <!-- Header Section -->
    <div class="border-gray-200 mb-4 pt-6 md:pt-10">
        <!-- Breadcrumb Navigation -->
       <div class="pl-5 md:pl-10">
       <?php
        $breadcrumbs = [
            ['label' => 'Home', 'url' => 'index.php'],
            ['label' => htmlspecialchars($category['category_name']), 'url' => 'category_page.php?cat_id=' . $category['category_id']],
           
        ];
        include 'breadcrumb.php';
        ?>
       </div>

       <!-- Subcategory Title -->
<h1 class="text-2xl md:text-4xl font-bold text-[#2c3e50] mb-1 pl-5 md:pl-10 pr-5 md:pr-10">
    <?= htmlspecialchars($subcategory['subcategory_name']) ?>
</h1>

<!-- Subcategory Description -->
<p class="text-sm md:text-base text-gray-500 mb-3 leading-relaxed pl-5 md:pl-10 pr-5 md:pr-10">
    <?= !empty($subcategory['description_']) 
        ? htmlspecialchars($subcategory['description_']) 
        : 'Articles in ' . htmlspecialchars($subcategory['subcategory_name']) . '.' ?>
</p>

        <!-- Articles Section Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pl-5 md:pl-10 pr-5 md:pr-10 mb-2">
            <!-- Article Count -->
            <div class="text-sm text-gray-600 font-medium">
                Show <span id="articleCount"><?= count($articles) ?></span> Articles
            </div>

            <!-- Search Bar -->
            <div class="relative w-full md:w-80">
                <input type="text" 
       id="articleSearchInput" 
       class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-[#4f46e5] focus:ring-2 focus:ring-[#4f46e5]/10 transition-all" 
       placeholder="Search articles">
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>

        <!-- Divider Line -->
        <hr class="border-gray-300">
    </div>

    <!-- Articles List -->
    <div class="flex flex-col gap-4 px-5 md:px-10" id="articlesList">
        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article): ?>
                   
                    <div class="article-card bg-white border border-gray-200 rounded-lg p-5 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all" 
                     onclick="window.location.href='view_article.php?id=<?= $article['article_id'] ?>'">
                    <!-- Row 1: Title and article type ;3 -->
                  
                <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                    <h3 class="text-base md:text-lg font-bold text-[#2c3e50] flex-1 order-1"><?= htmlspecialchars($article['title']) ?></h3>
                    <?php 
                    $type_display = $article_type_display[$article['article_type']] ?? [
                        'name' => htmlspecialchars($article['article_type']),
                        'bg_color' => 'bg-gray-100',
                        'text_color' => 'text-gray-800'
                    ];
                    ?>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $type_display['bg_color'] ?> <?= $type_display['text_color'] ?> whitespace-nowrap order-2 md:order-2">
                        <?= $type_display['name'] ?>
                    </span>
                </div>
                    
                    <!-- Row 2: Description Preview with See More Link -->
                    <div class="text-xs md:text-sm text-gray-600 leading-relaxed line-clamp-3">
                        <?= htmlspecialchars($article['preview']) ?>
                        <?php if (!empty($article['preview_full_length']) && $article['preview_full_length'] > 160): ?>
                            <span class="text-blue-600 font-semibold cursor-pointer hover:text-blue-800 ml-1">See more...</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Row 3: Tags and Date (Side by side) -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mt-3">
                        <!-- Tags on the left -->
                        <div class="flex flex-wrap gap-2 max-w-xs">
                            <?php if (!empty($article['tags'])): ?>
                                <div class="flex items-center gap-1.5 mr-2">
                                    <i class="bi bi-tags text-gray-500" style="font-size: 0.875rem;"></i>
                                </div>
                                <?php foreach ($article['tags'] as $tag): ?>
                                    <span class="inline-block px-2.5 py-1 text-xs font-medium bg-blue-50 border border-blue-200 text-blue-700 rounded-full">
                                        <?= htmlspecialchars($tag) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Date on the right -->
                        <span class="text-gray-500 text-xs whitespace-nowrap">
                            Created on <?= date('M d, Y', strtotime($article['created_at'])) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10 bg-white rounded-lg text-gray-600">
                <p>No articles available under this subcategory.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

       

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <script>
        // Article search function
        const articleSearchInput = document.getElementById('articleSearchInput');
        const articlesList = document.getElementById('articlesList');
        const articleCards = articlesList.querySelectorAll('.article-card');
        const articleCount = document.getElementById('articleCount');

        articleSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            articleCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const preview = card.querySelector('div.text-sm').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || preview.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none';
                }
            });

            // Update article count
            articleCount.textContent = visibleCount;
        });
    </script>

<?php include 'chatbot_widget.php'; ?>

</body>
</html>