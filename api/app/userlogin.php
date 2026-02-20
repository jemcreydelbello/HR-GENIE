<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['client_id'])) {
    header('Location: index.php');
    exit();
}

// Capture error message
$error = '';
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Genie | Login</title>
 <link rel="icon" type="image/jpeg" href="../admin/assets/img/intellismart.jpg">
<link rel="stylesheet" href="../admin/styles.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<style>
/* Notification bar styling */
#notifBar {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #dc2626; /* red for warning/error */
    color: #ffffff; 
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.5s ease-in-out;
}
#notifBar.show {
    opacity: 1;
    transform: translateX(0);
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

    <!-- Right Panel - Login Form -->
    <div class="right-panel">
        <div class="login-form-container">
            <div class="form-header">
                <h3 class="form-title">Welcome Aboard!</h3>
                <p class="form-subtitle">Enter your username and password to proceed.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="client_login_process.php" class="login-form">
                <div class="form-group">
                    <label for="username">Email or Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your email or username"
                        value="<?= isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : ''; ?>"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 3C6 3 3.5 5.5 2 8C3.5 10.5 6 13 10 13C14 13 16.5 10.5 18 8C16.5 5.5 14 3 10 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="10" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember_me" <?= isset($_COOKIE['remember_username']) ? 'checked' : ''; ?>>
                        <span>Remember Me</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <!-- reCAPTCHA -->
                <div class="recaptcha-container" style="margin: 1rem 0; display: flex; justify-content: center;">
                    <div class="g-recaptcha" data-sitekey="<?php 
                        require_once '../config/google_oauth_config.php';
                        echo RECAPTCHA_SITE_KEY; 
                    ?>"></div>
                </div>

                <button type="submit" class="login-btn">Log In</button>

                <!-- OR separator -->
                <div style="text-align: center; margin-top: 1.5rem; position: relative;">
                    <div style="display: flex; align-items: center; margin: 1rem 0;">
                        <div style="flex: 1; height: 1px; background: #E5E7EB;"></div>
                        <span style="padding: 0 1rem; color: #6B7280; font-size: 0.875rem;">OR</span>
                        <div style="flex: 1; height: 1px; background: #E5E7EB;"></div>
                    </div>
                    <button type="button" onclick="signInWithGoogle()" class="google-signin-btn" style="
                        width: 100%;
                        padding: 0.75rem 1rem;
                        background: #FFFFFF;
                        border: 1px solid #D1D5DB;
                        border-radius: 8px;
                        font-size: 0.875rem;
                        font-weight: 500;
                        color: #374151;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.75rem;
                        transition: all 0.2s;
                    ">
                        <svg width="18" height="18" viewBox="0 0 18 18">
                            <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
                            <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.96-2.184l-2.908-2.258c-.806.54-1.837.86-3.052.86-2.347 0-4.33-1.584-5.04-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                            <path fill="#FBBC05" d="M3.96 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.174 0 7.55 0 9s.348 2.826.957 4.039l3.003-2.332z"/>
                            <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.96 7.293C4.67 5.156 6.653 3.58 9 3.58z"/>
                        </svg>
                        Sign in with Google
                    </button>
                </div>
            </form>
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

// Toggle password
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
togglePassword.addEventListener('click', () => {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
});

// Google Sign-In
function signInWithGoogle() {
    <?php
    require_once '../config/google_oauth_config.php';
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    $params = [
        'client_id' => GOOGLE_CLIENT_ID_CLIENT,
        'redirect_uri' => GOOGLE_REDIRECT_URI_CLIENT,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    $auth_url = GOOGLE_AUTH_URL . '?' . http_build_query($params);
    ?>
    window.location.href = '<?= $auth_url ?>';
}

// Verify reCAPTCHA
document.querySelector('.login-form').addEventListener('submit', function(e) {
    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
        e.preventDefault();
        alert('Please complete the reCAPTCHA verification.');
        return false;
    }
});
</script>



</body>
</html>
