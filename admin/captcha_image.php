<?php
/**
 * CAPTCHA Image Generator
 * 
 * This file generates a CAPTCHA image based on the code stored in the session.
 * Can be used as an alternative to the text-based CAPTCHA.
 */
session_start();

// Check if CAPTCHA is set in session
if (!isset($_SESSION['captcha'])) {
    // Generate a random captcha code (using uppercase and numbers only)
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $captcha = '';
    $length = 6;
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, $max)];
    }
    
    $_SESSION['captcha'] = $captcha;
    
    // Log the new CAPTCHA (for debugging - remove in production)
    // error_log("New CAPTCHA generated for image: " . $_SESSION['captcha']);
}

// Set the content type header for image
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Create image with dimensions
$width = 200;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// Define colors
$bg_color = imagecolorallocate($image, 241, 243, 245);
$text_color = imagecolorallocate($image, 33, 37, 41);
$noise_color = imagecolorallocate($image, 206, 212, 218);
$line_color = imagecolorallocate($image, 173, 181, 189);
$accent_color = imagecolorallocate($image, 13, 110, 253);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add border
imagerectangle($image, 0, 0, $width-1, $height-1, $line_color);

// Add random dots
for ($i = 0; $i < 150; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $noise_color);
}

// Add random lines
for ($i = 0; $i < 5; $i++) {
    $line_x1 = rand(0, $width/4);
    $line_y1 = rand(0, $height);
    $line_x2 = rand(3*$width/4, $width);
    $line_y2 = rand(0, $height);
    imageline($image, $line_x1, $line_y1, $line_x2, $line_y2, $line_color);
}

// Add a blue accent line for better visibility
imageline($image, 0, rand(0, $height), $width, rand(0, $height), $accent_color);

// Get CAPTCHA text
$captcha = $_SESSION['captcha'];
$text_length = strlen($captcha);

// Add text with character randomization
$font_size = 5; // Using built-in fonts
$char_width = imagefontwidth($font_size);
$char_height = imagefontheight($font_size);
$total_width = $char_width * $text_length;
$start_x = ($width - $total_width) / 2;
$base_y = ($height - $char_height) / 2 + $char_height;

for ($i = 0; $i < $text_length; $i++) {
    $angle = rand(-15, 15); // Slight rotation for each character
    $char_x = $start_x + $i * $char_width + rand(-3, 3);
    $char_y = $base_y + rand(-3, 3);
    
    // Alternate between colors
    $char_color = ($i % 2 === 0) ? $text_color : $accent_color;
    
    imagechar($image, $font_size, $char_x, $char_y, $captcha[$i], $char_color);
}

// Output the image
imagepng($image);
imagedestroy($image);
?>