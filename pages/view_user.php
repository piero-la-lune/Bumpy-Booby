<?php

if (isset($_GET['id']) && isset($config['users'][$_GET['id']])) {

if (isset($_POST['edit_user'])) {
	$settings = new Settings();
	$ans = $settings->update_user($_GET['id'], $_POST);
	if ($ans === true) {
		$this->addAlert(Trad::A_MODIF_SAVED, 'alert-success');
	}
	else {
		$this->addAlert($ans);
	}
}

$user = $config['users'][$_GET['id']];
$id = intval($_GET['id']);

$title = htmlspecialchars($user['username']);

$content = '
	<h1>'.htmlspecialchars($user['username']).'</h1>
';


$edit = ''; $edit_user = '';
$remove_upload = ''; $space_used = '';
if (($config['loggedin'] && $_SESSION['id'] == $id) || canAccess('settings')) {
	$edit = '<a href="javascript:;" class="a-edit a-icon-hover"><i class="icon-edit"></i></a>';
	$edit_user = '
<div class="i-display" style="display:none">
	<label for="password">'.Trad::F_PASSWORD.'</label>
	<input type="password" name="password" id="password" class="input-normal" />
	<p class="help">'.Trad::F_TIP_PASSWORD.'</p>
	<label for="email">'.Trad::F_EMAIL.'</label>
	<input type="email" name="email" id="email" class="input-large" value="'.htmlspecialchars($user['email']).'" />
	<p class="help">'.Trad::F_TIP_USER_EMAIL.'</p>
	'.(($config['email']) ? '' : '<p class="help">'.Trad::F_TIP_NOTIFICATIONS_DISABLED.'</p>').'
	<label for="notifications">'.Trad::F_NOTIFICATIONS.'</label>
	<select name="notifications" id="notifications">
		'.Text::options(array(
			'never' => Trad::S_NEVER,
			'me' => Trad::S_ME,
			'always' => Trad::S_ALWAYS
		), $user['notifications']).'
	</select>
	<p class="help">'.Trad::F_TIP_NOTIFICATIONS.'</p>
	<div class="form-actions">
		<input type="hidden" name="token" value="'.getToken().'" />
		<input type="hidden" name="edit_user" value="1" />
		<button type="submit" class="btn btn-primary">'.Trad::V_APPLY.'</button>
	</div>
</div>
	';
	
	$remove_upload = '<a href="javascript:;" class="a-remove-upload a-icon-hover"><i class="icon-trash"></i></a>';
	$uploader = Uploader::getInstance();
	$space = $uploader->get_spaceused($id);
	$percent = intval($space*100/Text::to_bytes($config['allocated_space']));
	$space_used = '
<div class="progress">
	<div class="bar" style="width:'.$percent.'%"></div>
	'.str_replace(array('%remain%', '%total%'), array(
		'<span>'.Text::to_xbytes(Text::to_bytes($config['allocated_space'])-$space).'</span>',
		$config['allocated_space']
	), Trad::S_SIZE_REMAINING).'
</div>
	';
}

$view_more = '';
if (canAccess('issues')) {
	foreach ($config['projects'] as $k => $v) {
		if (canAccessProject($k)) {
			$view_more .= '<div class="div-view-personnal-issues"><p>';
			if (!onlyDefaultProject()) {
				$view_more .= str_replace('%name%', $k, Trad::F_PROJECT_X)
					.'<br />';
			}
			$view_more .= '<a href="'.Url::parse($k.'/issues',
				array('user' => $id)).'">'
				.Trad::S_VIEW_PARTICIPATION
			.'</a><br />';
			foreach ($config['statuses'] as $q => $w) {
				if (strpos($w['name'], '%user%') !== false) {
					$view_more .= '<a href="'.Url::parse($k.'/issues',
						array('status' => $q.','.$id)).'">'
						.str_replace(
							'%status%',
							Text::status($q, $id, false),
							Trad::S_VIEW_STATUS
						)
					.'</a><br />';
				}
			}
			$view_more .= '</p></div>';
		}
	}
}

$content .= '

<form action="'.Url::parse('users/'.$id).'" method="post" class="form form-user">
	<div class="t-display">
		<img src="'.Text::identicon($user['username']).'" class="identicon" />
		<p><span class="strong">'.htmlspecialchars($user['username']).'</span>
			'.$edit.'
			<br /><small>'.$config['groups'][$user['group']].'</small>
		</p>
		'.$view_more.'
	</div>
	'.$edit_user.'
</form>

<p>&nbsp;</p>

<div class="div-table">

';


if (canAccess('view_upload')) {
	$uploader = Uploader::getInstance();
	$uploads = $uploader->getAll();
	$up = '';
	foreach ($uploads as $u) {
		if ($u['user'] == $id) {
			$src = Text::upload($u['name']);
			if ($u['mime-type']) {
				$upl = '<img src="'.$src.'" class="upload-tiny" />';
			}
			else {
				$upl = htmlspecialchars($u['display']);
			}
			$up .= '
				<p data-name="'.$u['name'].'" data-user="'.$id.'">
					<a href="'.$src.'" class="file">'.$upl.'</a>
					<span>'.Text::to_xbytes($u['size']).'</span> '.$remove_upload.'
				</p>
			';
		}
	}
	if (empty($up)) { $up = '<p>'.Trad::S_NO_UPLOAD.'</p>'; }
	$content .= '
		<div class="div-cell div-cell-left div-list-uploads">
			<h2>'.Trad::T_UPLOADS.'</h2>
			'.$up.'
			'.$space_used.'
		</div>
	';
}

if (canAccess('issues')) {

	$activity = array(); $nb_issues = $config['nb_last_activity_user'];
	for ($i=0; $i < $nb_issues; $i++) { 
		$activity[$i] = array('project' => '', 'id' => 0, 'time' => 0, 'edit' => 0);
	}

	foreach ($config['projects'] as $k => $v) {
		if (!canAccessProject($k)) { continue; }
		$issues = Issues::getInstance($k);
		$iss = $issues->getAll();
		foreach ($iss as $i) {
			if ($i['openedby'] == $id && $i['date'] > $activity[$nb_issues-1]['time']) {
				$activity[$nb_issues-1] = array('project' => $k, 'id' => $i['id'], 'time' => $i['date'], 'edit' => 0);
				usort($activity, array('OrderFilter', 'compare_time'));
			}
			if ($i['edit'] > $activity[$nb_issues-1]['time']) {
				foreach ($i['edits'] as $e) {
					if (empty($e)) { continue; }
					if ($e['by'] == $id && $e['date'] > $activity[$nb_issues-1]['time']) {
						$activity[$nb_issues-1] = array('project' => $k, 'id' => $i['id'], 'time' => $e['date'], 'edit' => $e['id']);
						usort($activity, array('OrderFilter', 'compare_time'));
					}
				}
			}
		}
	}

	$activities = '';
	foreach ($activity as $v) {
		if ($v['time'] == 0) { continue; }
		$issues = Issues::getInstance($v['project']);
		$i = $issues->get($v['id']);
		if ($v['edit'] == 0) {
			$edit = array(
				'id' => 0,
				'type' => 'newissue',
				'by' => $i['openedby'],
				'date' => $i['date']);
			$preview = '<br />'.Text::intro($i['text'], $config['length_preview_text']);
		}
		else {
			$edit = $i['edits'][$v['edit']];
			if ($edit['type'] == 'comment') {
				$preview = '<br />'.Text::intro($edit['text'], $config['length_preview_text']);
			}
			else {
				$preview = '';
			}
		}
		$project = (onlyDefaultProject()) ? '' : $v['project'].' – ';
		$activities .= '
<div class="div-last-edit">
	<a class="a-summary" href="'.Url::parse($v['project'].'/issues/'.$i['id']).'">'.$project.htmlspecialchars($i['summary']).'</a>
	'.$preview.'
	<br />
	'.Issues::get_preview_edit($edit).'
</div>
		';
	}
	if (empty($activities)) { $activities = '<p>'.Trad::S_NO_ACTIVITY.'</p>'; }

	$content .= '
<div class="div-cell div-last-edits">
	<h2>'.Trad::T_LAST_ACTIVITY.'</h2>
	'.$activities.'
</div>
	';

}

$content .= '</div>';

}
else {
	$load = 'error/404';
}


?>