<?php

class Pager {

	public function get($a, $url, $render, $per_page) {
		global $config;

		$keys = array_keys($a);
		$nb = count($keys);

		$page = (isset($_GET['pagen']) && !empty($_GET['pagen'])) ?
			intval($_GET['pagen']):
			1;
		$page = ($page < 1) ? 1 : $page;
		$nbpages = ceil($nb/$per_page);
		$start = $per_page*($page-1);

		if ($nb == 0 || $start > $nb) { return false; }

		$end = min($start+$per_page, $nb);

		$proceed = array();
		for ($i=$start; $i<$end; $i++) {
			$proceed[] = $a[$keys[$i]];
		}

		$html = call_user_func($render, $proceed);

		$html .= '<div class="pager">';
		if ($page > 1) {
			$url->addParam('pagen', $page-1);
			$html .= '<a href="'.$url->get().'" class="previous a-icon-hover">'
				.'<i class="icon-chevron-left"></i> '
				.Trad::W_PREVIOUS
			.'</a>';
		}
		$html .= '&nbsp;<span class="current">'
			.str_replace(
				array('%nb1%', '%nb2%'),
				array($page, $nbpages),
				Trad::W_CURRENT)
		.'</span>&nbsp;';
		if ($page < $nbpages) {
			$url->addParam('pagen', $page+1);
			$html .= '<a href="'.$url->get().'" class="next a-icon-hover">'
				.Trad::W_NEXT
				.' <i class="icon-chevron-right"></i>'
			.'</a>';
		}
		$html .= '</div>';
		return $html;
	}

}