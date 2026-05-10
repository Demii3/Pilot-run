<?php
/**
 * Mail Configuration
 * 
 * Configure your email service here
 * Supported: smtp, gmail, sendgrid, mailgun
 */

return [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    
    // SMTP Configuration (for generic SMTP servers)
    'smtp' => [
        'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'), // 'tls' or 'ssl'
    ],
    
    // Gmail Configuration
    'gmail' => [
        'email' => env('GMAIL_EMAIL', 'your-email@gmail.com'),
        'app_password' => env('GMAIL_APP_PASSWORD', ''), // Use App Password, not regular password
    ],
    
    // From Address
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@chengshi-construction.com'),
        'name' => env('MAIL_FROM_NAME', 'Chengshi Construction Corp'),
    ],
];

/**
 * Helper function to get env variables
 * Falls back to .env file if it exists
 */
function env($key, $default = null) {
    // Check PHP environment
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    // Check $_SERVER
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    
    // Try to load from .env file
    static $envVars = null;
    if ($envVars === null) {
        $envVars = [];
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                list($k, $v) = explode('=', $line, 2);
                $envVars[trim($k)] = trim($v, '\'"');
            }
        }
    }
    
    return isset($envVars[$key]) ? $envVars[$key] : $default;
}
?>
