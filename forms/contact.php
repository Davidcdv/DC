<?php
// Contact form handler using PHPMailer with SMTP for Render deployment

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader (installed via composer.json)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
  http_response_code(500);
  echo 'Composer autoload not found. Please add composer.json and deploy.';
  exit;
}
require $autoload;

// Basic method and field validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Invalid request method';
  exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Contact Form');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
  http_response_code(422);
  echo 'Missing required fields';
  exit;
}

$toEmail = getenv('CONTACT_TO_EMAIL') ?: 'you@example.com';

$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->Host       = getenv('SMTP_HOST');
  $mail->SMTPAuth   = true;
  $mail->Username   = getenv('SMTP_USERNAME');
  $mail->Password   = getenv('SMTP_PASSWORD');
  $mail->Port       = getenv('SMTP_PORT') ?: 587;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

  $fromAddress = getenv('SMTP_FROM') ?: $mail->Username;
  $mail->setFrom($fromAddress, $name);
  $mail->addReplyTo($email, $name);
  $mail->addAddress($toEmail);

  $mail->Subject = $subject;
  $mail->Body    = "From: {$name} <{$email}>\n\n{$message}";
  $mail->AltBody = $mail->Body;

  $mail->send();
  // The frontend validator expects the exact string 'OK' on success
  echo 'OK';
} catch (Exception $e) {
  http_response_code(500);
  echo 'Mailer Error';
}
?>
