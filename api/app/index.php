<?php
session_start();
require_once "connect.php";

$is_logged_in = isset($_SESSION['client_id']) && !empty($_SESSION['client_id']);

// Handle AJAX search requests (before authentication check)
if (isset($_GET['q'])) {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $results = [];

    if (!empty($query)) {
        $sql = "
    SELECT DISTINCT 
        a.article_id as id,
        a.title,
        a.category as category,
        'article' as type
    FROM articles a
    WHERE (a.title LIKE ? OR a.content LIKE ?)
    AND a.status = 'Published'
    
    UNION
    
    SELECT DISTINCT 
        sc.subcategory_id as id,
        sc.subcategory_name as title,
        c.category_name as category,
        'subcategory' as type
    FROM subcategories sc
    LEFT JOIN categories c ON sc.category_id = c.category_id
    WHERE LOWER(sc.subcategory_name) LIKE LOWER(?)
       OR LOWER(sc.description_) LIKE LOWER(?)
    
    UNION
    
    SELECT DISTINCT 
        c.category_id as id,
        c.category_name as title,
        c.description_ as category,
        'category' as type
    FROM categories c
    WHERE LOWER(c.category_name) LIKE LOWER(?) 
       OR LOWER(c.description_) LIKE LOWER(?)
    
    LIMIT 100
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $searchTerm = '%' . $query . '%';
    $stmt->bind_param('ssssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}
    }
    
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// Redirect to login if not authenticated
if (!$is_logged_in) {
    header("Location: userlogin.php");
    exit();
}

$user_avatar = null;
$user_name = null;
$user_email = null;

// Fetch user data if logged in
if ($is_logged_in) {
    $user_sql = "SELECT user_name, email, avatar FROM GOOGLE_OAUTH_USERS WHERE oauth_id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param('i', $_SESSION['client_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_avatar = $user_data['avatar'];
        $user_name = $user_data['user_name'];
        $user_email = $user_data['email'];
        $_SESSION['client_picture'] = $user_avatar;
        $_SESSION['client_name'] = $user_name;
        $_SESSION['client_email'] = $user_email;
    }
    $user_stmt->close();
}

$categories = [];
$sql = "SELECT category_id, category_name, description_, category_image FROM categories ORDER BY category_name ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Genie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hide navbar search on index.php */
        .navbar-search-icon {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-50">

<?php include "navbar.php"; ?>

<!-- search bar -->
 <div class="relative w-full min-h-[250px] sm:min-h-[300px] md:min-h-[400px] lg:min-h-[500px] bg-[url('assets/img/search.png')] bg-cover bg-center overflow-visible z-40 flex items-center justify-center">   
    <section class="z-[9999] flex flex-col items-center px-4 sm:px-5">
        <h1 class="text-[36px] sm:text-[36px] md:text-[36px] lg:text-[43px] font-bold text-white mb-4 sm:mb-6 md:mb-8 text-center">
            How can we help you today?
        </h1>

        <div class="relative w-full max-w-xs sm:max-w-md md:max-w-[650px] z-50">
            <input
                id="searchInput"
                type="text"
                placeholder="Search with keywords..."
                autocomplete="off"
                class="w-full py-3 sm:py-4 md:py-5 pl-4 sm:pl-5 md:pl-6 pr-12 sm:pr-14 md:pr-16 text-sm sm:text-base md:text-lg rounded-lg sm:rounded-xl shadow-lg md:shadow-xl
                       border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            >
            <button
                type="button"
                id="searchBtn"
                class="absolute right-0 top-0 bottom-0 px-3 sm:px-4 md:px-6 rounded-r-lg sm:rounded-r-xl
                       bg-blue-500 hover:bg-blue-600 text-white transition">
                <i class="bi bi-search text-sm sm:text-base"></i>
            </button>

            <div id="searchResults" class="absolute w-full mt-2 top-full left-0 z-[9999]"></div>
        </div>
    </section>
</div>

<!-- categories -->
<section class="bg-gray-50 py-8 sm:py-12 md:py-16">
    <div class="px-4 sm:px-6 md:px-12 mx-auto">
        <div class="mb-8 sm:mb-10 md:mb-12">
            <h2 class="sm:text-[20px] md:text-[20px] lg:text-[20px] font-bold text-gray-900 mb-3 sm:mb-4">Categories</h2>
            <p class="text-gray-600 text-sm sm:text-base md:text-lg">Related topics organized by category.</p>
        </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6">
            <?php foreach ($categories as $cat): ?>
                <div
                    onclick="window.location.href='category_page.php?cat_id=<?= $cat['category_id'] ?>'"
                    class="cursor-pointer bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden hover:shadow-xl transition transform hover:scale-105 flex flex-col h-full">
                    
                                        <!-- Category Content -->
                    <div class="p-4 sm:p-5 md:p-6 pb-0 flex flex-col flex-grow">
                        <h3 class="font-bold text-base sm:text-lg md:text-md text-gray-900 mb-2 sm:mb-3 line-clamp-2">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </h3>
                        <p class="text-gray-600 text-sm sm:text-base flex-grow line-clamp-3 mb-3 sm:mb-0">
                            <?= $cat['description_']
                                ? htmlspecialchars($cat['description_'])
                                : 'View subcategories under this category.' ?>
                        </p>
                    </div>
                    <div class="px-4 sm:px-5 md:px-6 pb-4 sm:pb-5 md:pb-6">
                        <button class="border-2 border-blue-600 text-blue-600 font-semibold py-2 px-3 sm:px-4 rounded-full hover:bg-blue-600 hover:text-white transition text-center text-xs sm:text-sm w-full">
                            VIEW MORE
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<?php include "chatbot_widget.php"; ?>

<script>
// Search functionality
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const searchResults = document.getElementById('searchResults');

// Live search on input
searchInput.addEventListener('input', async function() {
    const query = this.value.trim();
    
    if (query === '') {
        searchResults.innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (!Array.isArray(data) || data.length === 0) {
            searchResults.innerHTML = '<div class="bg-white rounded-lg p-4 text-gray-600 text-center">No results found</div>';
            return;
        }
        
        let html = '<div class="bg-white rounded-lg shadow-lg overflow-hidden">';
        let resultCount = 0;
        const maxResults = 4;
        const totalResults = data.length;
        
        // Loop to display first four results
        for (let item of data) {
            if (resultCount >= maxResults) break;
            
            if (item.type === 'article') {
                html += `
                    <div class="p-4 border-b hover:bg-gray-50 cursor-pointer transition" onclick="window.location.href='view_article.php?id=${item.id}'">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-file-text text-blue-500"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">${item.title}</h4>
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded inline-block mt-1">
                                    Article • ${item.category}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                resultCount++;
            } else if (item.type === 'subcategory') {
                html += `
                    <div class="p-4 border-b hover:bg-gray-50 cursor-pointer transition" onclick="window.location.href='subcategory_page.php?subcat_id=${item.id}'">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-folder2 text-orange-500"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">${item.title}</h4>
                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded inline-block mt-1">
                                    Subcategory • ${item.category}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                resultCount++;
            } else if (item.type === 'category') {
                html += `
                    <div class="p-4 border-b hover:bg-gray-50 cursor-pointer transition" onclick="window.location.href='category_page.php?cat_id=${item.id}'">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-folder-fill text-green-500"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">${item.title}</h4>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded inline-block mt-1">
                                    Category
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                resultCount++;
            }
        }
        
        // Show "See More" link only if there are more than 4 results
        if (totalResults > maxResults) {
            html += `
                <div class="p-4 bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-center border-t">
                    <a href="view_results.php?q=${encodeURIComponent(query)}" class="text-blue-500 hover:text-blue-700 font-semibold flex items-center justify-center gap-2">
                        See More <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            `;
        }
        
        html += '</div>';
        searchResults.innerHTML = html;
    } catch (error) {
        console.error('Search error:', error);
        searchResults.innerHTML = '<div class="bg-white rounded-lg p-4 text-red-600 text-center">Error fetching results</div>';
    }
});

// Search button click - go to full results page
searchBtn.addEventListener('click', function() {
    const query = searchInput.value.trim();
    if (query !== '') {
        window.location.href = `view_results.php?q=${encodeURIComponent(query)}`;
    }
});

// Enter key to search
searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const query = this.value.trim();
        if (query !== '') {
            window.location.href = `view_results.php?q=${encodeURIComponent(query)}`;
        }
    }
});

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        searchResults.innerHTML = '';
    }
});
</script>

</body>
</html>
