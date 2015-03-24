<?php

class Settings {

	protected $config = array();
	protected $errors = array();

	public function __construct() {
		global $config;
		$this->config = $config;
	}

	public function save() {
		global $config;
		$sav = $this->config;
		$sav['last_update'] = time();
		$sav['version'] = VERSION;
		$config = $sav;
		unset($sav['loggedin']); # This is not a global variable
		unset($sav['users']); # Users settings are saved in another file
		update_file(FILE_CONFIG, Text::hash($sav));
	}
	protected function save_users() {
		global $config;
		$config['users'] = $this->config['users'];
		update_file(FILE_USERS, Text::hash($this->config['users']));
	}

	public function changes($post) {
		$this->errors = array();
		$this->c_global($post);
		$this->c_projects($post);
		$this->c_uploads($post);
		$this->c_display($post);
		$this->c_colors($post);
		$this->c_statuses($post);
		$this->c_labels($post);
		$this->c_groups($post);
		$this->c_permissions($post);
		$this->c_users($post);
		$this->save();
		$this->save_users();
		return $this->errors;
	}

	protected function c_global($post) {
		if (!canAccess('settings')) { return false; }
		if (isset($post['title'])) {
			$this->config['title'] = htmlspecialchars($post['title']);
		}
		if (isset($post['intro'])) {
			$this->config['intro'] = $post['intro'];
		}
		if (isset($post['url'])) {
			$post['url'] = preg_replace('#//$#', '/', $post['url']);
			if (filter_var($post['url'], FILTER_VALIDATE_URL)) {
				$this->config['url'] = $post['url'];
			}
			else {
				$this->errors[] = 'validate_url';
			}
		}
		if (isset($post['url_rewriting'])) {
			if (empty($post['url_rewriting'])) {
				$this->config['url_rewriting'] = false;
			}
			else {
				$this->config['url_rewriting'] = filter_var(
					$post['url_rewriting'],
					FILTER_SANITIZE_URL
				);
				$this->url_rewriting();
			}
		}
		if (isset($post['email'])) {
			if (empty($post['email'])) {
				$this->config['email'] = false;
			}
			elseif (filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
				$this->config['email'] = $post['email'];
			}
			else {
				$this->errors[] = 'validate_email';
			}
		}
		if (isset($post['language'])
			&& $post['language'] != $this->config['language']
			&& Text::check_language($post['language'])
		) {
			$this->config['language'] = $post['language'];
			$this->errors[] = 'language_modified';
		}
		if (isset($post['logs_enabled'])) {
			if ($post['logs_enabled'] == 'true') {
				$this->config['logs_enabled'] = true;
			}
			else {
				$this->config['logs_enabled'] = false;
			}
		}
		return true;
	}

	protected function c_projects($post) {
		if (!canAccess('settings')
			|| !isset($post['project_id'])
			|| !is_array($post['project_id'])
			|| !isset($post['project_old_id'])
			|| !is_array($post['project_old_id'])
			|| !isset($post['project_description'])
			|| !is_array($post['project_description'])
			|| count($post['project_id']) != count($post['project_old_id'])
			|| count($post['project_id']) != count($post['project_description'])
		) { return false; }
		foreach ($this->config['projects'] as $k => $v) {
			if (!in_array($k, $post['project_old_id'])) {
				$folder = str_replace('%name%', $k, FOLDER_PROJECT);
				unlink(DIR_DATABASE.$folder.FILE_ISSUES);
				rmdir(DIR_DATABASE.$folder);
			}
		}
		$projects = array();
		$to_be_renamed = array();
		foreach ($post['project_id'] as $k => $v) {
			$id = Text::purge($v, false);
			# $old_id can be empty
			$old_id = Text::purge($post['project_old_id'][$k], false);
			if (empty($id)) {
				$id = Text::randomKey(8);
			}
			if (isset($projects[$id])) {
				$this->errors[] = 'validate_same_project_name';
				# We don't want to overwrite the issues file
				$id = Text::randomKey(8);
			}
			$permissions = array();
			$groups = $this->config['groups']; $groups['none'] = '';
			foreach ($groups as $key => $value) {
				$name = 'permission_projects_'.$old_id.'_'.$key;
				if (!isset($post[$name]) || $post[$name] != '0') {
					$permissions[] = $key;
				}
			}
			if ($id != $old_id && isset($this->config['projects'][$old_id])) {
				$to_be_renamed[$old_id] = $id;
			}
			$projects[$id] = array(
				'description' => $post['project_description'][$k],
				'can_access' => $permissions
			);
		}
		while (!empty($to_be_renamed)) {
			$loop = array();
			foreach ($to_be_renamed as $k => $v) {
				$folder_old = str_replace('%name%', $k, FOLDER_PROJECT);
				$folder_new = str_replace('%name%', $v, FOLDER_PROJECT);
				if (!is_dir(DIR_DATABASE.$folder_old)) {
					# Should not happen
					continue;
				}
				if (is_dir(DIR_DATABASE.$folder_new)) {
					# We don't want to overwrite the issues file, but we want
					# to rename the folder, in case another project need it
					$new = Text::randomKey(8);
					$folder = str_replace('%name%', $new, FOLDER_PROJECT);
					rename(DIR_DATABASE.$folder_old, DIR_DATABASE.$folder);
					$loop[$new] = $v; # Try next time
				}
				else {
					# We can rename the folder without overwriting a project
					rename(DIR_DATABASE.$folder_old, DIR_DATABASE.$folder_new);
				}
			}
			$to_be_renamed = $loop;
		}
		# Create directory for each new project
		foreach ($projects as $k => $p) {
			check_dir(str_replace('%name%', $k, FOLDER_PROJECT));
		}
		$this->config['projects'] = $projects;
		return true;
	}

	protected function c_uploads($post) {
		if (!canAccess('settings')) { return false; }
		if (isset($post['max_size_upload'])) {
			$this->config['max_size_upload'] =
				Text::to_xbytes(Text::to_bytes($post['max_size_upload']));
		}
		if (isset($post['allocated_space'])) {
			$this->config['allocated_space'] =
				Text::to_xbytes(Text::to_bytes($post['allocated_space']));
		}
		return true;
	}

	protected function c_display($post) {
		if (!canAccess('settings')) { return false; }
		if (isset($post['issues_per_page'])) {
			$this->config['issues_per_page'] =
				intval($post['issues_per_page']);
		}
		if (isset($post['search_per_page'])) {
			$this->config['search_per_page'] =
				intval($post['search_per_page']);
		}
		if (isset($post['length_preview_text'])) {
			$this->config['length_preview_text'] =
				intval($post['length_preview_text']);
		}
		if (isset($post['length_search_text'])) {
			$this->config['length_search_text'] =
				intval($post['length_search_text']);
		}
		if (isset($post['length_preview_project'])) {
			$this->config['length_preview_project'] =
				intval($post['length_preview_project']);
		}
		if (isset($post['nb_last_activity_dashboard'])) {
			$this->config['nb_last_activity_dashboard'] =
				intval($post['nb_last_activity_dashboard']);
		}
		if (isset($post['nb_last_activity_user'])) {
			$this->config['nb_last_activity_user'] =
				intval($post['nb_last_activity_user']);
		}
		return true;
	}

	protected function c_colors($post) {
		if (!canAccess('settings')
			|| !isset($post['color_hex'])
			|| !is_array($post['color_hex'])
		) { return false; }
		$colors = array();
		foreach ($post['color_hex'] as $v) {
			$colors[] = Text::checkColor($v);
		}
		foreach ($this->config['labels'] as $k => $v) {
			if (!in_array($v['color'], $colors)) {
				$this->config['labels'][$k]['color'] = DEFAULT_COLOR;
			}
		}
		$this->config['colors'] = $colors;
		return true;
	}

	protected function c_statuses($post) {
		if (!canAccess('settings')
			|| !isset($post['status_id'])
			|| !is_array($post['status_id'])
			|| !isset($post['status_name'])
			|| !is_array($post['status_name'])
			|| !isset($post['status_color'])
			|| !is_array($post['status_color'])
			|| !isset($post['status_dashboard'])
			|| !is_array($post['status_dashboard'])
			|| count($post['status_id']) != count($post['status_name'])
			|| count($post['status_id']) != count($post['status_color'])
			|| count($post['status_id']) != count($post['status_dashboard'])
		) { return false; }
		$statuses = array();
		foreach ($post['status_id'] as $k => $v) {
			$id = Text::purge($v);
			if (empty($id)) { continue; }
			$dashboard = ($post['status_dashboard'][$k] == 'true') ?
				true:
				false;
			$statuses[$id] = array(
				'name' => htmlspecialchars($post['status_name'][$k]),
				'color' => Text::checkColor($post['status_color'][$k]),
				'dashboard' => $dashboard
			);
		}
		if (!isset($statuses[DEFAULT_STATUS])) {
			$statuses[DEFAULT_STATUS] =
				$this->config['statuses'][DEFAULT_STATUS];
			$this->errors[] = 'default_status_removed';
		}
		foreach ($this->config['projects'] as $k => $v) {
			$issues = Issues::getInstance($k);
			$issues->check_statuses($statuses);
		}
		$this->config['statuses'] = $statuses;
		return true;
	}

	protected function c_labels($post) {
		if (!canAccess('settings')
			|| !isset($post['label_id'])
			|| !is_array($post['label_id'])
			|| !isset($post['label_name'])
			|| !is_array($post['label_name'])
			|| !isset($post['label_color'])
			|| !is_array($post['label_color'])
			|| count($post['label_id']) != count($post['label_name'])
			|| count($post['label_id']) != count($post['label_color'])
		) { return false; }
		$labels = array();
		foreach ($post['label_id'] as $k => $v) {
			$id = Text::purge($v);
			if (empty($id)) { continue; }
			$labels[$id] = array(
				'name' => htmlspecialchars($post['label_name'][$k]),
				'color' => Text::checkColor($post['label_color'][$k])
			);
		}
		if (!isset($labels[PRIVATE_LABEL])) {
			$labels[PRIVATE_LABEL] = $this->config['labels'][PRIVATE_LABEL];
			$this->errors[] = 'private_label_removed';
		}
		foreach ($this->config['projects'] as $k => $v) {
			$issues = Issues::getInstance($k);
			$issues->check_labels($labels);
		}
		$this->config['labels'] = $labels;
		return true;
	}

	protected function c_groups($post) {
		if (!canAccess('settings')
			|| !isset($post['group_id'])
			|| !is_array($post['group_id'])
			|| !isset($post['group_name'])
			|| !is_array($post['group_name'])
			|| count($post['group_id']) != count($post['group_name'])
		) { return false; }
		$groups = array();
		foreach ($post['group_id'] as $k => $v) {
			$id = Text::purge($v);
			if (empty($id)) { continue; }
			$groups[$id] = htmlspecialchars($post['group_name'][$k]);
		}
		if (!isset($groups[DEFAULT_GROUP])) {
			$groups[DEFAULT_GROUP] = $this->config['groups'][DEFAULT_GROUP];
			$this->errors[] = 'default_group_removed';
		}
		if (!isset($groups[DEFAULT_GROUP_SUPERUSER])) {
			$groups[DEFAULT_GROUP_SUPERUSER] =
				$this->config['groups'][DEFAULT_GROUP_SUPERUSER];
			$this->errors[] = 'default_group_superuser_removed';
		}
		foreach ($this->config['users'] as $k => $u) {
			if (!array_key_exists($u['group'], $groups)) {
				$this->config['users'][$k]['group'] = DEFAULT_GROUP;
			}
		}
		$this->config['groups'] = $groups;
		return true;
	}

	protected function c_permissions($post) {
		if (!canAccess('settings')) { return false; }
		$permissions = $this->config['permissions'];
		$groups = array_keys($this->config['groups']); $groups[] = 'none';
		foreach ($permissions as $k => $v) {
			$permissions[$k] = array();
			foreach ($groups as $g) {
				if (!isset($post['permission_'.$k.'_'.$g])) { return false; }
				if ($post['permission_'.$k.'_'.$g] == "1") {
					$permissions[$k][] = $g;
				}
			}
		}
		$this->config['permissions'] = $permissions;
		return true;
	}

	protected function c_users($post) {
		if (!canAccess('settings')
			|| !isset($post['user_id'])
			|| !is_array($post['user_id'])
			|| !isset($post['user_username'])
			|| !is_array($post['user_username'])
			|| !isset($post['user_password'])
			|| !is_array($post['user_password'])
			|| !isset($post['user_email'])
			|| !is_array($post['user_email'])
			|| !isset($post['user_notifications'])
			|| !is_array($post['user_notifications'])
			|| !isset($post['user_group'])
			|| !is_array($post['user_group'])
			|| count($post['user_id']) != count($post['user_username'])
			|| count($post['user_id']) != count($post['user_password'])
			|| count($post['user_id']) != count($post['user_email'])
			|| count($post['user_id']) != count($post['user_notifications'])
			|| count($post['user_id']) != count($post['user_group'])
		) { return false; }
		$users = array();
		$newKey = Text::newKey($this->config['users']);
		foreach ($post['user_id'] as $k => $v) {
			if (empty($v)) {
				$id = $newKey;
				$newKey++;
			}
			else {
				$id = intval($v);
				if (!isset($this->config['users'][$id])) { continue; }
			}
			foreach ($users as $u) {
				if ($u['username'] == $post['user_username'][$k]) {
					$this->errors[] = 'validate_same_username';
				}
			}
			$username = $post['user_username'][$k];
			if (empty($post['user_password'][$k])
				&& isset($this->config['users'][$id])
			) {
				$hash = $this->config['users'][$id]['hash'];
			}
			else {
				$hash = Text::getHash($post['user_password'][$k], $username);
			}
			if (filter_var($post['user_email'][$k], FILTER_VALIDATE_EMAIL)) {
				$email = $post['user_email'][$k];
			}
			else {
				$email = false;
			}
			if ($post['user_notifications'][$k] == 'always' && $email) {
				$notifications = 'always';
			}
			elseif ($post['user_notifications'][$k] == 'me' && $email) {
				$notifications = 'me';
			}
			else {
				$notifications = 'never';
			}
			if (isset($this->config['groups'][$post['user_group'][$k]])) {
				$group = $post['user_group'][$k];
			}
			else {
				$group = DEFAULT_GROUP;
			}
			$users[$id] = array(
				'id' => $id,
				'username' => $username,
				'hash' => $hash,
				'email' => $email,
				'notifications' => $notifications,
				'group' => $group,
				'login_failed' => 0,
				'wait_until' => time()
			);
		}
		$this->config['users'] = $users;
		return true;
	}

	public function url_rewriting() {
		if ($rewriting = Url::getRules()) {
			$base = $this->config['url_rewriting'];
			$text = 'ErrorDocument 404 '.$base.'error/404'."\n\n"
				.'RewriteEngine on'."\n"
				.'RewriteBase '.$base."\n\n";
			foreach ($rewriting as $r) {
				if (isset($r['condition'])
					&& $r['condition'] == 'file_doesnt_exist'
				) {
					$text .= "\n".'RewriteCond %{REQUEST_FILENAME} !-f'."\n";
				}
				$text .= 'RewriteRule '.$r['rule'].' '.$r['redirect'].' [QSA,L]'."\n";
			}
			$text .= 'RewriteRule ^([a-zA-Z0-9-]+)/?$ $1/dashboard [R=301,L]';
			file_put_contents('.htaccess', $text);
		}
	}

	public function update_user($id, $post) {
		if (!isset($this->config['users'][$id])
			|| !$this->config['loggedin']
			|| ($_SESSION['id'] != $id && !canAccess('settings'))
			|| !isset($post['password'])
			|| !isset($post['email'])
			|| !isset($post['notifications'])
			|| !isset($post['token'])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (!tokenOk($post['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		if (empty($post['password'])) {
			$hash = $this->config['users'][$id]['hash'];
		}
		else {
			$hash = Text::getHash(
				$post['password'],
				$this->config['users'][$id]['username']
			);
		}
		if (filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
			$email = $post['email'];
		}
		else {
			$email = false;
		}
		if ($post['notifications'] == 'always' && $email) {
			$notifications = 'always';
		}
		elseif ($post['notifications'] == 'me' && $email) {
			$notifications = 'me';
		}
		else {
			$notifications = 'never';
		}
		$this->config['users'][$id]['hash'] = $hash;
		$this->config['users'][$id]['email'] = $email;
		$this->config['users'][$id]['notifications'] = $notifications;

		$this->save_users();
		return true;
	}

	public function new_user($post) {
		if (!canAccess('signup')
			|| !isset($post['username'])
			|| !isset($post['password'])
			|| !isset($post['email'])
			|| !isset($post['token'])
		) {
			return Trad::A_ERROR_FORM;
		}
		if (!tokenOk($post['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		if (empty($post['username']) || empty($post['password'])) {
			return Trad::A_ERROR_EMPTY;
		}
		foreach ($this->config['users'] as $u) {
			if ($u['username'] == $post['username']) {
				return Trad::A_ERROR_SAME_USERNAME;
			}
		}
		$id = Text::newKey($this->config['users']);
		$username = $post['username'];
		$hash = Text::getHash($post['password'], $username);
		if (filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
			$email = $post['email'];
		}
		else {
			$email = false;
		}
		$notifications = ($email) ? 'me' : 'never';
		$this->config['users'][$id] = array(
			'id' => $id,
			'username' => $username,
			'hash' => $hash,
			'email' => $email,
			'notifications' => $notifications,
			'group' => DEFAULT_GROUP,
			'login_failed' => 0,
			'wait_until' => time()
		);

		$this->save_users();
		return true;
	}

	public function login_failed($id) {
		if (!isset($this->config['users'][$id])) { return false; }
		$user = &$this->config['users'][$id];
		$user['login_failed']++;
		if ($user['login_failed'] < 10) {
			$user['wait_until'] = time();
		}
		elseif ($user['login_failed'] < 20) {
			$user['wait_until'] = time()+600; # 10 minutes
		}
		elseif ($user['login_failed'] < 30) {
			$user['wait_until'] = time()+1800; # half hour
		}
		else {
			$user['wait_until'] = time()+3600; # one hour
		}
		$this->save_users();
		unset($user);
	}
	public function login_successful($id) {
		if (!isset($this->config['users'][$id])) { return false; }
		if ($this->config['users'][$id]['login_failed'] > 0) {
			$this->config['users'][$id]['login_failed'] = 0;
			$this->save_users();
		}
	}

	public static function get_path() {
		$http = 'http://';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
			$http = 'https://';
		}
		$server = $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != '80') {
			$server .= ':'.$_SERVER['SERVER_PORT'];
		}
		return $http.$server.Text::dir($_SERVER['SCRIPT_NAME']);
	}

	public static function get_default_config($language = 'en') {
		return array(
			'title' => 'Bumpy Booby',
			'url' => Settings::get_path(),
			'url_rewriting' => false,
			'intro' => '',
			'email' => false,
			'language' => $language,
			'max_size_upload' => '1MB',
			'allocated_space' => '2MB',
			'issues_per_page' => 12,
			'search_per_page' => 12,
			'length_search_text' => 120,
			'length_preview_text' => 100,
			'length_preview_project' => 200,
			'nb_last_activity_dashboard' => 5,
			'nb_last_activity_user' => 5,
			'nb_last_activity_rss' => 20,
			'logs_enabled' => false,
			'projects' => array(
				'default' => array(
					'description' => '',
					'can_access' => array('none', 'default', 'developper', 'superuser')
				)
			),
			'permissions' => array(
				'home' => array('none', 'default', 'developper', 'superuser'),
				'dashboard' => array('none', 'default', 'developper', 'superuser'),
				'issues' =>  array('none', 'default', 'developper', 'superuser'),
				'private_issues' => array(),
				'search' => array('none', 'default', 'developper', 'superuser'),
				'new_issue' => array('default', 'developper', 'superuser'),
				'edit_issue' => array('superuser'),
				'update_issue' => array('developper', 'superuser'),
				'post_comment' => array('default', 'developper', 'superuser'),
				'edit_comment' => array('superuser'),
				'view_user' => array('none', 'default', 'developper', 'superuser'),
				'settings' => array('superuser'),
				'upload' => array('default', 'developper', 'superuser'),
				'view_upload' => array('none', 'default', 'developper', 'superuser'),
				'signup' => array('none'),
				'view_errors' => array('superuser')
			),
			'groups' => array(
				'default' => Trad::W_USER,
				'developper' => Trad::W_DEVELOPPER,
				'superuser' => Trad::W_SUPERUSER
			),
			'statuses' => array(
				'default' => array(
					'name' => Trad::W_S_NEW,
					'color' => '#427693',
					'dashboard' => true
				),
				'confirmed' => array(
					'name' => Trad::W_S_CONFIRMED,
					'color' => '#E77661',
					'dashboard' => true
				),
				'assigned' => array(
					'name' => Trad::W_S_ASSIGNED,
					'color' => '#6B4A9D',
					'dashboard' => false
				),
				'resolved' => array(
					'name' => Trad::W_S_RESOLVED,
					'color' => '#7FB63F',
					'dashboard' => true
				),
				'rejected' => array(
					'name' => Trad::W_S_REJECTED,
					'color' => '#C75480',
					'dashboard' => false
				)
			),
			'labels' => array(
				'urgent' => array(
					'name' => Trad::W_L_URGENT,
					'color' => '#B94A48'
				),
				'improvement' => array(
					'name' => Trad::W_L_IMPROVEMENT,
					'color' => '#355479'
				),
				'private' => array(
					'name' => Trad::W_L_PRIVATE,
					'color' => '#F89406'
				)
			),
			'colors' => array('#999999', '#666666', '#333333', '#468847', '#7FB63F', '#73E33E', '#C3FA35', '#C0FFA4', '#FFE6A4', '#FFE500', '#FFD547', '#F89406', '#835E17', '#B94A48', '#E77661', '#C58574', '#F3A4FF', '#A4CBFF', '#35BAC0', '#427693', '#2F7965', '#355479', '#6B4A9D', '#963A6C', '#C75480', '#FFA4A4', '#E40068'),
			'users' => array(),
			'salt' => Text::randomKey(40),
			'version' => VERSION,
			'last_update' => false
		);
	}
}

?>