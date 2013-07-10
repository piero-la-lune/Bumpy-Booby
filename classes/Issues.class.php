<?php

class Issues {

	private static $instance = array();
	protected $project;
	protected $issues = array();
	public $lastcomment;
	public $lastissue;

	public function __construct($project) {
		global $config;
		$this->project = $project;
		$folder = str_replace('%name%', $this->project, FOLDER_PROJECT);
		if (!is_file(DIR_DATABASE.$folder.FILE_ISSUES)) {
			if (!isset($config['projects'][$project])) {
				# Should not happen
				# But we don't want to create an useless new file anyway
				return true;
			}
			$ids_users = array_keys($config['users']);
			$this->issues = array(
				1 => array(
					'id' => 1,
					'summary' => Trad::S_FIRST_ISSUE_TITLE,
					'text' => Trad::S_FIRST_ISSUE,
					'date' => time(),
					'edit' => time(),
					'open' => 1,
					'openedby' => array_shift($ids_users),
					'assignedto' => NULL,
					'status' => DEFAULT_STATUS,
					'labels' => array(),
					'dependencies' => array(),
					'uploads' => array(),
					'mailto' => array(),
					'edits' => array()
				)
			);
			check_dir($folder);
			check_file($folder.FILE_ISSUES, Text::hash($this->issues));
		}
		$this->issues = Text::unhash(get_file($folder.FILE_ISSUES));
	}

	public static function getInstance($project = NULL) {
		if (!$project) { $project = getProject(); }
		if (!isset(self::$instance[$project])) {
			self::$instance[$project] = new Issues($project);
		}
		return self::$instance[$project];
	}

	protected function save() {
		update_file(str_replace(
			'%name%',
			$this->project,
			FOLDER_PROJECT
		).FILE_ISSUES, Text::hash($this->issues));
	}

	public function exists($id) {
		return (
			   isset($this->issues[$id])
			&& !empty($this->issues[$id])
			&& (canAccess('private_issues')
				|| !in_array(PRIVATE_LABEL, $this->issues[$id]['labels']))
		);
	}

	public function html_list($a) {
		global $config;
		$html = '';
		foreach ($a as $issue) {
			$labels = '';
			foreach ($issue['labels'] as $la) {
				$lab = $config['labels'][$la];
				$url = Url::parse($this->project.'/labels/'.$la);
				$labels .= '<a href="'.$url.'" class="label" '
					.'style="background-color:'.$lab['color'].'">'
					.$lab['name']
				.'</a>';
			}
			if (!empty($labels)) {
				$labels = '<div class="labels">'.$labels.'</div>';
			}
			$nbcomments = 0;
			foreach ($issue['edits'] as $e) {
				if (!empty($e) && $e['type'] == 'comment') { $nbcomments++; }
			}
			$id = $issue['id'];
			$url = Url::parse($this->project.'/issues/'.$id);
			$by = str_replace(array('%user%', '%time%'), array(
				Text::username($issue['openedby'], true),
				Text::ago($issue['date'])
			), Trad::S_ISSUE_CREATED);
			$color = $config['statuses'][$issue['status']]['color'];
			$class = ($issue['open']) ? 'a-summary' : 'a-summary closed';
			$html .= '<div class="div-preview-issue">'
				.'<div class="table">'
					.'<div class="cell-left">'
						.'<a class="a-id-issue" href="'.$url.'" '
							.'style="background:'.$color.'">'
							.'<span>#</span>'.$id
						.'</a>'
					.'</div>'
					.'<div class="cell-right">'
						.'<a class="'.$class.'" href="'.$url.'">'
							.htmlspecialchars($issue['summary'])
						.'</a>'
						.'<span class="grey">'
							.$by.'&nbsp;–&nbsp;'
							.'<a href="'.$url.'#comments" class="a-nb-comment">'
								.'<i class="icon-comment"></i>'.$nbcomments
							.'</a>'
						.'</span>'
						.$labels
					.'</div>'
				.'</div>'
			.'</div>';
		}
		$html = Text::remove_blanks($html);
		return $html;
	}
	public static function get_preview_edit($e) {
		global $config;
		$ret = '<div class="box box-update" id="e-'.$e['id'].'">'
			.'<div class="top">';
		if ($e['type'] == 'status') {
			$color = $config['statuses'][$e['changedto']]['color'];
			$ret .= str_replace(
				array('%user%', '%time%', '%status%'),
				array(
					Text::username($e['by'], true),
					Text::ago($e['date']),
					'<span class="btn-status" style="background:'.$color.'">'
						.Text::status($e['changedto'], $e['assignedto'])
					.'</span>'
				),
				Trad::S_ISSUE_STATUS_UPDATED);
		}
		if ($e['type'] == 'comment') {
			$ret .= str_replace(
				array('%adj%', '%user%', '%time%'),
				array(
					'<span class="btn-commented">'.Trad::W_COMMENTED.'</span>',
					Text::username($e['by'], true),
					Text::ago($e['date'])
				),
				Trad::S_ISSUE_UPDATED);
		}
		elseif ($e['type'] == 'open' && $e['changedto']) {
			$ret .= str_replace(
				array('%adj%', '%user%', '%time%'),
				array(
					'<span class="btn-open">'.Trad::W_REOPENED.'</span>',
					Text::username($e['by'], true),
					Text::ago($e['date'])
				),
				Trad::S_ISSUE_UPDATED);
		}
		elseif ($e['type'] == 'open') {
			$ret .= str_replace(
				array('%adj%', '%user%', '%time%'),
				array(
					'<span class="btn-closed">'.Trad::W_CLOSED.'</span>',
					Text::username($e['by'], true),
					Text::ago($e['date'])
				),
				Trad::S_ISSUE_UPDATED);
		}
		elseif ($e['type'] == 'newissue') { # not really an edit
			$ret .= str_replace(
				array('%adj%', '%user%', '%time%'),
				array(
					'<span class="btn-open">'.Trad::W_OPENED.'</span>',
					Text::username($e['by'], true),
					Text::ago($e['date'])
				),
				Trad::S_ISSUE_UPDATED);
		}
		return $ret.'</div></div>';
	}

	public function get($id = NULL) {
		if (!canAccess('issues') || !$this->exists($id)) { return false; }
		return $this->issues[$id];
	}

	public function getAll() {
		if (!canAccess('issues')) { return false; }
		$ret = array();
		foreach ($this->issues as $k => $v) {
			if (!empty($v)
				&& (!in_array(PRIVATE_LABEL, $v['labels'])
					|| canAccess('private_issues'))
			) { $ret[$k] = $v; }
		}
		return $ret;
	}

	public function new_issue($post) {
		global $config;
		if (!canAccess('new_issue')
			|| !isset($post['issue_summary'])
			|| !isset($post['issue_text'])
			|| !isset($post['uploads'])
			|| !isset($post['token'])
			|| empty($post['issue_summary'])
			|| empty($post['issue_text'])
		) { return Trad::A_ERROR_FORM; }
		if (canAccess('update_issue')
			&& (!isset($post['issue_status'])
				|| !isset($post['issue_assignedto'])
				|| !isset($post['issue_dependencies'])
				|| !isset($post['issue_labels'])
				|| !array_key_exists($post['issue_status'], $config['statuses'])
			)
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		if ($config['loggedin']
			&& isset($config['users'][$_SESSION['id']])
		) {
			$by = intval($_SESSION['id']);
		}
		else { $by = NULL; }

		$uploads = array();
		if (canAccess('upload') && !empty($post['uploads'])) {
			$uploader = Uploader::getInstance();
			$u = explode(',', $post['uploads']);
			foreach ($u as $v) {
				if ($uploader->get($v)) {
					$uploads[] = $v;
				}
			}
		}

		$status = DEFAULT_STATUS;
		$assignedto = NULL;
		$dependencies = array();
		$labels = array();
		if (canAccess('update_issue')) {
			$status = $post['issue_status'];
			if (array_key_exists($post['issue_assignedto'], $config['users'])) {
				$assignedto = $post['issue_assignedto'];
			}
			$dp = explode(',', $post['issue_dependencies']);
			foreach ($dp as $d) {
				$d = str_replace(array('#', ' '), '', $d);
				if ($this->exists($d)) {
					$dependencies[] = $d;
				}
			}
			$la = explode(',', $post['issue_labels']);
			foreach ($la as $l) {
				if (isset($config['labels'][$l])
					&& (canAccess('private_issues') || $l != PRIVATE_LABEL)
				) {
					$labels[] = $l;
				}
			}
		}

		$id = Text::newKey($this->issues);

		$this->issues[$id] = array(
			'id' => $id,
			'summary' => $post['issue_summary'],
			'text' => $post['issue_text'],
			'date' => time(),
			'edit' => time(),
			'open' => true,
			'openedby' => $by,
			'assignedto' => $assignedto,
			'status' => $status,
			'labels' => $labels,
			'dependencies' => $dependencies,
			'uploads' => $uploads,
			'mailto' => array(),
			'edits' => array()
		);
		$this->lastissue = $id;

		if ($by !== NULL
			&& $config['users'][$by]['notifications'] != 'never'
		) {
			$this->update_mailto($id, array($by => true));
		}
		$mail = new Mail(Trad::M_NEW_ISSUE_O, Trad::M_NEW_ISSUE);
		$mail->replace(array(
			'%id%' => $id,
			'%summary%' => $post['issue_summary'],
			'%by%' => Text::username($by),
			'%url%' => Url::parse($this->project.'/issues/'.$id)
		));
		foreach ($config['users'] as $u) {
			if ($u['notifications'] == 'never') { continue; }
			if ($u['notifications'] == 'me') { continue; }
			if ($u['id'] == $by) { continue; }
			$mail->replace_personal(array(
				'%username%' => Text::username($u['id'])
			));
			$mail->send($u['email']);
		}

		$this->save();
		return true;
	}

	public function edit_issue($id, $edits) {
		global $config;
		if (!canAccess('edit_issue')
			|| !isset($edits['text'])
			|| !isset($edits['summary'])
			|| !isset($edits['token'])
			|| !$this->exists($id)
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($edits['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		$this->issues[$id]['summary'] = $edits['summary'];
		$this->issues[$id]['text'] = $edits['text'];

		$this->save();
		return true;
	}

	public function delete_issue($id, $edits) {
		global $config;
		if (!canAccess('edit_issue')
			|| !isset($edits['token'])
			|| !$this->exists($id)
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($edits['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		# We don't destroy it because we don't want to give its ID to a new comment
		$this->issues[$id] = array();

		$this->save();
		return true;
	}

	public function update_issue($id, $edits) {
		global $config;
		if (!canAccess('update_issue')
			|| !isset($edits['issue_status'])
			|| !isset($edits['issue_assignedto'])
			|| !isset($edits['issue_dependencies'])
			|| !isset($edits['issue_labels'])
			|| !isset($edits['issue_open'])
			|| !isset($edits['token'])
			|| !$this->exists($id)
			|| !array_key_exists($edits['issue_status'], $config['statuses'])
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($edits['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		$status = $edits['issue_status'];

		if (empty($edits['issue_assignedto'])
			|| !array_key_exists($edits['issue_assignedto'], $config['users']))
			{ $assignedto = NULL; }
		else { $assignedto = $edits['issue_assignedto']; }

		$dependencies = array();
		if (!empty($edits['issue_dependencies'])) {
			$depends = explode(',', $edits['issue_dependencies']);
			foreach ($depends as $d) {
				$d = str_replace(array('#', ' '), '', $d);
				# if the user can't see private issues, we do not want to
				# delete private dependencies
				if ($this->exists($d, false)) {
					$dependencies[] = $d;
				}
			}
		}

		$labels = array();
		if (!empty($edits['issue_labels'])) {
			$la = explode(',', $edits['issue_labels']);
			foreach ($la as $v) {
				if (isset($config['labels'][$v])) {
					if ($v != PRIVATE_LABEL || canAccess('private_issues')) {
						$labels[] = $v;
					}
				}
			}
		}

		$open = $this->issues[$id]['open'];
		if ($edits['issue_open'] == 'open') { $open = true; }
		elseif ($edits['issue_open'] == 'closed') { $open = false; }

		if ($config['loggedin']
			&& isset($config['users'][$_SESSION['id']])
		) {
			$by = intval($_SESSION['id']);
		}
		else { $by = NULL; }

		$i = &$this->issues[$id];
		if ($status != $i['status'] || $assignedto != $i['assignedto']) {
			$eid = Text::newKey($i['edits']);
			$i['edits'][$eid] = array(
				'id' => $eid,
				'type' => 'status',
				'changedto' => $status,
				'assignedto' => $assignedto,
				'by' => $by,
				'date' => time()
			);
			$i['edit'] = time();
		}
		if ($open != $i['open']) {
			$eid = Text::newKey($i['edits']);
			$i['edits'][Text::newKey($i['edits'])] = array(
				'id' => $eid,
				'type' => 'open',
				'changedto' => $open,
				'by' => $by,
				'date' => time()
			);
			$i['edit'] = time();
		}

		$i['status'] = $status;
		$i['assignedto'] = $assignedto;
		$i['dependencies'] = $dependencies;
		$i['labels'] = $labels;
		$i['open'] = $open;
		unset($i);

		$this->save();
		return true;
	}

	public function check_statuses($statuses) {
		foreach ($this->issues as $k => $i) {
			if (empty($i)) { continue; }
			if (!array_key_exists($i['status'], $statuses)) {
				$this->issues[$k]['status'] = DEFAULT_STATUS;
			}
			foreach ($i['edits'] as $l => $e) {
				if (!empty($e)
					&& $e['type'] == 'status'
					&& !array_key_exists($e['changedto'], $statuses)
				) {
					$this->issues[$k]['edits'][$l] = array();
				}
			}
		}
		$this->save();
	}

	public function check_labels($labels) {
		foreach ($this->issues as $k => $i) {
			if (empty($i)) { continue; }
			$l = array();
			foreach ($i['labels'] as $v) {
				if (array_key_exists($v, $labels)) {
					$l[] = $v;
				}
			}
			$this->issues[$k]['labels'] = $l;
		}
		$this->save();
	}

	public function check_uploads($name) {
		foreach ($this->issues as $k => $i) {
			if (empty($i)) { continue; }
			$u = array();
			foreach ($i['uploads'] as $v) {
				if ($v != $name) { $u[] = $v; }
			}
			$this->issues[$k]['uploads'] = $u;
			foreach ($i['edits'] as $k2 => $e) {
				if (empty($e)) { continue; }
				$u = array();
				foreach ($e['uploads'] as $v) {
					if ($v != $name) { $u[] = $v; }
				}
				$this->issues[$k]['edits'][$k2]['uploads'] = $u;
			}
		}
		$this->save();
	}

	public function comment($id, $post) {
		global $config;
		if (!canAccess('post_comment')
			|| !isset($post['comment'])
			|| empty($post['comment'])
			|| !isset($post['uploads'])
			|| !isset($post['token'])
			|| !$this->exists($id)
			|| !$this->issues[$id]['open']
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		if ($config['loggedin']
			&& isset($config['users'][$_SESSION['id']])
		) {
			$by = intval($_SESSION['id']);
		}
		else { $by = NULL; }

		$uploads = array();
		if (canAccess('upload') && !empty($post['uploads'])) {
			$uploader = Uploader::getInstance();
			$u = explode(',', $post['uploads']);
			foreach ($u as $v) {
				if ($uploader->get($v)) {
					$uploads[] = $v;
				}
			}
		}

		$cid = Text::newKey($this->issues[$id]['edits']);
		$comment = array(
			'id' => $cid,
			'type' => 'comment',
			'by' => $by,
			'date' => time(),
			'text' => $post['comment'],
			'uploads' => $uploads
		);
		$this->issues[$id]['edits'][$cid] = $comment;
		$this->issues[$id]['edit'] = time();
		$this->lastcomment = $cid;

		if ($by !== NULL
			&& $config['users'][$by]['notifications'] != 'never'
			&& !isset($this->issues[$id]['mailto'][$by])
		) {
			$this->update_mailto($id, array($by => true));
		}
		$mailto = $this->issues[$id]['mailto'];
		$mail = new Mail(Trad::M_NEW_COMMENT_O, Trad::M_NEW_COMMENT);
		$mail->replace(array(
			'%id%' => $id,
			'%summary%' => $this->issues[$id]['summary'],
			'%by%' => Text::username($by),
			'%url%' => Url::parse(
				$this->project.'/issues/'.$id,
				array(),
				'e-'.$cid
			)
		));
		foreach ($config['users'] as $u) {
			if ($u['notifications'] == 'never'
				|| $u['notifications'] == 'me'
			) {
				if (!isset($mailto[$u['id']])) { continue; }
				elseif (!$mailto[$u['id']]) { continue; }
			}
			if ($u['id'] == $by) { continue; }
			$mail->replace_personal(array(
				'%username%' => Text::username($u['id'])
			));
			$mail->send($u['email']);
		}

		$this->save();
		return true;
	}

	public function edit_comment($id, $edits) {
		global $config;
		if (!isset($edits['comment'])
			|| !isset($edits['comment_id'])
			|| !isset($edits['token'])
			|| !$this->exists($id)
			|| !isset($this->issues[$id]['edits'][$edits['comment_id']])
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($edits['token'])) {
			return Trad::A_ERROR_TOKEN;
		}
		if (empty($edits['comment'])) { return true; }

		$comment = &$this->issues[$id]['edits'][$edits['comment_id']];

		if (!canAccess('edit_comment')
			&& (!$config['loggedin'] || $_SESSION['id'] != $comment['by'])
		) { return Trad::A_ERROR_FORM; }
		
		if (empty($edits['comment'])) {
			return Trad::A_ERROR_EMPTY_COMMENT;
		}
		$comment['text'] = $edits['comment'];
		unset($comment);

		$this->save();
		return true;
	}

	public function delete_comment($id, $post) {
		global $config;
		if (!$this->exists($id)
			|| !isset($post['comment_id'])
			|| !isset($post['token'])
			|| !isset($this->issues[$id]['edits'][$post['comment_id']])
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) {
			return Trad::A_ERROR_TOKEN;
		}

		$comment = $this->issues[$id]['edits'][$post['comment_id']];
		if (!canAccess('edit_comment')
			&& (!$config['loggedin'] || $_SESSION['id'] != $comment['by'])
		) { return Trad::A_ERROR_FORM; }

		// We don't destroy it because we don't want to give its ID to a new comment
		$this->issues[$id]['edits'][$post['comment_id']] = array();
		
		$this->save();
		return true;
	}

	public function change_notif($id, $post) {
		global $config;
		if (!$this->exists($id)
			|| !isset($post['change_notif'])
			|| !isset($post['token'])
			|| !$config['loggedin']
			|| !isset($config['users'][$_SESSION['id']])
		) { return Trad::A_ERROR_FORM; }
		if (!tokenOk($post['token'])) {
			return Trad::A_ERROR_TOKEN;
		}
		$change = ($post['change_notif'] == '1') ? true : false;
		$this->update_mailto($id, array(intval($_SESSION['id']) => $change));
		
		$this->save();
		return true;
	}

	protected function update_mailto($id, $add = array()) {
		global $config;
		if (!isset($this->issues[$id])
			|| !is_array($add)
		) {
			return false;
		}
		$mailto = &$this->issues[$id]['mailto'];
		foreach ($add as $k => $v) {
			if (!isset($config['users'][$k])) { continue; }
			$mailto[$k] = $v;
		}
		unset($mailto);
		return true;
	}

}

?>