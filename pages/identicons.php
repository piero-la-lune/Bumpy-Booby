<?php

if (isset($_GET['seed']) && preg_match('#^[a-f0-9]{32}$#', $_GET['seed'])) {
	$seed = $_GET['seed'];
	$filename = DIR_DATABASE.FOLDER_IDENTICONS.$seed.'.png';
	header("Content-Type: image/png");

	if (file_exists($filename)) {
		readfile($filename);
	}
	else {
		$identicon = new Identicons($seed);
		$img = $identicon->build($seed);
		imagepng($img, $filename);
		imagepng($img);
	}
	exit;
}
else {
	$page->load('error/404');
}

?>