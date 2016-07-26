<?php

# Bumpy Booby
# Copyright (c) 2013-2015 Pierre Monchalin
# <http://bumpy-booby.derivoile.fr>
# 
# Permission is hereby granted, free of charge, to any person obtaining
# a copy of this software and associated documentation files (the
# "Software"), to deal in the Software without restriction, including
# without limitation the rights to use, copy, modify, merge, publish,
# distribute, sublicense, and/or sell copies of the Software, and to
# permit persons to whom the Software is furnished to do so, subject to
# the following conditions:
# 
# The above copyright notice and this permission notice shall be
# included in all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

### Load classes
function loadclass($classe) { require './classes/'.str_replace('\\', '/', $classe).'.class.php'; }
spl_autoload_register('loadClass');

### Catch errors
set_error_handler(array('Text', 'capture_error'));

define('NAME', 'Bumpy Booby');
define('VERSION', '0.3');
define('AUTHOR', 'Pierre Monchalin');
define('URL', 'http://bumpy-booby.derivoile.fr');

### Languages
define('LANGUAGES', 'en,fr'); # Separated by a comma
define('DEFAULT_LANGUAGE', 'en'); # Used only during installation

### Directories and files
define('DIR_CURRENT', dirname(__FILE__).'/');
define('DIR_DATABASE', dirname(__FILE__).'/database/');
define('DIR_LANGUAGES', dirname(__FILE__).'/languages/');
define('FOLDER_PROJECT', 'project_%name%/');
define('FOLDER_UPLOADS', 'uploads/');
define('FOLDER_IDENTICONS', 'identicons/');
define('FILE_LOGS', 'logs.txt');
define('FILE_CONFIG', 'config.php');
define('FILE_USERS', 'users.php');
define('FILE_UPLOADS', 'uploads.php');
define('FILE_ISSUES', 'issues.php');

### Standart settings
define('DEFAULT_COLOR', '#333333');
define('DEFAULT_STATUS', 'default');
define('DEFAULT_GROUP', 'default');
define('DEFAULT_GROUP_SUPERUSER', 'superuser');
define('DEFAULT_USER', 'nobody');
define('DEFAULT_PROJECT', 'default');
define('PRIVATE_LABEL', 'private');
define('TIMEOUT', 3600); # 1 hour
define('TIMEOUT_TOKENS', 900); # 15 minutes
define('SALT', 'How are you doing, pumpkin?');

### Thanks to Sebsauvage and Shaarli for the way I store data
define('PHPPREFIX', '<?php /* '); # Prefix to encapsulate data in php code.
define('PHPSUFFIX', ' */ ?>'); # Suffix to encapsulate data in php code.

### Default settings
if (is_file(DIR_DATABASE.FILE_CONFIG)) {
	$config = Text::unhash(get_file(FILE_CONFIG));
	$config['users'] = Text::unhash(get_file(FILE_USERS));
	# We need $config to load the correct language
	require DIR_LANGUAGES.'Trad_'.$config['language'].'.class.php';
}
else {
	# We load language first because we need it in $config
	if (isset($_POST['language']) && Text::check_language($_POST['language'])) {
		# Needed at installation
		require DIR_LANGUAGES.'Trad_'.$_POST['language'].'.class.php';
	}
	else {
		require DIR_LANGUAGES.'Trad_'.DEFAULT_LANGUAGE.'.class.php';
	}
	$config = Settings::get_default_config(DEFAULT_LANGUAGE);
}

### Upgrade
if ($config['version'] != VERSION) {
	require DIR_CURRENT.'upgrade.php';
	exit;
}

### Manage sessions
$cookie = session_get_cookie_params();
	# Force cookie path (but do not change lifetime)
session_set_cookie_params($cookie['lifetime'], Text::dir($_SERVER["SCRIPT_NAME"]));
	# Use cookies to store session.
ini_set('session.use_cookies', 1);
	# Force cookies for session.
ini_set('session.use_only_cookies', 1);
	# Prevent php to use sessionID in URL if cookies are disabled.
ini_set('session.use_trans_sid', false);
session_name('BumpyBooby');
session_start();


$page = new Page();


### Try to get the current project
function getProject() {
	global $config;
	if (onlyDefaultProject()) { return DEFAULT_PROJECT; }
	if (isset($_GET['project'])) {
		if (!isset($config['projects'][$_GET['project']])) { return false; }
		if (!canAccessProject($_GET['project'])) { return false; }
		return $_GET['project'];
	}
	return false;
}
function onlyDefaultProject() {
	global $config;
	if (count($config['projects']) == 1
		&& isset($config['projects'][DEFAULT_PROJECT])
	) {
		return true;
	}
	return false;
}

### Check permissions
function canAccess($page) {
	global $config;
	if ($config['last_update'] === false) { return true; }
	if (!isset($config['permissions'][$page])) { return true; }
	if (!$config['loggedin']) { $group = 'none'; }
	else {
		if (isset($config['users'][$_SESSION['id']])) {
			$group = $config['users'][$_SESSION['id']]['group'];
		}
		else { $group = 'none'; }
	}
	if (in_array($group, $config['permissions'][$page])) {
		return true;
	}
	return false;
}
function canAccessProject($project) {
	global $config;
	if (!isset($config['projects'][$project])) { return false; }
	if (!$config['loggedin']) { $group = 'none'; }
	else {
		if (isset($config['users'][$_SESSION['id']])) {
			$group = $config['users'][$_SESSION['id']]['group'];
		}
		else { $group = 'none'; }
	}
	if (in_array($group, $config['projects'][$project]['can_access'])) {
		return true;
	}
	return false;
}

### Manage tokens
if (!isset($_SESSION['tokens'])) { $_SESSION['tokens'] = array(); }
function getToken() {
    $rnd = Text::randomKey(40);
    $_SESSION['tokens'][$rnd] = time()+TIMEOUT_TOKENS;
    return $rnd;
}
function tokenOk($token) {
    if (isset($_SESSION['tokens'][$token])) {
    	$t = $_SESSION['tokens'][$token];
        unset($_SESSION['tokens'][$token]); // Token is used: destroy it.
        if (time() > $t) { return false; } // Token expired.
        return true; // Token is ok.
    }
    return false; // Wrong token, or already used.
}

### Returns the IP address of the client
# (used to prevent session cookie hijacking)
function getAllIPs() {
    $ip = $_SERVER["REMOTE_ADDR"];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    	$ip .= '_'.$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
    	$ip .= '_'.$_SERVER['HTTP_CLIENT_IP'];
    }
    return $ip;
}

### Authentification
function logout() {
	if (isset($_SESSION['uid'])) {
		unset($_SESSION['uid']);
		unset($_SESSION['id']);
		unset($_SESSION['username']);
		unset($_SESSION['ip']);
	}
}
if (isset($_POST['login'])
	&& isset($_POST['username'])
	&& isset($_POST['password'])
	&& isset($_POST['token'])
) {
	if (!tokenOk($_POST['token'])) {
		$page->addAlert(Trad::A_ERROR_TOKEN);
	}
	else {
		logout();
		$settings = new Settings;
		$matching_user = array();
		$wait = time();
		foreach ($config['users'] as $u) {
			if ($u['username'] == $_POST['username']) {
				$wait = max($wait, $u['wait_until']);
				if ($u['hash'] ==
					Text::getHash($_POST['password'], $_POST['username'])
				) {
					$matching_user = $u;
				}
				else {
					$settings->login_failed($u['id']);
				}
			}
		}
		if ($wait > time()) {
			$page->addAlert(str_replace(
				array('%duration%', '%period%'),
				Text::timeDiff($wait, time()),
				Trad::A_ERROR_LOGIN_WAIT
			));
		}
		elseif (!empty($matching_user)) {
			$_SESSION['uid'] = Text::randomKey(40);
			$_SESSION['id'] = $matching_user['id'];
			$_SESSION['username'] = $matching_user['username'];
			$_SESSION['ip'] = getAllIPs();
			$_SESSION['expires_on'] = time()+TIMEOUT;
				# 0 means "When browser closes"
			session_set_cookie_params(0, Text::dir($_SERVER["SCRIPT_NAME"]));
			session_regenerate_id(true);
			logm('Login successful.');
			$settings->login_successful($u['id']);
			$page->addAlert(Trad::A_LOGGED, 'alert-success');
		}
		else {
			logm('Login failed for user “'.str_replace(
				array("\n", "\t"),
				'',
				$_POST['username'])
			.'”');
			$wait = time();
			foreach ($config['users'] as $u) {
				if ($u['username'] == $_POST['username']) {
					$wait = max($wait, $u['wait_until']);
				}
			}
			if ($wait > time()) {
				$page->addAlert(str_replace(
					array('%duration%', '%period%'),
					Text::timeDiff($wait,time()),
					Trad::A_ERROR_CONNEXION_WAIT
				));
			}
			else {
				$page->addAlert(Trad::A_ERROR_CONNEXION);
			}
		}
	}
}
elseif (isset($_POST['logout']) && isset($_POST['token'])) {
	if (tokenOk($_POST['token'])) {
		logout();
		$page->addAlert(Trad::A_LOGGED_OUT, 'alert-success');
	}
	else {
		$page->addAlert(Trad::A_ERROR_TOKEN);
	}
}
if (!isset($_SESSION['uid'])
	|| empty($_SESSION['uid'])
	|| $_SESSION['ip'] != getAllIPs()
	|| time() > $_SESSION['expires_on']
) {
	logout();
	$config['loggedin'] = false;
}
else {
	$_SESSION['expires_on'] = time()+TIMEOUT;
	$config['loggedin'] = true;
}

### Manage directories and files
function update_file($filename, $content) {
	if (file_put_contents(DIR_DATABASE.$filename, $content) === false
		|| strcmp(file_get_contents(DIR_DATABASE.$filename), $content) != 0)
	{
		logm('Enable to write file “'. DIR_DATABASE.$filename.'”');
		Text::stop(str_replace(
			'%name%',
			DIR_DATABASE.$filename,
			Trad::A_ERROR_FILE_WRITE
		));
	}
}
function get_file($filename) {
	$text = file_get_contents(DIR_DATABASE.$filename);
	if ($text === false) {
		logm('Enable to read file “'. DIR_DATABASE.$filename.'”');
		Text::stop(str_replace(
			'%name%',
			DIR_DATABASE.$filename,
			Trad::A_ERROR_FILE
		));
	}
	return $text;
}
function check_dir($dirname) {
	if (!is_dir(DIR_DATABASE.$dirname)
		&& (!mkdir(DIR_DATABASE.$dirname, 0705)
			|| !chmod(DIR_DATABASE.$dirname, 0705))
	) {
		logm('Enable to create directory “'. DIR_DATABASE.$filename.'”');
		Text::stop(str_replace(
			'%name%',
			DIR_DATABASE.$dirname,
			Trad::A_ERROR_DIRECTORY)
		);
	}
}
function check_file($filename, $content = '') {
	if (!is_file(DIR_DATABASE.$filename)) {
		update_file($filename, $content);
	}
}
function logm($text) {
	global $config;
	if ($config['logs_enabled']) {
		$text = date('Y-m-d H:i:s').' – '.$_SERVER["REMOTE_ADDR"].' – '.$text."\n";
    	file_put_contents(DIR_DATABASE.FILE_LOGS, $text, FILE_APPEND);
    }
}
check_dir('');
check_dir(FOLDER_UPLOADS);
check_dir(FOLDER_IDENTICONS);
check_file(FILE_LOGS);
check_file(FILE_UPLOADS, Text::hash(array()));
check_file(FILE_USERS, Text::hash(array()));
check_file('.htaccess', "Allow from none\nDeny from all\n");


if (!is_file(DIR_DATABASE.FILE_CONFIG)) {
	$page->load('install');
}
else {
	if (!isset($_GET['page'])) { $_GET['page'] = 'home'; }
	if ($_GET['page'] == 'ajax') {
		require dirname(__FILE__).'/pages/ajax.php';
		exit;
	}
	if ($_GET['page'] == 'rss') {
		require dirname(__FILE__).'/pages/rss.php';
		exit;
	}
	elseif ($_GET['page'] == 'identicons') {
		require dirname(__FILE__).'/pages/identicons.php';
		exit;
	}
	elseif ($_GET['page'] == 'downloads') {
		require dirname(__FILE__).'/pages/downloads.php';
		# We do not exit because we want to load error/404 or error/403
		# in some cases
	}
	else {
		$page->load($_GET['page']);
	}
}

$menu = '';
if (getProject()) {
	if (canAccess('dashboard')) {
		$menu .= '<li class="m_dashboard">'
			.'<a href="'.Url::parse(getProject().'/dashboard').'">'
				.Trad::T_DASHBOARD
			.'</a>'
		.'</li>';
	}
	if (canAccess('issues')) {
		$menu .= '<li class="m_issues">'
			.'<a href="'.Url::parse(getProject().'/issues').'">'
				.Trad::T_BROWSE_ISSUES
			.'</a>'
		.'</li>';
	}
	if (canAccess('new_issue')) {
		$menu .= '<li class="m_newissue">'
			.'<a href="'.Url::parse(getProject().'/issues/new').'">'
				.Trad::T_NEW_ISSUE
			.'</a>'
		.'</li>';
	}
}
elseif (canAccess('home')) {
	$menu = '<li class="m_home">'
		.'<a href="'.Url::parse('home').'">'.Trad::T_PROJECTS.'</a>'
	.'</li>';
}
if (canAccess('settings')) {
	$menu .= '<li class="m_settings">'
		.'<a href="'.Url::parse('settings').'">'.Trad::T_SETTINGS.'</a>'
	.'</li>';
}

?>


<!DOCTYPE html>

<!--[if lt IE 8]><html dir="ltr" lang="<?php echo $config['language']; ?>" class="ie lt8 lt9"><![endif]-->
<!--[if IE 8]><html dir="ltr" lang="<?php echo $config['language']; ?>" class="ie ie8 lt9"><![endif]-->
<!--[if IE 9]><html dir="ltr" lang="<?php echo $config['language']; ?>" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html dir="ltr" lang="<?php echo $config['language']; ?>"><!--<![endif]-->

	<head>

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

		<link rel="shortcut icon" href="<?php echo Url::parse('favicon.ico'); ?>" />
		<link rel="apple-touch-icon" href="<?php echo Url::parse('apple-touch-icon.png'); ?>" />

		<link rel="stylesheet" href="<?php echo Url::parse('public/css/app.min.css'); ?>" />

		<!--[if lt IE 9]>
			<script src="<?php echo Url::parse('public/js/html5.js'); ?>"></script>
			<script src="<?php echo Url::parse('public/js/respond.js'); ?>"></script>
		<![endif]-->

		<title><?php echo $page->getTitle(); ?> – <?php echo $config['title']; ?></title>

		<?php

			if (getProject()) {
				echo '<link rel="alternate" type="application/rss+xml" href="'
					.Url::parse(getProject().'/rss').'" title="'.Trad::W_RSS.'" />';
			}

		?>

	</head>

	<body>

		<?php echo $page->getAlerts(); ?>
		<!--[if lt IE 8]>
			<div class="alert alert-error"><?php echo Trad::A_IE; ?></div>
		<![endif]-->

		<header>
			<div class="header-inner">
				<a href="javascript:;" class="a-menu">
					<span class="bar"></span>
					<span class="bar"></span>
					<span class="bar"></span>
				</a>
				<span class="brand"><?php
					echo '<a href="'.Url::parse('home').'">'.$config['title'].'</a>';
					if (getProject() && !onlyDefaultProject()) {
						echo '<span class="slash">/</span><a class="a-project" href="'.Url::parse(getProject().'/dashboard').'">'.getProject().'</a>';
					}
				?></span>
			</div>
		</header>

		<div class="main">

			<aside class="main-right">
				<div class="main-right-open div-affix"><?php echo Trad::W_MENU; ?></div>
				<div class="main-right-inner div-affix">
					<nav>
						<ul>
							<?php echo $menu; ?>
						</ul>
					</nav>
					<?php 
						if (canAccess('search') && getProject()) {
					?>
					<form action="<?php echo Url::parse(getProject().'/search'); ?>" method="post" class="form-search">
						<input type="hidden" name="action" value="search" />
						<input type="text" name="q" value="<?php echo (isset($_GET['q'])) ? htmlspecialchars($_GET['q']) : ''; ?>" placeholder="<?php echo Trad::S_SEARCH; ?>" class="input-left" />
						<button type="submit" class="a-icon-hover"><i class="icon-white icon-search"></i></button>
					</form>
					<?php
						}
					?>
					<div class="div-labels">
					<?php
						if (getProject()) {
							foreach ($config['labels'] as $k => $v) {
								if ($k != PRIVATE_LABEL
									|| canAccess('private_issues')
								) {
									echo '<a href="'.Url::parse(getProject().'/labels/'.$k).'" class="label" style="background-color:'.$v['color'].'">'.$v['name'].'</a>';
								}
							}
						}
					?>
					</div>
					<?php
						if (!$config['loggedin']) {
					?>
					<form action="" method="post" class="form-log-in">
						<?php
							if (canAccess('signup')) {
						?>
						<a href="<?php echo Url::parse('signup'); ?>" class="btn"><?php echo Trad::V_SIGNUP; ?></a>
						<?php
							}
						?>
						<input type="text" name="username" placeholder="<?php echo Trad::F_USERNAME2; ?>" />
						<input type="password" name="password" placeholder="<?php echo Trad::F_PASSWORD2; ?>" class="input-left" />
						<button type="submit" class="a-icon-hover"><i class="icon-white icon-circle-arrow-right"></i></button>
						<input type="hidden" name="token" value="<?php echo getToken(); ?>" />
						<input type="hidden" name="login" value="1" />
					</form>
					<?php
						}
						else {
					?>
					<form method="post" class="form-log-out">
						<p><?php echo str_replace('%user%', '<a href="'.Url::parse('users/'.intval($_SESSION['id'])).'">'.htmlspecialchars($_SESSION['username']).'</a>', Trad::S_WELCOME); ?></p>
						<input type="hidden" name="token" value="<?php echo getToken(); ?>" />
						<input type="hidden" name="logout" value="1" />
						<button type="submit" class="a-icon-hover"><i class="icon-white icon-off"></i></button>
					</form>
					<?php
						}
					?>
					<div class="div-copyright">
						<?php
							if (getProject()) {
								echo '<a href="'.Url::parse(getProject().'/rss').'">'
									.Trad::W_RSS
									.'</a><br />';
							}
							echo str_replace(
								'%name%',
								'<a href="'.URL.'">'.NAME.' '.VERSION.'</a>',
								Trad::S_COPYRIGHT
							);
						?>
					</div>
				</div>
			</aside>

			<section class="main-left">
				<?php echo $page->getContent(); ?>
			</section>

		</div>

		<script src="<?php echo Url::parse('public/js/highlighter.js'); ?>"></script>
		<script src="<?php echo Url::parse('public/js/jquery-1.9.1.min.js'); ?>"></script>
		<script>
			var ajax = "<?php echo Url::parse('public/ajax'); ?>",
				token = "<?php echo getToken(); ?>",
				verb_edit = "<?php echo Trad::V_EDIT; ?>",
				verb_preview = "<?php echo Trad::V_PREVIEW; ?>",
				confirm_delete_issue = "<?php echo Trad::A_CONFIRM_DELETE_ISSUE; ?>",
				confirm_delete_comment = "<?php echo Trad::A_CONFIRM_DELETE_COMMENT; ?>",
				confirm_delete_upload = "<?php echo Trad::A_CONFIRM_DELETE_UPLOAD; ?>";
			$(document).ready(function() {
				$(".m_<?php echo $page->getSafePage(); ?>").addClass("active");
			});
		</script>
		<script src="<?php echo Url::parse('public/js/scripts.min.js'); ?>"></script>
		<script>
			<?php echo $page->getJavascript(); ?>
		</script>

	</body>

</html>
