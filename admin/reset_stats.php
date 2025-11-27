<?php
require '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete all visits
        $conn->query("TRUNCATE TABLE visits");
        
        // Delete all victims
        $conn->query("TRUNCATE TABLE victims");
        
        // Reset login_count di config_data
        $result = $conn->query("SELECT config_data FROM settings LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $config_data = json_decode($row['config_data'], true);
            $config_data['login_count'] = 0;
            $updated_config = json_encode($config_data);
            $conn->query("UPDATE settings SET config_data = '{$conn->real_escape_string($updated_config)}' WHERE id = 1");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'All data has been reset successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
