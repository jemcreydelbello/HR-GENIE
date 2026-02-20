<?php
session_start();
include 'connect.php';
require_once '../config/google_oauth_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_response
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data)
        ]
    ];
    
    $context = stream_context_create($options);
    $verify_response = @file_get_contents($recaptcha_url, false, $context);
    $verify_response = json_decode($verify_response);
    
    if (!$verify_response || !$verify_response->success) {
        $error = 'reCAPTCHA verification failed. Please try again.';
        header('Location: userlogin.php?error=' . urlencode($error));
        exit();
    }
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both email/username and password.';
        header('Location: userlogin.php?error=' . urlencode($error));
        exit();
    }
    
    // Query google_oauth_users table - search by username or email
    $sql = "SELECT oauth_id, user_name, email, avatar, password_hash, approved FROM GOOGLE_OAUTH_USERS WHERE user_name = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password (using bcrypt)
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables (CLIENT - use client_ prefix)
            $_SESSION['client_id'] = $user['oauth_id'];
            $_SESSION['client_name'] = $user['user_name'];
            $_SESSION['client_email'] = $user['email'];
            $_SESSION['client_picture'] = $user['avatar'];
            
            // Remember me functionality
            if ($remember_me) {
                setcookie('remember_username', $user['user_name'], time() + (86400 * 30), '/'); // 30 days
            } else {
                setcookie('remember_username', '', time() - 3600, '/'); // Delete cookie
            }
            
            // Redirect to dashboard
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Invalid username or password.';
    }
    
    // If login failed, redirect back with error
    header('Location: userlogin.php?error=' . urlencode($error));
    exit();
} else {
    header('Location: userlogin.php');
    exit();
}
?>
