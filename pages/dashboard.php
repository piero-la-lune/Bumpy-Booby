<?php

$title = Trad::T_DASHBOARD;

$intro = '<div class="div-intro text-container">'
	.Text::markdown($config['projects'][getProject()]['description'])
	.'</div>';

if (canAccess('issues')) {

	$issues = Issues::getInstance();
	$nb_display = $config['nb_last_activity_dashboard'];

	$activity = array();
	for ($i=0; $i<$nb_display; $i++) {
		$activity[$i] = array('id' => 0, 'time' => 0, 'edit' => 0);
	}
	$nb_statuses = array();
	foreach ($config['statuses'] as $k => $v) {
		if ($v['dashboard']) { $nb_statuses[$k] = 0; }
	}

	$iss = $issues->getAll();
	foreach ($iss as $i) {
		if (isset($nb_statuses[$i['status']])) {
			$nb_statuses[$i['status']]++;
		}
		if ($i['date'] > $activity[$nb_display-1]['time']) {
			$activity[$nb_display-1] = array(
				'id' => $i['id'],
				'time' => $i['date'],
				'edit' => 0
			);
			usort($activity, array('OrderFilter', 'compare_time'));
		}
		if ($i['edit'] > $activity[$nb_display-1]['time']) {
			foreach ($i['edits'] as $e) {
				if (empty($e)) { continue; }
				if ($e['date'] > $activity[$nb_display-1]['time']) {
					$activity[$nb_display-1] = array(
						'id' => $i['id'],
						'time' => $e['date'],
						'edit' => $e['id']
					);
					usort($activity, array('OrderFilter', 'compare_time'));
				}
			}
		}
	}

	$edits = '';
	foreach ($activity as $v) {
		if ($v['time'] == 0) { continue; }
		$i = $iss[$v['id']];
		if ($v['edit'] == 0) {
			$edit = array(
				'id' => 0,
				'type' => 'newissue',
				'by' => $i['openedby'],
				'date' => $i['date']);
			$preview = '<br />'
				.Text::intro($i['text'], $config['length_preview_text']);
		}
		else {
			$edit = $i['edits'][$v['edit']];
			if ($edit['type'] == 'comment') {
				$preview = '<br />'
					.Text::intro($edit['text'], $config['length_preview_text']);
			}
			else {
				$preview = '';
			}
		}
		$edits .= '<div class="div-last-edit">'
			.'<a class="a-summary" href="'
				.Url::parse(getProject().'/issues/'.$i['id'])
				.'">'
				.htmlspecialchars($i['summary'])
			.'</a>'
			.$preview
			.'<br />'.Issues::get_preview_edit($edit)
		.'</div>';
	}

	$pie_html = '';
	$sum = array_sum($nb_statuses);
	if ($sum > 0) {
		$pie = array();
		$start = 0;
		foreach ($nb_statuses as $k => $v) {
			$angle = $v/$sum*2*pi();
			$pie[] = Array(
				'nb' => $v,
				'start' => $start,
				'end' => $start+$angle,
				'color' => $config['statuses'][$k]['color'],
				'url' => Url::parse(getProject().'/issues',
					array('statuses' => $k, 'open' => 'all'))
			);
			$start = $start + $angle;
		}
		$pie_html = '<div class="div-pie-statuses" style="display:none">'
			.'<script>'
				.'var pies_data = {"pie-statuses": '.json_encode($pie).'};'
			.'</script>'
			.'<canvas class="pie-statuses" width="300" height="300" '
			.'data-data="pie-statuses">'
			.'</canvas>'
			.'<div class="div-nb-status"></div>'
		.'</div>';
	}

	$content = '<div class="div-table">'
		.'<div class="div-cell div-cell-left">'
			.'<h1>'.Trad::T_DASHBOARD.'</h1>'
			.$intro
			.$pie_html
		.'</div>'
		.'<div class="div-cell div-last-edits">'
			.'<h2>'.Trad::T_LAST_UPDATES.'</h2>'
			.$edits
		.'</div>'
	.'</div>';

}
else {
	$content = '<h1>'.Trad::T_DASHBOARD.'</h1>'
		.$intro
		.'<p>'.Trad::A_PLEASE_LOGIN_ISSUES.'</p>';
}

?>