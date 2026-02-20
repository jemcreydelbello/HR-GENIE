<?php
include "connect.php";

$article_types = [
    'standard' => 'Standard',
    'step_by_step' => 'Step by Step',
    'simple_question' => 'Simple Question'
];

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$articles = [];
$categories = [];
$subcategories = [];

if ($query !== '') {
    $likeQuery = "%{$query}%";
    
    $sql = "
        SELECT DISTINCT 
            a.article_id as id,
            a.article_id,
            a.title,
            a.article_type,
            a.category as category,
            a.created_at,
            'article' as type
        FROM articles a
        WHERE (a.title LIKE ? OR a.content LIKE ?)
           AND a.status = 'Published'
        
        UNION
        
        SELECT DISTINCT 
            sc.subcategory_id as id,
            NULL as article_id,
            sc.subcategory_name as title,
            NULL as article_type,
            c.category_name as category,
            NULL as created_at,
            'subcategory' as type
        FROM subcategories sc
        LEFT JOIN categories c ON sc.category_id = c.category_id
        WHERE LOWER(sc.subcategory_name) LIKE LOWER(?)
           OR LOWER(sc.description_) LIKE LOWER(?)
        
        UNION
        
        SELECT DISTINCT 
            c.category_id as id,
            NULL as article_id,
            c.category_name as title,
            NULL as article_type,
            c.description_ as category,
            NULL as created_at,
            'category' as type
        FROM categories c
        WHERE LOWER(c.category_name) LIKE LOWER(?) 
           OR LOWER(c.description_) LIKE LOWER(?)
    ";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ssssss', $likeQuery, $likeQuery, $likeQuery, $likeQuery, $likeQuery, $likeQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if ($row['type'] === 'article') {
                $preview = '';
                
                if ($row['article_type'] === 'standard') {
                    $preview_stmt = $conn->prepare("SELECT description FROM article_standard WHERE article_id = ? LIMIT 1");
                    $preview_stmt->bind_param('i', $row['article_id']);
                    $preview_stmt->execute();
                    $preview_result = $preview_stmt->get_result();
                    if ($preview_result->num_rows > 0) {
                        $preview_row = $preview_result->fetch_assoc();
                        $preview = strip_tags(substr($preview_row['description'], 0, 150));
                    }
                    $preview_stmt->close();
                } elseif ($row['article_type'] === 'step_by_step') {
                    $preview_stmt = $conn->prepare("SELECT step_title FROM article_steps WHERE article_id = ? ORDER BY step_number LIMIT 1");
                    $preview_stmt->bind_param('i', $row['article_id']);
                    $preview_stmt->execute();
                    $preview_result = $preview_stmt->get_result();
                    if ($preview_result->num_rows > 0) {
                        $preview_row = $preview_result->fetch_assoc();
                        $preview = strip_tags(substr($preview_row['step_title'], 0, 150));
                    }
                    $preview_stmt->close();
                } elseif ($row['article_type'] === 'simple_question') {
                    $preview_stmt = $conn->prepare("SELECT answer FROM article_qa WHERE article_id = ? LIMIT 1");
                    $preview_stmt->bind_param('i', $row['article_id']);
                    $preview_stmt->execute();
                    $preview_result = $preview_stmt->get_result();
                    if ($preview_result->num_rows > 0) {
                        $preview_row = $preview_result->fetch_assoc();
                        $preview = strip_tags(substr($preview_row['answer'], 0, 150));
                    }
                    $preview_stmt->close();
                }
                
                $articles[] = [
                    'article_id' => $row['article_id'],
                    'title' => $row['title'],
                    'preview' => $preview,
                    'category' => $row['category'] ?? 'Uncategorized',
                    'created_at' => $row['created_at'],
                    'type' => $row['article_type']
                ];
            } elseif ($row['type'] === 'subcategory') {
                $subcategories[] = [
                    'subcategory_id' => $row['id'],
                    'subcategory_name' => $row['title'],
                    'description_' => $row['category']
                ];
            } elseif ($row['type'] === 'category') {
                $categories[] = [
                    'category_id' => $row['id'],
                    'category_name' => $row['title'],
                    'description_' => $row['category'],
                    'subcategory_count' => 0 // Will be updated below
                ];
            }
        }
        $stmt->close();
    }
    
    // Fetch subcategory counts for categories
    foreach ($categories as &$category) {
        $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM subcategories WHERE category_id = ?");
        if ($count_stmt) {
            $count_stmt->bind_param('i', $category['category_id']);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $category['subcategory_count'] = $count_row['count'] ?? 0;
            $count_stmt->close();
        }
    }
    unset($category); // Break reference
}

// Remove duplicate articles by article_id
$unique_articles = [];
foreach ($articles as $article) {
    $unique_articles[$article['article_id']] = $article;
}
$articles = array_values($unique_articles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Results | HRIS Genie</title>
<link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body class="bg-[#f5f7fb] flex flex-col min-h-screen">

<?php include "navbar.php"; ?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto w-full flex-1">

    <!-- Header Section -->
    <div class="mb-4 pt-4 md:pt-10 px-4 md:px-0">
          <!-- Breadcrumb Navigation -->
       <div class="md:pl-10">
       <?php
        $breadcrumbs = [
            ['label' => 'Home', 'url' => 'index.php'],
        ];
        include 'breadcrumb.php';
        ?>
        </div>

        <!-- Page Title -->
        <h1 class="text-lg md:text-4xl font-bold text-[#2c3e50] mb-1 md:pl-10">
            Search Results for: "<?= htmlspecialchars($query) ?>"
        </h1>

        <!-- Description -->
        <p class="text-gray-500 text-xs md:text-base mb-3 leading-relaxed md:pl-10">
            <?= !empty($query) ? 'Articles and categories related to "' . htmlspecialchars($query) . '"' : 'No search term provided.' ?>
        </p>

        <!-- Articles Section Header -->
        <div class="flex flex-row justify-between items-center gap-2 md:gap-4 md:px-10 md:pr-10 mb-2">
            <!-- Article Count -->
            <div class="text-xs md:text-sm text-gray-600 font-medium whitespace-nowrap md:pl-0" id="articleCount">
           Show  <?= count($articles) + count($categories) + count($subcategories) ?> Results
            </div>

            <!-- Search & Filter -->
            <div class="flex flex-row w-auto gap-2 md:gap-3 items-center">
                <!-- Search Bar -->
                <div class="relative w-32 md:w-80 hidden md:block">
                    <input type="text" 
                           id="searchInputPage" 
                           class="w-full pl-3 pr-8 py-1.5 md:py-2.5 border border-gray-300 rounded-md text-xs md:text-sm focus:outline-none focus:border-[#4f46e5] focus:ring-2 focus:ring-[#4f46e5]/10 transition-all" 
                           placeholder="Search...">
                    <span class="absolute right-2 md:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none text-xs md:text-sm">
                        <i class="bi bi-search"></i>
                    </span>
                </div>

                <!-- Type Filter Dropdown -->
                <div class="relative w-auto">
                    <button class="flex items-center justify-center gap-1 md:gap-2 px-2 md:px-4 py-1.5 md:py-2.5 bg-white border border-gray-300 rounded-md text-xs md:text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all whitespace-nowrap" id="articleTypeBtn">
                        <span class="hidden md:inline">Filter by Type</span><span class="md:hidden">Filter</span> <span class="text-xs ml-1"><i class="bi bi-filter"></i></span>
                    </button>
                    <div id="articleTypeDropdown" class="absolute hidden top-full right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 w-40 md:min-w-[200px] overflow-hidden">
                        <button type="button" class="article-type-filter w-full text-left px-4 py-2 hover:bg-blue-50 text-gray-700 text-sm" data-type="">All Results</button>
                        <button type="button" class="article-type-filter w-full text-left px-4 py-2 hover:bg-blue-50 text-gray-700 text-sm" data-type="article">Articles</button>
                        <button type="button" class="article-type-filter w-full text-left px-4 py-2 hover:bg-blue-50 text-gray-700 text-sm" data-type="subcategory">Subcategories</button>
                        <button type="button" class="article-type-filter w-full text-left px-4 py-2 hover:bg-blue-50 text-gray-700 text-sm" data-type="category">Categories</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider Line -->
        <hr class="border-gray-300 md:mx-10">
    </div>

    <!-- Single Column Combined Layout -->
    <div class="px-4 md:px-10 mb-6 pt-4 md:pt-6">
        <h2 class="text-base md:text-lg font-bold text-[#2c3e50] mb-4">All Results</h2>
        <div class="flex flex-col gap-4" id="resultsList">
            <?php if (!empty($articles) || !empty($categories)): ?>
                    <?php foreach ($articles as $article): ?>
                    
                    <div class="article-card bg-white rounded-lg p-3 md:p-4 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col justify-between" 
                         onclick="window.location.href='view_article.php?id=<?= $article['article_id'] ?>'"
                         data-type="article"
                         data-article-type="<?= htmlspecialchars($article['type']) ?>">
                        <!-- Content Section -->
                        <div>
                            <!-- Row 1: Title with Badges -->
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2 mb-2">
                                <!-- Title -->
                                <h3 class="font-bold text-[#2c3e50] text-sm md:text-base break-words flex-1"><?= htmlspecialchars($article['title']) ?></h3>
                                 <div class="flex justify-end mt-2 gap-1 sm:gap-2">
                                <!-- Type Badge -->
                                <span class="bg-purple-100 text-purple-700 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium whitespace-nowrap">
                                    <?= htmlspecialchars($article_types[$article['type']] ?? 'Unknown') ?>
                                </span>
                                
                                <!-- Category -->
                                <span class="bg-blue-100 text-blue-700 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium whitespace-nowrap">
                                    <?= htmlspecialchars($article['category']) ?>
                                </span>
                            </div>
                            </div>
                            
                            
                            <!-- Row 3: Description -->
                            <p class="text-gray-600 text-xs md:text-sm line-clamp-2 mb-0"><?= htmlspecialchars($article['preview']) ?>
                                <a href="view_article.php?id=<?= $article['article_id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium ml-1">read more...</a>
                            </p>
                        </div>
                        
                        <!-- date -->
                        <div class="flex justify-end mt-2">
                            <span class="text-gray-500 text-xs whitespace-nowrap">
                                <?= date('M d, Y', strtotime($article['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($subcategories as $subcat): ?>
                    <a href="subcategory_page.php?subcat_id=<?= htmlspecialchars($subcat['subcategory_id']) ?>" 
                       class="subcategory-card bg-white rounded-lg p-3 md:p-5 hover:shadow-md hover:-translate-y-0.5 transition-all" 
                       data-type="subcategory">
                        <div class="flex items-start gap-2 md:gap-3">
                            <i class="bi bi-folder-check text-lg md:text-xl text-orange-500 mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-[#2c3e50] mb-1 text-sm md:text-base break-words"><?= htmlspecialchars($subcat['subcategory_name']) ?></h3>
                                <p class="text-xs md:text-sm text-gray-600 line-clamp-2"><?= htmlspecialchars($subcat['description_']) ?></p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                
                <?php foreach ($categories as $cat): ?>
                    <a href="category_page.php?cat_id=<?= htmlspecialchars($cat['category_id']) ?>" 
                       class="category-card bg-white rounded-lg p-3 md:p-5 hover:shadow-md hover:-translate-y-0.5 transition-all" 
                       data-type="category">
                        <div class="flex items-start gap-2 md:gap-3">
                            <i class="bi bi-folder text-lg md:text-xl text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-[#2c3e50] mb-1 text-sm md:text-base break-words"><?= htmlspecialchars($cat['category_name']) ?></h3>
                                <p class="text-xs md:text-sm text-gray-600">
                                    View <?= $cat['subcategory_count'] ?> 
                                    <?php if ($cat['subcategory_count'] == 0 || $cat['subcategory_count'] == 1): ?>
                                        subcategory
                                    <?php else: ?>
                                        subcategories
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8 bg-white rounded-lg text-gray-600">
                    <p>No results found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

            </div>
<!-- Footer -->
<?php include "footer.php"; ?>

<script>
const searchInputPage = document.getElementById('searchInputPage');
const resultsList = document.getElementById('resultsList');
const allCards = resultsList ? resultsList.querySelectorAll('[data-type]') : [];
const articleTypeBtn = document.getElementById('articleTypeBtn');
const articleTypeDropdown = document.getElementById('articleTypeDropdown');
const filterButtons = document.querySelectorAll('.article-type-filter');
const articleCount = document.getElementById('articleCount');

// Search filter
searchInputPage.addEventListener('input', function() {
    const term = this.value.toLowerCase();
    let visibleCount = 0;
    
    allCards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const description = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
        
        if ((title.includes(term) || description.includes(term)) && !card.classList.contains('hidden-by-type')) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    articleCount.textContent = `Show ${visibleCount} Results`;
});

// Dropdown toggle
articleTypeBtn.addEventListener('click', e => {
    e.stopPropagation();
    articleTypeDropdown.classList.toggle('hidden');
});
document.addEventListener('click', () => articleTypeDropdown.classList.add('hidden'));

// Filter by type
filterButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const selectedType = this.dataset.type;
        articleTypeBtn.innerHTML = this.textContent.trim() + ' <span class="text-xs text-gray-600"><i class="bi bi-filter"></i></span>';
        let visibleCount = 0;
        
        allCards.forEach(card => {
            const cardType = card.dataset.type;
            
            let shouldShow = true;
            
            if (selectedType === '') {
                shouldShow = true;
            } else if (selectedType === 'article') {
                shouldShow = cardType === 'article';
            } else if (selectedType === 'subcategory') {
                shouldShow = cardType === 'subcategory';
            } else if (selectedType === 'category') {
                shouldShow = cardType === 'category';
            }
            
            if (shouldShow) {
                const term = searchInputPage.value.toLowerCase();
                if (term) {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
                    shouldShow = title.includes(term) || description.includes(term);
                }
            }
            
            if (shouldShow) {
                card.style.display = 'block';
                card.classList.remove('hidden-by-type');
                visibleCount++;
            } else {
                card.style.display = 'none';
                card.classList.add('hidden-by-type');
            }
        });
        
        articleCount.textContent = `Show ${visibleCount} Results`;
        articleTypeDropdown.classList.add('hidden');
    });
});
</script>

</body>
</html>