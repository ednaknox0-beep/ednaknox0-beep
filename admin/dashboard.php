<?php
require '../includes/config.php';
require '../includes/functions.php';

if(!isset($_SESSION['license'])) header('Location: index.php');

// Total Views = semua pengunjung yang akses website
$total_views = $conn->query("SELECT COUNT(*) FROM visits")->fetch_row()[0];

// Total Logins = baca dari config_data JSON
$total_logins = 0;
$settings_result = $conn->query("SELECT config_data FROM settings LIMIT 1");
if($settings_result && $settings_result->num_rows > 0) {
    $row = $settings_result->fetch_assoc();
    $config_data = json_decode($row['config_data'], true);
    $total_logins = $config_data['login_count'] ?? 0;
}

// Total Bots = jumlah bot yang terdeteksi dari visits
$total_bots = $conn->query("SELECT COUNT(*) FROM visits WHERE is_bot=1")->fetch_row()[0];

// Total Humans = jumlah manusia dari visits (tidak termasuk bot)
$total_humans = $total_views - $total_bots;

// Recent Visits = 10 kunjungan terakhir
$recent_visits = $conn->query("SELECT * FROM visits ORDER BY visit_time DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kangen Bojo Admin Panel - Dashboard</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(0, 255, 65, 0.1);
            border: 1px solid rgba(0, 255, 65, 0.3);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #00ff41, #00d4ff);
            box-shadow: 0 0 10px rgba(0, 255, 65, 0.5);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 12px 48px rgba(0, 0, 0, 0.7),
                0 0 60px rgba(0, 255, 65, 0.2);
            border-color: #00ff41;
        }

        .stat-title {
            color: #00d4ff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .stat-value {
            color: #00ff41;
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(0, 255, 65, 0.5);
            animation: countUp 1s ease-out;
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 40px;
            opacity: 0.15;
        }

        .recent-visits {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(0, 255, 65, 0.1);
            border: 1px solid rgba(0, 255, 65, 0.3);
        }

        .section-title {
            color: #00ff41;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 10px rgba(0, 255, 65, 0.5);
            letter-spacing: 1px;
        }

        .live-indicator {
            width: 12px;
            height: 12px;
            background: #00ff41;
            border-radius: 50%;
            animation: pulse 2s infinite;
            box-shadow: 0 0 10px #00ff41;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background: rgba(0, 255, 65, 0.1);
            border-bottom: 2px solid #00ff41;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 255, 65, 0.1);
            color: #00ff41;
            font-size: 13px;
        }

        th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            color: #00d4ff;
        }

        tbody tr {
            transition: all 0.3s;
            background: rgba(0, 0, 0, 0.2);
        }

        tbody tr:hover {
            background: rgba(0, 255, 65, 0.1);
            transform: scale(1.01);
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-human {
            background: rgba(0, 255, 65, 0.2);
            color: #00ff41;
            border: 1px solid #00ff41;
        }

        .badge-bot {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .status-text {
            color: #00d4ff;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-text::before {
            content: '● ';
            color: #00ff41;
        }

        .refresh-btn {
            background: linear-gradient(135deg, #00ff41, #00d4ff);
            color: #0a0e27;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-bottom: 20px;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.3);
            font-size: 12px;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 30px rgba(0, 255, 65, 0.5);
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
            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }

            .stat-value {
                font-size: 36px;
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
                <a href="config.php">Site Configuration</a>
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

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👁️</div>
                <div class="stat-title">Total Views</div>
                <div class="stat-value" id="totalViews"><?= $total_views ?></div>
                <div class="status-text">● Live Tracking</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🔐</div>
                <div class="stat-title">Total Logins</div>
                <div class="stat-value" id="totalLogins"><?= $total_logins ?></div>
                <div class="status-text">● Real-time Updates</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">🤖</div>
                <div class="stat-title">Bot Detected</div>
                <div class="stat-value" id="botDetected"><?= $total_bots ?></div>
                <div class="status-text">● Auto Detection</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-title">Human Visitors</div>
                <div class="stat-value" id="humanVisitors"><?= $total_humans ?></div>
                <div class="status-text">● Active Now</div>
            </div>
        </div>


        <div class="recent-visits">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: 	wrap; gap: 15px;">
                <div class="section-title">
                    <div class="live-indicator"></div>
                    Recent Visits
                </div>
                <div style="display: flex; gap: 10px;">
                    <button class="refresh-btn" onclick="refreshData()">🔄 Refresh</button>
                    <button class="refresh-btn" style="background: linear-gradient(135deg, #ef4444, #dc2626);" onclick="resetData()">🗑️ Reset All</button>
                </div>
            </div>

            <table>
                <tr>
                    <th>Date</th>
                    <th>IP Address</th>
                    <th>Country</th>
                    <th>ISP</th>
                    <th>Type</th>
                </tr>
                <?php while($row = $recent_visits->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['visit_time'] ?></td>
                    <td><?= $row['ip_address'] ?></td>
                    <td><?= $row['country'] ?></td>
                    <td><?= $row['isp'] ?></td>
                    <td><?= $row['is_bot'] ? 'Bot' : 'Human' ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
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

        // Store original values from database
        const originalValues = {
            views: parseInt(document.getElementById('totalViews').textContent),
            logins: parseInt(document.getElementById('totalLogins').textContent),
            bots: parseInt(document.getElementById('botDetected').textContent),
            humans: parseInt(document.getElementById('humanVisitors').textContent)
        };

        function animateValue(id, start, end, duration) {
            const element = document.getElementById(id);
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                    current = end;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        function refreshData() {
            // Fetch real data from database via AJAX
            fetch('get_stats.php')
                .then(response => response.json())
                .then(data => {
                    animateValue('totalViews', originalValues.views, data.total_views, 800);
                    animateValue('totalLogins', originalValues.logins, data.total_logins, 800);
                    animateValue('botDetected', originalValues.bots, data.total_bots, 800);
                    animateValue('humanVisitors', originalValues.humans, data.total_humans, 800);
                    
                    // Update stored values
                    originalValues.views = data.total_views;
                    originalValues.logins = data.total_logins;
                    originalValues.bots = data.total_bots;
                    originalValues.humans = data.total_humans;
                    
                    // Reload table
                    location.reload();
                })
                .catch(err => console.error('Error refreshing data:', err));
        }

        function resetData() {
            if (confirm('⚠️ Are you sure? This will delete ALL recent activity and reset all counters!')) {
                fetch('reset_stats.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ All data has been reset!');
                        location.reload();
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error resetting data:', err);
                    alert('❌ Error resetting data');
                });
            }
        }

        // Auto refresh every 30 seconds
        setInterval(refreshData, 30000);
    </script>
</body>
</html>