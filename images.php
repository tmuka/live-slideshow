<?php 

$dir = 'img/';
$images = glob($dir.'*.{jpg,JPG,png,PNG,gif,GIF}',GLOB_BRACE);
//shuffle($images);
echo implode(",", $images);
