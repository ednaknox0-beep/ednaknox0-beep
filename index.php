<?php
require 'includes/config.php';
require 'includes/functions.php';

// Jangan jalankan blocker jika sudah ada parameter blocked (dari redirect blocker)
$blocked = $_GET['blocked'] ?? '';
if (empty($blocked)) {
    require 'includes/blocker.php';
}

$client = getClientInfo();
// Skip logging visits when admin session exists (prevents self-generated counts)
if (!isset($_SESSION['license'])) {
    $is_bot = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $client['userAgent']) ? 1 : 0;
    $stmt = $conn->prepare("INSERT INTO visits (ip_address, country, isp, device, browser, user_agent, is_bot) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", 
        $client['ip'],
        $client['country'],
        $client['isp'],
        $client['device'],
        $client['browser'],
        $client['userAgent'],
        $is_bot
    );
    $stmt->execute();
    $stmt->close();
}

// If public_site_url + signin_path configured, redirect there. Otherwise use local login
$redirect = 'login';
if (!empty($config['public_site_url'])) {
    $base = rtrim($config['public_site_url'], '/');
    $path = ltrim($config['signin_path'] ?? 'login', '/');
    $redirect = $base . '/' . $path;
}

// Tambah parameter blocked jika ada (dari blocker redirect)
if ($blocked) {
    $redirect .= (strpos($redirect, '?') !== false ? '&' : '?') . 'blocked=' . urlencode($blocked);
}

header('Location: ' . $redirect);
exit;
?>