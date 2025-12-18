<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

function clean($value) {
    return trim(strip_tags($value));
}

$fullname = isset($_POST['fullname']) ? clean($_POST['fullname']) : '';
$email = isset($_POST['email']) ? clean($_POST['email']) : '';
$phone = isset($_POST['phone']) ? clean($_POST['phone']) : '';
$subject = isset($_POST['subject']) ? clean($_POST['subject']) : '(geen onderwerp)';
$message = isset($_POST['message']) ? clean($_POST['message']) : '';

if (empty($fullname) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.html');
    exit;
}

$to = 'morrismelchior@outlook.com';
$email_subject = "Nieuw contactformulier bericht: " . $subject;
$email_body = "Naam: $fullname\n";
$email_body .= "E-mail: $email\n";
$email_body .= "Telefoon: $phone\n\n";
$email_body .= "Bericht:\n$message\n";

$headers = 'From: Website Contact <no-reply@yourdomain.com>' . "\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n";

// Try to use PHPMailer via Composer if available, otherwise fallback to mail()
$sent = false;
try {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
        if (file_exists(__DIR__ . '/smtp_config.php')) {
            $SMTP_OPTIONS = require __DIR__ . '/smtp_config.php';
        } else {
            $SMTP_OPTIONS = [];
        }

        // Allow overriding sensitive credentials from environment variables
        $envPassword = getenv('SMTP_PASSWORD');
        if ($envPassword !== false && $envPassword !== '') {
            $SMTP_OPTIONS['password'] = $envPassword;
        }
        $envUser = getenv('SMTP_USERNAME');
        if ($envUser !== false && $envUser !== '') {
            $SMTP_OPTIONS['username'] = $envUser;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        if (!empty($SMTP_OPTIONS['host'])) {
            $mail->isSMTP();
            $mail->Host = $SMTP_OPTIONS['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $SMTP_OPTIONS['username'];
            $mail->Password = $SMTP_OPTIONS['password'];
            $mail->SMTPSecure = $SMTP_OPTIONS['encryption'] ?? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $SMTP_OPTIONS['port'] ?? 587;
            // Enable debugging output to log for diagnosis
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                $logFile = __DIR__ . '/logs/mail.log';
                $time = date('c');
                file_put_contents($logFile, "[SMTP DEBUG] $time $str\n", FILE_APPEND | LOCK_EX);
            };
        }

        $fromAddr = $SMTP_OPTIONS['from'] ?? 'no-reply@yourdomain.com';
        $fromName = $SMTP_OPTIONS['from_name'] ?? 'Website Contact';

        $mail->setFrom($fromAddr, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($email, $fullname);
        $mail->Subject = $email_subject;
        $mail->Body = $email_body;
        $mail->AltBody = strip_tags($email_body);
        $mail->send();
        $sent = true;
    } else {
        // fallback to PHP mail()
        $sent = mail($to, $email_subject, $email_body, $headers);
    }
} catch (Exception $e) {
    $sent = false;
    @file_put_contents(__DIR__ . '/logs/mail.log', date('c') . " PHPMailer exception: " . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
}

$logLine = sprintf("%s | method=%s | to=%s | from=%s | phone=%s | subject=%s | sent=%s\n", date('c'), (file_exists(__DIR__ . '/vendor/autoload.php') ? 'phpmailer' : 'mail'), $to, $email, $phone, $subject, $sent ? '1' : '0');
@file_put_contents(__DIR__ . '/logs/mail.log', $logLine, FILE_APPEND | LOCK_EX);

// Redirect with status so the thank-you page can show result
if ($sent) {
    header('Location: contact-thanks.html?sent=1');
    exit;
} else {
    header('Location: contact-thanks.html?sent=0');
    exit;
}

?>
