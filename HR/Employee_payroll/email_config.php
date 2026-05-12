<?php
return [
    // Use 'smtp' for authenticated SMTP, or 'mail' to use PHP mail().
    'transport' => 'smtp',

    // Sender details that appear in the email.
    'from_email' => 'garcia.justinsimon.09172004@gmail.com',
    'from_name' => 'Chengshi Construction Corp',

    // SMTP server settings.
    // Gmail example: smtp.gmail.com + SSL 465 (or TLS 587 with smtp_encryption => 'tls').
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 465,
    'smtp_encryption' => 'ssl',

    // SMTP authentication credentials.
    // For Gmail, use an App Password (not your regular account password).
    'smtp_username' => 'garcia.justinsimon.09172004@gmail.com',
    'smtp_password' => 'budmgiiekydvhhin'
];
