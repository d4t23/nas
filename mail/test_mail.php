<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'mail/PHPMailer.php';
require 'mail/SMTP.php';
require 'mail/Exception.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'dauditelemka@gmail.com';
    $mail->Password = 'fdch womg zvhb wxsb';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom('dauditelemka@gmail.com', 'Test');
    $mail->addAddress('dauditelemka@gmail.com');

    $mail->Subject = 'SMTP Test';
    $mail->Body = 'If you see this, SMTP is working';

    $mail->send();

    echo "✅ Email sent successfully!";
} catch (Exception $e) {
    echo "❌ Error: " . $mail->ErrorInfo;
}
