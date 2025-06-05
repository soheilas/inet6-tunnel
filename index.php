<?php
session_start();

// --- CONFIGURATION ---
$admin_username = 'admin'; // نام کاربری خود را اینجا وارد کنید
$admin_password_hash = password_hash('admin', PASSWORD_DEFAULT); // یک رمز عبور بسیار قوی انتخاب و هش آن را جایگزین کنید
$dataFile = '/var/www/licensing/data/clients.txt'; // مسیر فایل ذخیره اطلاعات کلاینت‌ها
$days_to_warn_before_expiry = 7; // تعداد روز قبل از انقضا برای نمایش هشدار

// --- LOGIN LOGIC ---
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (isset($_POST['login_action'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] === $admin_username && password_verify($_POST['password'], $admin_password_hash)) {
            $_SESSION['loggedin'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = "نام کاربری یا رمز عبور اشتباه است.";
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (!$is_logged_in) {
    // --- LOGIN FORM HTML ---
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007AFF; /* iOS Blue */
            --primary-hover-color: #0056b3;
            --background-color: #f0f2f5; /* Light gray, similar to iOS settings */
            --card-background-color: #ffffff;
            --text-color: #1c1c1e; /* iOS Dark Text */
            --secondary-text-color: #8e8e93; /* iOS Gray Text */
            --border-color: #d1d1d6;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --error-bg-color: #ffebee;
            --error-text-color: #c62828;
        }
        body { font-family: 'Vazirmatn', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--background-color); margin: 0; padding: 20px; box-sizing: border-box; }
        .login-container { width: 100%; max-width: 360px; }
        .login-card { background: var(--card-background-color); padding: 35px 30px; border-radius: 18px; box-shadow: 0 10px 30px var(--shadow-color); text-align: center; }
        .login-card h1 { margin-top: 0; margin-bottom: 30px; color: var(--text-color); font-size: 1.7rem; font-weight: 600; }
        .form-group { margin-bottom: 20px; text-align: right; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: var(--secondary-text-color); font-size: 0.9rem; }
        .form-control { width: 100%; padding: 14px; border: 1px solid var(--border-color); border-radius: 10px; box-sizing: border-box; font-family: 'Vazirmatn', sans-serif; font-size: 1rem; background-color: #f8f8f8; color: var(--text-color); transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .form-control:focus { outline: none; border-color: var(--primary-color); background-color: var(--card-background-color); box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.15); }
        .btn-login { background-color: var(--primary-color); color: white; padding: 14px 20px; border: none; border-radius: 10px; cursor: pointer; font-size: 1rem; font-weight: 500; width: 100%; transition: background-color 0.2s ease, transform 0.1s ease; }
        .btn-login:hover { background-color: var(--primary-hover-color); }
        .btn-login:active { transform: scale(0.98); }
        .alert-login-error { background: var(--error-bg-color); color: var(--error-text-color); padding: 12px 15px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid var(--error-text-color)Opaci; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>ورود به پنل مدیریت</h1>
            <?php if (isset($login_error)): ?>
                <div class="alert-login-error"><?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="login_action" value="1">
                <div class="form-group">
                    <label for="username">نام کاربری</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">رمز عبور</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-login">ورود</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    exit; 
}

// --- DATE CONVERSION FUNCTIONS (Unchanged) ---
function gregorian_to_jalali_parts($gy, $gm, $gd) { /* ... as before ... */ 
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334]; $jy = ($gy <= 1600) ? 0 : 979; $gy -= ($gy <= 1600) ? 621 : 1600; $gy2 = ($gm > 2) ? ($gy + 1) : $gy; $days = (365 * $gy) + ((int)($gy2 / 4)) - ((int)($gy2 / 100)) + ((int)($gy2 / 400)) - 80 + $gd + $g_d_m[$gm - 1]; $jy += 33 * ((int)($days / 12053)); $days %= 12053; $jy += 4 * ((int)($days / 1461)); $days %= 1461; $jy += (int)(($days - 1) / 365); if ($days > 365) $days = ($days - 1) % 365; $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30); $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30)); return [$jy, $jm, $jd];
}
function jalali_to_gregorian_parts($jy, $jm, $jd) { /* ... as before ... */
    $jy += 1595; $days = -355668 + (365 * $jy) + (((int)($jy / 33)) * 8) + ((int)((($jy % 33) + 3) / 4)) + $jd + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186); $gy = 400 * ((int)($days / 146097)); $days %= 146097; if ($days > 36524) { $gy += 100 * ((int)(--$days / 36524)); $days %= 36524; if ($days >= 365) $days++; } $gy += 4 * ((int)($days / 1461)); $days %= 1461; if ($days > 365) { $gy += (int)(($days - 1) / 365); $days = ($days - 1) % 365; } $gd = $days + 1; $sal_a = [0, 31, (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; $gm = 0; while ($gm < 13 && $gd > $sal_a[$gm]) { $gd -= $sal_a[$gm]; $gm++; } return [$gy, $gm, $gd];
}

// --- CRUD OPERATIONS ---
$error_message = null;
$success_message = null;

function read_clients($file_path) {
    if (!file_exists($file_path)) return []; // Return empty if file doesn't exist, to allow creation
    if (!is_readable($file_path)) {
        error_log("Permission denied: Cannot read client file at " . $file_path);
        return "خطا: امکان خواندن فایل کلاینت‌ها وجود ندارد. مجوزها را بررسی کنید.";
    }
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $clients_data = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || trim($line) === '') continue;
        $parts = explode(',', $line, 4); 
        if (count($parts) >= 3) {
            $clients_data[] = [
                'ip' => trim($parts[0]),
                'expiry' => trim($parts[1]),
                'active' => trim($parts[2]),
                'name' => isset($parts[3]) ? trim($parts[3]) : 'نامشخص',
            ];
        }
    }
    return $clients_data;
}

function write_clients($file_path, $clients_data) {
    $lines = ["# IP,تاریخ_انقضا_میلادی,فعال,نام_مشتری"]; // Header line
    foreach ($clients_data as $client) {
        $ip = $client['ip'] ?? '';
        $expiry = $client['expiry'] ?? '';
        $active = $client['active'] ?? '';
        $name = $client['name'] ?? '';
        $lines[] = "{$ip},{$expiry},{$active},{$name}";
    }

    $fp = @fopen($file_path, 'w');
    if (!$fp) {
        error_log("Failed to open file for writing: " . $file_path . " - Check permissions and path.");
        return "خطا: امکان باز کردن فایل (" . basename($file_path) . ") برای نوشتن وجود ندارد. مجوزهای فایل و پوشه والد آن را بررسی کنید.";
    }

    if (flock($fp, LOCK_EX)) {
        if (fwrite($fp, implode("\n", $lines) . "\n") === false) {
            error_log("Failed to write to file after acquiring lock: " . $file_path);
            $err_msg = "خطا: نوشتن اطلاعات در فایل (" . basename($file_path) . ") با مشکل مواجه شد.";
        } else {
             $err_msg = true; // Success
        }
        fflush($fp);
        flock($fp, LOCK_UN);
    } else {
        error_log("Failed to acquire lock for file: " . $file_path);
        $err_msg = "خطا: فایل در حال حاضر توسط فرآیند دیگری قفل شده است. لطفاً لحظاتی دیگر دوباره تلاش کنید.";
    }
    fclose($fp);
    return $err_msg;
}

$raw_clients_data = read_clients($dataFile);
if (is_string($raw_clients_data)) { // Check if read_clients returned an error string
    $error_message = $raw_clients_data;
    $clients_list_for_ops = []; // Provide an empty list if reading failed
} else {
    $clients_list_for_ops = $raw_clients_data;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // $clients_list_for_ops is already populated or is an error string handled above
    $action_taken_status = false; // Will hold true on success, or error string on failure

    if ($_POST['action'] == 'add' && isset($_POST['ip'], $_POST['client_name'], $_POST['shamsi_date'])) {
        // ... (add logic as before, but use $clients_list_for_ops)
        $ip = trim($_POST['ip']);
        $client_name = trim($_POST['client_name']);
        $shamsi_date = trim($_POST['shamsi_date']);
        
        $date_parts = explode('/', $shamsi_date);
        if (count($date_parts) == 3 && ctype_digit($date_parts[0]) && ctype_digit($date_parts[1]) && ctype_digit($date_parts[2])) {
            list($gy, $gm, $gd) = jalali_to_gregorian_parts((int)$date_parts[0], (int)$date_parts[1], (int)$date_parts[2]);
            $expiry = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
            
            $ip_exists = false;
            foreach ($clients_list_for_ops as $c) { if ($c['ip'] === $ip) { $ip_exists = true; break; } }

            if (!$ip_exists) {
                $temp_clients_list = $clients_list_for_ops; // Work on a copy
                $temp_clients_list[] = ['ip' => $ip, 'expiry' => $expiry, 'active' => '1', 'name' => $client_name];
                $write_result = write_clients($dataFile, $temp_clients_list);
                if($write_result === true) {
                    $success_message = "کلاینت «${client_name}» با IP ($ip) با موفقیت اضافه شد."; $action_taken_status = true;
                } else { $error_message = $write_result; }
            } else { $error_message = "کلاینت با IP ($ip) قبلاً ثبت شده است."; }
        } else { $error_message = "فرمت تاریخ شمسی اشتباه است (مثال: ۱۴۰۳/۰۱/۲۰)."; }

    } elseif ($_POST['action'] == 'edit' && isset($_POST['old_ip'], $_POST['ip'], $_POST['client_name'], $_POST['shamsi_date'])) {
        // ... (edit logic as before, use $clients_list_for_ops)
        $old_ip = trim($_POST['old_ip']); $new_ip = trim($_POST['ip']); $client_name = trim($_POST['client_name']); $shamsi_date = trim($_POST['shamsi_date']);
        $date_parts = explode('/', $shamsi_date);
        if (count($date_parts) == 3 && ctype_digit($date_parts[0]) && ctype_digit($date_parts[1]) && ctype_digit($date_parts[2])) {
            list($gy, $gm, $gd) = jalali_to_gregorian_parts((int)$date_parts[0], (int)$date_parts[1], (int)$date_parts[2]);
            $expiry = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
            $found = false; $temp_clients_list = $clients_list_for_ops;
            foreach ($temp_clients_list as &$c) { 
                if ($c['ip'] === $old_ip) { $c['ip'] = $new_ip; $c['name'] = $client_name; $c['expiry'] = $expiry; $found = true; break; }
            } unset($c);
            if ($found) {
                $write_result = write_clients($dataFile, $temp_clients_list);
                if($write_result === true) { $success_message = "کلاینت «${client_name}» با IP ($new_ip) ویرایش شد."; $action_taken_status = true; } else { $error_message = $write_result; }
            } else { $error_message = "کلاینت اصلی برای ویرایش یافت نشد."; }
        } else { $error_message = "فرمت تاریخ شمسی برای ویرایش اشتباه است."; }

    } elseif ($_POST['action'] == 'delete' && isset($_POST['delete_ip'])) {
        // ... (delete logic as before, use $clients_list_for_ops)
        $deleteIP = trim($_POST['delete_ip']); $clients_filtered = []; $deleted = false; $deleted_name = '';
        foreach ($clients_list_for_ops as $c) { if ($c['ip'] === $deleteIP) { $deleted = true; $deleted_name = $c['name']; continue; } $clients_filtered[] = $c; }
        if ($deleted) {
            $write_result = write_clients($dataFile, $clients_filtered);
            if($write_result === true) { $success_message = "کلاینت «${deleted_name}» با IP ($deleteIP) حذف شد."; $action_taken_status = true; } else { $error_message = $write_result; }
        } else { $error_message = "کلاینت برای حذف یافت نشد."; }
        
    } elseif ($_POST['action'] == 'toggle' && isset($_POST['toggle_ip'])) {
        // ... (toggle logic as before, use $clients_list_for_ops)
        $toggleIP = trim($_POST['toggle_ip']); $found = false; $toggled_name = ''; $temp_clients_list = $clients_list_for_ops;
        foreach ($temp_clients_list as &$c) { 
            if ($c['ip'] === $toggleIP) { $c['active'] = $c['active'] === '1' ? '0' : '1'; $found = true; $toggled_name = $c['name']; break; }
        } unset($c);
        if ($found) {
            $write_result = write_clients($dataFile, $temp_clients_list);
            if($write_result === true) { $success_message = "وضعیت کلاینت «${toggled_name}» تغییر کرد."; $action_taken_status = true; } else { $error_message = $write_result; }
        } else { $error_message = "کلاینت برای تغییر وضعیت یافت نشد."; }
    }
    
    if ($action_taken_status === true && $success_message) {
        $_SESSION['flash_success'] = $success_message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($error_message) { // If any error occurred during POST actions
        $_SESSION['flash_error'] = $error_message;
        // Potentially redirect even on error to prevent re-submission, or display error directly
        // For now, we let it fall through to display error on current page if action_taken_status is not true.
        // If $action_taken_status is an error string itself, it means write failed.
         if ($action_taken_status !== true && is_string($action_taken_status)){
             $_SESSION['flash_error'] = $action_taken_status; // This captures write_client error specifically
         }
         header("Location: " . $_SERVER['PHP_SELF']); // Always redirect after POST
         exit;
    }
}

if (isset($_SESSION['flash_success'])) {
    $success_message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error_message = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// --- PREPARE DATA FOR DISPLAY (re-read data after potential modifications) ---
$clients_for_display = [];
$final_raw_clients = read_clients($dataFile); // Re-read for display consistency

if (is_string($final_raw_clients)) {
    if(empty($error_message)) $error_message = $final_raw_clients; // Show read error if no other error is set
    $final_raw_clients = []; // Ensure it's an array for the loop
}

$total_clients = count($final_raw_clients);
$active_clients_count = 0;
$inactive_clients_count = 0;
$expired_clients_count = 0;

foreach ($final_raw_clients as $rc) {
    $expiry_timestamp = strtotime($rc['expiry']);
    $now_timestamp = time();
    $is_expired = $expiry_timestamp < $now_timestamp;
    $days_remaining = floor(($expiry_timestamp - $now_timestamp) / (60 * 60 * 24));

    if ($is_expired) {
        $expired_clients_count++;
    } elseif ($rc['active'] == '1') {
        $active_clients_count++;
    } else {
        $inactive_clients_count++;
    }
    
    list($jy, $jm, $jd) = gregorian_to_jalali_parts((int)date('Y', $expiry_timestamp), (int)date('m', $expiry_timestamp), (int)date('d', $expiry_timestamp));
    $shamsi_expiry = sprintf("%04d/%02d/%02d", $jy, $jm, $jd);

    $clients_for_display[] = [
        'ip' => $rc['ip'], 'expiry_gregorian' => $rc['expiry'], 'expiry_shamsi' => $shamsi_expiry,
        'active' => $rc['active'], 'name' => $rc['name'], 'is_expired' => $is_expired,
        'days_remaining' => $days_remaining
    ];
}

$edit_client_data = null;
if (isset($_GET['edit_ip'])) {
    $edit_ip_target = trim($_GET['edit_ip']);
    foreach ($clients_for_display as $client_item) {
        if ($client_item['ip'] == $edit_ip_target) { $edit_client_data = $client_item; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت لایسنس</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007AFF; --primary-hover-color: #005bb5; --primary-active-color: #004EA0;
            --success-color: #34C759; --warning-color: #FF9500; --danger-color: #FF3B30;
            --background-color: #f0f2f5; --card-background-color: #ffffff;
            --text-color: #1d1d1f; --secondary-text-color: #6e6e73; --tertiary-text-color: #8e8e93;
            --border-color: #e0e0e5; --input-bg-color: #f8f9fa; --shadow-color: rgba(0, 0, 0, 0.08);
            --font-family: 'Vazirmatn', sans-serif;
            --border-radius-sm: 6px; --border-radius-md: 10px; --border-radius-lg: 16px;
            --transition-duration: 0.25s;
            --cubic-bezier: cubic-bezier(0.4, 0, 0.2, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family); background-color: var(--background-color); min-height: 100vh; padding: 20px; color: var(--text-color); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .main-header { display: flex; justify-content: space-between; align-items: center; background-color: var(--card-background-color); padding: 15px 25px; border-radius: var(--border-radius-lg); margin-bottom: 25px; box-shadow: 0 4px 12px var(--shadow-color); }
        .main-header h1 { color: var(--text-color); font-weight: 700; font-size: 1.6rem; display: flex; align-items: center; gap: 12px; }
        .main-header h1 svg { width: 30px; height: 30px; fill: var(--primary-color); }
        .btn-logout { background-color: transparent; color: var(--danger-color); padding: 8px 12px; border-radius: var(--border-radius-md); text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; border: 1px solid transparent; transition: background-color var(--transition-duration) var(--cubic-bezier), color var(--transition-duration) var(--cubic-bezier); }
        .btn-logout:hover { background-color: #ffeeed; color: #c41b14; }
        .btn-logout svg { width: 18px; height: 18px; }

        .card { background-color: var(--card-background-color); border-radius: var(--border-radius-lg); padding: 25px; margin-bottom: 25px; box-shadow: 0 4px 12px var(--shadow-color); }
        .card-header { margin-bottom: 20px; color: var(--text-color); font-size: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .card-header svg { width: 24px; height: 24px; fill: var(--primary-color); }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 0.9rem; color: var(--secondary-text-color); }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); font-size: 1rem; font-family: var(--font-family); transition: border-color var(--transition-duration) var(--cubic-bezier), box-shadow var(--transition-duration) var(--cubic-bezier); background-color: var(--input-bg-color); }
        .form-control:focus { outline: none; border-color: var(--primary-color); background-color: var(--card-background-color); box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1); }
        
        .btn { padding: 10px 22px; border: none; border-radius: var(--border-radius-md); font-size: 0.95rem; font-weight: 500; cursor: pointer; transition: background-color var(--transition-duration) var(--cubic-bezier), transform 0.1s ease; font-family: var(--font-family); display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; line-height: 1.5; }
        .btn svg { width: 18px; height: 18px; }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-primary:hover { background-color: var(--primary-hover-color); }
        .btn-primary:active { background-color: var(--primary-active-color); }
        .btn-cancel { background-color: #e0e0e0; color: var(--text-color); }
        .btn-cancel:hover { background-color: #d1d1d1; }

        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table th, .table td { padding: 14px 16px; text-align: right; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; vertical-align: middle; }
        .table th { background-color: #f8f9fa; color: var(--secondary-text-color); font-weight: 600; position: sticky; top: 0; z-index: 1; }
        .table td { background-color: var(--card-background-color); color: var(--text-color); }
        .table tbody tr:hover td { background-color: #f5f5f7; }
        .table tr:first-child th:first-child { border-top-left-radius: var(--border-radius-md); }
        .table tr:first-child th:last-child { border-top-right-radius: var(--border-radius-md); }
        .table tr:last-child td:first-child { border-bottom-left-radius: var(--border-radius-md); }
        .table tr:last-child td:last-child { border-bottom-right-radius: var(--border-radius-md); }
        .table tr:last-child td { border-bottom: none; }
        .table td code { background-color: var(--input-bg-color); padding: 4px 8px; border-radius: var(--border-radius-sm); font-size: 0.85rem; color: var(--secondary-text-color); border: 1px solid var(--border-color);}
        .table .actions-cell { width: 1%; white-space: nowrap; } /* Prevent wrapping for action buttons */
        .table .actions { display: flex; gap: 8px; }
        .table .actions .btn { padding: 7px 10px; font-size: 0.85rem; }
        .table .actions .btn svg { width: 16px; height: 16px; }
        
        .btn-edit { background-color: var(--primary-color); color:white; opacity:0.85; } .btn-edit:hover{opacity:1;}
        .btn-toggle-active { background-color: var(--warning-color); color:white; opacity:0.85;} .btn-toggle-active:hover{opacity:1;}
        .btn-toggle-inactive { background-color: var(--success-color); color:white; opacity:0.85;} .btn-toggle-inactive:hover{opacity:1;}
        .btn-delete { background-color: var(--danger-color); color:white; opacity:0.85;} .btn-delete:hover{opacity:1;}

        .status-badge { padding: 5px 12px; border-radius: var(--border-radius-lg); font-size: 0.8rem; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
        .status-badge svg { width: 14px; height: 14px; }
        .status-active { background-color: #e6f6e9; color: #28a745; }
        .status-inactive { background-color: #fff8e1; color: #ff9800; }
        .status-expired { background-color: #ffebee; color: #e53935; }
        .expiry-warning td { background-color: #fffde7 !important; } 
        .expiry-date-cell small { font-weight: 500; }
        .expiry-date-cell.critical small { color: var(--danger-color) !important; }

        .alert-message { padding: 12px 18px; border-radius: var(--border-radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 0.95rem; animation: fadeIn 0.5s var(--cubic-bezier); }
        .alert-message svg { width: 20px; height: 20px; }
        .alert-success { background-color: #e6f6e9; color: #1d643b; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background-color: var(--card-background-color); padding: 20px; border-radius: var(--border-radius-lg); text-align: center; box-shadow: 0 4px 12px var(--shadow-color); transition: transform 0.2s var(--cubic-bezier); }
        /* .stat-card:hover { transform: translateY(-3px); } */
        .stat-number { font-size: 2.2rem; font-weight: 700; margin-bottom: 5px; }
        .stat-label { color: var(--secondary-text-color); font-size: 0.9rem; }

        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); align-items: center; justify-content: center; opacity: 0; transition: opacity var(--transition-duration) var(--cubic-bezier); }
        .modal.show { display: flex; opacity: 1; }
        .modal-content { background-color: var(--card-background-color); padding: 30px; border-radius: var(--border-radius-lg); text-align: center; width: 90%; max-width: 420px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transform: scale(0.95); transition: transform var(--transition-duration) var(--cubic-bezier); }
        .modal.show .modal-content { transform: scale(1); }
        .modal-content p { margin-bottom: 25px; font-size: 1.1rem; color: var(--text-color); line-height: 1.6; }
        .modal-buttons { display: flex; justify-content: center; gap: 15px; }
        .modal-buttons .btn { min-width: 120px; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .form-card, .list-card, .stat-card { animation: fadeIn 0.5s var(--cubic-bezier) 0.1s backwards; }

        @media (max-width: 768px) {
            body { padding: 15px; }
            .main-header { flex-direction: column; gap: 15px; text-align: center; padding: 20px; }
            .main-header h1 { font-size: 1.4rem; }
            .form-grid { grid-template-columns: 1fr; }
            .table th, .table td { padding: 12px 10px; font-size: 0.85rem;}
            .table .actions-cell { width: auto; }
            .table .actions { flex-wrap: wrap; justify-content: flex-start; }
        }
         /* SVG Icons */
        .icon { display: inline-block; width: 1em; height: 1em; stroke-width: 0; stroke: currentColor; fill: currentColor; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <h1>
                <svg viewBox="0 0 24 24" class="icon"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                پنل مدیریت لایسنس
            </h1>
            <a href="?logout=1" class="btn-logout" title="خروج از حساب کاربری">
                <svg viewBox="0 0 24 24" class="icon"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                خروج
            </a>
        </header>

        <?php if ($success_message): ?>
        <div class="alert-message alert-success">
            <svg viewBox="0 0 24 24" class="icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            <?= htmlspecialchars($success_message) ?>
        </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <div class="alert-message alert-error">
             <svg viewBox="0 0 24 24" class="icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number" style="color: var(--primary-color);"><?= $total_clients ?></div><div class="stat-label">کل کلاینت‌ها</div></div>
            <div class="stat-card"><div class="stat-number" style="color: var(--success-color);"><?= $active_clients_count ?></div><div class="stat-label">کلاینت‌های فعال</div></div>
            <div class="stat-card"><div class="stat-number" style="color: var(--warning-color);"><?= $inactive_clients_count ?></div><div class="stat-label">کلاینت‌های غیرفعال</div></div>
            <div class="stat-card"><div class="stat-number" style="color: var(--danger-color);"><?= $expired_clients_count ?></div><div class="stat-label">کلاینت‌های منقضی</div></div>
        </div>

        <div class="card form-card">
            <h2 class="card-header">
                <svg viewBox="0 0 24 24" class="icon"><path d="<?= $edit_client_data ? 'M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z' : 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z' ?>"/></svg>
                <?= $edit_client_data ? 'ویرایش اطلاعات کلاینت' : 'افزودن کلاینت جدید' ?>
            </h2>
            <form method="post">
                <input type="hidden" name="action" value="<?= $edit_client_data ? 'edit' : 'add' ?>">
                <?php if ($edit_client_data): ?>
                <input type="hidden" name="old_ip" value="<?= htmlspecialchars($edit_client_data['ip']) ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="ip">آدرس IP</label>
                        <input type="text" id="ip" name="ip" class="form-control" required 
                               value="<?= htmlspecialchars($edit_client_data['ip'] ?? '') ?>" placeholder="مثال: 192.168.1.100">
                    </div>
                    <div class="form-group">
                        <label for="client_name">نام مشتری</label>
                        <input type="text" id="client_name" name="client_name" class="form-control" required
                               value="<?= htmlspecialchars($edit_client_data['name'] ?? '') ?>" placeholder="مثال: شرکت فناوری نوین">
                    </div>
                    <div class="form-group">
                        <label for="shamsi_date">تاریخ انقضا (فرمت: سال/ماه/روز)</label>
                        <input type="text" id="shamsi_date" name="shamsi_date" class="form-control" required
                               value="<?= htmlspecialchars($edit_client_data['expiry_shamsi'] ?? '') ?>" placeholder="مثال: ۱۴۰۳/۱۲/۲۹">
                    </div>
                </div>
                
                <div style="margin-top: 25px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" class="icon"><path d="<?= $edit_client_data ? 'M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z' : 'M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z' ?>"/></svg>
                        <?= $edit_client_data ? 'ذخیره تغییرات' : 'افزودن کلاینت' ?>
                    </button>
                    <?php if ($edit_client_data): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-cancel">
                         <svg viewBox="0 0 24 24" class="icon"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                        انصراف
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card list-card">
            <h2 class="card-header">
                <svg viewBox="0 0 24 24" class="icon"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zm0-8h14V7H7v2z"/></svg>
                لیست کلاینت‌ها
            </h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>نام مشتری</th>
                            <th>آدرس IP</th>
                            <th>تاریخ انقضا</th>
                            <th>وضعیت</th>
                            <th class="actions-cell">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients_for_display)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--tertiary-text-color);">تاکنون هیچ کلاینتی ثبت نشده است.</td></tr>
                        <?php else: ?>
                        <?php foreach ($clients_for_display as $client): 
                            $row_class = '';
                            $is_critical_expiry = false;
                            if (!$client['is_expired'] && $client['active'] == '1' && $client['days_remaining'] <= $days_to_warn_before_expiry && $client['days_remaining'] >= 0) {
                                $row_class = 'expiry-warning';
                                if ($client['days_remaining'] <= 3) $is_critical_expiry = true; // Example: more critical if <=3 days
                            }
                        ?>
                            <tr class="<?= $row_class ?>">
                                <td><strong><?= htmlspecialchars($client['name']) ?></strong></td>
                                <td><code><?= htmlspecialchars($client['ip']) ?></code></td>
                                <td class="expiry-date-cell <?= $is_critical_expiry ? 'critical' : '' ?>">
                                    <?= htmlspecialchars($client['expiry_shamsi']) ?>
                                    <?php if (!$client['is_expired'] && $client['active'] == '1'): ?>
                                        <br><small>
                                            (<?= $client['days_remaining'] >= 0 ? htmlspecialchars(round($client['days_remaining'])) . ' روز مانده'  : 'تاریخ گذشته'?>)
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($client['is_expired']): ?>
                                        <span class="status-badge status-expired"><svg viewBox="0 0 24 24" class="icon"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>منقضی شده</span>
                                    <?php elseif ($client['active'] == '1'): ?>
                                        <span class="status-badge status-active"><svg viewBox="0 0 24 24" class="icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>فعال</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive"><svg viewBox="0 0 24 24" class="icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zM10 17l6-5-6-5v10z"/></svg>غیرفعال</span> <!-- Placeholder, find pause icon -->
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <div class="actions">
                                        <a href="?edit_ip=<?= urlencode($client['ip']) ?>" class="btn btn-edit" title="ویرایش کلاینت">
                                            <svg viewBox="0 0 24 24" class="icon"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="toggle_ip" value="<?= htmlspecialchars($client['ip']) ?>">
                                            <button type="submit" class="btn <?= $client['active'] == '1' ? 'btn-toggle-active' : 'btn-toggle-inactive' ?>" title="<?= $client['active'] == '1' ? 'غیرفعال کردن' : 'فعال کردن' ?>">
                                                <svg viewBox="0 0 24 24" class="icon"><path d="<?= $client['active'] == '1' ? 'M6 19h4V5H6v14zm8-14v14h4V5h-4z' : 'M8 5v14l11-7z' ?>"/></svg>
                                            </button>
                                        </form>
                                        <form class="delete-form" method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="delete_ip" value="<?= htmlspecialchars($client['ip']) ?>">
                                            <button type="button" class="btn btn-delete delete-btn-trigger" data-name="<?= htmlspecialchars($client['name']) ?>" data-ip="<?= htmlspecialchars($client['ip']) ?>" title="حذف کلاینت">
                                                <svg viewBox="0 0 24 24" class="icon"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p id="deleteModalText">آیا از حذف این کلاینت اطمینان دارید؟</p>
            <div class="modal-buttons">
                <button id="confirmDeleteBtn" class="btn btn-delete">بله، حذف کن</button>
                <button id="cancelDeleteBtn" class="btn btn-cancel">انصراف</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('deleteModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const modalText = document.getElementById('deleteModalText');
        let formToSubmit = null;

        document.querySelectorAll('.delete-btn-trigger').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                formToSubmit = this.closest('form.delete-form');
                const clientName = this.dataset.name;
                const clientIp = this.dataset.ip;
                modalText.textContent = `آیا از حذف کلاینت «${clientName}» با IP (${clientIp}) اطمینان دارید؟`;
                modal.classList.add('show');
            });
        });

        function closeModal() {
            modal.classList.remove('show');
            formToSubmit = null;
        }

        confirmDeleteBtn.addEventListener('click', function() {
            if (formToSubmit) {
                formToSubmit.submit();
            }
            closeModal();
        });

        cancelDeleteBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(event) {
            if (event.target === modal) { // Clicked on backdrop
                closeModal();
            }
        });
        
        // Auto-hide success/error messages after a delay
        const alertMessages = document.querySelectorAll('.alert-message');
        alertMessages.forEach(alertMsg => {
            setTimeout(() => {
                alertMsg.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alertMsg.style.opacity = '0';
                alertMsg.style.transform = 'translateY(-10px)';
                setTimeout(() => alertMsg.remove(), 500);
            }, 5000); // 5 seconds
        });
    });
    </script>
</body>
</html>
