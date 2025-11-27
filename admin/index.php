<?php
require '../includes/config.php';
require '../includes/functions.php';

if(isset($_POST['license'])) {
    if(checkLicense($_POST['license'])) {
        $_SESSION['license'] = $_POST['license'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid license key";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kangen Bojo ID - Login</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
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
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }

        /* Glowing Orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 15s infinite ease-in-out;
            z-index: 0;
        }

        .orb1 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #00ff41, #00d4ff);
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }

        .orb2 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            bottom: -150px;
            right: -150px;
            animation-delay: 2s;
        }

        .orb3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #f093fb, #f5576c);
            top: 50%;
            left: 50%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(50px, -50px) scale(1.1);
            }
            50% {
                transform: translate(-30px, 30px) scale(0.9);
            }
            75% {
                transform: translate(30px, 50px) scale(1.05);
            }
        }

        /* Cyber Lines */
        .cyber-line {
            position: fixed;
            height: 2px;
            background: linear-gradient(90deg, transparent, #00ff41, transparent);
            opacity: 0.3;
            animation: moveLine 8s linear infinite;
            z-index: 0;
        }

        .line1 {
            width: 200px;
            top: 20%;
            left: -200px;
        }

        .line2 {
            width: 300px;
            top: 60%;
            right: -300px;
            animation-delay: 2s;
            animation-direction: reverse;
        }

        .line3 {
            width: 250px;
            top: 80%;
            left: -250px;
            animation-delay: 4s;
        }

        @keyframes moveLine {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(calc(100vw + 300px));
            }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 0 80px rgba(0, 255, 65, 0.1),
                inset 0 0 20px rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(0, 255, 65, 0.2);
            width: 90%;
            max-width: 420px;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #00ff41;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 
                0 0 10px rgba(0, 255, 65, 0.5),
                0 0 20px rgba(0, 255, 65, 0.3),
                0 0 30px rgba(0, 255, 65, 0.2);
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .logo p {
            color: #00d4ff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.8;
        }

        .terminal-text {
            color: #00ff41;
            font-size: 11px;
            margin-bottom: 25px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 5px;
            border-left: 3px solid #00ff41;
            font-family: 'Courier New', monospace;
        }

        .terminal-text::before {
            content: '> ';
            color: #00d4ff;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #00ff41;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper::before {
            content: '🔐';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            z-index: 1;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(0, 255, 65, 0.3);
            border-radius: 10px;
            color: #00ff41;
            font-size: 14px;
            font-family: 'Courier New', monospace;
            transition: all 0.3s;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #00ff41;
            box-shadow: 
                0 0 20px rgba(0, 255, 65, 0.3),
                inset 0 2px 10px rgba(0, 0, 0, 0.3);
            background: rgba(0, 0, 0, 0.5);
        }

        input::placeholder {
            color: rgba(0, 255, 65, 0.4);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #00ff41, #00d4ff);
            border: none;
            border-radius: 10px;
            color: #0a0e27;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0, 255, 65, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 255, 65, 0.5);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: rgba(0, 255, 65, 0.6);
            font-size: 11px;
        }

        .footer-text a {
            color: #00d4ff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-text a:hover {
            color: #00ff41;
            text-shadow: 0 0 10px rgba(0, 255, 65, 0.5);
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
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            color: #00ff41;
            margin-top: 15px;
            font-size: 12px;
        }

        .loading.active {
            display: block;
        }

        .loading::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0%, 20% {
                content: '.';
            }
            40% {
                content: '..';
            }
            60% {
                content: '...';
            }
            80%, 100% {
                content: '';
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            .logo h1 {
                font-size: 26px;
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
    <div class="orb orb3"></div>

    <!-- Cyber Lines -->
    <div class="cyber-line line1"></div>
    <div class="cyber-line line2"></div>
    <div class="cyber-line line3"></div>

    <!-- Particles -->
    <script>
        for(let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 10 + 's';
            particle.style.animationDuration = (Math.random() * 5 + 8) + 's';
            document.body.appendChild(particle);
        }
    </script>

    <!-- Login Container -->
    <div class="login-container">
        <div class="logo">
            <h1>⚡ KANGEN BOJO</h1>
            <p>ADMIN PANEL v2.0</p>
        </div>

        <div class="terminal-text">
            System Access Control // Authorized Personnel Only
        </div>

        <form method="post">
            <div class="form-group">
                <label for="licenseKey">License Key</label>
                <div class="input-wrapper">
                    <input type="text" id="licenseKey" name="license" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <span>ACCESS SYSTEM</span>
            </button>

            <div class="loading" id="loading">Authenticating</div>
        </form>

        <div class="footer-text">
            Protected by <a href="#">Kangen Bojo Security</a> © 2025
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

        // Resize canvas
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loading = document.getElementById('loading');
            const btn = document.querySelector('.login-btn');
            const licenseKey = document.getElementById('licenseKey').value;

            loading.classList.add('active');
            btn.disabled = true;
            btn.style.opacity = '0.6';

            setTimeout(() => {
                loading.classList.remove('active');
                btn.disabled = false;
                btn.style.opacity = '1';
                
                // Simulasi login berhasil
                alert('Access Granted! License: ' + licenseKey);
                // window.location.href = 'dashboard.html';
            }, 2000);
        });

        // Auto-format license key
        document.getElementById('licenseKey').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Z0-9]/gi, '').toUpperCase();
            let formatted = value.match(/.{1,4}/g)?.join('-') || value;
            e.target.value = formatted;
        });
    </script>
</body>
</html>