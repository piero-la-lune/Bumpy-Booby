<?php

if (!canAccess('view_upload')) {
	$page->load('error/403');
}
else {

	$uploader = Uploader::getInstance();
	$file = $uploader->get(Text::purge($_GET['file'], false));

	if ($file) {
		if ($file['mime-type']) {
			header('Content-Type: '.$file['mime-type']);
			readfile(DIR_DATABASE.FOLDER_UPLOADS.$file['name']);
			exit;
		}
		else {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.htmlspecialchars($file['display']).'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: '.filesize(DIR_DATABASE.FOLDER_UPLOADS.$file['name']));
			readfile(DIR_DATABASE.FOLDER_UPLOADS.$file['name']);
			exit;
		}
		exit;
	}

	$page->load('error/404');
}

?>