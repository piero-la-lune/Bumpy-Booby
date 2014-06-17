<?php

header('Content-Type: application/rss+xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<rss version="2.0">'."\n";

if (canAccess('issues') && getProject() !== false) {

	echo '	<channel>'."\n";
	echo '		<title>'.getProject().'</title>'."\n";
	echo '		<description>'.Trad::S_LAST_UPDATES.'</description>'."\n";
	echo '		<lastBuildDate>'.date('r').'</lastBuildDate>'."\n";
	echo '		<link>'.htmlspecialchars(Url::parse(getProject().'/dashboard')).'</link>'."\n";

	$issues = Issues::getInstance();

	$nb_display = $config['nb_last_activity_rss'];
	$activity = array();
	for ($i=0; $i<$nb_display; $i++) {
		$activity[$i] = array('id' => 0, 'time' => 0, 'edit' => 0);
	}

	$issues = $issues->getAll();
	foreach ($issues as $i) {
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
		$i = $issues[$v['id']];
		echo '		<item>'."\n";
		echo '			<title>#'.$i['id'].' '.htmlspecialchars($i['summary']).'</title>'."\n";
		echo '			<pubDate>'.date('r', $v['time']).'</pubDate>'."\n";
		if ($v['edit'] == 0) {
			echo '			<description><![CDATA['
				.htmlspecialchars(str_replace(
					array('%adj%', '%user%'),
					array(Trad::W_OPENED, Text::username($i['openedby'])),
					Trad::S_RSS_ISSUE_UPDATED
				))
				.'<br />'
				.Text::intro($i['text'], $config['length_preview_text'])
			.']]></description>'."\n";
			echo '			<link>'
				.htmlspecialchars(Url::parse(getProject().'/issues/'.$i['id']))
			.'</link>'."\n";
		}
		else {
			$edit = $i['edits'][$v['edit']];
			if ($edit['type'] == 'comment') {
				echo '			<description><![CDATA['
					.htmlspecialchars(str_replace(
						array('%adj%', '%user%'),
						array(Trad::W_COMMENTED, Text::username($edit['by'])),
						Trad::S_RSS_ISSUE_UPDATED
					))
					.'<br />'
					.Text::intro($edit['text'], $config['length_preview_text'])
				.']]></description>'."\n";
			}
			elseif ($edit['type'] == 'open' && $edit['changedto']) {
				echo '			<description><![CDATA['
					.htmlspecialchars(str_replace(
						array('%adj%', '%user%'),
						array(Trad::W_REOPENED, Text::username($edit['by'])),
						Trad::S_RSS_ISSUE_UPDATED
					))
				.']]></description>'."\n";
			}
			elseif ($edit['type'] == 'open') {
				echo '			<description><![CDATA['
					.htmlspecialchars(str_replace(
						array('%adj%', '%user%'),
						array(Trad::W_CLOSED, Text::username($edit['by'])),
						Trad::S_RSS_ISSUE_UPDATED
					))
				.']]></description>'."\n";
			}
			elseif ($edit['type'] == 'status') {
				echo '			<description><![CDATA['
					.htmlspecialchars(str_replace(
						array('%status%', '%user%'),
						array(
							Text::status($edit['changedto'], $edit['assignedto'], false),
							Text::username($edit['by'])
						),
						Trad::S_RSS_ISSUE_STATUS_UPDATED
					))
				.']]></description>'."\n";
			}
			echo '			<link>'
				.htmlspecialchars(Url::parse(getProject().'/issues/'.$i['id'], array(), 'e-'.$edit['id']))
			.'</link>'."\n";
		}
		echo '		</item>'."\n";
	}

	echo '	</channel>'."\n";

}

echo '</rss>';

exit;


?>