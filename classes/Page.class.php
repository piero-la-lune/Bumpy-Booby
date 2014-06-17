<?php

class Page {

	protected $page;
	protected $title;
	protected $content;
	protected $javascript;
	protected $errors = array();

	private $pages = array(
		'home',
		'install',
		'dashboard',
		'issues',
		'search',
		'view_issue',
		'new_issue',
		'view_user',
		'settings',
		'error/403',
		'error/404',
		'signup'
	);
	private $need_project = array(
		'dashboard',
		'issues',
		'search',
		'view_issue',
		'new_issue'
	);

	public function load($page) {
		$this->page = $page;
		$path = dirname(__FILE__).'/../pages/'.$page.'.php';
		if (!in_array($page, $this->pages)
			|| (in_array($page, $this->need_project) && !getProject())
			|| !is_file($path)
		) {
			$this->page = 'error/404';
			$path = dirname(__FILE__).'/../pages/error/404.php';
		}
		elseif (!canAccess($page)) {
			$this->page = 'error/403';
			$path = dirname(__FILE__).'/../pages/error/403.php';
		}
		global $config;
		include($path);

		if (isset($load)) { $this->load($load); }
		else {
			$this->title = $title;
			$this->content = $content;
			if (isset($javascript)) { $this->javascript = $javascript; }
			else { $this->javascript = ''; }
		}
	}

	public function getPage() {
		return $this->page;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getContent() {
		return $this->content;
	}
	public function getJavascript() {
		return $this->javascript;
	}
	public function getSafePage() {
		return Text::purge($this->page);
	}

	public function addAlert($txt, $type = 'alert-error') {
		$this->errors[] = array('text' => $txt, 'type' => $type);
	}
	public function getAlerts() {
		$txt = '';
		if (isset($_SESSION['alert'])) {
			$this->errors[] = $_SESSION['alert'];
			unset($_SESSION['alert']);
		}
		foreach ($this->errors as $error) {
			$txt .= '<div class="alert '.$error['type'].'">'
				.$error['text']
			.'</div>';
		}
		return $txt;
	}

}