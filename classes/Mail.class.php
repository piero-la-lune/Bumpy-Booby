<?php

class Mail {

	protected $object;
	protected $content;
	protected $personal_object;
	protected $personal_content;

	public function __construct($object, $content) {
		global $config;
		$this->object = $object;
		$this->content = $content;
		$this->replace(array('%title%' => $config['title']));
		if (getProject()) {
			if (onlyDefaultProject()) {
				$this->replace(array('%project%' => ''));
			}
			else {
				$this->replace(array('%project%' => getProject()));
			}
		}
	}

	public function replace($find) {
		foreach ($find as $k => $v) {
			$this->content = str_replace($k, $v, $this->content);
			$this->object = str_replace($k, $v, $this->object);
		}
		$this->personal_object = $this->object;
		$this->personal_content = $this->content;
	}

	public function replace_personal($find) {
		foreach ($find as $k => $v) {
			$this->personal_content = str_replace($k, $v, $this->content);
			$this->personal_object = str_replace($k, $v, $this->object);
		}
	}

	public function send($to) {
		global $config;
		if (!$config['email']
			|| !filter_var($to, FILTER_VALIDATE_EMAIL))
			{ return true; }

		$title = htmlspecialchars_decode($config['title']);
		$from = '=?UTF-8?B?'.base64_encode($title)."?=";

		$headers  = 'From: '.$from.' <'.$config['email'].'>'."\n";
		$headers .= 'MIME-Version: 1.0'."\n";
		$headers .= 'Content-Type: text/plain; charset="UTF-8"'."\n";
		$headers .= 'Content-Transfer-Encoding: 8bit'."\n";
		$headers .= "\n";

		$object = '=?UTF-8?B?'.base64_encode($this->personal_object).'?=';

		return mail($to, $object, $this->personal_content, $headers);
	}

}

?>