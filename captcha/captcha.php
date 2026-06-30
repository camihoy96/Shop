<?php
ob_clean();
session_set_cookie_params(0, "/");
session_start();

$captcha_code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 6);
$_SESSION['captcha'] = $captcha_code;

$width = 130;
$height = 40;
$image = imagecreate($width, $height);

$background = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 0, 0, 0);
$noise_color = imagecolorallocate($image, 100, 100, 100);

for ($i = 0; $i < 50; $i++) {
    imagefilledellipse($image, rand(0,$width), rand(0,$height), 2, 3, $noise_color);
}

for ($i = 0; $i < 2; $i++) {
    imageline($image, 0, rand(0,$height), $width, rand(0,$height), $noise_color);
}

$font_size = 5;
$center_x = ($width - imagefontwidth($font_size) * strlen($captcha_code)) / 2;
$center_y = ($height - imagefontheight($font_size)) / 2;

imagestring($image, $font_size, $center_x, $center_y, $captcha_code, $text_color);

header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
exit;
?>
