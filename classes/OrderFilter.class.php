<?php

class OrderFilter {

	public static $filter;

	public static function order_id_desc($a, $b) {
		if (empty($a)) { return -1; }
		if (empty($b)) { return 1; }
		return ($a['id'] < $b['id']) ? 1 : -1;
	}
	public static function order_id_asc($a, $b) {
		if (empty($a)) { return -1; }
		if (empty($b)) { return 1; }
		return ($a['id'] < $b['id']) ? -1 : 1;
	}
	public static function order_mod_desc($a, $b) {
		if (empty($a)) { return -1; }
		if (empty($b)) { return 1; }
		return ($a['edit'] < $b['edit']) ? 1 : -1;
	}
	public static function order_mod_asc($a, $b) {
		if (empty($a)) { return -1; }
		if (empty($b)) { return 1; }
		return ($a['edit'] < $b['edit']) ? -1 : 1;
	}

	public static function compare_time($a, $b) {
		if (empty($a)) { return -1; }
		if (empty($b)) { return 1; }
		return ($a['time'] > $b['time']) ? -1 : 1;
	}

	public static function filter_statuses($a) {
		return in_array($a['status'], self::$filter);
	}

	public static function filter_open($a) {
		return in_array($a['open'], self::$filter);
	}

	public static function filter_label($a) {
		foreach (self::$filter as $v) {
			if (!in_array($v, $a['labels'])) { return false; }
		}
		return true;
	}

	public static function filter_user($a) {
		if (in_array($a['openedby'], self::$filter)) { return true; }
		foreach ($a['edits'] as $e) {
			if (!empty($e) && in_array($e['by'], self::$filter)) {
				return true;
			}
		}
		return false;
	}

	public static function filter_status($a) {
		foreach (self::$filter as $k => $v) {
			if ($a['status'] != $k || $a['assignedto'] != $v) {
				return false;
			}
		}
		return true;
	}
}

?>