<?php

$title = Trad::T_DASHBOARD;

$content = '
	<h1>'.Trad::T_DASHBOARD.'</h1>
';
if (!empty($config['projects'][getProject()]['description'])) {
	$content .= '<div class="div-intro">'.Text::markdown($config['projects'][getProject()]['description']).'</div>';
}

if (canAccess('issues')) {

	$issues = Issues::getInstance();

	$activity = array(); $nb_display = $config['nb_last_activity_dashboard'];
	for ($i=0; $i < $nb_display; $i++) {
		$activity[$i] = array('id' => 0, 'time' => 0, 'edit' => 0);
	}
	$nb_open = 0;
	$nb_statuses = array();
	foreach ($config['statuses'] as $k => $v) {
		if ($v['dashboard']) { $nb_statuses[$k] = 0; }
	}

	$iss = $issues->getAll();
	foreach ($iss as $i) {
		if (isset($nb_statuses[$i['status']])) {
			$nb_statuses[$i['status']]++;
		}
		if ($i['open']) {
			$nb_open++;
		}
		if ($i['date'] > $activity[$nb_display-1]['time']) {
			$activity[$nb_display-1] = array('id' => $i['id'], 'time' => $i['date'], 'edit' => 0);
			usort($activity, array('OrderFilter', 'compare_time'));
		}
		if ($i['edit'] > $activity[$nb_display-1]['time']) {
			foreach ($i['edits'] as $e) {
				if (empty($e)) { continue; }
				if ($e['date'] > $activity[$nb_display-1]['time']) {
					$activity[$nb_display-1] = array('id' => $i['id'], 'time' => $e['date'], 'edit' => $e['id']);
					usort($activity, array('OrderFilter', 'compare_time'));
				}
			}
		}
	}

	$statuses = '';
	$max = max($nb_statuses);
	foreach ($nb_statuses as $k => $v) {
		$url = Url::parse(getProject().'/issues', array('statuses' => $k, 'open' => 'all'));
		$color = $config['statuses'][$k]['color'];
		$height = 160;
		$size = 4;
		if ($max > 0) {
			$height = $height*$v/$max;
			$size = $size*$v/$max;
		}
		$statuses .= '
			<a href="'.$url.'" class="a-widget-status">
				<span style="background-color:'.$color.'; height:'.$height.'px; line-height:'.$height.'px; font-size:'.$size.'em">'.$v.'</span>
				'.Text::status($k, NULL, false, false).'
			</a>
		';
	}
	$statuses = Text::remove_blanks($statuses);

	$edits = '';
	foreach ($activity as $v) {
		if ($v['time'] == 0) { continue; }
		$i = $iss[$v['id']];
		$url = Url::parse(getProject().'/issues/'.$i['id']);
		if ($v['edit'] == 0) {
			$text = Text::intro($i['text'], $config['lenght_preview_text']);
			$edits .= '
				<div>
					<a href="'.$url.'" class="summary">'.htmlspecialchars($i['summary']).'</a>
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
				$edits .= '
					<div>
						<a href="'.$url.'#e-'.$edit['id'].'" class="summary">'.htmlspecialchars($i['summary']).'</a>
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
				$edits .= '
					<div>
						<a href="'.$url.'#e-'.$edit['id'].'" class="summary">'.htmlspecialchars($i['summary']).'</a>
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
				$edits .= '
					<div>
						<a href="'.$url.'#e-'.$edit['id'].'" class="summary">'.htmlspecialchars($i['summary']).'</a>
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
				$edits .= '
					<div>
						<a href="'.$url.'#e-'.$edit['id'].'" class="summary">'.htmlspecialchars($i['summary']).'</a>
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

	$content .= '

		<div class="div-table">
			<div class="div-statuses">
				'.$statuses.'
				<br />
				<a href="'.Url::parse(getProject().'/issues').'" class="btn-open btn-tag">'.$nb_open.' <span>'.Trad::W_OPEN.'</span></a>
			</div>

			<div class="div-last-edits">
				<h3>'.Trad::T_LAST_UPDATES.'</h3>
				'.$edits.'
			</div>
		</div>

	';

}
else {
	$content .= '<p>'.Trad::A_PLEASE_LOGIN_ISSUES.'</p>';
}

?>