<?php 

$dir = 'img/';
$images = glob($dir.'*.[jJ][pP][gG]');
//shuffle($images);
echo implode(",", $images);
