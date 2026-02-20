<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_name = isset($_SESSION['client_name']) ? htmlspecialchars($_SESSION['client_name']) : '';
$user_email = isset($_SESSION['client_email']) ? htmlspecialchars($_SESSION['client_email']) : '';
$profile_picture = isset($_SESSION['client_picture']) ? $_SESSION['client_picture'] : null;
$is_logged_in = isset($_SESSION['client_id']) && !empty($_SESSION['client_id']);
?>

<style>
@font-face {
    font-family: 'Yaro Op Black';
    src: url('assets/font/yaro-op-black.woff2') format('woff2'),
         url('assets/font/yaro-op-black.woff') format('woff');
    font-weight: 900;
    font-style: normal;
    font-display: swap;
}
</style>

<nav class="navbar">
    <div class="logo" style="justify-content: flex-start;">
        <a href="index.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.25rem; flex-shrink: 0;">
            <img src="../admin/assets/img/hr2.png" alt="HR Logo" style="height: 30px; object-fit: contain;">
            <span style="font-family: 'Yaro Op Black', sans-serif; color: #00AEEF; font-size: 1.1rem; md:font-size: 1.4rem; margin-top: 3.5px; margin-left: 0px; white-space: nowrap;">H<span style="font-family: 'Yaro Op Black', sans-serif; color: #f07e32;">R</span></span>
            <span style="font-family: 'Yaro Op Black', sans-serif; color: #00AEEF; font-size: 1.1rem; md:font-size: 1.4rem; margin-top: 3.5px; margin-left: 0px; white-space: nowrap;">Ge<span style="font-family: 'Yaro Op Black', sans-serif; color: #f07e32;">nie</span></span>
        </a>
    </div>

    <div class="navbar-actions">
        <?php if ($is_logged_in): ?>
            <!-- Search Icon and Bar in Navbar -->
            <div class="relative" style="position: relative;">
                <button
                    type="button"
                    id="navbarSearchToggle"
                    class="text-gray-700 hover:text-blue-600 transition py-2 px-2 md:px-3"
                    title="Search">
                    <i class="bi bi-search text-lg"></i>
                </button>
                    <!-- on click search mweh -->
                <div id="navbarSearchContainer" 
                     class="absolute right-0 top-full mt-2 bg-white rounded-lg shadow-lg p-3 hidden z-50"
                     style="width: min(90vw, 350px); max-width: calc(100vw - 1rem);">
                    <input
                        id="navbarSearchInput"
                        type="text"
                        placeholder="Search..."
                        autocomplete="off"
                        class="w-full py-2 pl-4 pr-10 text-sm rounded-lg border border-gray-300 
                               focus:outline-none focus:ring-2 focus:ring-blue-500/30 bg-white">
                    <div id="navbarSearchResults" class="absolute w-full mt-2 top-full left-0 rounded-lg overflow-hidden" style="width: min(90vw, 350px); max-width: calc(100vw - 1rem);"></div>
                </div>
            </div>

            <div class="user-profile-container relative" style="overflow: visible;">
                <button
                    type="button"
                    class="user-avatar-btn cursor-pointer w-9 h-9 md:w-10 md:h-10 rounded-full overflow-hidden
                           hover:ring-2 hover:ring-blue-400 transition-all flex items-center justify-center flex-shrink-0"
                    onclick="toggleUserMenu(event)"
                    title="<?php echo $user_name; ?>">

                    <?php if ($profile_picture): ?>
                        <img src="<?php echo $profile_picture; ?>" alt="<?php echo $user_name; ?>"
                             class="w-full h-full rounded-full object-cover">
                    <?php else: ?>
                        <img src="assets/img/defaultt.jpg" alt="Default Avatar"
                             class="w-full h-full rounded-full object-cover">
                    <?php endif; ?>
                </button>

                <div id="userDropdownMenu"
                     class="absolute top-full right-0 mt-2 w-56 md:w-64 bg-white
                            border border-gray-200 rounded-lg shadow-2xl z-50"
                     style="display: none;">

                    <div class="p-3 md:p-4 border-b border-gray-200 flex gap-3">
                        <?php if ($profile_picture): ?>
                            <img src="<?php echo $profile_picture; ?>" alt="<?php echo $user_name; ?>"
                                 class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover flex-shrink-0">
                        <?php else: ?>
                            <img src="assets/img/defaultt.jpg" alt="Default Avatar"
                                 class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover flex-shrink-0">
                        <?php endif; ?>

                        <div class="flex flex-col justify-center min-w-0 flex-1">
                            <div class="font-semibold text-gray-900 truncate text-sm md:text-base"><?php echo $user_name; ?></div>
                            <div class="text-xs md:text-sm text-gray-600 truncate"><?php echo $user_email; ?></div>
                        </div>
                    </div>

                    <a href="my_tickets.php"
                       class="block px-3 md:px-4 py-2 md:py-3 text-gray-700 text-sm md:text-base hover:bg-gray-50 border-b border-gray-200">
                        My Tickets
                    </a>

                    <a href="userlogout.php"
                       class="block px-3 md:px-4 py-2 md:py-3 text-red-600 text-sm md:text-base hover:bg-red-50">
                        Logout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>
<script>
// Navbar search toggle functionality
const navbarSearchToggle = document.getElementById('navbarSearchToggle');
const navbarSearchContainer = document.getElementById('navbarSearchContainer');
const navbarSearchInput = document.getElementById('navbarSearchInput');
const navbarSearchResults = document.getElementById('navbarSearchResults');

// Toggle search bar visibility
navbarSearchToggle.addEventListener('click', function(e) {
    e.stopPropagation();
    // Close profile dropdown when opening search
    const userDropdown = document.getElementById('userDropdownMenu');
    if (userDropdown) {
        userDropdown.style.display = 'none';
    }
    navbarSearchContainer.classList.toggle('hidden');
    if (!navbarSearchContainer.classList.contains('hidden')) {
        navbarSearchInput.focus();
    } else {
        navbarSearchResults.innerHTML = '';
    }
});

// Live search on input
navbarSearchInput.addEventListener('input', async function() {
    const query = this.value.trim();
    
    if (query === '') {
        navbarSearchResults.innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`index.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (!Array.isArray(data) || data.length === 0) {
            navbarSearchResults.innerHTML = '<div class="bg-white rounded-lg p-4 text-gray-600 text-center text-sm">No results found</div>';
            return;
        }
        
        let html = '<div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">';
        let resultCount = 0;
        const maxResults = 4;
        const totalResults = data.length;
        
        // Loop to display first four results
        for (let item of data) {
            if (resultCount >= maxResults) break;
            
            if (item.type === 'article') {
                html += `
                    <div class="p-3 border-b hover:bg-gray-50 cursor-pointer transition text-sm" onclick="window.location.href='view_article.php?id=${item.id}'">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-file-text text-blue-500"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800 text-xs">${item.title}</h4>
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded inline-block mt-0.5">
                                    Article
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                resultCount++;
            } else if (item.type === 'subcategory') {
                html += `
                    <div class="p-3 border-b hover:bg-gray-50 cursor-pointer transition text-sm" onclick="window.location.href='subcategory_page.php?subcat_id=${item.id}'">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-folder2 text-orange-500"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800 text-xs">${item.title}</h4>
                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded inline-block mt-0.5">
                                    Subcategory
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                resultCount++;
            } else if (item.type === 'category') {
                html += `
                    <div class="p-3 border-b hover:bg-gray-50 cursor-pointer transition text-sm" onclick="window.location.href='category_page.php?cat_id=${item.id}'">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-folder-fill text-green-500"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800 text-xs">${item.title}</h4>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded inline-block mt-0.5">
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
                <div class="p-3 bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-center border-t">
                    <a href="view_results.php?q=${encodeURIComponent(query)}" class="text-blue-500 hover:text-blue-700 font-semibold text-xs flex items-center justify-center gap-2">
                        See More <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            `;
        }
        
        html += '</div>';
        navbarSearchResults.innerHTML = html;
    } catch (error) {
        console.error('Search error:', error);
        navbarSearchResults.innerHTML = '<div class="bg-white rounded-lg p-4 text-red-600 text-center text-sm">Error fetching results</div>';
    }
});

// Enter key to search full results
navbarSearchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const query = this.value.trim();
        if (query !== '') {
            window.location.href = `view_results.php?q=${encodeURIComponent(query)}`;
        }
    }
});

// Close search when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        navbarSearchContainer.classList.add('hidden');
        navbarSearchResults.innerHTML = '';
    }
});

function toggleUserMenu(event) {
    event.preventDefault();
    event.stopPropagation();

    const dropdown = document.getElementById('userDropdownMenu');
    if (!dropdown) return;

    // Close search dropdown when opening profile
    const searchContainer = document.getElementById('navbarSearchContainer');
    if (searchContainer) {
        searchContainer.classList.add('hidden');
        navbarSearchResults.innerHTML = '';
    }

    dropdown.style.display =
        dropdown.style.display === 'block' ? 'none' : 'block';
}

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    const userContainer = document.querySelector('.user-profile-container');
    const dropdown = document.getElementById('userDropdownMenu');

    if (userContainer && dropdown && !userContainer.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});
</script>
