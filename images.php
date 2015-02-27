<?php 

if(isset($_REQUEST['promos'])){
	$dir = 'img/promos/';
} else {
	$dir = 'img/';
}
$images = glob($dir.'*.{png,PNG,jpg,JPG,gif,GIF}',GLOB_BRACE);
//shuffle($images);
echo implode(",", $images);
