<?php

# Cheating to avoid the issue of get parameters when URL Rewriting is not
# enabled.
if (isset($_POST['action']) && $_POST['action'] = 'sort-filter') {
	$url = new Url(getProject().'/issues');
	if (isset($_GET['label'])) {
		$url = new Url(getProject().'/labels/'.$_GET['label']);
	}
	if (isset($_POST['user'])) {
		$url->addParam('user', $_POST['user']);
	}
	if (isset($_POST['status'])) {
		$url->addParam('status', $_POST['status']);
	}
	if (isset($_POST['sort'])) {
		$url->addParam('sort', $_POST['sort']);
	}
	if (isset($_POST['statuses'])) {
		$url->addParam('statuses', $_POST['statuses']);
	}
	if (isset($_POST['open'])) {
		$url->addParam('open', $_POST['open']);
	}
	if (isset($_POST['perpage'])) {
		$url->addParam('perpage', $_POST['perpage']);
	}
	header('Location: '.$url->get());
	exit;
}

$issues = Issues::getInstance();

$title = Trad::T_BROWSE_ISSUES;

$a = $issues->getAll();

$error404 = false;
$url = new Url(getProject().'/issues');

$label = NULL;
if (isset($_GET['label'])) {
	if (isset($config['labels'][$_GET['label']])) {
		OrderFilter::$filter = array($_GET['label']);
		$a = array_filter($a, array('OrderFilter', 'filter_label'));
		$url = new Url(getProject().'/labels/'.$_GET['label']);
		$label = $config['labels'][$_GET['label']];
	}
	else {
		$error404 = true;
	}
}

$user = NULL;
if (isset($_GET['user'])) {
	$id = intval($_GET['user']);
	if (isset($config['users'][$id])) {
		OrderFilter::$filter = array($id);
		$a = array_filter($a, array('OrderFilter', 'filter_user'));
		$url->addParam('user', $id);
		$user = $config['users'][$id];
	}
	else {
		$error404 = true;
	}
}

$status = NULL;
if (isset($_GET['status'])) {
	$p = explode(',', $_GET['status']);
	if (count($p) == 2
		&& isset($config['statuses'][$p[0]])
		&& isset($config['users'][$p[1]])
	) {
		OrderFilter::$filter = array($p[0] => $p[1]);
		$a = array_filter($a, array('OrderFilter', 'filter_status'));
		$url->addParam('status', implode(',', $p));
		$status = array($p[0] => $p[1]);
	}
	else {
		$error404 = true;
	}
}

$open = 'open';
if (isset($_GET['open'])) {
	if ($_GET['open'] == 'closed') {
		OrderFilter::$filter = array(false);
		$a = array_filter($a, array('OrderFilter', 'filter_open'));
		$url->addParam('open', 'closed');
		$open = 'closed';
	}
	elseif ($_GET['open'] == 'open') {
		OrderFilter::$filter = array(true);
		$a = array_filter($a, array('OrderFilter', 'filter_open'));
		$url->addParam('open', 'open');
		$open = 'open';
	}
	else {
		$url->addParam('open', 'all');
		$open = 'all';
	}
}
else {
	OrderFilter::$filter = array(true);
	$a = array_filter($a, array('OrderFilter', 'filter_open'));
}

$statuses = array_keys($config['statuses']);
if (isset($_GET['statuses']) && !empty($_GET['statuses'])) {
	$statuses = array();
	$st = explode(",", $_GET['statuses']);
	foreach ($st as $s) {
		if (isset($config['statuses'][$s])) {
			$statuses[] = $s;
		}
	}
	OrderFilter::$filter = $statuses;
	$a = array_filter($a, array('OrderFilter', 'filter_statuses'));
	$url->addParam('statuses', implode(',', $statuses));
}

$sort = array(0 => 'id', 1 => 'desc');
if (isset($_GET['sort'])
	&& method_exists('OrderFilter', 'order_'.$_GET['sort'])
) {
	$url->addParam('sort', $_GET['sort']);
	$sort = explode('_', $_GET['sort']);
}
uasort($a, array('OrderFilter', 'order_'.implode('_', $sort)));

$perpage = $config['issues_per_page'];
if (isset($_GET['perpage'])) {
	$perpage = intval($_GET['perpage']);
	if ($perpage <= 1) { $perpage = $config['issues_per_page']; }
	$url->addParam('perpage', $perpage);
}

$pager = new Pager();
$html = $pager->get(
	$a,
	$url,
	array($issues, 'html_list'),
	$perpage
);
$nb = count($a);

if ($nb == 0) {
	$html = '<p>&nbsp;</p><p>'.Trad::S_NO_ISSUE.'</p><p>&nbsp;</p>';
}

if (!$html || $error404) {
	$load = 'error/404';
}
else {

$content = '<h1>'.Trad::T_BROWSE_ISSUES
	.' <small>'.str_replace('%nb%', $nb, Trad::S_MATCHING_ISSUES).'</small>'
	.'</h1>';

if (!canAccess('new_issue')
	&& !$config['loggedin']
	&& canAccess('signup')
	&& in_array(DEFAULT_GROUP, $config['permissions']['post_comment'])
) {
	$content .= '<p>'.Trad::A_PLEASE_LOGIN_ISSUE.'</p><p>&nbsp;</p>';
}

$content .= '
	<div class="div-table-issues">
		<div class="div-issues">'.$html.'</div>';

$open_selected = ($open != 'closed') ?
	'btn-open selected':
	'btn-open unselected';
$closed_selected = ($open != 'open') ?
	'btn-closed selected':
	'btn-closed unselected';

$content .= '<div class="div-filter-issues">'
	.'<div class="div-affix">'
		.'<div class="box box-sort-filter">'
			.'<div class="inner-form">'
				.'<form action="'.$url->getBase().'" method="post">';
if ($label) {
	$content .= '<p>'
					.Trad::F_FILTER_LABELS
					.'<a href="javascript:;" class="label" style="'
					.'background-color:'.$label['color'].'">'
						.$label['name']
					.'</a>'
				.'</p>';
}
if ($user) {
	$content .= '<p>'
					.Trad::F_FILTER_USERS
					.'&nbsp;<a href="javascript:;">'
						.htmlspecialchars($user['username'])
					.'</a>'
				.'</p>'
				.'<input type="hidden" name="user" value="'.$user['id'].'" />';
}
$content .= 	'<p>'
					.Trad::F_FILTER_STATES
					.'<a href="javascript:;" class="'.$open_selected.'">'
						.Trad::W_OPEN
					.'</a><a href="javascript:;" class="'.$closed_selected.'">'
						.Trad::W_CLOSED
					.'</a>'
				.'</p>'
				.'<p>'
					.Trad::F_FILTER_STATUSES;
if ($status) {
	$val = array();
	foreach ($status as $k => $v) {
		$s = $config['statuses'][$k];
		$content .= '<a href="javascript:;" class="btn-status disabled" style="'
					.'background-color:'.$s['color'].'" data-id="'.$k.'" >'
						.Text::status($k, $v, false)
					.'</a>';
		$val[] = $k.','.$v;
	}
	$content .= '<input type="hidden" name="status" value="'
		.implode(',', $val).'" />';
}
else {
	foreach ($config['statuses'] as $k => $v) {
		$selected = (in_array($k, $statuses)) ?
			'btn-status selected':
			'btn-status unselected';
		$content .= '<a href="javascript:;" class="'.$selected.'" style="'
					.'background-color:'.$v['color'].'" data-id="'.$k.'" >'
						.Text::status($k, NULL, false, false)
					.'</a>';
	}
}
$content .= 	'</p>'
				.'<p>'
					.Trad::F_SORT_BY.'<br />'
					.'<select>'
						.Text::options(array(
							'id' => Trad::F_SORT_ID,
							'mod' => Trad::F_SORT_MOD
						), $sort[0])
					.'</select>'
					.'<select>'
						.Text::options(array(
							'desc' => Trad::F_SORT_DESC,
							'asc' => Trad::F_SORT_ASC
						), $sort[1])
					.'</select>'
				.'</p>'
				.'<p>'
					.Trad::F_ISSUES_PAGE.'&nbsp;'
					.'<select name="perpage">'
						.Text::options(array(
							$config['issues_per_page'] =>
								$config['issues_per_page'],
							'10' => 10,
							'20' => 20,
							'50' => 50,
							'100' => 100
						), $perpage)
					.'</select>'
				.'</p>'
				.'<input type="hidden" name="sort" value="id_desc" />'
				.'<input type="hidden" name="statuses" value="" />'
				.'<input type="hidden" name="open" value="" />'
				.'<input type="hidden" name="action" value="sort-filter" />'
				.'<div class="form-actions">'
					.'<button type="submit" class="btn btn-primary">'
						.Trad::V_APPLY
					.'</button>'
				.'</div>'
				.'</form>'
			.'</div>'
		.'</div>'
	.'</div>'
.'</div>'
	.'</div>';

}

?>