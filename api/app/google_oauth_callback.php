<?php
session_start();
require_once '../admin/db.php';
require_once '../config/google_oauth_config.php';

if (!isset($_GET['code'])) {
    header('Location: userlogin.php?error=' . urlencode('Google authentication failed.'));
    exit();
}

$code = $_GET['code'];
$state = $_GET['state'] ?? '';

// Verify state to prevent CSRF attacks
if (!empty($state) && (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state'])) {
    header('Location: userlogin.php?error=' . urlencode('Invalid state parameter.'));
    exit();
}

// Exchange authorization code for access token
$token_data = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID_CLIENT,
    'client_secret' => GOOGLE_CLIENT_SECRET_CLIENT,
    'redirect_uri' => GOOGLE_REDIRECT_URI_CLIENT,
    'grant_type' => 'authorization_code'
];

$ch = curl_init(GOOGLE_TOKEN_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header('Location: userlogin.php?error=' . urlencode('Failed to get access token from Google.'));
    exit();
}

$token_response = json_decode($response, true);

if (!isset($token_response['access_token'])) {
    header('Location: userlogin.php?error=' . urlencode('Invalid response from Google.'));
    exit();
}

$access_token = $token_response['access_token'];

// Get user info from Google
$ch = curl_init(GOOGLE_USERINFO_URL . '?access_token=' . urlencode($access_token));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);

$user_info_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    header('Location: userlogin.php?error=' . urlencode('Failed to get user info from Google.'));
    exit();
}

$user_info = json_decode($user_info_response, true);

if (!isset($user_info['id']) || !isset($user_info['email'])) {
    header('Location: userlogin.php?error=' . urlencode('Invalid user info from Google.'));
    exit();
}

$google_id = $user_info['id'];
$email = $user_info['email'];
$name = $user_info['name'] ?? $user_info['given_name'] ?? 'Google User';
$picture = $user_info['picture'] ?? null;

// Check if user exists in GOOGLE_OAUTH_USERS table by Google ID
$check_sql = "SELECT * FROM GOOGLE_OAUTH_USERS WHERE google_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param('s', $google_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    // User exists, log them in directly
    $_SESSION['client_id'] = $user['oauth_id'];
    $_SESSION['client_name'] = $user['user_name'];
    $_SESSION['client_email'] = $user['email'];
    $_SESSION['client_picture'] = $user['avatar'] ?? null;
    $_SESSION['google_id'] = $google_id;
    $_SESSION['login_method'] = 'google';
    
    $conn->close();
    header('Location: index.php');
    exit();
} else {
    // New user - store Google data in session and redirect to signup form
    $_SESSION['google_data'] = [
        'id' => $google_id,
        'email' => $email,
        'name' => $name,
        'picture' => $picture
    ];
    
    $conn->close();
    header('Location: usersignin.php');
    exit();
}
?>
