<?php

$issues = Issues::getInstance();

if (isset($_GET['id']) && $issue = $issues->get($_GET['id'])) {

$form_pc_c = ''; $form_pc_a = '';
$form_ec_c = ''; $form_ec_a = ''; $form_ec_id = '';
$form_ei_a = '';
if (isset($_POST['post_comment'])) {
	$ans = $issues->comment($issue['id'], $_POST);
	if ($ans === true) {
		$url = Url::parse(getProject().'/issues/'.$issue['id'], array(), 'e-'.$issues->lastcomment);
		header('Location: '.$url);
		exit;
	}
	$form_pc_a = '<div class="alert alert-error">'.$ans.'</div>';
	$form_pc_c = htmlspecialchars($_POST['comment']);
}
elseif (isset($_POST['delete_comment'])) {
	$ans = $issues->delete_comment($issue['id'], $_POST);
	if ($ans === true) {
		$this->addAlert(Trad::A_SUCCESS_DELETE_COMMENT, 'alert-success');
		# Need to update the issue
		$issue = $issues->get($_GET['id']);
	}
	else {
		$this->addAlert($ans);
	}
}
elseif (isset($_POST['edit_comment'])) {
	$ans = $issues->edit_comment($issue['id'], $_POST);
	if ($ans === true) {
		# Need to update the issue
		$issue = $issues->get($_GET['id']);
	}
	else {
		$form_ec_a = '<div class="alert alert-error">'.$ans.'</div>';
		$form_ec_c = htmlspecialchars($_POST['comment']);
		$form_ec_id = intval($_POST['comment_id']);
	}
}
elseif (isset($_POST['update_content'])) {
	$ans = $issues->edit_issue($issue['id'], $_POST);
	if ($ans === true) {
		# Need to update the issue
		$issue = $issues->get($_GET['id']);
	}
	else {
		$form_ei_a = '<div class="alert alert-error">'.$ans.'</div>';
	}
}
elseif (isset($_POST['update_details'])) {
	$ans = $issues->update_issue($issue['id'], $_POST);
	if ($ans === true) {
		# Need to update the issue
		$issue = $issues->get($_GET['id']);
	}
	else {
		$this->addAlert($ans);
	}
}
elseif (isset($_POST['delete_issue'])) {
	$ans = $issues->delete_issue($issue['id'], $_POST);
	if ($ans === true) {
		$_SESSION['alert'] = array('text' => Trad::A_SUCCESS_DELETE_ISSUE, 'type' => 'alert-success');
		header('Location: '.Url::parse(getProject().'/issues'));
		exit;
	}
	else {
		$this->addAlert($ans);
	}
}
elseif (isset($_POST['notifications'])) {
	$ans = $issues->change_notif($issue['id'], $_POST);
	if ($ans === true) {
		# Need to update the issue
		$issue = $issues->get($_GET['id']);
	}
	else {
		$this->addAlert($ans);
	}
}

$title = Trad::W_ISSUE.' #'.$issue['id'].': ';
$title .= str_replace(array('<', '>'), '', $issue['summary']);
$token = getToken();

################################### Display edits ####################################

$edits = '';
foreach ($issue['edits'] as $k => $e) {
	if (empty($e)) { continue; }
	$id = 'e-'.$e['id'];
	if ($e['type'] == 'comment') {
		$manage = ''; $edit_comment = '';
		$comment = ($e['id'] == $form_ec_id) ? $form_ec_c : htmlspecialchars($e['text']);
		$displ_f = ($e['id'] == $form_ec_id) ? 'block' : 'none';
		$displ_c = ($e['id'] == $form_ec_id) ? 'none' : 'block';
		$alert = ($e['id'] == $form_ec_id) ? $form_ec_a : '';
		if (canAccess('edit_comment') || ($config['loggedin'] && $_SESSION['id'] == $e['by'])) {
			$manage = '<div class="manage"><a href="javascript:;" class="a-edit a-icon-hover"><i class="icon-edit"></i></a>&nbsp;<a href="javascript:;" class="a-remove-comment a-icon-hover"><i class="icon-trash"></i></a></div>';
			$edit_comment = '
<div class="inner-form i-display" style="display:'.$displ_f.'">
	<form action="'.Url::parse(getProject().'/issues/'.$issue['id'], array(), $id).'" method="post">
		'.$alert.'
		<textarea name="comment" rows="6">'.$comment.'</textarea>
		<div class="form-actions">
			<a href="javascript:;" class="btn btn-cancel">'.Trad::V_CANCEL.'</a>
			<button type="submit" class="btn btn-primary">'.Trad::V_UPDATE.'</button>
		</div>
		<input type="hidden" name="comment_id" value="'.$e['id'].'" />
		<input type="hidden" name="token" value="'.$token.'" />
		<input type="hidden" name="edit_comment" value="1" />
	</form>
</div>
			';
		}
		$uploads = '';
		if (canAccess('view_upload') && !empty($e['uploads'])) {
			$uploads = '<div class="div-uploads">';
			$uploader = Uploader::getInstance();
			foreach ($e['uploads'] as $v) {
				$u = $uploader->get($v);
				$src = Text::upload($u['name']);
				$uploads .= '<a href="'.$src.'">';
				if ($u['mime-type']) {
					$uploads .= '<img src="'.$src.'" class="upload-tiny" />';
				}
				else {
					$uploads .= '<i class="icon-file"></i> '.htmlspecialchars($u['display']);
				}
				$uploads .= '</a>';
			}
			$uploads .= '</div>';
		}
		$edits .= '
<div class="box-identicon">
	<img src="'.Text::identicon(Text::username($e['by'])).'" alt="'.Text::username($e['by']).' '.Trad::W_PROFILEPIC.'" />
</div>
<div class="box box-comment" id="'.$id.'">
	<div class="top">
		'.$manage.'
		'.str_replace(
			array('%adj%', '%user%', '%time%'),
			array(
				'<span class="btn-commented">'.Trad::W_COMMENTED.'</span>',
				Text::username($e['by'], true),
				Text::ago($e['date'])
			),
			Trad::S_ISSUE_UPDATED).'
	</div>
	<div class="inner t-display" style="display:'.$displ_c.'">
		<div class="text-container">'.Text::markdown($e['text']).'</div>
		'.$uploads.'
	</div>
	'.$edit_comment.'
</div>
		';
	}
	else {
		$edits .= Issues::get_preview_edit($e);
	}
}



############################# Display post comment form #############################

$form_comment = '';
if (canAccess('post_comment') && $issue['open']) {
	$message = '';
	if (!$config['loggedin']
		&& canAccess('signup')
		&& in_array(DEFAULT_GROUP, $config['permissions']['post_comment']))
	{
		$message = '<p class="help">'.Trad::A_SHOULD_LOGIN.'</p>';
	}
	$form_comment = '
<div class="box box-post-comment">
	<div class="top">
		<div class="manage"><a href="javascript:;" class="a-help-markdown a-icon-hover"><i class="icon-question-sign"></i></a></div>
		<i class="icon-comment"></i>'.Trad::S_COMMENT_LEAVE.'
	</div>
	<div class="inner-form">
		<div class="div-help-markdown">'.Trad::HELP_MARKDOWN.'</div>
		'.$form_pc_a.'
		<form action="'.Url::parse(getProject().'/issues/'.$issue['id'], array(), 'post_comment').'" method="post" id="post_comment">
			<textarea name="comment" rows="6" required>'.$form_pc_c.'</textarea>
			<div class="preview text-container" style="display:none"></div>
			'.$message.'
			<div class="form-actions">
				<button type="button" class="btn btn-preview">'.Trad::V_PREVIEW.'</button>
				<button type="submit" class="btn btn-primary">'.Trad::V_COMMENT.'</button>
			</div>
			<input type="hidden" name="uploads" value="" />
			<input type="hidden" name="token" value="'.$token.'" />
			<input type="hidden" name="post_comment" value="1" />
		</form>
	</div>
</div>
	';
	if (canAccess('upload')) {
		$uploads = (isset($_POST['post_comment']) && isset($_POST['uploads'])) ?
			explode(',', $_POST['uploads']):
			array();
		$form_comment .= Uploader::get_html('.box-post-comment form', $uploads);
	}
}
elseif ($issue['open']
	&& !$config['loggedin']
	&& canAccess('signup')
	&& in_array(DEFAULT_GROUP, $config['permissions']['post_comment'])) {
	$form_comment = '
		<div class="box">
			<div class="top">
				<i class="icon-comment"></i> '.Trad::S_COMMENT_LEAVE.'
			</div>
			<div class="inner"><p>'.Trad::A_PLEASE_LOGIN_COMMENT.'</p></div>
		</div>
	';
}




################################### Display issue ###################################

$safe_title = htmlspecialchars($issue['summary']);
if (!$issue['open']) { $safe_title = '<del>'.$safe_title.'</del>'; }

$labels = '';
foreach ($issue['labels'] as $l) {
	$labels .= '<a href="'.Url::parse(getProject().'/labels/'.$l).'" class="label" style="background-color:'.$config['labels'][$l]['color'].'">'.$config['labels'][$l]['name'].'</a>';
	}

$dependencies = '';
if (!empty($issue['dependencies'])) {
	$d = array();
	foreach ($issue['dependencies'] as $v) {
		# Do not display deleted issues or private issues
		if ($issues->exists($v)) {
			$d[] = '<a href="'.Url::parse(getProject().'/issues/'.$v).'" class="a-issue"><span>#</span>'.$v.'</a>';
		}
	}
	$dependencies = implode(', ', $d);
}

$statuses = array();
foreach ($config['statuses'] as $k => $v) {
	$statuses[$k] = $v['name'];
}
$users = array('nobody' => Trad::W_NOBODY);
foreach ($config['users'] as $k => $u) {
	$users[$k] = htmlspecialchars($u['username']);
}


$manage_issue = '';
$update_issue = '';
if (canAccess('update_issue')) {
	$manage_issue = '<li><a href="javascript:;" class="a-edit-content">'.Trad::V_UPDATE_CONTENT.'</a></li>';
	if ($issue['open']) {
		$btn = '<button type="submit" class="btn btn-close btn-primary">'.str_replace('%adjective%', Trad::V_CLOSE, Trad::V_UPDATE_AND).'</button>';
	}
	else {
		$btn = '<button type="submit" class="btn btn-reopen btn-primary">'.str_replace('%adjective%', Trad::V_REOPEN, Trad::V_UPDATE_AND).'</button>';	
	}
	$l = '';
	foreach ($config['labels'] as $k => $v) {
		if (canAccess('private_issues') || $k != PRIVATE_LABEL) { 
			if (in_array($k, $issue['labels'])) { $selected = 'selected'; }
			else { $selected = 'unselected'; }
			$l .= '<a href="javascript:;" class="label '.$selected.'" style="background-color:'.$v['color'].'" data-id="'.$k.'">'.$v['name'].'</a>';
		}
	}
	$update_issue = '
<div class="inner-form div-right i-display" style="display:none">
	<form action="'.Url::parse(getProject().'/issues/'.$issue['id']).'" method="post" class="form-details">
		<input type="hidden" name="update_details" value="1" />
		<input type="hidden" name="token" value="'.$token.'" />
		<label for="issue_status">'.Trad::F_STATUS.'</label>
		<select name="issue_status" class="select-status">'.Text::options($statuses, $issue['status']).'</select>
		<select name="issue_assignedto" class="select-users">'.Text::options($users, $issue['assignedto']).'</select>
		<label for="issue_dependencies">'.Trad::F_RELATED.'</label>
		<input type="text" name="issue_dependencies" value="'.((empty($issue['dependencies'])) ? '' : '#'.implode(', #', $issue['dependencies'])).'" placeholder="#1, #2, ..." />
		<label>'.Trad::F_LABELS2.'</label>
		<p class="p-edit-labels">'.$l.'</p>
		<input type="hidden" name="issue_labels" value="" />
		<input type="hidden" name="issue_open" value="'.$issue['open'].'" />
		<p class="form-actions">
			<a href="javascript:;" class="btn btn-cancel">'.Trad::V_CANCEL.'</a>
			<button type="submit" class="btn btn-primary">'.Trad::V_UPDATE.'</button>
			'.$btn.'
		</p>
	</form>
</div>
	';
}
$edit_issue = '';
$displ_f = (empty($form_ei_a)) ? 'none'            : 'table-cell';
$displ_t = (empty($form_ei_a)) ? 'table-cell'           : 'none';
$text =    (empty($form_ei_a)) ? $issue['text']    : $_POST['text'];
$summary = (empty($form_ei_a)) ? $issue['summary'] : $_POST['summary'];
if (canAccess('edit_issue')) {
	$manage_issue .= '<li><a href="javascript:;" class="a-edit-details">'.Trad::V_UPDATE_DETAILS.'</a></li><li><a href="javascript:;" class="a-remove-issue">'.Trad::V_REMOVE_ISSUE.'</a></li>';
	$edit_issue = '
<div class="inner-form div-left i-display" style="display:'.$displ_f.'">
	<form action="'.Url::parse(getProject().'/issues/'.$issue['id']).'" method="post">
		<input type="hidden" name="update_content" value="1" />
		<input type="hidden" name="token" value="'.$token.'" />
		'.$form_ei_a.'
		<input type="text" name="summary" value="'.htmlspecialchars($summary).'" required />
		<textarea name="text" rows="12" required>'.htmlspecialchars($text).'</textarea>
		<div class="form-actions">
			<a href="javascript:;" class="btn btn-cancel">'.Trad::V_CANCEL.'</a>
			<button type="submit" class="btn btn-primary">'.Trad::V_UPDATE.'</button>
		</div>
	</form>
</div>
	';
}
$uploads = '';
if (canAccess('view_upload') && !empty($issue['uploads'])) {
	$uploads = '<div class="div-uploads">';
	$uploader = Uploader::getInstance();
	foreach ($issue['uploads'] as $v) {
		$u = $uploader->get($v);
		$src = Text::upload($u['name']);
		$uploads .= '<a href="'.$src.'">';
		if ($u['mime-type']) {
			$uploads .= '<img src="'.$src.'" class="upload-tiny" />';
		}
		else {
			$uploads .= '<i class="icon-file"></i> '.htmlspecialchars($u['display']);
		}
		$uploads .= '</a>';
	}
	$uploads .= '</div>';
}
$notifications = '';
if ($config['email']
	&& $config['loggedin']
	&& isset($config['users'][$_SESSION['id']])
	&& $config['users'][$_SESSION['id']]['email'])
{
	if (isset($issue['mailto'][$_SESSION['id']])) {
		$value = ($issue['mailto'][$_SESSION['id']]) ? 0 : 1;
	}
	else {
		$value = ($config['users'][$_SESSION['id']]['notifications'] == 'always') ? 0 : 1;
	}
	$text = ($value) ? Trad::S_START_NOTIF : Trad::S_STOP_NOTIF;
	$text .= '<input type="hidden" name="change_notif" value="'.$value.'" />';
	$manage_issue .= '<li><a href="javascript:;" class="a-notifications">'.$text.'</a></li>';
}

$content = '

<h1><span class="span-id" style="background:'.$config['statuses'][$issue['status']]['color'].'"><span>#</span>'.$issue['id'].'</span>'.$safe_title.'</h1>

<div class="box box-issue">
	<div class="div-table">
		<div class="inner div-left t-display" style="display:'.$displ_t.'">
			<div class="text-container">'.Text::markdown($issue['text']).'</div>
			'.$uploads.'
		</div>
		'.$edit_issue.'
		<div class="inner-form div-right t-display">
			<div class="div-status" style="background:'.$config['statuses'][$issue['status']]['color'].'">'.Text::status($issue['status'], $issue['assignedto']).'</div>
			<p class="p-text">'.str_replace(
				array('%adj%', '%user%', '%time%'),
				array(
					Trad::W_OPENED,
					Text::username($issue['openedby'], true),
					Text::ago($issue['date'])
				),
				Trad::S_ISSUE_UPDATED).'
			</p>
			'.((empty($dependencies)) ? '' : '<p class="p-text">'.Trad::F_RELATED.' '.$dependencies.'</p>').'
			<p class="p-labels">'.$labels.'</p>
			<form action="'.Url::parse(getProject().'/issues/'.$issue['id']).'" method="post">
				<input type="hidden" name="action" value="1" />
				<input type="hidden" name="token" value="'.$token.'" />
				<ul class="ul-actions">'.$manage_issue.'</ul>
			</form>
		</div>
		'.$update_issue.'
	</div>
</div>

<div class="div-table-comments" id="comments">
	<div class="div-comments">'.$edits.'</div>
	<div class="div-post-comment"><div class="div-affix">'.$form_comment.'</div></div>
</div>

';

}
else {
	$load = 'error/404';
}


?>