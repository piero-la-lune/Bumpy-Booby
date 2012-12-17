<?php

	if (onlyDefaultProject()) {
		header('Location: '.Url::parse(DEFAULT_PROJECT.'/dashboard'));
		exit;
	}

	$title = Trad::T_PROJECTS;
	$content = '
		<h1>'.Trad::T_PROJECTS.'</h1>
	';
	if (!empty($config['intro'])) {
		$content .= '<div class="div-intro">'.Text::markdown($config['intro']).'</div>';
	}
	$projects = '';
	$i = 0;
	foreach ($config['projects'] as $k => $v) {
		if (canAccessProject($k)) {
			if ($i % 2 == 0) { $projects .= '<div class="div-preview-projects">'; }
			$text = Text::intro($v['description'], $config['lenght_preview_project'], false);
			$projects .= '
				<div class="div-preview-project">
					<a href="'.Url::parse($k.'/dashboard').'">
						<span>'.$k.'</span>
						'.$text.'
					</a>
				</div>
			';
			if ($i % 2 == 1) { $projects .= '</div>'; }
			$i++;
		}
	}
	if (!empty($projects)) {
		if ($i % 2 == 1) { $projects .= '<div class="div-preview-project">&nbsp;</div></div>'; }
		$content .= $projects;
	}
	else {
		$content .= '<p>'.Trad::S_NO_PROJECT.'</p>';
	}

?>