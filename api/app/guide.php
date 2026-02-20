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
    <title>System Guide - HR Genie</title>
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
            System Guide & How to Use Hrdotnet Genie
        </h1>
        <p class="text-xl text-white text-center drop-shadow-lg max-w-2xl">
            Master the features of our comprehensive HR management platform
        </p>
    </section>
</div>

<!-- Getting Started -->
<section class="bg-white py-20">
    <div class="px-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20 items-center">
            <div class="flex items-center justify-center">
                <button onclick="openVideoModal()" class="w-full flex items-center justify-center cursor-pointer group">
                    <div class="relative w-full bg-black rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition">
                        <img src="https://img.youtube.com/vi/lPJcv8oB4I0/maxresdefault.jpg" alt="YouTube Video" class="w-full h-auto">
                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40 transition">
                            <div class="bg-red-600 rounded-full p-6 shadow-lg group-hover:bg-red-700 transition transform group-hover:scale-110">
                                <i class="bi bi-play-fill text-white text-5xl"></i>
                            </div>
                        </div>
                    </div>
                </button>
            </div>

            <div>
                <div class="flex items-center gap-4 mb-8">
                    <h3 class="text-3xl font-bold text-gray-900">How to Submit Support Tickets</h3>
                </div>
                <div class="space-y-5">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-blue-600 text-white font-bold text-lg">1</div>
                        <div>
                            <h4 class="font-semibold text-lg text-gray-900">Click "Submit Ticket"</h4>
                            <p class="text-gray-600 text-base">Navigate to any page and find the "Submit Ticket" button in the navigation bar or use the quick access tile</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-blue-600 text-white font-bold text-lg">2</div>
                        <div>
                            <h4 class="font-semibold text-lg text-gray-900">Fill in Your Details</h4>
                            <p class="text-gray-600 text-base">Provide your name, email, and select an appropriate category for your issue</p>
                        </div>
                    </div>
                     <div class="flex gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-blue-600 text-white font-bold text-lg">3</div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Describe Your Issue</h4>
                            <p class="text-gray-600">Provide a clear subject and detailed description of your problem or question</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-lg bg-blue-600 text-white font-bold text-lg">4</div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Attach Files (Optional)</h4>
                            <p class="text-gray-600">Upload screenshots, documents, or other relevant files to help us understand your issue better</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Modal -->
        <div id="videoModal" class="hidden fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4" onclick="closeVideoModal(event)">
            <div class="relative w-full max-w-4xl bg-black rounded-2xl overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
                <button onclick="closeVideoModal()" class="absolute top-4 right-4 bg-red-600 hover:bg-red-700 text-white rounded-full p-2 z-10 transition">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
                <div class="relative w-full" style="padding-bottom: 56.25%;">
                    <iframe 
                        class="absolute inset-0 w-full h-full"
                        src="https://www.youtube.com/embed/lPJcv8oB4I0?autoplay=1"
                        title="How to Submit Support Ticket"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Core Features -->
        <div class="mb-20 -mr-12">
            <div class="relative bg-gradient-to-r from-orange-400 via-orange-500 to-orange-600 py-4 overflow-hidden rounded-2xl ml-auto w-3/5 text-right pr-12">
                <h3 class="text-2xl font-bold text-white mb-3 drop-shadow-lg">Key System Features</h3>
                <p class="text-l text-white drop-shadow-lg">Explore the powerful capabilities of Hrdotnet Genie</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12 px-12">
                <!-- Employee Management Card -->
                <div class="feature-card bg-orange-100 rounded-xl shadow-md p-8 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-person-check text-3xl text-blue-600"></i>
                            <h4 class="text-xl font-bold text-gray-900">Employee Management</h4>
                        </div>
                    </div>
                    <div class="feature-content">
                        <p class="text-gray-700 mb-4 font-semibold">Centralized employee database with comprehensive profiles, documents, and tracking</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Personnel information</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Document management</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Performance tracking</li>
                        </ul>
                    </div>
                </div>

                <!-- Attendance & Leave Card -->
                <div class="feature-card bg-orange-100 rounded-xl shadow-md p-8 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-calendar-check text-3xl text-purple-600"></i>
                            <h4 class="text-xl font-bold text-gray-900">Attendance & Leave</h4>
                        </div>
                    </div>
                    <div class="feature-content">
                        <p class="text-gray-700 mb-4 font-semibold">Track attendance, manage leave requests, and monitor work hours efficiently</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Leave management</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Attendance tracking</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Work hour monitoring</li>
                        </ul>
                    </div>
                </div>

                <!-- Payroll Management Card -->
                <div class="feature-card bg-orange-100 rounded-xl shadow-md p-8 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-cash-coin text-3xl text-green-600"></i>
                            <h4 class="text-xl font-bold text-gray-900">Payroll Management</h4>
                        </div>
                    </div>
                    <div class="feature-content">
                        <p class="text-gray-700 mb-4 font-semibold">Automated payroll processing with tax calculations and compliance</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Salary processing</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Tax compliance</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Benefits management</li>
                        </ul>
                    </div>
                </div>

                <!-- Reports & Analytics Card -->
                <div class="feature-card bg-orange-100 rounded-xl shadow-md p-8 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-graph-up text-3xl text-orange-600"></i>
                            <h4 class="text-xl font-bold text-gray-900">Reports & Analytics</h4>
                        </div>
                    </div>
                    <div class="feature-content">
                        <p class="text-gray-700 mb-4 font-semibold">Comprehensive reporting tools for data-driven insights and decision making</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Custom reports</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Data analytics</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Trend analysis</li>
                        </ul>
                    </div>
                </div>

                <!-- Recruitment Card -->
                <div class="feature-card bg-orange-100 rounded-xl shadow-md p-8 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-people text-3xl text-red-600"></i>
                            <h4 class="text-xl font-bold text-gray-900">Recruitment</h4>
                        </div>
                    </div>
                    <div class="feature-content">
                        <p class="text-gray-700 mb-4 font-semibold">Streamlined recruitment process from job posting to onboarding</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Job posting</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Candidate tracking</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Onboarding tools</li>
                        </ul>
                    </div>
                </div>

                <!-- Performance Tracking Card -->
                <div class="feature-card bg-orange-100 rounded-xl shadow-md p-8 hover:shadow-lg transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-award text-3xl text-indigo-600"></i>
                            <h4 class="text-xl font-bold text-gray-900">Performance Tracking</h4>
                        </div>
                    </div>
                    <div class="feature-content">
                        <p class="text-gray-700 mb-4 font-semibold">Track employee performance and manage appraisals effectively</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Goal setting</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Appraisals</li>
                            <li><i class="bi bi-check text-green-600 mr-2"></i>Review management</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include "footer.php"; ?>
<?php include "chatbot_widget.php"; ?>

<script>
function openVideoModal() {
    document.getElementById('videoModal').classList.remove('hidden');
}

function closeVideoModal(event) {
    if (event) event.preventDefault();
    document.getElementById('videoModal').classList.add('hidden');
}

// Close modal when pressing Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});
</script>

</body>
</html>
