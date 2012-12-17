<?php

// Cheating to avoid the issue of get parameters when URL Rewriting is not enabled. */
if (isset($_POST['action']) && $_POST['action'] = 'sort-filter') {
	$url = new Url(getProject().'/issues');
	if (isset($_GET['label'])) { $url = new Url(getProject().'/labels/'.$_GET['label']); }
	if (isset($_POST['sort'])) { $url->addParam('sort', $_POST['sort']); }
	if (isset($_POST['statuses'])) { $url->addParam('statuses', $_POST['statuses']); }
	if (isset($_POST['open'])) { $url->addParam('open', $_POST['open']); }
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
if (isset($_GET['sort']) && method_exists('OrderFilter', 'order_'.$_GET['sort'])) {
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
$html = $pager->get($a, $url, array($issues, 'html_list'), $config['issues_per_page']);
$nb = count($a);

if ($nb == 0) {
	$html = '<p>&nbsp;</p><p>'.Trad::S_NO_ISSUE.'</p><p>&nbsp;</p>';
}

if (!$html || $error404) {
	$load = 'error/404';
} else {

$content = '<h1>'.Trad::T_BROWSE_ISSUES.' <small>'.str_replace('%nb%', $nb, Trad::S_MATCHING_ISSUES).'</small></h1>';

$statuses_bool = count($filter_statuses) != count($config['statuses']) && !empty($filter_statuses);
if (!empty($filter_open) || $statuses_bool || isset($_GET['label'])) {
	$content .= '<p class="filter">'.Trad::F_FILTERS;
	if (isset($_GET['label'])) {
		$content .= '<span class="label" style="background-color:'.$config['labels'][$_GET['label']]['color'].'">'.$config['labels'][$_GET['label']]['name'].'</span>';
	}
	if (in_array(true, $filter_open)) {
		$content .= '<span class="btn-open btn-tag-small">'.Trad::W_OPEN.'</span> ';
	}
	if (in_array(false, $filter_open)) {
		$content .= '<span class="btn-closed btn-tag-small">'.Trad::W_CLOSED.'</span> ';
	}
	if ($statuses_bool) {
		foreach ($filter_statuses as $s) {
			$content .= '<span title="'.Text::status($s, NULL, false, false).'" class="square" style="background-color:'.$config['statuses'][$s]['color'].'"></span>';
		}
	}
	$content .= '</p>';
}

if (!canAccess('new_issue')
	&& !$config['loggedin']
	&& canAccess('signup')
	&& in_array(DEFAULT_GROUP, $config['permissions']['post_comment']))
{
	$content .= '<p style="margin-top: 0">'.Trad::A_PLEASE_LOGIN_ISSUE.'</p>';
}

$content .= $html;

$open_selected = (empty($filter_open) || in_array(true, $filter_open)) ? 'selected' : '';
$closed_selected = (empty($filter_open) || in_array(false, $filter_open)) ? 'selected' : '';

$content .= '
<div class="box-settings">
	<div class="top a-icon-hover">
		<i class="icon-chevron-down"></i>
		'.Trad::T_OPTIONS.'
	</div>
	<div class="box-in box-closed">
		<form action="'.$url->getBase().'" method="post" class="form-sort-filter">
			<p>
				'.Trad::F_FILTER_STATES.'
				<a href="javascript:;" class="btn-open btn-tag-small '.$open_selected.'">'.Trad::W_OPEN.'</a>
				<a href="javascript:;" class="btn-closed btn-tag-small '.$closed_selected.'">'.Trad::W_CLOSED.'</a>
			</p>
			<p>
				'.Trad::F_FILTER_STATUSES.'
			
';
foreach ($config['statuses'] as $k => $v) {
	$selected = (in_array($k, $filter_statuses)) ? 'selected' : '';
	$content .= ''
.'<a href="javascript:;" class="a-pick-status '.$selected.'" data-color="transparent" data-color-hover="'.$v['color'].'" data-id="'.$k.'" >'
.	Text::status($k, NULL, false, false)
.'</a>'
	;
}
$content .= '
			</p>
			<p>
				'.Trad::F_SORT_BY.'
				<select>
					'.Text::options(array(
						'id' => Trad::F_SORT_ID,
						'mod' => Trad::F_SORT_MOD
					), $arr_sort[0]).'
				</select>
				<select>
					'.Text::options(array(
						'desc' => Trad::F_SORT_DESC,
						'asc' => Trad::F_SORT_ASC
					), $arr_sort[1]).'
				</select>
			</p>
			<input type="hidden" name="sort" value="id_desc" />
			<input type="hidden" name="statuses" value="" />
			<input type="hidden" name="open" value="" />
			<input type="hidden" name="action" value="sort-filter" />
			<div class="form-actions">
				<button type="submit" class="btn btn-primary">'.Trad::V_APPLY.'</button>
			</div>
		</form>
	</div>
</div>
';

$javascript = '
	$(".form-sort-filter").submit(function(){
		var sel = $(this).find("select");

		var val = "";
		if (sel.eq(0).val() == "id") { val = "id_"; }
		else { val = "mod_"; }
		if (sel.eq(1).val() == "desc") { val = val+"desc"; }
		else { val = val+"asc"; }
		$(this).find("input[name=\"sort\"]").val(val);

		var arr = new Array();
		$(this).find(".a-pick-status.selected").each(function() {
			arr.push($(this).data("id"));
		});
		$(this).find("input[name=\"statuses\"]").val(arr.join(","));

		var val = "all";
		var ok1 = $(this).find(".btn-open").hasClass("selected");
		var ok2 = $(this).find(".btn-closed").hasClass("selected")
		if (ok1 && !ok2) {
			val = "open";
		}
		else if (ok2 && !ok1) {
			val = "closed";
		}
		$(this).find("input[name=\"open\"]").val(val);
	});
	$(".a-pick-status").hover(function(){
		$(this).css("border-bottom-color", $(this).data("color-hover"));
	}, function() {
		$(this).css("border-bottom-color", $(this).data("color"));
	});
	$(".a-pick-status").click(function(){
		$(this).toggleClass("selected");
		if ($(this).hasClass("selected")) {
			$(this).css("border-top-color", $(this).data("color-hover"));
			$(this).data("color", $(this).data("color-hover"));
		}
		else {
			$(this).css("border-top-color", "transparent");
			$(this).data("color", "transparent");
		}
	});
	$(".a-pick-status.selected").each(function() {
		$(this).css("border-color", $(this).data("color-hover"));
		$(this).data("color", $(this).data("color-hover"));
	});
	$(".form-sort-filter .btn-closed, .form-sort-filter .btn-open").click(function() {
		$(this).toggleClass("selected");
	});
';

}

?>