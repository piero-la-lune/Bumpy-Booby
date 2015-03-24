<?php

class Text {

	protected static $accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
		'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ',
		'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å',
		'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô',
		'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą',
		'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē',
		'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ',
		'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į',
		'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ',
		'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō',
		'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś',
		'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ',
		'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ',
		'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ',
		'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ',
		'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');

	protected static $without_accent = array('A', 'A', 'A', 'A', 'A', 'A',
		'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O',
		'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a',
		'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n',
		'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a',
		'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd',
		'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
		'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i',
		'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l',
		'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n',
		'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R',
		'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T',
		't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W',
		'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o',
		'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u',
		'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');

	public static function randomKey($length = 8) {
		return substr(
			sha1(uniqid('', true).'_'.mt_rand().SALT),
			mt_rand(0, 40-$length),
			$length
		);
	}

	public static function newKey($a) {
		if (empty($a)) { return 1; }
		return max(array_keys($a))+1;
	}

	public static function purge($txt, $tolower = true) {
		$txt = str_replace(self::$accents, self::$without_accent, $txt);
		$txt = preg_replace(
			array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'),
			array('', '-', ''),
			$txt
		);
		if ($tolower) { $txt = strtolower($txt); }
		return $txt;
	}

	public static function check_language($language) {
		$languages = explode(',', LANGUAGES);
		return in_array($language, $languages);
	}

	public static function checkColor($color) {
		if (preg_match('/rgb/i', $color)) {
			$color = preg_replace('/[^0-9,]/', '', $color);
			if (!preg_match('/^[0-9]{1,3},[0-9]{1,3},[0-9]{1,3}$/', $color)) {
				return DEFAULT_COLOR;
			}
			$colors = explode(',', $color);
			$r = dechex($colors[0]); if (strlen($r) < 2) { $r = '0'.$r; }
			$g = dechex($colors[1]); if (strlen($g) < 2) { $g = '0'.$g; }
			$b = dechex($colors[2]); if (strlen($b) < 2) { $b = '0'.$b; }
			$color = '#'.$r.$g.$b;
		}
		else {
			$color = preg_replace('/[^a-fA-F0-9#]/', '', $color);
			if (!preg_match('/^#[a-fA-F0-9]{3,6}$/', $color)) {
				return DEFAULT_COLOR;
			}
		}
		return mb_strtoupper($color);
	}

	public static function getHash($password, $username = '') {
		global $config;
		return sha1($password.$username.$config['salt']);
	}

	public static function timeDiff($time1, $time2) {
		$period = array(
			Trad::W_SECONDE,
			Trad::W_MINUTE,
			Trad::W_HOUR,
			Trad::W_DAY,
			Trad::W_WEEK,
			Trad::W_MONTH,
			Trad::W_YEAR,
			Trad::W_DECADE
		);
		$periods = array(
			Trad::W_SECONDE_P,
			Trad::W_MINUTE_P,
			Trad::W_HOUR_P,
			Trad::W_DAY_P,
			Trad::W_WEEK_P,
			Trad::W_MONTH_P,
			Trad::W_YEAR_P,
			Trad::W_DECADE_P
		);
		$lengths = array("60","60","24","7","4.35","12","10");
		$difference = abs($time1 - $time2);
		for ($j=0; $difference>=$lengths[$j] && $j<count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
		$difference = round($difference);
		if ($difference != 1) {
			return array($difference, $periods[$j]);
		}
		return array($difference, $period[$j]);
	}
	public static function ago($time) {
		return str_replace(
			array('%duration%', '%pediod%'),
			self::timeDiff(time(), $time),
			Trad::S_AGO
		);
	}

	public static function intro($text, $length, $quote = true, $find = NULL) {
		$text = ' '.strip_tags(Text::markdown($text)).' ';
		$text = Text::remove_blanks($text);
		$text = str_replace('  ', ' ', $text);
		if ($find) {
			$start = strpos($text, $find);
			if ($start === false || $start-$length/2 < 0) { $start = 0; }
			else { $start = $start-$length/2; }
		}
		else {
			$start = 0;
		}
		$cuttext = substr($text, $start);
		if ($cuttext != $text && ($nb = strpos($cuttext, ' ')) !== false) {
			$subtext = Trad::W_SUSPENSION.ltrim(substr_replace(
				$cuttext,
				'',
				0,
				$nb+1
			));
		}
		else {
			$subtext = ltrim($cuttext);
		}
		$cuttext = substr($subtext, 0, $length);
		if ($cuttext != $subtext && ($nb = strrpos($cuttext, ' ')) !== false) {
			$subtext = rtrim(substr_replace(
				$cuttext,
				'',
				$nb
			)).Trad::W_SUSPENSION;
		}
		else {
			$subtext = rtrim($cuttext);
		}
		if ($quote) {
			return str_replace('%text%', $subtext, Trad::W_EXTRACT);
		}
		else { return $subtext; }
	}

	public static function markdown($text) {
		$markdown = new Markdown();
		$text = self::beforeMarkdown($text);
		$text = $markdown->transform($text);
		$text = self::afterMarkdown($text);
		$purifier = new HTMLPurifier();
		$text = $purifier->purify($text);
		return $text;
	}

	protected static function beforeMarkdown($text) {
		return $text;
	}
	protected static function afterMarkdown($text) {
		if (getProject()) {
			$text = preg_replace_callback(
				'/(^|\s)#([0-9]+)(\s|$)/',
				function($m) {
					$issues = Issues::getInstance(getProject());
					if ($issues->get($m[2])) {
						return $m[1]
							.'<a href="'.Url::parse(getProject().'/issues/'.$m[2]).'">'
							.'#'.$m[2]
							.'</a>'
							.$m[3];
					}
					return $m[0];
				},
				$text
			);
		}
		$text = preg_replace_callback(
			'/(^|\s)([a-zA-Z0-9-]+)#([0-9]+)(\s|$)/',
			function($m) {
				global $config;
				if (isset($config['projects'][$m[2]])
					&& canAccessProject($m[2])
				) {
					$issues = Issues::getInstance($m[2]);
					if ($issues->get($m[3])) {
						return $m[1]
							.'<a href="'.Url::parse($m[2].'/issues/'.$m[3]).'">'
							.$m[2].'#'.$m[3]
							.'</a>'
							.$m[4];
					}
				}
				return $m[0];
			},
			$text
		);
		$text = preg_replace_callback(
			'/(^|\s)@([^\s]+)(\s|$)/',
			function($m) {
				global $config;
				foreach ($config['users'] as $u) {
					if ($u['username'] == $m[2]) {
						return $m[1]
							.'<a href="'.Url::parse('users/'.$u['id']).'">'
							.'@'.$m[2]
							.'</a>'
							.$m[3];
					}
				}
				return $m[0];
			},
			$text
		);
		$text = preg_replace_callback(
			'/(^|\s)@{([^}]+)}(\s|$)/',
			function($m) {
				global $config;
				foreach ($config['users'] as $u) {
					if ($u['username'] == $m[2]) {
						return $m[1]
							.'<a href="'.Url::parse('users/'.$u['id']).'">'
							.'@'.$m[2]
							.'</a>'
							.$m[3];
					}
				}
				return $m[0];
			},
			$text
		);
		return $text;
	}

	public static function username($id, $html = false, $nobody = false) {
		global $config;
		if (isset($config['users'][$id])) {
			if ($html) {
				return '<a href="'.Url::parse('users/'.$id).'">'
					.htmlspecialchars($config['users'][$id]['username'])
				.'</a>';
			}
			return $config['users'][$id]['username'];
		}
		else {
			if ($nobody) { return Trad::W_NOBODY; }
			return Trad::W_SOMEONE;
		}
	}

	public static function status($status = '', $assignedto = NULL,
		$html = true, $nobody = true
	) {
		global $config;
		if (!isset($config['statuses'][$status])) { $status = DEFAULT_STATUS; }
		return str_replace(
			'%user%',
			Text::username($assignedto, $html, $nobody),
			$config['statuses'][$status]['name']
		);
	}

	public static function identicon($text) {
		return Url::parse('public/identicons/'.md5($text).'.png');
	}
	public static function upload($name) {
		return Url::parse('public/uploads/'.$name);
	}

	public static function options($arr, $sel) {
		$ret = '';
		foreach ($arr as $k => $v) {
			$data = '';
			if (preg_match('/%([a-z]+)%/', $v, $matches)) {
				$data = ' data-match="'.$matches[1].'"';
			}
			$v = preg_replace('/%[a-z]+%/', Trad::W_SUSPENSION, $v);
			$ret .= '<option value="'.$k.'"'.$data;
			if ($k == $sel) { $ret .= ' selected'; }
			$ret .= '>'.$v.'</option>';
		}
		return $ret;
	}

	public static function to_bytes($val) {
		$val = trim($val);
		switch (strtolower(substr($val, -1))) {
			case 'g': $val = (int)substr($val, 0, -1) * 1073741824; break;
			case 'm': $val = (int)substr($val, 0, -1) * 1048576; break;
			case 'k': $val = (int)substr($val, 0, -1) * 1024; break;
			case 'b':
				switch (strtolower(substr($val, -2, 1))) {
					case 'm': $val = (int)substr($val, 0, -2) * 1048576; break;
					case 'k': $val = (int)substr($val, 0, -2) * 1024; break;
					case 'g': $val = (int)substr($val, 0, -2) * 1073741824; break;
					default : $val = (int)$val; break;
				}
				break;
			default: $val = (int)$val; break;
	     }
	     return $val;
	}
	public static function to_xbytes($val) {
		$val = (int)$val;
		if ($val >= 1073741824) {
			return round($val/1073741824, 2).'GB';
		}
		elseif ($val >= 1048576) {
			return round($val/1048576, 2).'MB';
		}
		elseif ($val >= 1024) {
			return round($val/1024, 2).'KB';
		}
		return $val.'B';
	}

	public static function dir($name) {
		return preg_replace('#//$#', '/', dirname($name).'/');
	}

	public static function hash($object) {
		return PHPPREFIX.base64_encode(gzdeflate(serialize($object))).PHPSUFFIX;
	}
	public static function unhash($text) {
		return unserialize(gzinflate(base64_decode(substr(
			$text,
			strlen(PHPPREFIX),
			-strlen(PHPSUFFIX)
		))));
	}

	public static function remove_blanks($text, $replace = '') {
		return str_replace(array("\n", "\t"), $replace, $text);
	}

	public static function capture_error($errno, $errstr, $errfile, $errline) {
		$text = str_replace(
			array('%title%', '%message%', '%file%', '%line%'),
			array(Trad::errors($errno), $errstr, $errfile, $errline),
			Trad::A_ERROR
		);
		logm(strip_tags(str_replace('<br />', ' ', $text)));
		self::stop($text, false);
		return true;
	}
	public static function stop($text, $stop = true) {
		if (!canAccess('view_errors')) {
			if (!$stop) { return true; }
			$text = Trad::A_ERROR_FATAL;
		}
		header('Content-Type: text/html; charset=utf-8');
		$html = '
			<html>
			<head>
			<style>
			body {
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
				font-size: 14px;
				font-weight: normal;
			}
			.error {
				max-width: 400px;
				margin: 20px auto;
				padding: 12px;
				border: 4px solid #FF7373;
				background: #FFE0E0;
			}
			</style>
			</head>
			<body>
				<div class="error">'.$text.'</div>
			</body>
			</html>
		';
		if ($stop) {
			die($html);
			exit;
		}
		else {
			echo $html;
		}
	}
}

?>