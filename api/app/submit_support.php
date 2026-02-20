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
    <title>Submit Support Ticket - Hrdotnet Genie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50">

<?php include "navbar.php"; ?>

<!-- Header Section with Background -->
<div class="relative w-full h-64 sm:h-80 md:h-96 lg:h-[400px] bg-[url('assets/img/lounge.png')] bg-cover bg-center overflow-hidden">
    <div class="absolute inset-0 bg-black/40"></div>
    <section class="absolute inset-x-0 inset-y-0 z-20 flex flex-col items-center justify-center px-4 sm:px-5 md:px-12">
        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-white mb-2 sm:mb-4 text-center drop-shadow-lg">
            Submit a Support Ticket
        </h1>
        <p class="text-sm sm:text-base md:text-lg lg:text-xl text-white text-center drop-shadow-lg max-w-2xl px-2">
            We're here to help. Tell us what you need assistance with
        </p>
    </section>
</div>

<!-- Submit Ticket Form Section -->
<section class="bg-white py-8 sm:py-12 md:py-16 lg:py-20">
    <div class="px-4 sm:px-6 md:px-8 lg:px-12 max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 md:p-8">
            <form id="ticketForm" class="space-y-4 sm:space-y-5 md:space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 md:gap-6">
                    <!-- Full Name -->
                    <div class="flex flex-col gap-2">
                        <label for="ticketFullName" class="text-xs sm:text-sm font-semibold text-gray-700">Full Name <span class="text-red-600">*</span></label>
                        <input type="text" id="ticketFullName" name="full_name" value="<?= htmlspecialchars($user_name ?? '') ?>" placeholder="Your full name" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-gray-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="fullNameError text-red-600 text-xs hidden"></div>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-2">
                        <label for="ticketEmail" class="text-xs sm:text-sm font-semibold text-gray-700">Email <span class="text-red-600">*</span></label>
                        <input type="email" id="ticketEmail" name="email" value="<?= htmlspecialchars($user_email ?? '') ?>" placeholder="Your email address" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-gray-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="emailError text-red-600 text-xs hidden"></div>
                    </div>
                </div>

                <!-- Category -->
                <div class="flex flex-col gap-2">
                    <label for="ticketCategory" class="text-xs sm:text-sm font-semibold text-gray-700">Category <span class="text-red-600">*</span></label>
                    <select id="ticketCategory" name="category" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="categoryError text-red-600 text-xs hidden"></div>
                </div>

                <!-- Subject -->
                <div class="flex flex-col gap-2">
                    <label for="ticketSubject" class="text-xs sm:text-sm font-semibold text-gray-700">Subject <span class="text-red-600">*</span></label>
                    <input type="text" id="ticketSubject" name="subject" placeholder="Brief description of your issue" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-gray-900 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="subjectError text-red-600 text-xs hidden"></div>
                </div>

                <!-- Message -->
                <div class="flex flex-col gap-2">
                    <label for="ticketMessage" class="text-xs sm:text-sm font-semibold text-gray-700">Message <span class="text-red-600">*</span></label>
                    <textarea id="ticketMessage" name="message" placeholder="Please provide detailed information about your issue" required class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg text-gray-900 text-xs sm:text-sm min-h-32 sm:min-h-40 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <div class="messageError text-red-600 text-xs hidden"></div>
                </div>

                <!-- Attachment -->
                <div class="flex flex-col gap-2">
                    <label for="ticketAttachment" class="text-xs sm:text-sm font-semibold text-gray-700">Attachment (Optional)</label>
                    <input type="file" id="ticketAttachment" name="attachment" accept="image/*,.pdf,.doc,.docx" class="w-full text-xs sm:text-sm text-gray-600 file:mr-2 sm:file:mr-3 file:py-2 file:px-2 sm:file:px-3 file:rounded-md file:border file:border-gray-300 file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                    <div class="attachmentError text-red-600 text-xs hidden"></div>
                </div>

                <!-- Success Message -->
                <div id="submitTicketSuccess" class="hidden bg-green-500 text-white px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-xs sm:text-sm font-medium text-center"></div>

                <!-- Error Message -->
                <div id="submitTicketError" class="hidden bg-red-500 text-white px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-xs sm:text-sm font-medium text-center"></div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t border-gray-200">
                    <button type="submit" id="submitTicketBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 sm:py-3 px-3 sm:px-4 rounded-lg transition-colors text-sm sm:text-base">Submit Ticket</button>
                    <button type="reset" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 sm:py-3 px-3 sm:px-4 rounded-lg transition-colors text-sm sm:text-base">Clear</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
const ticketForm = document.getElementById('ticketForm');

if (ticketForm) {
    ticketForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(ticketForm);
        const submitBtn = document.getElementById('submitTicketBtn');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            const res = await fetch('submit_ticket.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                const successMsg = document.getElementById('submitTicketSuccess');
                successMsg.textContent = `Ticket submitted successfully! Ticket Number: ${data.ticket_number}`;
                successMsg.classList.remove('hidden');
                
                ticketForm.reset();
                
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                const errorMsg = document.getElementById('submitTicketError');
                errorMsg.textContent = data.error || 'Failed to submit ticket. Please try again.';
                errorMsg.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error submitting ticket:', error);
            const errorMsg = document.getElementById('submitTicketError');
            errorMsg.textContent = 'An error occurred. Please try again.';
            errorMsg.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Ticket';
        }
    });
}
</script>

<?php include "footer.php"; ?>
<?php include "chatbot_widget.php"; ?>

</body>
</html>
