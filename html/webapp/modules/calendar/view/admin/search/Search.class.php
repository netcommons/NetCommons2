<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Admin_Search extends Action
{
	// リクエストを受け取るため
	var $block_id = null;
	var $limit = null;
	var $offset = null;
	var $only_count = null;
	var $target_room = null;

	// WHERE句用パラメータ
	var $params = null;
	var $sqlwhere = null;
	
	// 値をセットするため
	var $count = 0;
	var $results = null;
	
	// Filterによりセット
	var $room_id_arr = null; 
	
	// コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	
    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		//配置していない
		$_user_id = $this->session->getParameter("_user_id");

		//表示するルームなし
		$sqlwhere = " WHERE 1=1";
		if (empty($this->room_id_arr) && empty($_user_id)) {
			return 'success'; 
		}
		if (!empty($_user_id) && empty($this->target_room)) {
			$this->room_id_arr[] = CALENDAR_ALL_MEMBERS_ID;
		}
		$sqlwhere .= " AND plan.room_id IN (".implode(",", $this->room_id_arr).")";
		$sqlwhere .= $this->sqlwhere;
		$sql = "SELECT COUNT(*)" .
				" FROM {calendar_plan} plan" .
				" LEFT JOIN {calendar_plan_details} details ON (plan.plan_id=details.plan_id)". 
				$sqlwhere;
		$result = $this->db->execute($sql, $this->params, null ,null, false);
		if ($result !== false) {
			$this->count = $result[0][0];
		} else {
			$this->count = 0;
		}
		if ($this->only_count == _ON) {
			return 'count';
		}

		if ($this->count > 0) {
			$sql = "SELECT plan.*, details.*" .
					" FROM {calendar_plan} plan" .
					" LEFT JOIN {calendar_plan_details} details ON (plan.plan_id=details.plan_id)". 
					$sqlwhere.
					" ORDER BY plan.start_time_full";
			$this->results =& $this->db->execute($sql ,$this->params, $this->limit, $this->offset, true, array($this, '_fetchcallback'));
		}
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド(blocks)
	 * @param result adodb object
	 * @access	private
	 */
	function _fetchcallback($result) 
	{
		$ret = array();
		$i = 0;
		while ($row = $result->fetchRow()) {
			$ret[$i] = array();
			if ($row['room_id'] == 0) {
				$ret[$i]['room_name'] = CALENDAR_ALL_MEMBERS_LANG;
			} else {
				$ret[$i]['room_id'] = $row['room_id'];
			}
			$ret[$i]['pubDate'] = $row['start_time_full'];
			$ret[$i]['title_icon'] = $row['title_icon'];
			$ret[$i]['title'] = $row['title'];
    		$ret[$i]['url'] = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
								"&page_id=".$this->session->getParameter("_main_page_id").
								"&active_center=calendar_view_main_init" .
								"&date=".timezone_date($row['start_time_full'],false,"Ymd").
								"&current_time=".timezone_date($row['start_time_full'],false,"His").
								"&display_type=".CALENDAR_DAILY;
			
			$ret[$i]['description'] = "";
			if ($row['location'] != "") {
				$ret[$i]['description'] .= sprintf(CALENDAR_LOCATION, $row['location']);
			}
			if ($row['contact'] != "") {
				$ret[$i]['description'] .= sprintf(CALENDAR_CONTACT, $row['contact']);
			}
			if ($row['description'] != "") {
				$ret[$i]['description'] .= sprintf(CALENDAR_DESCRIPTION, $row['description']);
			}
			
			$ret[$i]['user_id'] = $row['insert_user_id'];
			$ret[$i]['user_name'] = $row['insert_user_name'];
			$i++;
		}
		return $ret;
	}
}
?>