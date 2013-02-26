<?php

	if (onlyDefaultProject()) {
		header('Location: '.Url::parse(DEFAULT_PROJECT.'/dashboard'));
		exit;
	}

	$title = Trad::T_PROJECTS;
	$content = '<h1>'.Trad::T_PROJECTS.'</h1>'
		.'<div class="div-intro">'
			.Text::markdown($config['intro'])
		.'</div>';

	$projects = '';
	$i = 0;
	foreach ($config['projects'] as $k => $v) {
		if (canAccessProject($k)) {
			if ($i % 2 == 0) {
				$projects .= '<div class="div-preview-projects">';
			}
			$cut = $config['length_preview_project'];
			$projects .= '<div class="div-preview-project">'
				.'<a href="'.Url::parse($k.'/dashboard').'">'
					.'<span>'.$k.'</span>'
					.Text::intro($v['description'], $cut, false)
					.'</a>'
			.'</div>';
			if ($i % 2 == 1) {
				$projects .= '</div>';
			}
			$i++;
		}
	}
	if (!empty($projects)) {
		if ($i % 2 == 1) {
			$projects .= '<div class="div-preview-project">&nbsp;</div></div>';
		}
		$content .= $projects;
	}
	else {
		$content .= '<p>'.Trad::S_NO_PROJECT.'</p>';
	}

?>