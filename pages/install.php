<?php

$title = Trad::T_INSTALLATION;

$languages = array();
$l = explode(',', LANGUAGES);
foreach ($l as $v) { $languages[$v] = $v; }

$content = '<h1>'.Trad::T_INSTALLATION.'</h1><p>'.Trad::S_INTRO_INSTALL.'</p>';

if (isset($_POST['language']) && Text::check_language($_POST['language'])) {

	$content .= '

<form action="'.Url::parse('install').'" method="post">

	<legend>'.Trad::W_SUPERUSER.'</legend>

		<label for="user_username">'.Trad::F_USERNAME.'</label>
		<input type="text" class="input-medium" id="user_username" name="user_username[]" />
		
		<label for="user_password">'.Trad::F_PASSWORD.'</label>
		<input type="password" class="input-medium" id="user_password" name="user_password[]" />

	<legend>'.Trad::T_GLOBAL_SETTINGS.'</legend>

		<label for="title">'.Trad::F_NAME.'</label>
		<input type="text" class="input-medium" id="title" name="title" value="Bumpy Booby" />

		<label for="url">'.Trad::F_URL.'</label>
		<input type="url" class="input-large" id="url" name="url" value="'.$config['url'].'" />

	<p>&nbsp;</p>
	
	<div class="form-actions">
		<input type="hidden" name="action" value="save" />
		<input type="hidden" name="language" value="'.$_POST['language'].'" />
		<button type="submit" class="btn btn-primary">'.Trad::V_SAVE_CONFIG.'</button>
	</div>

</form>

	';
}
else {

	$content .= '<p>&nbsp;</p>

<form action="'.Url::parse('install').'" method="post">

	<label for="language">'.Trad::F_LANGUAGE.'</label>
	<select class="input-small" id="language" name="language">'.Text::options($languages, DEFAULT_LANGUAGE).'</select>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary">'.Trad::V_CONTINUE.' <i class="icon-white icon-chevron-right"></i></button>
	</div>

</form>

	';
}

if (is_file(DIR_DATABASE.FILE_CONFIG)) {
	$content = '
		<div class="alert alert-error">
			'.Trad::A_ERROR_INSTALL.'
		</div>
	';
}
elseif (isset($_POST['action'])
	&& isset($_POST['language']) && Text::check_language($_POST['language']))
{
	$config = Settings::get_default_config($_POST['language']);
	$settings = new Settings();
	$post = $_POST;
	$post['user_id'] = array('');
	$post['user_email'] = array('');
	$post['user_notifications'] = array('never');
	$post['user_group'] = array(DEFAULT_GROUP_SUPERUSER);
	$ans = $settings->changes($post);
	if (!empty($ans)) {
		foreach ($ans as $v) {
			$this->addAlert(Trad::settings($v));
		}
	}
	else {
		$_SESSION['alert'] = array('text' => Trad::A_SUCCESS_INSTALL, 'type' => 'alert-success');
		header('Location: '.Url::parse('home'));
		exit;
	}
}

?>