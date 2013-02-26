<?php

# Cheating to avoid the issue of get parameters when URL Rewriting is not
# enabled.
if (isset($_POST['action']) && $_POST['action'] = 'sort-filter') {
	$url = new Url(getProject().'/issues');
	if (isset($_GET['label'])) {
		$url = new Url(getProject().'/labels/'.$_GET['label']);
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

$sortOrFilter = false;
$error404 = false;
$url = new Url(getProject().'/issues');

if (isset($_GET['label'])) {
	if (!isset($config['labels'][$_GET['label']])) {
		$error404 = true;
	}
	else {
		OrderFilter::$filter = array($_GET['label']);
		$a = array_filter($a, array('OrderFilter', 'filter_label'));
		if (!isset($_GET['open'])) { $_GET['open'] = "all"; }
		$url = new Url(getProject().'/labels/'.$_GET['label']);
	}
}
if (isset($_GET['open'])) {
	if ($_GET['open'] == 'closed') {
		$filter_open = array(false);
		OrderFilter::$filter = array(false);
		$a = array_filter($a, array('OrderFilter', 'filter_open'));
	}
	else if ($_GET['open'] == 'open') {
		$filter_open = array(true);
		OrderFilter::$filter = array(true);
		$a = array_filter($a, array('OrderFilter', 'filter_open'));
	}
	else {
		$filter_open = array();
	}
	$sortOrFilter = true;
}
else {
	$filter_open = array(true);
	OrderFilter::$filter = array(true);
	$a = array_filter($a, array('OrderFilter', 'filter_open'));
}
if (isset($_GET['statuses'])) {
	$statuses = explode(",", $_GET['statuses']);
	$filter_statuses = array();
	foreach ($statuses as $s) {
		if (isset($config['statuses'][$s])) {
			$filter_statuses[] = $s;
		}
	}
	OrderFilter::$filter = $filter_statuses;
	$a = array_filter($a, array('OrderFilter', 'filter_statuses'));
	$sortOrFilter = true;
}
else {
	$filter_statuses = array_keys($config['statuses']);
}
if (isset($_GET['sort'])
	&& method_exists('OrderFilter', 'order_'.$_GET['sort'])
) {
	$sort = $_GET['sort'];
	$sortOrFilter = true;
}
else {
	$sort = 'id_desc';
}
uasort($a, array('OrderFilter', 'order_'.$sort));
$arr_sort = explode('_', $sort);

if ($sortOrFilter) {
	if (in_array(true, $filter_open)) { $open = 'open'; }
	else if (in_array(false, $filter_open)) { $open = 'closed'; }
	else { $open = 'all'; }
	$url->addParam('sort', $sort);
	$url->addParam('statuses', implode(',', $filter_statuses));
	$url->addParam('open', $open);
}
$pager = new Pager();
if (isset($_GET['perpage'])) {
	$perpage = intval($_GET['perpage']);
	$url->addParam('perpage', $perpage);
}
else {
	$perpage = $config['issues_per_page'];
}
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

$open_selected = (empty($filter_open) || in_array(true, $filter_open)) ?
	'btn-open selected':
	'btn-open unselected';
$closed_selected = (empty($filter_open) || in_array(false, $filter_open)) ?
	'btn-closed selected':
	'btn-closed unselected';

$content .= '<div class="div-filter-issues">'
	.'<div class="div-affix">'
		.'<div class="box box-sort-filter">'
			.'<div class="inner-form">'
				.'<form action="'.$url->getBase().'" method="post">';
if (isset($_GET['label'])) {
	$label = $config['labels'][$_GET['label']];
	$content .= '<p>'
					.Trad::F_FILTER_LABELS
					.'<a href="javascript:;" class="label" style="'
					.'background-color:'.$label['color'].'">'
						.$label['name']
					.'</a>'
				.'</p>';
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
foreach ($config['statuses'] as $k => $v) {
	$selected = (in_array($k, $filter_statuses)) ?
		'btn-status selected':
		'btn-status unselected';
	$content .= '<a href="javascript:;" class="'.$selected.'" style="'
				.'background-color:'.$v['color'].'" data-id="'.$k.'" >'
					.Text::status($k, NULL, false, false)
				.'</a>';
}
$content .= 	'</p>'
				.'<p>'
					.Trad::F_SORT_BY.'<br />'
					.'<select>'
						.Text::options(array(
							'id' => Trad::F_SORT_ID,
							'mod' => Trad::F_SORT_MOD
						), $arr_sort[0])
					.'</select>'
					.'<select>'
						.Text::options(array(
							'desc' => Trad::F_SORT_DESC,
							'asc' => Trad::F_SORT_ASC
						), $arr_sort[1])
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