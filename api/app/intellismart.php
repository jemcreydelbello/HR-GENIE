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
    <title>Intellismart Technology Inc. - Hrdotnet Genie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50">

<?php include "navbar.php"; ?>

<!-- Header Background Section -->
<div class="relative w-full h-[400px] bg-[url('assets/img/lounge.png')] bg-cover bg-center overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-40"></div>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <h1 class="text-5xl md:text-6xl font-bold text-white text-center drop-shadow-lg">
            Intellismart Technology Inc.
        </h1>
        <p class="text-xl text-white mt-4 drop-shadow-lg">Leading innovator in digital solutions and enterprise software</p>
    </div>
</div>

<!-- Content Section -->
<section class="bg-white py-20">
    <div class="px-12">
        
        <!-- About Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 mb-20 items-center">
            <div class="flex flex-col justify-center">
                <h2 class="text-4xl font-bold text-gray-900 mb-6">About Intellismart</h2>
                <p class="text-lg text-gray-600 mb-5 leading-relaxed">
                    Intellismart Technology Inc. is a forward-thinking software company dedicated to transforming businesses through innovative technology solutions. With over a decade of experience, we've helped thousands of organizations streamline their operations and achieve digital excellence.
                </p>
                <p class="text-lg text-gray-600 mb-5 leading-relaxed">
                    Our commitment to quality, innovation, and customer satisfaction sets us apart in the competitive tech industry. We believe in building lasting partnerships with our clients and understanding their unique challenges.
                </p>
                <p class="text-lg text-gray-600 leading-relaxed">
                    Our flagship Hrdotnet Genie system represents the pinnacle of HR management technology, designed to streamline processes and empower organizations worldwide.
                </p>
            </div>
            <div class="flex items-center justify-center">
                <img src="assets/img/Intellisupport.jpg" alt="Intellismart Support" class="rounded-2xl shadow-lg w-full max-w-lg h-auto object-cover">
            </div>
        </div>

        <!-- Core Values and Services Grid with Light Gray Background -->
        <div class="bg-gray-100 rounded-2xl p-16 -mx-12 px-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                
                <!-- Core Values Section -->
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-8">Our Core Values</h3>
                    <div class="space-y-6">
                    <!-- Innovation -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-600 text-white">
                                <i class="bi bi-lightning-charge text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Innovation</h4>
                            <p class="text-sm text-gray-600 mt-2">Continuously develop cutting-edge solutions that drive business growth and transform industries</p>
                        </div>
                    </div>

                    <!-- Excellence -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-indigo-600 text-white">
                                <i class="bi bi-star-fill text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Excellence</h4>
                            <p class="text-sm text-gray-600 mt-2">Deliver exceptional quality in every project, product, and customer interaction without compromise</p>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-purple-600 text-white">
                                <i class="bi bi-hand-thumbs-up text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Support</h4>
                            <p class="text-sm text-gray-600 mt-2">Provide 24/7 dedicated customer support and consultation services to ensure success</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div>
                <h3 class="text-3xl font-bold text-gray-900 mb-8">Our Services</h3>
                <div class="space-y-6">
                    <!-- Cloud Solutions -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-600 text-white text-lg">
                                <i class="bi bi-cloud"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Cloud Solutions</h4>
                            <p class="text-sm text-gray-600 mt-2">Secure, scalable cloud infrastructure that grows with your business needs and demands</p>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-purple-600 text-white text-lg">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Security</h4>
                            <p class="text-sm text-gray-600 mt-2">Enterprise-grade security and comprehensive data protection solutions for peace of mind</p>
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-green-600 text-white text-lg">
                                <i class="bi bi-bar-chart"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Analytics</h4>
                            <p class="text-sm text-gray-600 mt-2">Data-driven insights and comprehensive reporting tools for informed decision-making</p>
                        </div>
                    </div>

                    <!-- Development -->
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-orange-600 text-white text-lg">
                                <i class="bi bi-code-square"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Development</h4>
                            <p class="text-sm text-gray-600 mt-2">Custom software development tailored to your unique business requirements and goals</p>
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
