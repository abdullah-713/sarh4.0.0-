<?php
// Generate PWA icon — run once then delete
$size = 192;
$img = imagecreatetruecolor($size, $size);

// Orange background #FF8C00
$orange = imagecolorallocate($img, 255, 140, 0);
imagefill($img, 0, 0, $orange);

// White text
$white = imagecolorallocate($img, 255, 255, 255);

// Draw "صرح" centered (using built-in font since TTF may not be available)
$text = 'SARH';
$font_size = 5; // largest built-in
$tw = imagefontwidth($font_size) * strlen($text);
$th = imagefontheight($font_size);
$x = ($size - $tw) / 2;
$y = ($size - $th) / 2;
imagestring($img, $font_size, (int)$x, (int)$y, $text, $white);

// Save
imagepng($img, __DIR__ . '/icon-192.png');
imagedestroy($img);

echo 'icon-192.png created!';
