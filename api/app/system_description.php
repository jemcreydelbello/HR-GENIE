<?php
session_start();
require_once "connect.php";

$is_logged_in = isset($_SESSION['client_id']) && !empty($_SESSION['client_id']);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Description - HR Genie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50">

<?php include "navbar.php"; ?>

<!-- Header Section with Background -->
<div class="relative w-full h-[400px] bg-[url('assets/img/lounge.png')] bg-cover bg-center overflow-hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <section class="absolute inset-x-0 inset-y-0 z-20 flex flex-col items-center justify-center px-5">
        <h1 class="text-5xl md:text-6xl font-bold text-white mb-4 text-center drop-shadow-lg">
            System Architecture & Content Organization
        </h1>
        <p class="text-xl text-white text-center drop-shadow-lg max-w-2xl">
            Understanding how Hrdotnet Genie organizes information for optimal navigation
        </p>
    </section>
</div>

<!-- Content Section -->
<section class="bg-gradient-to-b from-gray-50 to-white py-20">
    <div class="px-12">
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-2xl p-8 border border-indigo-200 mb-20 max-w-4xl mx-auto">
            <h3 class="text-2xl font-bold text-gray-900 mb-8 text-center">How to Navigate Hrdotnet Genie</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg p-6 border-2 border-indigo-300 shadow-md text-center">
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-house text-2xl text-indigo-600"></i>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-2">1. Start at Home</h4>
                    <p class="text-sm text-gray-600">Visit the main FAQ page</p>
                </div>

                <div class="bg-white rounded-lg p-6 border-2 border-indigo-300 shadow-md text-center">
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-folder text-2xl text-indigo-600"></i>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-2">2. Choose Category</h4>
                    <p class="text-sm text-gray-600">Browse by main topic</p>
                </div>

                <div class="bg-white rounded-lg p-6 border-2 border-indigo-300 shadow-md text-center">
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-diagram-2 text-2xl text-indigo-600"></i>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-2">3. Select Subcategory</h4>
                    <p class="text-sm text-gray-600">Narrow down your topic</p>
                </div>

                <div class="bg-white rounded-lg p-6 border-2 border-indigo-300 shadow-md text-center">
                    <div class="bg-indigo-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-file-earmark text-2xl text-indigo-600"></i>
                    </div>
                    <h4 class="font-bold text-gray-900 mb-2">4. Read Article</h4>
                    <p class="text-sm text-gray-600">Get your answer</p>
                </div>
            </div>
        </div>

        <!-- Article Types and Content Categories Section -->
        <div class="bg-gray-100 rounded-lg p-12 mb-20">
            <div class="space-y-8">
                <!-- Content Category Row -->
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Content Category</h3>
                    
                    <div class="grid grid-cols-3 gap-8">
                        <!-- Category -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Category</h4>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Short, concise Q&A format articles that provide quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Frequently asked questions</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Minimal text</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Easy to scan</li>
                                <li class="text-xs text-gray-600 italic mt-2">Example: "How do I reset my password?" or "Where do I find my pay stub?"</li>
                            </ul>
                        </div>

                        <!-- Subcategory -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Subcategory</h4>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Short, concise Q&A format articles that provide quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Frequently asked questions</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Minimal text</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Easy to scan</li>
                                <li class="text-xs text-gray-600 italic mt-2">Example: "How do I reset my password?" or "Where do I find my pay stub?"</li>
                            </ul>
                        </div>

                        <!-- Article Type -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Article Type</h4>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Short, concise Q&A format articles that provide quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Frequently asked questions</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Minimal text</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Easy to scan</li>
                                <li class="text-xs text-gray-600 italic mt-2">Example: "How do I reset my password?" or "Where do I find my pay stub?"</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Article Type Row -->
                <div class="mt-12">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Article Type</h3>
                    
                    <div class="grid grid-cols-3 gap-8">
                        <!-- Simple Question -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Simple Question</h4>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Short, concise Q&A format articles that provide quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Frequently asked questions</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Minimal text</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Easy to scan</li>
                                <li class="text-xs text-gray-600 italic mt-2">Example: "How do I reset my password?" or "Where do I find my pay stub?"</li>
                            </ul>
                        </div>

                        <!-- Step by Step -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Step by Step</h4>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Short, concise Q&A format articles that provide quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Frequently asked questions</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Minimal text</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Easy to scan</li>
                                <li class="text-xs text-gray-600 italic mt-2">Example: "How do I reset my password?" or "Where do I find my pay stub?"</li>
                            </ul>
                        </div>

                        <!-- Standard -->
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Standard</h4>
                            <ul class="space-y-3 text-sm text-gray-700">
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Short, concise Q&A format articles that provide quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Frequently asked questions</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Quick answers</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Minimal text</li>
                                <li><i class="bi bi-check text-gray-600 mr-2"></i>Easy to scan</li>
                                <li class="text-xs text-gray-600 italic mt-2">Example: "How do I reset my password?" or "Where do I find my pay stub?"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include "footer.php"; ?>
<?php include "chatbot_widget.php"; ?>

</body>
</html>
