<?php
$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;

// IMPORTANT
$mail->Username = 'dauditelemka@gmail.com';
$mail->Password = 'fdch womg zvhb wxsb';

$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

// FIX for XAMPP connection issues
$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
];

$mail->setFrom('dauditelemka@gmail.com', 'GoCloud');
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = 'Verify Your Account';
$mail->Body = "Your OTP is: <b>$otp</b>";

$mail->send();