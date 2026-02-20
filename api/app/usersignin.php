<?php
session_start();
include 'connect.php';

// Redirect if already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: index.php');
    exit();
}

// Check if coming from Google OAuth
if (!isset($_SESSION['google_data'])) {
    header('Location: userlogin.php');
    exit();
}

$google_data = $_SESSION['google_data'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $avatar = trim($_POST['avatar'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $error = 'Name is required';
    } elseif (empty($department)) {
        $error = 'Department is required';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Check if user already exists
        $check_sql = "SELECT oauth_id FROM GOOGLE_OAUTH_USERS WHERE google_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('s', $google_data['id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing user
            $update_sql = "UPDATE GOOGLE_OAUTH_USERS SET user_name = ?, email = ?, avatar = ?, department = ?, password_hash = ?, approved = 1 WHERE google_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ssssss', $name, $email, $avatar, $department, $password_hash, $google_data['id']);
            
            if ($update_stmt->execute()) {
                $_SESSION['client_id'] = $user['oauth_id'] ?? null;
                $_SESSION['client_name'] = $name;
                $_SESSION['client_email'] = $email;
                $_SESSION['client_picture'] = $avatar ?? null;
                header('Location: index.php');
                exit();
            } else {
                $error = 'Error updating profile: ' . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            // Insert new user
            $insert_sql = "INSERT INTO GOOGLE_OAUTH_USERS (google_id, user_name, email, avatar, department, password_hash, approved) VALUES (?, ?, ?, ?, ?, ?, 1)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param('ssssss', $google_data['id'], $name, $email, $avatar, $department, $password_hash);
            
            if ($insert_stmt->execute()) {
                $success = 'Account created successfully!';
                // Clear google session data after successful registration
                unset($_SESSION['google_data']);
                // Auto-login and redirect to index
                $_SESSION['client_id'] = $insert_stmt->insert_id;
                $_SESSION['client_name'] = $name;
                $_SESSION['client_email'] = $email;
                $_SESSION['client_picture'] = $avatar ?? null;
                header('Location: index.php');
                exit();
            } else {
                $error = 'Error creating account: ' . $insert_stmt->error;
            }
            $insert_stmt->close();
        }
        
        $check_stmt->close();
    }
}

// Get list of departments
$departments = ['HR', 'Finance', 'IT', 'Operations', 'Marketing', 'Sales', 'Production', 'Logistics', 'Administration', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Complete Your Profile - HR Genie</title>
<link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
<link rel="stylesheet" href="../styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>
.profile-header {
    text-align: center;
    margin-bottom: 30px;
}

.profile-header h2 {
    font-size: 1.75rem;
    color: #1f2937;
    margin-bottom: 10px;
}

.profile-header p {
    color: #6b7280;
    font-size: 0.95rem;
}

.avatar-upload {
    position: relative;
    margin: 0 auto 20px;
    width: 120px;
    height: 120px;
}

.avatar-upload img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #667eea;
}

.avatar-upload-label {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #667eea;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid white;
    transition: all 0.3s;
}

.avatar-upload-label:hover {
    background: #764ba2;
    transform: scale(1.1);
}

.password-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.error-message {
    background-color: #fee;
    color: #c33;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #c33;
}

.success-message {
    background-color: #efe;
    color: #3c3;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #3c3;
}

.submit-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    padding: 40px;
    text-align: center;
    max-width: 500px;
    width: 90%;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-icon {
    font-size: 3.5rem;
    color: #f59e0b;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
}

.modal-title {
    font-size: 1.5rem;
    color: #1f2937;
    font-weight: 700;
    margin-bottom: 15px;
}

.modal-message {
    color: #6b7280;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 30px;
}

.modal-btn {
    display: inline-block;
    padding: 12px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 1rem;
}

.modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}
</style>
</head>
<body>
<div class="login-container">
    <!-- Logo at top left -->
    <div class="top-logo">
        <a href="index.php" style="text-decoration: none;"><h1 style="color: white;">HR Genie</h1></a>
    </div>

    <!-- Left Panel - Slideshow Banner -->
    <div class="left-panel">
        <div class="slideshow-container">
            <div class="slide active">
                <img src="assets/img/login3.png" alt="Slide 1" class="slide-image">
            </div>
            <div class="slide active">
                <img src="assets/img/login2.png" alt="Slide 2" class="slide-image">
            </div>
            <div class="slide">
                <img src="assets/img/login3.png" alt="Slide 3" class="slide-image">
            </div>
        </div>
        <div class="shapes-overlay">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>
        <div class="slide-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
        </div>
    </div>

    <!-- Right Panel - Profile Form -->
    <div class="right-panel">
        <div class="login-form-container">
            <div class="profile-header">
                <h2>Complete Your Profile</h2>
                <p>Welcome! Please complete your registration details</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Avatar Display -->
                <div class="avatar-upload">
                    <img id="avatarDisplay" src="<?= htmlspecialchars($google_data['picture'] ?? 'https://via.placeholder.com/120') ?>" alt="Avatar">
                    <label class="avatar-upload-label" title="Upload from Google" onclick="alert('Avatar is synced from Google. Edit the URL field to change.')">
                        <i class="bi bi-camera-fill" style="font-size: 1.2rem;"></i>
                    </label>
                </div>

                <!-- Avatar URL (Hidden input, can be edited) -->
                <input type="hidden" id="avatar" name="avatar" value="<?= htmlspecialchars($google_data['picture'] ?? '') ?>">

                <!-- Name Field -->
                <div class="form-group">
                    <label for="name">
                        <i class="bi bi-person-fill"></i> Full Name
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= htmlspecialchars($google_data['name'] ?? '') ?>" 
                        placeholder="Enter your full name"
                        required
                    >
                </div>

                <!-- Email Field (Read-only) -->
                <div class="form-group">
                    <label for="email">
                        <i class="bi bi-envelope-fill"></i> Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?= htmlspecialchars($google_data['email'] ?? '') ?>" 
                        readonly
                        style="background-color: #f5f5f5; cursor: not-allowed;"
                    >
                </div>

                <!-- Department Dropdown -->
                <div class="form-group">
                    <label for="department">
                        <i class="bi bi-building"></i> Department
                    </label>
                    <select id="department" name="department" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Password Fields -->
                <div class="password-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="bi bi-lock-fill"></i> Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Min 6 characters"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="bi bi-lock-fill"></i> Confirm Password
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Re-enter password"
                            required
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">
                    <i class="bi bi-check-lg"></i> Complete Registration
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <p style="color: #666; font-size: 0.9rem;">
                    Already have an account? <a href="userlogin.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>



<script>
// Slideshow
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const indicators = document.querySelectorAll('.indicator');
const totalSlides = slides.length;

function showSlide(index) {
    slides.forEach(s => s.classList.remove('active'));
    indicators.forEach(i => i.classList.remove('active'));
    slides[index].classList.add('active');
    indicators[index].classList.add('active');
}
function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    showSlide(currentSlide);
}
setInterval(nextSlide, 5000);
indicators.forEach((ind, i) => {
    ind.addEventListener('click', () => {
        currentSlide = i;
        showSlide(currentSlide);
    });
});

// Update avatar display when URL changes
document.getElementById('avatarDisplay').addEventListener('error', function() {
    this.src = 'https://via.placeholder.com/120?text=Avatar';
});

// Password validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    if (this.value && password && this.value !== password) {
        this.style.borderColor = '#ff4444';
    } else if (this.value && password && this.value === password) {
        this.style.borderColor = '#44ff44';
    } else {
        this.style.borderColor = '#ddd';
    }
});

// Modal Management
function closeModal() {
    const modal = document.getElementById('approvalModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

// Close modal when clicking outside of it
window.addEventListener('click', function(event) {
    const modal = document.getElementById('approvalModal');
    if (modal && event.target === modal) {
        closeModal();
    }
});
</script>

<?php include 'chatbot_widget.php'; ?>

</body>
</html>
