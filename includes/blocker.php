<?php
global $config;
$client = getClientInfo();

// Jika sudah ada parameter blocked di URL, jangan jalankan blocker lagi (hindari redirect loop)
$blocked_flag = $_GET['blocked'] ?? '';
if (!empty($blocked_flag)) {
    return;
}

// Deteksi bot
$is_bot = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $client['userAgent']) ? true : false;

// Implementasi semua blocker
if (($config['blocker_settings']['stopbot'] ?? false) && $is_bot) { 
    header('Location: login?blocked=bot');
    exit;
}
// Country blocking: only active when explicitly enabled as a blocking feature
// Note: admin UI uses 'country_lock' to control language auto-detection, not access blocking.
$allowed_countries = $config['allowed_countries'] ?? [];
$country_block_enabled = ($config['blocker_settings']['country_block'] ?? false) || ($config['block_country'] ?? false);

// Normalize allowed_countries: bisa array atau string CSV
if (is_string($allowed_countries)) {
    $allowed_countries = array_map('trim', explode(',', $allowed_countries));
    $allowed_countries = array_map('strtoupper', $allowed_countries);
}

if ($country_block_enabled && !empty($allowed_countries) && !in_array($client['country'], $allowed_countries)) {
    header('Location: login?blocked=country');
    exit;
}
if (($config['blocker_settings']['vpn'] ?? false) && isVPN($client['ip'])) { 
    header('Location: login?blocked=vpn');
    exit;
}
if (($config['blocker_settings']['proxy_port'] ?? false) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
    header('Location: login?blocked=proxy');
    exit;
}
if (($config['blocker_settings']['user_agent'] ?? false) && empty($client['userAgent'])) { 
    header('Location: login?blocked=ua');
    exit;
}

// One Time Access
if ($config['blocker_settings']['one_time'] ?? false) {
    if (isset($_SESSION['visited'])) { 
        header('Location: login?blocked=onetimeaccess');
        exit;
    }
    else $_SESSION['visited'] = true;
}
?>