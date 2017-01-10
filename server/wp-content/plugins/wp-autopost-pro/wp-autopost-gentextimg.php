<?php
header("Content-type: image/png");
header("Cache-Control: no-cache");


$text = $_GET['text'];
$size = $_GET['size'];
$font = $_GET['font'];

$red = $_GET['r'];
$green = $_GET['g'];
$blue = $_GET['b'];

$text = mb_convert_encoding($text, "html-entities","utf-8" );

$coord = imagettfbbox( $size, 0, $font, $text );
$w = abs( $coord[2]-$coord[0] ) + 5;
$h = abs( $coord[1]-$coord[7] ) + 2;
$H = $h+$size/2;

$im=imagecreatetruecolor( $w, $H );
imagealphablending( $im, true );	
imageantialias( $im, true );	
imagesavealpha( $im, true );	
$bgcolor = imagecolorallocatealpha( $im,238,238,238,68 ); 		
imagefill( $im, 0, 0, $bgcolor );


$color = imagecolorallocate( $im, $red, $green, $blue );
$posion = imagettftext( $im, $size, 0, 0, $h, $color, $font, $text );

imagepng($im);
imagedestroy($im);

?>

