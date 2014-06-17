<?php

if (!isset($config)) {
	exit;
}

function strict_lower($a, $b) {
	$ea = explode('.', $a);
	$eb = explode('.', $b);
	for ($i=0; $i < count($ea); $i++) { 
		if (!isset($eb[$i])) { $eb[$i] = 0; }
		$na = intval($ea[$i]);
		$nb = intval($eb[$i]);
		if ($na > $nb) { return false; }
		if ($na < $nb) { return true; }
	}
	return false;
}

if (strict_lower($config['version'], '0.3')) {

	$config['nb_last_activity_rss'] = 20;

}

$settings = new Settings();
if ($config['url_rewriting']) { $settings->url_rewriting(); }
$settings->save();

header('Content-Type: text/html; charset=utf-8');
die('Mise à jour effectuée avec succès ! Raffraichissez cette page pour accéder à Bumpy Boopby.');

?>