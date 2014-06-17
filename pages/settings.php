<?php

$title = Trad::T_SETTINGS;

if (isset($_GET['action']) && $_GET['action'] = 'export' && getProject()) {
	header('Content-Type: text/plain');
	$folder = str_replace('%name%', getProject(), FOLDER_PROJECT);
	$issues = Text::unhash(get_file($folder.FILE_ISSUES));
	echo json_encode($issues);
	exit;
}
if (isset($_GET['action']) && $_GET['action'] = 'export_users') {
	header('Content-Type: text/plain');
	$users = Text::unhash(get_file(FILE_USERS));
	echo json_encode($users);
	exit;
}

if (isset($_POST['action']) && isset($_POST['token'])) {
	if (!tokenOk($_POST['token'])) {
		$this->addAlert(Trad::A_ERROR_TOKEN);
	}
	else {
		$settings = new Settings();
		$ans = $settings->changes($_POST);
		if (!empty($ans)) {
			foreach ($ans as $v) {
				$this->addAlert(Trad::settings($v));
			}
		}
		else {
			$this->addAlert(Trad::A_MODIF_SAVED, 'alert-success');
		}
	}
}

$languages = array();
$l = explode(',', LANGUAGES);
foreach ($l as $v) { $languages[$v] = $v; }

$t_projects = '';
function getTrProject($v, $d) {
	return '<tr>'
		.'<td>'
			.'<label>'.Trad::F_NAME.'</label>'
			.'<input type="text" class="input-medium" '
				.'name="project_id[]" value="'.$v.'" required />'
			.'<input type="hidden" name="project_old_id[]" value="'.$v.'" />'
			.'<label>'.Trad::F_DESCRIPTION.'</label>'
			.'<textarea name="project_description[]" rows="3">'
				.htmlspecialchars($d)
			.'</textarea>'
			.'<p class="help">'.Trad::F_TIP_DESCRIPTION.'</p>'
		.'</td>'
		.'<td class="td-actions">'
			.'<div class="btn-group-up-down">'
				.'<a href="javascript:;" class="btn a-up">'
					.'<i class="icon-chevron-up"></i>'
				.'</a>'
				.'<a href="javascript:;" class="btn a-down">'
					.'<i class="icon-chevron-down"></i>'
				.'</a>'
			.'</div>'
			.'<a href="'
				.Url::parse('settings', array('action' => 'export', 'project' => $v))
				.'" class="btn btn-export">'
				.'<i class="icon-share"></i>'
			.'</a>'
			.'<a href="javascript:;" class="btn a-remove-project">'
				.'<i class="icon-trash"></i>'
			.'</a>'
		.'</td>'
	.'</tr>';
}
foreach ($config['projects'] as $k => $v) {
	$t_projects .= getTrProject($k, $v['description']);
}


$t_colors = '';
function getTrColor($v) {
	return '<tr>'
		.'<td>'
			.'<input type="text" class="input-small color" value="'.$v.'" '
				.'name="color_hex[]" required />'
		.'</td>'
		.'<td>'
			.'<span class="label" style="background-color:'.$v.'">'
				.Trad::W_EXAMPLE
			.'</span>'
		.'</td>'
		.'<td class="td-actions">'
			.'<div class="btn-group-up-down">'
				.'<a href="javascript:;" class="btn a-up">'
					.'<i class="icon-chevron-up"></i>'
				.'</a>'
				.'<a href="javascript:;" class="btn a-down">'
					.'<i class="icon-chevron-down"></i>'
				.'</a>'
			.'</div>'
			.'<a href="javascript:;" class="btn a-remove">'
				.'<i class="icon-trash"></i>'
			.'</a>'
		.'</td>'
	.'</tr>';
}
foreach ($config['colors'] as $v) {
	$t_colors.= getTrColor($v);
}


$t_statuses = '';
function getTrStatus($k, $v) {
	global $config;
	$colors = '';
	foreach ($config['colors'] as $co) {
		$colors .= '<a href="javascript:;" class="square" '
			.'style="background-color:'.$co.'"></a>';
	}
	$remove = ''; $disabled = 'readonly';
	if ($k != DEFAULT_STATUS) {
		$remove = '<a href="javascript:;" class="btn a-remove">'
			.'<i class="icon-trash"></i>'
		.'</a>';
		$disabled = '';
	}
	$active = ($v['dashboard']) ? 'active' : '';
	$dashboard = ($v['dashboard']) ? 'true' : 'false';
	return '<tr>'
		.'<td>'
			.'<input type="text" class="input-small" value="'.$k.'" '
				.'name="status_id[]" '.$disabled.' required />'
		.'</td>'
		.'<td>'
			.'<input type="text" class="input-medium input-inline" '
				.'value="'.$v['name'].'" name="status_name[]" required />'
			.'<a href="javascript:;" class="btn input-inline btn-color">'
				.'<span class="square" style="background-color:'
					.$v['color'].'"></span>'
			.'</a>'
			.'<a href="javascript:;" class="btn btn-check input-inline '
				.'btn-home '.$active.'"><i class="icon-home"></i></a>'
			.'<div class="div-pick-color">'
				.$colors
			.'</div>'
			.'<input type="hidden" value="'.$v['color'].'" '
				.'name="status_color[]" class="input-color" />'
			.'<input type="hidden" value="'.$dashboard.'" '
				.'name="status_dashboard[]" class="input-home" />'
		.'</td>'
		.'<td class="td-actions">'
			.'<div class="btn-group-up-down">'
				.'<a href="javascript:;" class="btn a-up">'
					.'<i class="icon-chevron-up"></i>'
				.'</a>'
				.'<a href="javascript:;" class="btn a-down">'
					.'<i class="icon-chevron-down"></i>'
				.'</a>'
			.'</div>'
			.$remove
		.'</td>'
	.'</tr>';
}
foreach ($config['statuses'] as $k => $v) {
	$t_statuses .= getTrStatus($k, $v);
}


$t_labels = '';
function getTrLabel($k, $v, $c) {
	global $config;
	$colors = '';
	foreach ($config['colors'] as $co) {
		$colors .= ' <a href="javascript:;" class="label" '
			.'style="background-color:'.$co.'">'.Trad::W_EXAMPLE.'</a>';
	}
	$remove = ''; $disabled = 'readonly';
	if ($k != PRIVATE_LABEL) {
		$remove = '<a href="javascript:;" class="btn a-remove">'
			.'<i class="icon-trash"></i></a>';
		$disabled = '';
	}
	return '<tr>'
		.'<td>'
			.'<input type="text" class="input-small" value="'.$k.'" '
				.'name="label_id[]" '.$disabled.' required />'
		.'</td>'
		.'<td>'
			.'<input type="text" class="input-small input-inline" '
				.'value="'.$v.'" name="label_name[]" required />'
			.'<a href="javascript:;" class="btn btn-color input-inline">'
				.'<span class="square" style="background-color:'.$c.'"></span>'
			.'</a>'
			.'<span class="label" style="background-color:'.$c.'">'.$v.'</span>'
			.'<div class="div-pick-color">'
				.$colors
			.'</div>'
			.'<input type="hidden" value="'.$c.'" name="label_color[]" '
				.'class="input-color" />'
		.'</td>'
		.'<td class="td-actions">'
			.'<div class="btn-group-up-down">'
				.'<a href="javascript:;" class="btn a-up">'
					.'<i class="icon-chevron-up"></i>'
				.'</a>'
				.'<a href="javascript:;" class="btn a-down">'
					.'<i class="icon-chevron-down"></i>'
				.'</a>'
			.'</div>'
			.$remove
		.'</td>'
	.'</tr>';
}
foreach ($config['labels'] as $k => $v) {
	$t_labels .= getTrLabel($k, $v['name'], $v['color']);
}


$t_groups = '';
function getTrGroup($k, $v) {
	$remove = ''; $disabled = 'readonly';
	if ($k != DEFAULT_GROUP && $k != DEFAULT_GROUP_SUPERUSER) {
		$remove = '<a href="javascript:;" class="btn a-remove">'
			.'<i class="icon-trash"></i></a>';
		$disabled = '';
	}
	return '<tr>'
		.'<td>'
			.'<input type="text" class="input-small" value="'.$k.'" '
				.'name="group_id[]" '.$disabled.' required />'
		.'</td>'
		.'<td>'
			.'<input type="text" class="input-medium" value="'.$v.'" '
				.'name="group_name[]" required />'
		.'</td>'
		.'<td class="td-actions">'
			.'<div class="btn-group-up-down">'
				.'<a href="javascript:;" class="btn a-up">'
					.'<i class="icon-chevron-up"></i>'
				.'</a>'
				.'<a href="javascript:;" class="btn a-down">'
					.'<i class="icon-chevron-down"></i>'
				.'</a>'
			.'</div>'
			.$remove
		.'</td>'
	.'</tr>';
}
foreach ($config['groups'] as $k => $v) {
	$t_groups .= getTrGroup($k, $v);
}


$t_permissions = '';
foreach ($config['projects'] as $k => $v) {
	$buttons = '';
	$a = array_reverse($config['groups']);
	$a['none'] = Trad::W_NOT_LOGGED;
	$a = array_reverse($a);
	foreach ($a as $key => $value) {
		$active = '';
		$int = '0';
		if (in_array($key, $v['can_access'])) {
			$active = ' active'; $int = '1';
		}
		$buttons .= '<button type="button" class="btn btn-check'.$active.'">'
			.$value
			.'<input type="hidden" name="permission_projects_'.$k.'_'.$key.'" '
				.'value="'.$int.'" />'
		.'</button>';
	}
	$t_permissions .= '<tr>'
		.'<td>'
			.'<label>'.str_replace('%name%', $k, Trad::F_PROJECT_X).'</label>'
			.'<p class="p-buttons">'.$buttons.'</p>'
		.'</td>'
	.'</tr>';
}
foreach ($config['permissions'] as $k => $v) {
	$buttons = '';
	$a = array_reverse($config['groups']);
	$a['none'] = Trad::W_NOT_LOGGED;
	$a = array_reverse($a);
	foreach ($a as $key => $value) {
		$active = '';
		$int = '0';
		if (in_array($key, $v)) { $active = ' active'; $int = '1'; }
		$buttons .= '<button type="button" class="btn btn-check'.$active.'">'
			.$value
			.'<input type="hidden" name="permission_'.$k.'_'.$key.'" '
				.'value="'.$int.'" />'
		.'</button>';
	}
	$t_permissions .= '<tr>'
		.'<td>'
			.'<label>'.Trad::permissions($k, 'title').'</label>'
			.'<p class="p-form">'.Trad::permissions($k).'</p>'
			.'<p class="p-buttons">'.$buttons.'</p>'
		.'</td>'
	.'</tr>';
}


$t_users = '';
function getTrUser($u) {
	global $config;
	$select = '';
	foreach ($config['groups'] as $k => $v) {
		$select .= '<option ';
		if ($u['group'] == $k) { $select .= 'selected '; }
		$select .= 'value="'.$k.'">'.$v.'</option>';
	}
	return '<tr>'
		.'<td>'
			.'<div class="t-display">'
				.'<img src="'.Text::identicon($u['username']).'" '
					.'class="identicon" />'
				.'<p>'
					.'<span class="strong">'
						.htmlspecialchars($u['username'])
					.'</span>'
					.'<a href="javascript:;" class="a-user-edit a-icon-hover">'
						.'<i class="icon-edit"></i>'
					.'</a>'
					.'<br />'
					.'<small>'.$config['groups'][$u['group']].'</small>'	.''
				.'</p>'
			.'</div>'
			.'<div class="i-display" style="display:none">'
				.'<label>'.Trad::F_USERNAME.'</label>'
				.'<input type="text" class="input-medium" '
					.'name="user_username[]" '
					.'value="'.htmlspecialchars($u['username']).'" required />'
				.'<label>'.Trad::F_PASSWORD.'</label>'
				.'<input type="password" class="input-medium" '
					.'name="user_password[]" />'
				.'<p class="help">'.Trad::F_TIP_PASSWORD.'</p>'
				.'<label>'.Trad::F_EMAIL.'</label>'
				.'<input type="email" class="input-medium" name="user_email[]" '
					.'value="'.htmlspecialchars($u['email']).'" />'
				.'<label>'.Trad::F_NOTIFICATIONS.'</label>'
				.'<select name="user_notifications[]" class="input-medium">'
					.Text::options(array(
						'never' => Trad::S_NEVER,
						'me' => Trad::S_ME,
						'always' => Trad::S_ALWAYS
					), $u['notifications'])
				.'</select>'
				.'<label>'.Trad::F_GROUP.'</label>'
				.'<select class="input-medium" name="user_group[]">'
					.$select
				.'</select>'
				.'<input type="hidden" name="user_id[]" value="'.$u['id'].'" />'
			.'</div>'
		.'</td>'
		.'<td class="td-actions">'
			.'<div class="btn-group-up-down">'
				.'<a href="javascript:;" class="btn a-up">'
					.'<i class="icon-chevron-up"></i>'
				.'</a>'
				.'<a href="javascript:;" class="btn a-down">'
					.'<i class="icon-chevron-down"></i>'
				.'</a>'
			.'</div>'
			.'<a href="javascript:;" class="btn a-remove">'
				.'<i class="icon-trash"></i>'
			.'</a>'
		.'</td>'
	.'</tr>';
}
foreach ($config['users'] as $u) {
	$t_users .= getTrUser($u);
}


$content = '

	<h1>'.Trad::T_SETTINGS.'</h1>

	<ul class="text-container">
		<li><a href="#t1">'.Trad::T_GLOBAL_SETTINGS.'</a></li>
		<li><a href="#t2">'.Trad::T_APPEARANCE.'</a></li>
		<li><a href="#t3">'.Trad::T_ISSUES.'</a></li>
		<li><a href="#t4">'.Trad::T_GROUPS.'</a></li>
		<li><a href="#t5">'.Trad::T_USERS.'</a></li>
	</ul>

<form method="post" action="'.Url::parse('settings').'">

	<h2 id="t1" class="first">'.Trad::T_GLOBAL_SETTINGS.'</h2>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_GENERAL_SETTINGS.'
		</div>
		<div class="inner-form" style="display:none">
			<label for="title">'.Trad::F_NAME.'</label>
			<input type="text" name="title" id="title" class="input-medium" value="'.$config['title'].'" required />
			<p class="help">'.Trad::F_TIP_NAME.'</p>

			<label for="url">'.Trad::F_URL.'</label>
			<input type="url" name="url" id="url" class="input-medium" value="'.$config['url'].'" required />

			<label for="url">'.Trad::F_URL_REWRITING.'</label>
			<input type="text" name="url_rewriting" id="url_rewriting" class="input-medium" value="'.$config['url_rewriting'].'" />
			<p class="help">'.Trad::F_TIP_URL_REWRITING.'</p>

			<label for="intro">'.Trad::F_INTRO.'</label>
			<textarea name="intro" id="intro" rows="3">'.htmlspecialchars($config['intro']).'</textarea>
			<p class="help">'.str_replace('%name%', DEFAULT_PROJECT, Trad::F_TIP_INTRO).'</p>

			<label for="email">'.Trad::F_EMAIL.'</label>
			<input type="email" name="email" id="email" class="input-medium" value="'.$config['email'].'">
			<p class="help">'.Trad::F_TIP_EMAIL.'</p>

			<label for="language">'.Trad::F_LANGUAGE.'</label>
			<select name="language" id="language" class="input-medium">'.Text::options($languages, $config['language']).'</select>

			<label for="logs_enabled">'.Trad::F_LOGS.'</label>
			<select name="logs_enabled" id="logs_enabled" class="input-medium">'.Text::options(array('true' => Trad::W_ENABLED, 'false' => Trad::W_DISABLED), ($config['logs_enabled'] ? 'true' : 'false')).'</select>
		</div>
	</div>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_PROJECTS.'
		</div>
		<div class="inner-form" style="display:none">
			<table class="table">
				<tbody>
					'.$t_projects.'
				</tbody>
			</table>
			<p class="form-actions"><a href="javascript:;" class="btn btn-add-project">'.Trad::F_ADD_PROJECT.'</a></p>
		</div>
	</div>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_UPLOADS.'
		</div>
		<div class="inner-form" style="display:none">
			<label for="max_size_upload">'.Trad::F_MAX_UPLOAD.'</label>
			<input type="text" name="max_size_upload" id="max_size_upload" class="input-small" value="'.$config['max_size_upload'].'" required />
			<p class="help">'.Trad::F_TIP_MAX_UPLOAD.'</p>

			<label for="allocated_space">'.Trad::F_ALLOCATED_SPACE.'</label>
			<input type="text" name="allocated_space" id="allocated_space" class="input-small" value="'.$config['allocated_space'].'" required />
			<p class="help">'.Trad::F_TIP_ALLOCATED_SPACE.'</p>
		</div>
	</div>
	
	<div class="form-actions">
		<a href="'.Url::parse('settings').'" class="btn">'.Trad::V_CANCEL.'</a>
		<button type="submit" class="btn btn-primary">'.Trad::V_SAVE_CONFIG.'</button>
	</div>

	<h2 id="t2">'.Trad::T_APPEARANCE.'</h2>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_DISPLAY.'
		</div>
		<div class="inner-form" style="display:none">
			<label for="issues_per_page">'.Trad::F_ISSUES_PAGE.'</label>
			<input type="text" name="issues_per_page" id="issues_per_page" class="input-small" value="'.$config['issues_per_page'].'" />
			<label for="search_per_page">'.Trad::F_ISSUES_PAGE_SEARCH.'</label>
			<input type="text" name="search_per_page" id="search_per_page" class="input-small" value="'.$config['search_per_page'].'" />
			<label for="length_preview_text">'.Trad::F_PREVIEW_ISSUE .'</label>
			<input type="text" name="length_preview_text" id="length_preview_text" class="input-small" value="'.$config['length_preview_text'].'" />
			<label for="length_search_text">'.Trad::F_PREVIEW_SEARCH.'</label>
			<input type="text" name="length_search_text" id="length_search_text" class="input-small" value="'.$config['length_search_text'].'" />
			<label for="length_preview_project">'.Trad::F_PREVIEW_PROJECT.'</label>
			<input type="text" name="length_preview_project" id="length_preview_project" class="input-small" value="'.$config['length_preview_project'].'" />
			<label for="nb_last_activity_dashboard">'.Trad::F_LAST_EDITS.'</label>
			<input type="text" name="nb_last_activity_dashboard" id="nb_last_activity_dashboard" class="input-small" value="'.$config['nb_last_activity_dashboard'].'" />
			<label for="nb_last_activity_user">'.Trad::F_LAST_ACTIVITY.'</label>
			<input type="text" name="nb_last_activity_user" id="nb_last_activity_user" class="input-small" value="'.$config['nb_last_activity_user'].'" />
		</div>
	</div>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_COLORS.'
		</div>
		<div class="inner-form" style="display:none">
			<table class="table">
				<thead>
					<tr>
						<th>'.Trad::W_HEX.'</th>
						<th>'.Trad::W_RENDERING.'</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					'.$t_colors.'
				</tbody>
			</table>
			<p style="text-align:center"><a href="javascript:;" class="btn btn-success btn-add-color">'.Trad::F_ADD_COLOR.'</a></p>
		</div>
	</div>

	<div class="form-actions">
		<a href="'.Url::parse('settings').'" class="btn">'.Trad::V_CANCEL.'</a>
		<button type="submit" class="btn btn-primary">'.Trad::V_SAVE_CONFIG.'</button>
	</div>

	<h2 id="t3">'.Trad::T_ISSUES.'</h2>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_STATUSES.'
		</div>
		<div class="inner-form" style="display:none">
			<p class="p-tip">'.Trad::F_TIP_ID_STATUS.'</p>
			<table class="table">
				<thead>
					<tr>
						<th>'.Trad::W_ID.'</th>
						<th>'.Trad::W_DISPLAY_NAME.'</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					'.$t_statuses.'
				</tbody>
			</table>
			<p class="form-actions"><a href="javascript:;" class="btn btn-add-status">'.Trad::F_ADD_STATUS.'</a></p>
		</div>
	</div>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_LABELS.'
		</div>
		<div class="inner-form" style="display:none">
			<p class="p-tip">'.Trad::F_TIP_ID_LABEL.'</p>
			<table class="table">
				<thead>
					<tr>
						<th>'.Trad::W_ID.'</th>
						<th>'.Trad::W_RENDERING.'</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					'.$t_labels.'
				</tbody>
			</table>
			<p class="form-actions"><a href="javascript:;" class="btn btn-add-label">'.Trad::F_ADD_LABEL.'</a></p>
		</div>
	</div>

	<div class="form-actions">
		<a href="'.Url::parse('settings').'" class="btn">'.Trad::V_CANCEL.'</a>
		<button type="submit" class="btn btn-primary">'.Trad::V_SAVE_CONFIG.'</button>
	</div>

	<h2 id="t4">'.Trad::T_GROUPS.'</h2>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_GROUPS.'
		</div>
		<div class="inner-form" style="display:none">
			<p class="p-tip">'.Trad::F_TIP_ID_GROUP.'</p>
			<table class="table">
				<thead>
					<tr>
						<th>'.Trad::W_ID.'</th>
						<th>'.Trad::W_DISPLAY_NAME.'</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					'.$t_groups.'
				</tbody>
			</table>
			<p class="form-actions"><a href="javascript:;" class="btn btn-add-group">'.Trad::F_ADD_GROUP.'</a></p>
		</div>
	</div>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_PERMISSIONS.'
		</div>
		<div class="inner-form" style="display:none">
			<table class="table">
				<tbody>
					'.$t_permissions.'
				</tbody>
			</table>
		</div>
	</div>

	<div class="form-actions">
		<a href="'.Url::parse('settings').'" class="btn">'.Trad::V_CANCEL.'</a>
		<button type="submit" class="btn btn-primary">'.Trad::V_SAVE_CONFIG.'</button>
	</div>
	
	<h2 id="t5">'.Trad::T_USERS.'</h2>

	<div class="box box-settings">
		<div class="top a-icon-hover">
			<i class="icon-chevron-down"></i>'.Trad::F_USERS.'
		</div>
		<div class="inner-form" style="display:none">
			<table class="table">
				<tbody>
					'.$t_users.'
				</tbody>
			</table>
			<p style="text-align:center"><a href="javascript:;" class="btn btn-success btn-add-user">'.Trad::F_ADD_USER.'</a><a href="'.Url::parse('settings', array('action' => 'export_users')).'" class="btn btn-export-users"><i class="icon-share"></i></a></p>
		</div>
	</div>

	<div class="form-actions">
		<a href="'.Url::parse('settings').'" class="btn">'.Trad::V_CANCEL.'</a>
		<button type="submit" class="btn btn-primary">'.Trad::V_SAVE_CONFIG.'</button>
	</div>

	<input type="hidden" name="action" value="save" />
	<input type="hidden" name="token" value="'.getToken().'" />

</form>
';

$javascript = '

$(document).ready(function(){
	$(document).on("keyup", ".color", function() {
		$(this).closest("tr").find(".label").css("background-color", $(this).val());
	});
	$(document).on("click", ".btn-color", function() {
		$(this).closest("tr").find(".div-pick-color").show();
	});
	$(document).on("click", ".div-pick-color .square", function() {
		var color = $(this).css("background-color");
		$(this).closest("tr")
			.find(".div-pick-color").hide().end()
			.find(".input-color").val(color).end()
			.find(".btn-color .square").css("background-color", color);
		return false;
	});
	$(".btn-home").click(function() {
		if ($(this).hasClass("active")) {
			$(this).closest("tr").find(".input-home").val("false");
		}
		else {
			$(this).closest("tr").find(".input-home").val("true");
		}
		$(this).toggleClass("active");
	});
	$(document).on("keyup", "input[name=\"label_name[]\"]", function() {
		$(this).closest("tr").find(".label").first().text($(this).val());
	});
	$(document).on("click", ".div-pick-color .label", function() {
		var color = $(this).css("background-color");
		$(this).closest("tr")
			.find(".div-pick-color").hide().end()
			.find(".input-color").val(color).end()
			.find(".btn-color .square").css("background-color", color).end()
			.find(".label").first().css("background-color", color);
	});
	$(".p-buttons .btn").click(function() {
		if ($(this).hasClass("active")) {
			$(this).find("input").val("0");
		}
		else {
			$(this).find("input").val("1");
		}
		$(this).toggleClass("active");
	});
	$(".a-user-edit").click(function() {
		$(this).closest("td")
			.find(".t-display").hide().end()
			.find(".i-display").show();
	});

	$(".btn-add-project").click(function() {
		$(this).closest(".box-settings").find("table tbody")
			.append(\''.str_replace(array("\n", "\r"), '', getTrProject('', '')).'\');
	});
	$(".btn-add-color").click(function() {
		$(this).closest(".box-settings").find("table tbody")
			.append(\''.str_replace(array("\n", "\r"), '', getTrColor('')).'\');
	});
	$(".btn-add-status").click(function() {
		$(this).closest(".box-settings").find("table tbody")
			.append(\''.str_replace(array("\n", "\r"), '', getTrStatus('', array('name' => '', 'color' => DEFAULT_COLOR, 'dashboard' => false))).'\');
	});
	$(".btn-add-label").click(function() {
		$(this).closest(".box-settings").find("table tbody")
			.append(\''.str_replace(array("\n", "\r"), '', getTrLabel('', '', DEFAULT_COLOR)).'\');
	});
	$(".btn-add-group").click(function() {
		$(this).closest(".box-settings").find("table tbody")
			.append(\''.str_replace(array("\n", "\r"), '', getTrGroup('', '')).'\');
	});
	$(".btn-add-user").click(function() {
		$(this).closest(".box-settings").find("table tbody")
			.append(\''.str_replace(array("\n", "\r", "'"), array('', '', "\'"), getTrUser(array('id' => '', 'username' => '', 'group' => DEFAULT_GROUP, 'email' => '', 'notifications' => 'never'))).'\')
			.find("tr").last()
				.find(".t-display").hide().end()
				.find(".i-display").show();
	});
	$(document).on("click", ".a-remove-project", function() {
		if (confirm("'.Trad::A_CONFIRM_DELETE_PROJECT.'")) {
			$(this).closest("tr").remove();
		}
	});
	$(document).on("click", ".a-remove", function() {
		$(this).closest("tr").remove();
	});
	$(document).on("click", ".a-up", function() {
		var tr = $(this).closest("tr");
		if (tr.prev().length <= 0) { return false; }
		tr.replaceWith(tr.prev().after(tr.clone(true)));
	});
	$(document).on("click", ".a-down", function() {
		var tr = $(this).closest("tr");
		if (tr.next().length <= 0) { return false; }
		tr.replaceWith(tr.next().after(tr.clone(true)));
	});
});


';

?>