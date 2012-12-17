<?php

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'markdown' && isset($_POST['text'])) {
		$obj = array(
			'success' => true,
			'text' => Text::markdown($_POST['text'])
		);
		echo json_encode($obj);
		exit;
	}
	elseif ($_POST['action'] == 'upload' && isset($_FILES['upload'])) {
		$uploader = Uploader::getInstance();
		$ans = $uploader->add_file($_FILES['upload'], $_POST);
		if ($ans === true) {
			$upload = $uploader->lastupload;
			$ret = '<div>'.htmlspecialchars($upload['display']);
			if ($config['loggedin']) {
				$ret .= '<a href="javascript:;" class="a-remove a-icon-hover">';
				$ret .= '<i class="icon-trash" data-name="'.$upload['name'].'"></i>';
				$ret .= '</a>';
			}
			$ret .= '</div>';
			$obj = array(
				'success' => true,
				'text' => $ret,
				'name' => $upload['name'],
				'token' => getToken()
			);
		}
		else {
			$obj = array('success' => false, 'token' => getToken(), 'text' => $ans);
		}
		$json = json_encode($obj);
		if (isset($_POST['type']) && $_POST['type'] == 'xhr') { echo $json; }
		else { echo '<script>window.top.upload_callback('.$json.');</script>'; }
		exit;
	}
	elseif ($_POST['action'] == 'upload_remove') {
		$uploader = Uploader::getInstance();
		$ans = $uploader->remove_file($_POST);
		if ($ans === true) {
			$obj = array('success' => true, 'token' => getToken());
		}
		else {
			$obj = array('success' => false, 'token' => getToken(), 'text' => $ans);
		}
		echo json_encode($obj);
		exit;
	}
	elseif ($_POST['action'] == 'upload_remove_linked') {
		$uploader = Uploader::getInstance();
		$ans = $uploader->remove_file_linked($_POST);
		if ($ans === true) {
			$obj = array('success' => true, 'token' => getToken());
			if (isset($_POST['user'])
				&& isset($config['users'][$_POST['user']])
				&& ($_SESSION['id'] == $_POST['user'] || canAccess('settings')))
			{
				$space = $uploader->get_spaceused($_POST['user']);
				$obj['space'] = Text::to_xbytes(Text::to_bytes($config['allocated_space'])-$space);
				$obj['percent'] = intval($space*100/Text::to_bytes($config['allocated_space']));
			}
		}
		else {
			$obj = array('success' => false, 'token' => getToken(), 'text' => $ans);
		}
		echo json_encode($obj);
		exit;
	}
}
if ($_SERVER['REQUEST_METHOD'] == 'POST'
	&& empty($_POST)
	&& empty($_FILES)
	&& $_SERVER['CONTENT_LENGTH'] > 0 )
	{
	$text = str_replace('%nb%', Text::to_xbytes(Uploader::get_maxsize()), Trad::A_ERROR_UPLOAD_SIZE);
	$obj = array('success' => false, 'token' => getToken(), 'text' => $text);
	$json = json_encode($obj);
	if (isset($_POST['type']) && $_POST['type'] == 'xhr') { echo $json; }
	else { echo '<script>window.top.upload_callback('.$json.');</script>'; }
	exit;
}

$obj = array(
	'success' => false,
	'token' => getToken(),
	'text' => Trad::A_ERROR_FORM
);
echo json_encode($obj);
exit;


?>