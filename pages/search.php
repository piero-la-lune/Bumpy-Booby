<?php

# Cheating to avoid the issue of get parameters when URL Rewriting is not
# enabled.
if (isset($_POST['action']) && $_POST['action'] = 'search') {
	$url = new Url(getProject().'/search');
	if (isset($_POST['q'])) { $url->addParam('q', $_POST['q']); }
	header('Location: '.$url->get());
	exit;
}

$issues = Issues::getInstance();
$text = '<p>'.Trad::S_NO_ISSUE.'</p>';
$small = '';
if (isset($_GET['q'])) {
	$q = trim($_GET['q']);
	if (preg_match('/^#?([0-9]+)$/', $q, $matches)) {
		if ($issues->get($matches[1])) {
			header('Location: '.Url::parse(getProject().'/issues/'.$matches[1]));
			exit;
		}
		$small = '#'.$matches[1];
	}
	elseif (preg_match('/^@(.+)$/', $q, $matches)) {
		foreach ($config['users'] as $u) {
			if ($u['username'] == $matches[1]) {
				header('Location: '.Url::parse('users/'.$u['id']));
				exit;
			}
		}
		$text = '<p>'.Trad::S_NO_USER.'</p>';
		$small = '@'.htmlspecialchars($matches[1]);
	}
	else {
		foreach ($config['users'] as $u) {
			if ($u['username'] == $q) {
				header('Location: '.Url::parse('users/'.$u['id']));
				exit;
			}
		}
		$words = array();
		$wds = explode(' ', $q);
		foreach ($wds as $w) {
			if (strlen($w) > 2) {
				$words[] = htmlspecialchars(strtolower($w));
			}
		}
		$matches = array();
		$iss = $issues->getAll();
		if (!$iss) {
			# if the user is not allowed to access issues
			$iss = array();
		}
		foreach ($iss as $i) {
			$points = 0;
			$sum = htmlspecialchars($i['summary']);
			$tex = htmlspecialchars($i['text']);
			$sumRep = $sum;
			$texRep = '';
			foreach ($words as $w) {
				$nbSum = stripos($sum, $w);
				$nbTex = stripos($tex, $w);
				if ($nbSum !== false) {
					$points = $points+4;
				}
				if ($nbTex !== false) {
					$points = $points+2;
					if (empty($texRep)) {
						$texRep = Text::intro(
							$tex,
							$config['length_search_text'],
							true,
							$w
						);
					}
				}
				foreach ($i['edits'] as $e) {
					if (empty($e) || $e['type'] != 'comment') { continue; }
					$com = htmlspecialchars($e['text']);
					$nbCom = stripos($com, $w);
					if ($nbCom !== false) {
						$points = $points+1;
						if (empty($texRep)) {
							$texRep = Text::intro(
								$com,
								$config['length_search_text'],
								true,
								$w
							);
						}
					}
				}
				$texRep = str_ireplace(
					$w,
					'<span class="found">'.$w.'</span>',
					$texRep
				);
				$sumRep = str_ireplace(
					$w,
					'<span class="found">'.$w.'</span>',
					$sumRep
				);
			}
			if ($points) {
				if (empty($texRep)) {
					$texRep = Text::intro(
						$tex,
						$config['length_search_text'],
						true
					);
				}
				$matches[] = array(
					'id' => $i['id'],
					'summary' => $sumRep,
					'text' => $texRep,
					'status' => $i['status'],
					'points' => $points,
					'edit' => $i['edit']
				);
			}
		}
		if (!empty($matches)) {
			usort($matches, function($a, $b) {
				if ($a['points'] > $b['points']) { return -1; }
				else if ($a['points'] < $b['points']) { return 1; }
				else if ($a['edit'] > $b['edit']) { return -1; }
				return -1;
			});
			function render($a) {
				global $config;
				$html = '';
				foreach ($a as $m) {
					$url = Url::parse(getProject().'/issues/'.$m['id']);
					$html .= '<div class="div-preview-issue">'
						.'<div class="table">'
							.'<div class="cell-left">'
								.'<a class="a-id-issue" href="'.$url.'" '
									.'style="background-color:'
									.$config['statuses'][$m['status']]['color']
									.'">'
									.'<span>#</span>'.$m['id']
								.'</a>'
							.'</div>'
							.'<div class="cell-right">'
								.'<a class="a-summary" href="'.$url.'">'
									.$m['summary']
									.'</a>'
								.'<span class="grey">'.$m['text'].'</span>'
							.'</div>'
						.'</div>'
					.'</div>';
				}
				$html = Text::remove_blanks($html);
				return $html;
			}
			$url = new Url(getProject().'/search', array('q' => $q));
			$pager = new Pager();
			$text = '<p>'.str_replace(
				'%nb%',
				count($matches),
				Trad::S_MATCHING_ISSUES
			).'.</p>';
			$text .= '<p>&nbsp;</p>';
			$text .= $pager->get(
				$matches,
				$url,
				'render',
				$config['search_per_page']
			);
		}
		$small = implode(' ', $words);
	}
}

$title = Trad::T_SEARCH;

$content = '<h1>'.Trad::T_SEARCH.' <small>'.$small.'</small></h1>';
$content .= $text;

?>