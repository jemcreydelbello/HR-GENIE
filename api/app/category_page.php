<?php
include "connect.php";

$cat_id = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
if ($cat_id === 0) {
    die("Invalid category.");
}

$cat_stmt = $conn->prepare("SELECT category_id, category_name, description_, category_image FROM categories WHERE category_id = ?");
if (!$cat_stmt) {
    die("Database error: " . $conn->error);
}
$cat_stmt->bind_param('i', $cat_id);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();

if ($cat_result->num_rows === 0) {
    die("Category not found.");
}

$category = $cat_result->fetch_assoc();

// Fix image path if it's relative
if (!empty($category['category_image']) && strpos($category['category_image'], 'http') === false) {
    $category['category_image'] = '../' . $category['category_image'];
}

$cat_stmt->close();

// Get subcategories for this category
$subcat_stmt = $conn->prepare("
    SELECT subcategory_id, category_id, subcategory_name, description_, created_by, created_at
    FROM subcategories 
    WHERE category_id = ? 
    ORDER BY created_at DESC
");
if (!$subcat_stmt) {
    die("Database error: " . $conn->error);
}
$subcat_stmt->bind_param('i', $cat_id);
$subcat_stmt->execute();
$subcat_result = $subcat_stmt->get_result();

$subcategories = [];
while ($row = $subcat_result->fetch_assoc()) {
    $subcategories[] = $row;
}

$subcat_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['category_name']) ?> - HR Genie</title>
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
 
    <div class="w-screen -ml-[calc((100vw-100%)/2)] border-gray-200 mb-4 pt-6 md:pt-10 pb-8 md:pb-0 relative w-full min-h-[250px] sm:min-h-[300px] md:min-h-[250px] lg:min-h-[250px]" 
         style="background-image: url('assets/img/search.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"> 
         <!-- Background overlay with fade gradient for readability -->
        <div class="absolute inset-0" style="background: linear-gradient(to right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.3), transparent);"></div>
        
        <!-- Content wrapper with relative positioning -->
        <div class="relative z-10">
          <!-- Breadcrumb Navigation -->
       <?php
        $breadcrumbs = [];
        include 'breadcrumb.php';
        ?>

      <!-- Category Title -->
        <h1 class="text-base sm:text-3xl md:text-3xl md:text-4xl font-bold text-white mb-1 pl-5 md:pl-10 pr-5 md:pr-10" style="font-size: 28px;">
        <?= htmlspecialchars($category['category_name']) ?>
        </h1>

        <!-- Category Description -->
          <p class="text-gray-100 text-sm sm:text-base mb-3 md:mb-6 leading-relaxed pl-5 md:pl-10 pr-5 md:pr-10 text-justify">
    <?= !empty($category['description_']) 
        ? htmlspecialchars($category['description_']) 
        : 'Information about ' . htmlspecialchars($category['category_name']) . '.' ?>
</p>

        <!-- Subcategories Section Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pl-5 md:pl-10 pr-5 md:pr-10 pb-8 md:pb-0">
            <!-- Subcategory Count -->
            <div class="text-sm text-gray-100 font-medium">
                Show <span id="subcategoryCount"><?= count($subcategories) ?></span> Subcategories
            </div>

            <!-- Search Bar -->
            <div class="relative w-full md:w-80">
                <input type="text" 
       id="categorySearchInput" 
       class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-[#4f46e5] focus:ring-2 focus:ring-[#4f46e5]/10 transition-all" 
       placeholder="Search subcategories">
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>
        </div>
    </div>
             <!-- Subcategories Header -->
    <section class="px-5 md:px-10 py-8 md:py-12">
        <div class="mb-8 md:mb-12">
            <h2 class="sm:text-[20px] md:text-[20px] lg:text-[20px] font-bold text-gray-900 mb-3 sm:mb-4">Subcategories</h2>
            <p class="text-gray-600 text-sm sm:text-base md:text-lg">Explore related topics available under this category.</p>
        </div>

    <!-- Subcategories List -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 md:gap-6 mb-2" id="subcategoriesList">
        <?php if (!empty($subcategories)): ?>
            <?php foreach ($subcategories as $subcat): ?>
                <div class="subcat-card bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden hover:shadow-xl transition transform hover:scale-105 cursor-pointer" 
                     onclick="window.location.href='subcategory_page.php?subcat_id=<?= $subcat['subcategory_id'] ?>'">
                    
                    <!-- Content Section -->
                    <div class="p-4 sm:p-5 md:p-6 flex flex-col h-full">
                                             
                        <!-- Title -->
                        <h3 class="font-bold text-base sm:text-lg md:text-md text-gray-900 mb-2 sm:mb-3 line-clamp-2">
                            <?= htmlspecialchars($subcat['subcategory_name']) ?>
                        </h3>
                        
                        <!-- Description -->
                        <p class="text-gray-600 text-sm sm:text-base flex-grow line-clamp-3 mb-3 sm:mb-4">
                            <?= !empty($subcat['description_']) 
                                ? htmlspecialchars($subcat['description_'])
                                : 'Browse articles in this subcategory.' ?>
                        </p>
                        
                        <!-- View More Button -->
                        <button class="border-2 border-blue-600 text-blue-600 font-semibold py-2 px-3 sm:px-4 rounded-full hover:bg-blue-600 hover:text-white transition text-center text-xs sm:text-sm w-full">
                            VIEW MORE
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-10 bg-white rounded-lg text-gray-600 col-span-full">
                <p>No subcategories available under this category.</p>
            </div>
        <?php endif; ?>
    </div>
    </section>
        </div>
       

    <!-- Footer -->
    <?php include "footer.php"; ?>

    <script>
        // Category search function
        const categorySearchInput = document.getElementById('categorySearchInput');
        const subcategoriesList = document.getElementById('subcategoriesList');
        const subcatCards = subcategoriesList.querySelectorAll('.subcat-card');
        const subcategoryCount = document.getElementById('subcategoryCount');

        categorySearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            subcatCards.forEach(card => {
                const title = card.querySelector('h3') ? card.querySelector('h3').textContent.toLowerCase() : '';
                const description = card.querySelector('p.text-gray-600') ? card.querySelector('p.text-gray-600').textContent.toLowerCase() : '';
                
                // Show card if search term is empty or matches title/description
                const matches = searchTerm === '' || title.includes(searchTerm) || description.includes(searchTerm);
                
                if (matches) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Update subcategory count
            subcategoryCount.textContent = visibleCount;
        });
    </script>


<?php include 'chatbot_widget.php'; ?>

</body>
</html>