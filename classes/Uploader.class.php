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
			|| !isset($file['name']))
			{ return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) { return Trad::A_ERROR_TOKEN; }

		$size = filesize($file['tmp_name']);
		$maxsize = Uploader::get_maxsize();
		if ($file['error'] == 1
			|| !$size
			|| $size <= 0
			|| $size > $maxsize)
			{
			return str_replace('%nb%', Text::to_xbytes($maxsize), Trad::A_ERROR_UPLOAD_SIZE);
		}
		
		if ($config['loggedin']
			&& isset($config['users'][$_SESSION['id']]))
		{
			$by = intval($_SESSION['id']);
		}
		else { $by = NULL; }

		if ($config['allocated_space']) {
			$space_left = Text::to_bytes($config['allocated_space'])-$this->get_spaceused($by);
			if ($size > $space_left) {
				return str_replace('%nb%', Text::to_xbytes($space_left), Trad::A_ERROR_UPLOAD_FULL);
			}
		}

		$name = $this->generateName($file['name']);
		$newfile = DIR_DATABASE.FOLDER_UPLOADS.$name;
		if (!is_uploaded_file($file['tmp_name'])
			|| !move_uploaded_file($file['tmp_name'], $newfile))
			{
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
		if (!isset($post['name'])
			|| !isset($post['token'])
			|| !isset($this->uploads[$post['name']])
			|| !canAccess('upload')
			|| !$config['loggedin']
			|| ($_SESSION['id'] != $this->uploads[$post['name']]['user']) && !canAccess('settings'))
			{ return Trad::A_ERROR_FORM; }
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
		if (!isset($post['name'])
			|| !isset($post['token'])
			|| !isset($this->uploads[$post['name']])
			|| !canAccess('upload')
			|| !$config['loggedin']
			|| ($_SESSION['id'] != $this->uploads[$post['name']]['user']) && !canAccess('settings'))
			{ return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) { return Trad::A_ERROR_TOKEN; }

		foreach ($config['projects'] as $k => $v) {
			$issues = Issues::getInstance($k);
			$issues->check_uploads($post['name']);
		}
		$post['token'] = getToken(); /* because we will check it a second time */
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

	public static function get_html() {
		if (!canAccess('upload')) { return ''; }
		return '
			<div class="box-uploads">
				<div class="top">
					<i class="icon-file"></i>
					'.Trad::S_UPLOAD_ADD.'
				</div>
				<iframe id="upload" name="upload" src="javascript:;" style="display:none"></iframe>
				<form action="'.Url::parse('public/ajax').'" method="post" enctype="multipart/form-data" target="upload" class="form-upload">
					<span class="btn btn-upload" data-loading-text="Uploading...">
						<i class="icon-folder-open"></i>
						<span>'.Trad::V_SELECT_FILE.'</span>
						<span class="text-loading">'.Trad::V_UPLOADING.'</span>
						<input type="file" name="upload" />
					</span>
					<input type="hidden" name="token" value="'.getToken().'" />
					<input type="hidden" name="action" value="upload" />
					<div class="progress">
						<div class="bar"></div>
					</div>
					<div class="uploads">
					</div>
				</form>
			</div>
		';
	}

	public static function get_javascript() {
		return '
		var form_u = $(".form-upload").eq(0);
		var array_uploads = new Object();
		function upload_callback(ans) {
			form_u.find(".btn-upload").toggleClass("disabled")
				.find("span").toggle().end()
				.find("i").toggle();
			form_u.find(".bar").css("width", 0);
			if (!ans.success) {
				alert(ans.text);
			}
			else {
				form_u.find(".uploads").append(ans.text);
				array_uploads[ans.name] = ans.name;
			}
			form_u.find("input[name=\"token\"]").val(ans.token);
			form_u.find("input[name=\"upload\"]").remove();
			form_u.find(".btn-upload").append("<input type=\"file\" name=\"upload\" />");
		}
		$(".form-upload input[name=\"upload\"]").live("change", function() {
			form_u = $(this).closest("form");
			var file = $(this)[0].files;

			if (typeof file != "undefined" && window.XMLHttpRequestUpload && window.FormData) {
				file = file[0];
				xhr = new XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
					if (evt.lengthComputable) {
						form_u.find(".bar").css("width", (evt.loaded/evt.total)*100+"%");
					}
				}, false);
				xhr.addEventListener("load", function(evt) {
					upload_callback(jQuery.parseJSON(evt.target.responseText));
				}, false);

				xhr.open("post", "'.Url::parse('public/ajax').'", true);

				var formData = new FormData();
				formData.append("type", "xhr");
				formData.append("token", form_u.find("input[name=\"token\"]").val());
				formData.append("action", "upload");
				formData.append("upload", file);
				xhr.send(formData);
			}
			else {
				form_u.submit();
			}
			form_u.find(".btn-upload").toggleClass("disabled")
				.find("span").toggle().end()
				.find("i").toggle();
		});
		var token_remove_upload = "'.getToken().'";
		$(".form-upload .uploads .icon-trash").live("click", function() {
			var div = $(this).closest("div");
			var name = $(this).data("name");
			$.ajax({
				type: "POST",
				url: "'.Url::parse('public/ajax').'",
				data: {
					action: "upload_remove",
					token: token_remove_upload,
					name: name
				}
			}).done(function(ans) {
				var ans = jQuery.parseJSON(ans);
				if (ans.success) {
					div.remove();
					delete array_uploads[name];
				}
				else {
					alert(ans.text);
				}
				token_remove_upload = ans.token;
			});	
		});
		';
	}

}

?>