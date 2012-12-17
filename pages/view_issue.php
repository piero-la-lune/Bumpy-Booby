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
	elseif (isset($_POST['update_issue'])) {
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
	elseif (isset($_POST['edit_issue'])) {
		$ans = $issues->edit_issue($issue['id'], $_POST);
		if ($ans === true) {
			# Need to update the issue
			$issue = $issues->get($_GET['id']);
		}
		else {
			$form_ei_a = '<div class="alert alert-error">'.$ans.'</div>';
		}
	}
	elseif (isset($_POST['change_notif'])) {
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
				$manage = '<div class="manage"><a href="javascript:;" class="a-edit a-icon-hover"><i class="icon-edit"></i></a>&nbsp;<a href="javascript:;" class="a-remove a-icon-hover"><i class="icon-trash"></i></a></div>';
				$edit_comment = '
					<div class="i-display" style="display:'.$displ_f.'">
						'.$alert.'
						<textarea name="comment" rows="6">'.$comment.'</textarea>
						<div class="form-actions">
							<a href="javascript:;" class="btn btn-cancel">'.Trad::V_CANCEL.'</a>
							<button type="submit" class="btn btn-primary">'.Trad::V_UPDATE.'</button>
						</div>
						<input type="hidden" name="comment_id" value="'.$e['id'].'" />
						<input type="hidden" name="token" value="'.$token.'" />
						<input type="hidden" name="edit_comment" value="1" />
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
						$uploads .= '<i class="icon-file"></i>'.htmlspecialchars($u['display']);
					}
					$uploads .= '</a>';
				}
				$uploads .= '<div class="spacer"></div></div>';
			}
			$edits .= '
				<div class="box-identicon">
					<img src="'.Text::identicon(Text::username($e['by'])).'" />
				</div>
				<form action="'.Url::parse(getProject().'/issues/'.$issue['id'], array(), $id).'" method="post">
					<div class="box box-comment" id="'.$id.'">
						<div class="top">
							'.$manage.'
							<i class="icon-comment"></i>
							'.str_replace(
								array('%adj%', '%user%', '%time%'),
								array(
									'<span class="btn-commented btn-tag-small">'.Trad::W_COMMENTED.'</span>',
									Text::username($e['by'], true),
									Text::ago($e['date'])
								),
								Trad::S_ISSUE_UPDATED).'
						</div>
						<div class="t-display" style="display:'.$displ_c.'">
							'.Text::markdown($e['text']).'
							'.$uploads.'
						</div>
						'.$edit_comment.'
					</div>
				</form>
			';
		}
		elseif ($e['type'] == 'status') {
			$edits .= '
				<div class="box-issue-update" id="'.$id.'">
					<i class="icon-retweet"></i>
					'.str_replace(
						array('%user%', '%time%', '%status%'),
						array(
							Text::username($e['by'], true),
							Text::ago($e['date']),
							'<span class="btn-status btn-tag-small" style="border-color:'.$config['statuses'][$e['changedto']]['color'].'">'.Text::status($e['changedto'], $e['assignedto']).'</span>'
						),
						Trad::S_ISSUE_STATUS_UPDATED).'
				</div>
			';
		}
		elseif ($e['type'] == 'open' && $e['changedto']) {
			$edits .= '
				<div class="box-issue-update" id="'.$id.'">
					<i class="icon-adjust"></i>
					'.str_replace(
						array('%adj%', '%user%', '%time%'),
						array(
							'<span class="btn-open btn-tag-small">'.Trad::W_REOPENED.'</span>',
							Text::username($e['by'], true),
							Text::ago($e['date'])
						),
						Trad::S_ISSUE_UPDATED).'</span>
				</div>
			';
		}
		elseif ($e['type'] == 'open') {
			$edits .= '
				<div class="box-issue-update" id="'.$id.'">
					<i class="icon-adjust"></i>
					'.str_replace(
						array('%adj%', '%user%', '%time%'),
						array(
							'<span class="btn-closed btn-tag-small">'.Trad::W_CLOSED.'</span>',
							Text::username($e['by'], true),
							Text::ago($e['date'])
						),
						Trad::S_ISSUE_UPDATED).'</span>
				</div>
			';
		}
		else { continue; } # Should not happen
	}

	$uploads = '';
	if (canAccess('upload') && $issue['open']) {
		$uploads = Uploader::get_html();
	}
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
			<div class="div-relative">
				<div class="box box-post-comment">
					<div class="top">
						<div class="manage"><a href="javascript:;" class="a-help-markdown a-icon-hover"><i class="icon-question-sign"></i></a></div>
						<i class="icon-comment"></i> '.Trad::S_COMMENT_LEAVE.'
					</div>
					<div class="div-help-markdown">'.Trad::HELP_MARKDOWN.'</div>
					'.$form_pc_a.'
					<form action="'.Url::parse(getProject().'/issues/'.$issue['id'], array(), 'post_comment').'" method="post" id="post_comment" class="form">
						<textarea name="comment" rows="6">'.$form_pc_c.'</textarea>
						<div class="preview"></div>
						'.$message.'
						<div class="form-actions">
							<a href="javascript:;" class="btn btn-preview">'.Trad::V_PREVIEW.'</a>
							<button type="submit" class="btn btn-primary">'.Trad::V_COMMENT.'</button>
						</div>
						<input type="hidden" name="comment_uploads" value="" />
						<input type="hidden" name="token" value="'.$token.'" />
						<input type="hidden" name="post_comment" value="1" />
					</form>
				</div>
				'.$uploads.'
			</div>
		';
	}
	elseif ($issue['open']
		&& !$config['loggedin']
		&& canAccess('signup')
		&& in_array(DEFAULT_GROUP, $config['permissions']['post_comment'])) {
		$form_comment = '
			<div class="box box-post-comment">
				<div class="top">
					<i class="icon-comment"></i> '.Trad::S_COMMENT_LEAVE.'
				</div>
				<p>'.Trad::A_PLEASE_LOGIN_COMMENT.'</p>
			</div>
		';
	}

	$update_issue = '';
	if (canAccess('update_issue')) {
		$update_issue = '<div class="manage"><a href="javascript:;" class="t-display a-edit a-icon-hover"><i class="icon-edit"></i></a><a href="javascript:;" class="i-display a-edit a-icon-hover"><i class="icon-circle-arrow-left"></i></a></div>';
	}

	$labels = '';
	foreach ($issue['labels'] as $l) {
		$labels .= '<a href="'.Url::parse(getProject().'/labels/'.$l).'" class="label" style="background-color:'.$config['labels'][$l]['color'].'">'.$config['labels'][$l]['name'].'</a>';
	}
	if (empty($labels)) { $labels = Trad::S_NOLABEL; }
	$labels_edit = '';
	foreach ($config['labels'] as $k => $v) {
		if ($k == PRIVATE_LABEL && !canAccess('private_issues')) { continue; }
		$class = (in_array($k, $issue['labels'])) ? 'label label-selected' : 'label';
		$labels_edit .= '<a href="javascript:;" class="'.$class.'" style="background-color:'.$v['color'].'" data-id="'.$k.'">'.$v['name'].'</a>';
	}

	$status = Text::status($issue['status'], $issue['assignedto']);
	$users = array('nobody' => Trad::W_NOBODY);
	foreach ($config['users'] as $k => $u) {
		$users[$k] = htmlspecialchars($u['username']);
	}

	if (empty($issue['dependencies'])) {
		$dependencies = Trad::S_NODEPENDENCY;
		$dependencies_edit = '';
	}
	else {
		$d = array();
		foreach ($issue['dependencies'] as $v) {
			$d[] = '<a href="'.Url::parse(getProject().'/issues/'.$v).'" class="a-issue"><span>#</span>'.$v.'</a>';
		}
		$dependencies = implode(', ', $d);
		$dependencies_edit = '#'.implode(', #', $issue['dependencies']);
	}

	$open = '';
	if ($issue['open']) {;
		$open = '<span class="btn-open btn-tag">'.Trad::W_OPEN.'</span>';
		$openSelect = 'open';
	}
	else {
		$open = '<span class="btn-closed btn-tag">'.Trad::W_CLOSED.'</span>';
		$openSelect = 'closed';
	}

	$statuses = array();
	foreach ($config['statuses'] as $k => $v) {
		$statuses[$k] = $v['name'];
	}


	$manage = '';
	$edit_issue = '';
	$displ_f = (empty($form_ei_a)) ? 'none' : 'block';
	$displ_t = (empty($form_ei_a)) ? 'block' : 'none';
	$text = (empty($form_ei_a)) ? $issue['text'] : $_POST['text'];
	$summary = (empty($form_ei_a)) ? $issue['summary'] : $_POST['summary'];
	if (canAccess('edit_issue')) {
		$manage = '<div class="manage"><a href="javascript:;" class="a-edit a-icon-hover"><i class="icon-edit"></i></a>&nbsp;<a href="javascript:;" class="a-remove a-icon-hover"><i class="icon-trash"></i></a></div>';
		$edit_issue = '
			<div class="i-display" style="display:'.$displ_f.'">
				'.$form_ei_a.'
				<input type="text" name="summary" value="'.htmlspecialchars($summary).'" required />
				<textarea name="text" rows="12" required>'.htmlspecialchars($text).'</textarea>
				<div class="form-actions">
					<a href="javascript:;" class="btn btn-cancel">'.Trad::V_CANCEL.'</a>
					<button type="submit" class="btn btn-primary">'.Trad::V_UPDATE.'</button>
				</div>
				<input type="hidden" name="token" value="'.$token.'" />
				<input type="hidden" name="edit_issue" value="1" />
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
				$uploads .= '<i class="icon-file"></i>'.htmlspecialchars($u['display']);
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
		$notifications = '
			<form method="post" action="'.Url::parse(getProject().'/issues/'.$issue['id']).'" class="form-change-notif">
				<input type="hidden" name="token" value="'.getToken().'" />
				<input type="hidden" name="change_notif" value="'.$value.'" />
				<button type="submit" class="btn btn-small">'.$text.'</button>
			</form>
		';
	}

	$content = '
		<h1>'.$open.'&nbsp;'.Trad::W_ISSUE.' #'.$issue['id'].' <small>'.htmlspecialchars($issue['summary']).'</small></h1>

		<div class="box-details">
			<form method="post" action="'.Url::parse(getProject().'/issues/'.$issue['id']).'">
				<div class="top">
					'.$update_issue.'
					<i class="icon-info-sign"></i> '.Trad::S_ISSUE_ABOUT.'
				</div>
				<table class="table">
					<tr>
						<td>'.Trad::F_STATUS.'</td>
						<td>
							<span class="t-display">'.$status.'</span>
							<span class="i-display">
								<select name="issue_status" class="select-status">'.Text::options($statuses, $issue['status']).'</select>
								<br />
								<select name="issue_assignedto" class="select-users">'.Text::options($users, $issue['assignedto']).'</select>
							</span>
						</td>
					</tr>
					<tr>
						<td>'.Trad::F_RELATED.'</td>
						<td>
							<span class="t-display">'.$dependencies.'</span>
							<span class="i-display"><input type="text" name="issue_dependencies" value="'.$dependencies_edit.'" /></span>
						</td>
					</tr>
					<tr>
						<td>'.Trad::F_LABELS2.'</td>
						<td>
							<span class="t-display">'.$labels.'</span>
							<span class="i-display">'.$labels_edit.'<input type="hidden" name="issue_labels" value="" /></span>
						</td>
					</tr>
					<tr class="i-display">
						<td colspan="2">
							<select name="issue_open">
								'.Text::options(array(
									'open' => Trad::W_OPEN,
									'closed' => Trad::W_CLOSED
								), $openSelect).'
							</select>
						</td>
					</tr>
				</table>
				<input type="hidden" name="token" value="'.$token.'" />
				<input type="hidden" name="update_issue" value="1" />
				<div class="form-actions i-display">
					<button type="submit" class="btn btn-primary">'.Trad::V_UPDATE.'</button>
				</div>
			</form>
			'.$notifications.'
		</div>

		<div class="box box-issue">
			<form action="'.Url::parse(getProject().'/issues/'.$issue['id']).'" method="post">
				<div class="top">
					'.$manage.'
					'.str_replace(
						array('%adj%', '%user%', '%time%'),
						array(
							'<span class="btn-open btn-tag-small">'.Trad::W_OPENED.'</span>',
							Text::username($issue['openedby'], true),
							Text::ago($issue['date'])
						),
						Trad::S_ISSUE_UPDATED).'
				</div>
				<div class="t-display" style="display:'.$displ_t.'">
					'.Text::markdown($issue['text']).'
					'.$uploads.'
				</div>
				'.$edit_issue.'
			</form>
		</div>

		<div class="spacer" id="comments"></div>

		'.$edits.'
		'.$form_comment.'

	';


	$javascript = '
		'.Uploader::get_javascript().'
		$("#post_comment").submit(function() {
			var val = "";
			for (var prop in array_uploads) {
				if (array_uploads.hasOwnProperty(prop)) { val += array_uploads[prop]+","; }
			}
			$(this).find("input[name=\"comment_uploads\"]").val(val);
		});
		$(".btn-preview").live("click", function() {
			var form = $(this).closest("form");
			var btn = $(this);
			$.ajax({
				type: "POST",
				url: "'.Url::parse('public/ajax').'",
				data: {
					action: "markdown",
					text: form.find("textarea").val()
				}
			}).done(function(ans) {
				var ans = jQuery.parseJSON(ans);
				if (ans.success) {
					form.find("textarea").hide();
					form.find(".preview").html(ans.text).show();
					form.find("pre code").each(function(i,e) { highlighter($(this), e); });
					btn.removeClass("btn-preview").addClass("btn-edit").text("'.Trad::V_EDIT.'");
				}
				else {
					alert(ans.text);
				}
			});
		});
		$(".btn-edit").live("click", function() {
			$(this).closest("form")
				.find(".preview").hide().end()
				.find("textarea").show();
			$(this).removeClass("btn-edit").addClass("btn-preview").text("'.Trad::V_PREVIEW.'");
		});
		$(".box-post-comment form").submit(function() {
			if ($(this).find("textarea").val() == "") { return false; }
		});

		$(".box-issue .a-edit, .box-comment .a-edit, .btn-cancel").click(function() {
			$(this).closest("form")
				.find(".t-display").toggle().end()
				.find(".i-display").toggle().end();
		});
		$(".box-comment .a-remove").click(function() {
			if (confirm("'.Trad::A_CONFIRM_DELETE_COMMENT.'")) {
				$(this).closest("form")
					.find("input[name=\"edit_comment\"]").attr("name", "delete_comment").end()
					.submit();
			}
		});
		$(".box-issue .a-remove").click(function() {
			if (confirm("'.Trad::A_CONFIRM_DELETE_ISSUE.'")) {
				$(this).closest("form")
					.find("input[name=\"edit_issue\"]").attr("name", "delete_issue").end()
					.submit();
			}
		});

		$(".box-details .a-edit").click(function() {
			$(this).closest("form")
				.find(".t-display").toggle().end()
				.find(".i-display").toggle().end();
			$(".box-details .select-status").change();
		});
		$(".box-details .select-status").change(function() {
			if ($(this).find("option:selected").data("match")) {
				$(".box-details .select-users").show();
			}
			else {
				$(".box-details .select-users").hide();
			}
		});
		$(".box-details .i-display .label").click(function() {
			$(this).toggleClass("label-selected");
		});
		$(".box-details form").submit(function() {
			var val = "";
			$(this).find(".label-selected").each(function() {
				val += $(this).data("id")+",";
			});
			$(this).find("input[name=\"issue_labels\"]").val(val);
		});
	';
}
else {
	$load = 'error/404';
}


?>