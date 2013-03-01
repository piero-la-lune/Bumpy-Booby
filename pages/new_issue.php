<?php

$form_s = (isset($_POST['issue_summary'])) ?
	htmlspecialchars($_POST['issue_summary']):
	'';
$form_t = (isset($_POST['issue_text'])) ?
	htmlspecialchars($_POST['issue_text']):
	'';
$form_st = (isset($_POST['issue_status'])) ?
	$_POST['issue_status']:
	DEFAULT_STATUS;
$form_u = (isset($_POST['issue_assignedto'])) ?
	$_POST['issue_assignedto']:
	DEFAULT_USER;
$form_d = (isset($_POST['issue_dependencies'])) ?
	htmlspecialchars($_POST['issue_dependencies']):
	'';
$form_l = (isset($_POST['issue_labels'])) ?
	explode(',', $_POST['issue_labels']):
	array();
$form_up = (isset($_POST['uploads'])) ?
	explode(',', $_POST['uploads']):
	array();
$token = getToken();

if (isset($_POST['new_issue'])) {
	$issues = Issues::getInstance();
	$ans = $issues->new_issue($_POST);
	if ($ans === true) {
		header('Location: '
			.Url::parse(getProject().'/issues/'.$issues->lastissue));
		exit;
	}
	$this->addAlert($ans);
}

$title = Trad::T_NEW_ISSUE;

$should_login = '';
if (!$config['loggedin']
	&& canAccess('signup')
	&& in_array(DEFAULT_GROUP, $config['permissions']['new_issue'])
) {
	$should_login = '<p class="help">'.Trad::A_SHOULD_LOGIN.'</p>';
}

$settings = '';
if (canAccess('update_issue')) {
	$statuses = array();
	foreach ($config['statuses'] as $k => $v) {
		$statuses[$k] = $v['name'];
	}
	$users = array(DEFAULT_USER => Trad::W_NOBODY);
	foreach ($config['users'] as $k => $u) {
		$users[$k] = htmlspecialchars($u['username']);
	}
	$labels = '';
	foreach ($config['labels'] as $k => $v) {
		if (canAccess('private_issues') || $k != PRIVATE_LABEL) {
			$selected = (in_array($k, $form_l)) ?
				'label selected':
				'label unselected';
			$labels .= '<a href="javascript:;" class="'.$selected.'" style="'
				.'background-color:'.$v['color'].'" data-id="'.$k.'">'
					.$v['name']
			.'</a>';
		}
	}
	$settings = '<div class="inner-form div-right">'
		.'<label for="issue_status">'.Trad::F_STATUS.'</label>'
		.'<select name="issue_status" class="select-status">'
			.Text::options($statuses, $form_st)
		.'</select>'
		.'<select name="issue_assignedto" class="select-users">'
			.Text::options($users, $form_u)
		.'</select>'
		.'<label for="issue_dependencies">'.Trad::F_RELATED.'</label>'
		.'<input type="text" name="issue_dependencies" value="'.$form_d.'" '
			.'placeholder="#1, #2, ..." />'
		.'<label>'.Trad::F_LABELS2.'</label>'
		.'<p class="p-edit-labels">'.$labels.'</p>'
		.'<input type="hidden" name="issue_labels" value="" />'
	.'</div>';
}

$content = '<h1>'.Trad::T_NEW_ISSUE.'</h1>'
.'<div class="box box-new-issue">'
	.'<div class="top">'
		.'<div class="manage">'
			.'<a href="javascript:;" class="a-help-markdown a-icon-hover">'
				.'<i class="icon-question-sign"></i>'
			.'</a>'
		.'</div>'
		.'<i class="icon-pencil"></i>'
		.Trad::F_WRITE
	.'</div>'
	.'<form action="'.Url::parse(getProject().'/issues/new').'" method="post" '
		.'class="div-table">'
		.'<div class="inner-form div-left">'
			.'<div class="div-help-markdown">'.Trad::HELP_MARKDOWN.'</div>'
			.'<input type="text" name="issue_summary" value="'.$form_s.'" '
				.'placeholder="'.Trad::F_SUMMARY.'" required />'
			.'<textarea name="issue_text" rows="12" '
				.'placeholder="'.Trad::F_CONTENT.'" required>'
				.$form_t
			.'</textarea>'
			.'<div class="preview" style="display:none"></div>'
			.$should_login
			.'<div class="form-actions">'
				.'<button type="button" class="btn btn-preview">'
					.Trad::V_PREVIEW
				.'</button>'
				.'<button type="submit" class="btn btn-primary">'
					.Trad::V_SUBMIT
				.'</button>'
			.'</div>'
			.'<input type="hidden" name="uploads" value="" />'
			.'<input type="hidden" name="token" value="'.$token.'" />'
			.'<input type="hidden" name="new_issue" value="1" />'
		.'</div>'
		.$settings
	.'</form>'
.'</div>';

if (canAccess('upload')) {
	$content .= Uploader::get_html('.box-new-issue form', $form_up);
}

?>