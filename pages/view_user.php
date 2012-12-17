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
	$id = $_GET['id'];

	$title = htmlspecialchars($user['username']);
	$content = '
		<h1>'.htmlspecialchars($user['username']).'</h1>
	';

	$edit = ''; $remove_upload = ''; $space_used = '';
	if ($config['loggedin'] && ($_SESSION['id'] == $id || canAccess('settings'))) {
		$edit = '<a href="javascript:;" class="a-edit a-icon-hover"><i class="icon-edit"></i></a>';
		$remove_upload = '<a href="javascript:;" class="a-remove a-icon-hover"><i class="icon-trash"></i></a>';
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

	$content .= '
		<form action="'.Url::parse('users/'.$id).'" method="post" class="form form-user">
			<div class="t-display">
				<img src="'.Text::identicon($user['username']).'" class="identicon" />
				<p class="title">'.htmlspecialchars($user['username']).'
					'.$edit.'
					<br /><small>'.$config['groups'][$user['group']].'</small>
				</p>
			</div>
			<div class="i-display">
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
		</form>
		<div class="div-table">
	';

	$javascript = '
		$(".a-edit").click(function() {
			$(".t-display").hide();
			$(".i-display").show();
		});
	';

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
				foreach ($i['edits'] as $e) {
					if (empty($e)) { continue; }
					if ($e['by'] == $id && $e['date'] > $activity[$nb_issues-1]['time']) {
						$activity[$nb_issues-1] = array('project' => $k, 'id' => $i['id'], 'time' => $e['date'], 'edit' => $e['id']);
						usort($activity, array('OrderFilter', 'compare_time'));
					}
				}
			}
		}

		$activities = '';
		foreach ($activity as $v) {
			if ($v['time'] == 0) { continue; }
			$issues = Issues::getInstance($v['project']);
			$i = $issues->get($v['id']);
			if (!$i) { continue; }
			$url = Url::parse($v['project'].'/issues/'.$i['id']);
			$project = (onlyDefaultProject()) ? '' : $v['project'].' – ';
			if ($v['edit'] == 0) {
				$text = Text::intro($i['text'], $config['lenght_preview_text']);
				$activities .= '
					<div>
						<span class="summary">'.$project.'<a href="'.$url.'">'.htmlspecialchars($i['summary']).'</a></span>
						<span class="span-info">
							<span class="span-preview">'.$text.'</span>
							'.str_replace(
								array('%adj%', '%user%', '%time%'),
								array(
									'<span class="btn-open btn-tag-small">'.Trad::W_OPENED.'</span>',
									Text::username($i['openedby'], true),
									Text::ago($i['date'])
								),
								Trad::S_ISSUE_UPDATED
							).'</span>
					</div>
				';		
			}
			else {
				$edit = $i['edits'][$v['edit']];
				if ($edit['type'] == 'comment') {
					$text = Text::intro($edit['text'], $config['lenght_preview_text']);
					$activities .= '
						<div>
							<span class="summary">'.$project.'<a href="'.$url.'#e-'.$edit['id'].'">'.htmlspecialchars($i['summary']).'</a></span>
							<span class="span-info">
								<span class="span-preview">'.$text.'</span>
								'.str_replace(
									array('%adj%', '%user%', '%time%'),
									array(
										'<span class="btn-commented btn-tag-small">'.Trad::W_COMMENTED.'</span>',
										Text::username($edit['by'], true),
										Text::ago($edit['date'])
									),
									Trad::S_ISSUE_UPDATED
								).'</span>
						</div>
					';
				}
				elseif ($edit['type'] == 'open' && $edit['changedto']) {
					$activities .= '
						<div>
							<span class="summary">'.$project.'<a href="'.$url.'#e-'.$edit['id'].'">'.htmlspecialchars($i['summary']).'</a></span>
							<span class="span-info">'.str_replace(
								array('%adj%', '%user%', '%time%'),
								array(
									'<span class="btn-open btn-tag-small">'.Trad::W_REOPENED.'</span>',
									Text::username($edit['by'], true),
									Text::ago($edit['date'])
								),
								Trad::S_ISSUE_UPDATED).'</span>
						</div>
					';
				}
				elseif ($edit['type'] == 'open') {
					$activities .= '
						<div>
							<span class="summary">'.$project.'<a href="'.$url.'#e-'.$edit['id'].'">'.htmlspecialchars($i['summary']).'</a></span>
							<span class="span-info">'.str_replace(
								array('%adj%', '%user%', '%time%'),
								array(
									'<span class="btn-closed btn-tag-small">'.Trad::W_CLOSED.'</span>',
									Text::username($edit['by'], true),
									Text::ago($edit['date'])
								),
								Trad::S_ISSUE_UPDATED).'</span>
						</div>
					';
				}
				elseif ($edit['type'] == 'status') {
					$activities .= '
						<div>
							<span class="summary">'.$project.'<a href="'.$url.'#e-'.$edit['id'].'" class="summary">'.htmlspecialchars($i['summary']).'</a></span>
							<span class="span-info">'.str_replace(
								array('%user%', '%time%', '%status%'),
								array(
									Text::username($edit['by'], true),
									Text::ago($edit['date']),
									'<span class="btn-status btn-tag-small" style="border-color:'.$config['statuses'][$edit['changedto']]['color'].'">'.Text::status($edit['changedto'], $edit['assignedto']).'</span>'
								),
								Trad::S_ISSUE_STATUS_UPDATED).'</span>
						</div>
					';
				}
				else { continue; } # Should not happen
			}
		}
		if (empty($activities)) { $activities = '<p>'.Trad::S_NO_ACTIVITY.'</p>'; }

		$content .= '
			<div class="div-last-activities">
				<h3>'.Trad::T_LAST_ACTIVITY.'</h3>
				'.$activities.'
			</div>
		';
	}

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
					<p>
						<a href="'.$src.'" class="file">'.$upl.'</a>
						<span>'.Text::to_xbytes($u['size']).'</span> '.$remove_upload.'
						<input type="hidden" name="trash" value="'.$u['name'].'" />
					</p>
				';
			}
		}
		if (empty($up)) { $up = '<p>'.Trad::S_NO_UPLOAD.'</p>'; }
		$content .= '
			<div class="div-all-uploads">
				<h3>'.Trad::T_UPLOADS.'</h3>
				'.$up.'
				'.$space_used.'
			</div>
		';

		$javascript .= '
			var token = "'.getToken().'";
			$(".a-remove").click(function() {
				if (!confirm("'.Trad::A_CONFIRM_DELETE_UPLOAD.'")) { return false; }
				var p = $(this).closest("p");
				var progress = $(this).closest(".div-all-uploads").find(".progress");
				$.ajax({
					type: "POST",
					url: "'.Url::parse('public/ajax').'",
					data: {
						action: "upload_remove_linked",
						token: token,
						name: p.find("input").val(),
						user: '.$id.'
					}
				}).done(function(ans) {
					var ans = jQuery.parseJSON(ans);
					if (ans.success) {
						p.remove();
						progress.find("span").html(ans.space);
						progress.find(".bar").css("width", ans.percent+"%");
					}
					else {
						alert(ans.text);
					}
					token = ans.token;
				});
			});
		';
	}

	$content .= '</div>';

}
else {
	$load = 'error/404';
}


?>