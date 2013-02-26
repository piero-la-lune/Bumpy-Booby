<?php

class Uploader {

	private static $instance;
	protected $uploads = array();
	public $lastupload;

	public function __construct() {
		global $config;
		$this->uploads = Text::unhash(get_file(FILE_UPLOADS));
	}

	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new Uploader();
		}
		return self::$instance;
	}

	protected function save() {
		update_file(FILE_UPLOADS, Text::hash($this->uploads));
	}

	public function add_file($file, $post) {
		global $config;
		if (!canAccess('upload')
			|| !isset($post['token'])
			|| !isset($file['error'])
			|| $file['error'] > 1
			|| !isset($file['tmp_name'])
			|| !isset($file['name'])
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) { return Trad::A_ERROR_TOKEN; }

		$size = filesize($file['tmp_name']);
		$maxsize = Uploader::get_maxsize();
		if ($file['error'] == 1
			|| !$size
			|| $size <= 0
			|| $size > $maxsize
		) {
			return str_replace(
				'%nb%',
				Text::to_xbytes($maxsize),
				Trad::A_ERROR_UPLOAD_SIZE
			);
		}
		
		if ($config['loggedin']
			&& isset($config['users'][$_SESSION['id']])
		) {
			$by = intval($_SESSION['id']);
		}
		else { $by = NULL; }

		if ($config['allocated_space']) {
			$space_left = Text::to_bytes($config['allocated_space'])
				- $this->get_spaceused($by);
			if ($size > $space_left) {
				return str_replace(
					'%nb%',
					Text::to_xbytes($space_left),
					Trad::A_ERROR_UPLOAD_FULL
				);
			}
		}

		$name = $this->generateName($file['name']);
		$newfile = DIR_DATABASE.FOLDER_UPLOADS.$name;
		if (!is_uploaded_file($file['tmp_name'])
			|| !move_uploaded_file($file['tmp_name'], $newfile)
		) {
			return Trad::A_ERROR_UPLOAD;
		}

		$type = NULL;
		if (preg_match('/(png|jpg|jpeg|gif)$/i', $name)) {
			$imgstats = @getimagesize($newfile);
			if ($imgstats && !empty($imgstats['mime'])) {
				$type = $imgstats['mime'];
			}
		}

		$this->uploads[$name] = array(
			'name' => $name,
			'display' => $file['name'],
			'size' => $size,
			'date' => time(),
			'mime-type' => $type,
			'user' => $by
		);
		$this->save();
		$this->lastupload = $this->uploads[$name];
		return true;
	}

	public function remove_file($post) {
		global $config;
		if (!canAccess('upload')
			|| !isset($post['name'])
			|| !isset($post['token'])
			|| !isset($this->uploads[$post['name']])
			|| (!canAccess('settings')
				&& (!$config['loggedin']
					|| $_SESSION['id'] != $this->uploads[$post['name']]['user']))
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) { return Trad::A_ERROR_TOKEN; }

		$name = $post['name'];
		$a = $this->uploads[$name];
		unset($this->uploads[$name]);
		$this->save();
		if (!unlink(DIR_DATABASE.FOLDER_UPLOADS.$name)) {
			logm('Unable to remove file “'.DIR_DATABASE.FOLDER_UPLOADS.$name.'”');
		}
		return true;
	}

	public function remove_file_linked($post) {
		global $config;
		if (!canAccess('upload')
			|| !isset($post['name'])
			|| !isset($post['token'])
			|| !isset($this->uploads[$post['name']])
			|| (!canAccess('settings')
				&& (!$config['loggedin']
					|| $_SESSION['id'] != $this->uploads[$post['name']]['user']))
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) { return Trad::A_ERROR_TOKEN; }

		foreach ($config['projects'] as $k => $v) {
			$issues = Issues::getInstance($k);
			$issues->check_uploads($post['name']);
		}
		$post['token'] = getToken(); # because we will check it a second time
		return $this->remove_file($post);
	}

	protected function generateName($name) {
		$name = Text::purge($name, false);
		while (array_key_exists($name, $this->uploads)) {
			$name = rand(0, 9).$name;
		}
		return $name;
	}

	public function get_spaceused($user) {
		global $config;
		if (!$config['loggedin']) { return 0; }
		$space = 0;
		foreach ($this->uploads as $u) {
			if ($u['user'] == $user) {
				$space = $space + $u['size'];
			}
		}
		return $space;
	}

	public function get($name) {
		if (!isset($this->uploads[$name])) { return false; }
		return $this->uploads[$name];
	}

	public function getAll() {
		return $this->uploads;
	}

	public static function get_maxsize() {
		global $config;
		$a = Text::to_bytes(ini_get('upload_max_filesize'));
		$b = Text::to_bytes(ini_get('post_max_size'));
		$c = Text::to_bytes(ini_get('memory_limit'));
		$d = Text::to_bytes($config['max_size_upload']);
		$maxsize = min($a, $b, $c, $d);
		return $maxsize;
	}

	public static function get_html($link, $uploads = array()) {
		global $config;
		if (!canAccess('upload')) { return ''; }
		$uploader = Uploader::getInstance();
		$up = ''; $up_a = array();
		foreach ($uploads as $u) {
			if ($u = $uploader->get($u)) {
				$up .= '<div data-name="'.$u['name'].'">'
					.htmlspecialchars($u['display']);
				if ($config['loggedin']) {
					$up .= ''
						.'<a href="javascript:;" class="a-remove a-icon-hover">'
							.'<i class="icon-trash"></i>'
						.'</a>';
				}
				$up .= '</div>';
				$up_a[] = $u['name'];
			}
		}
		return '<div class="box box-uploads">'
			.'<div class="top">'
				.'<i class="icon-file"></i>'.Trad::S_UPLOAD_ADD
			.'</div>'
			.'<iframe id="upload" name="upload" src="javascript:;" '
				.'style="display:none"></iframe>'
			.'<div class="inner-form">'
				.'<form action="'.Url::parse('public/ajax').'" method="post" '
					.'enctype="multipart/form-data" target="upload" '
					.'class="form-upload" data-link="'.$link.'" '
					.'data-uploads="'.implode(',', $up_a).'">'
					.'<span class="btn btn-upload">'
						.'<i class="icon-folder-open"></i>&nbsp;&nbsp;'
						.'<span>'.Trad::V_SELECT_FILE.'</span>'
						.'<span class="text-loading" style="display:none">'
							.Trad::V_UPLOADING
						.'</span>'
						.'<input type="file" name="upload" />'
					.'</span>'
					.'<input type="hidden" name="token" value="'.getToken().'" />'
					.'<input type="hidden" name="action" value="upload" />'
					.'<div class="progress">'
						.'<div class="bar"></div>'
					.'</div>'
					.'<div class="uploads">'
						.$up
					.'</div>'
				.'</form>'
			.'</div>'
		.'</div>';
	}

	public static function get_javascript() {
		return '';
	}

}

?>