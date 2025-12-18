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

if ($sent) {
    header('Location: contact-thanks.html');
    exit;
} else {
    echo "Er is een fout opgetreden bij het verzenden van het bericht. Probeer het later opnieuw.";
}

?>
