<?php
/**
 * CAPTCHA Refresh Handler
 * 
 * This file generates a new CAPTCHA code and returns it as plain text.
 * Used for AJAX refreshing of CAPTCHA without reloading the page.
 */
session_start();

// Generate a random CAPTCHA code
function generateCaptchaCode($length = 6) {
    // Use only uppercase letters and numbers for better readability
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $captcha = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, $max)];
    }
    
    return $captcha;
}

// Generate new CAPTCHA code
$_SESSION['captcha'] = generateCaptchaCode();

// Log the new CAPTCHA (for debugging - remove in production)
// error_log("New CAPTCHA generated: " . $_SESSION['captcha']);

// Output the new CAPTCHA code as plain text
header('Content-Type: text/plain');
echo $_SESSION['captcha'];
?>