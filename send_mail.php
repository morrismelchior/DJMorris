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

$sent = mail($to, $email_subject, $email_body, $headers);

$logLine = sprintf("%s | to=%s | from=%s | phone=%s | subject=%s | sent=%s\n", date('c'), $to, $email, $phone, $subject, $sent ? '1' : '0');
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
