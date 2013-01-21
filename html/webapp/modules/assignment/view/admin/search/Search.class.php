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
class Assignment_View_Admin_Search extends Action
{
	// リクエストを受け取るため
	var $block_id = null;
	var $limit = null;
	var $offset = null;
	var $only_count = null;

	// WHERE句用パラメータ
	var $params = null;
	var $sqlwhere = null;

	// 値をセットするため
	var $count = 0;
	var $results = null;

	// Filterによりセット
	var $block_id_arr = null;
	var $room_id_arr = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $pagesView = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		if ($this->block_id_arr) {
			$sqlwhere = " WHERE block.block_id IN (".implode(",", $this->block_id_arr).")";
		} else {
			return 'success';
		}

		$ownUserID = $this->session->getParameter("_user_id");
		$chiefRoomIDs = $this->pagesView->getRoomIdByUserId($ownUserID, _AUTH_CHIEF);

		$sqlwhere .= " AND (body.report_id = 0".
						" OR report.status = ".ASSIGNMENT_STATUS_REREASED . " AND body.report_id != 0 AND report.insert_user_id = '".$ownUserID."'".
						" OR report.status = ".ASSIGNMENT_STATUS_REREASED . " AND body.room_id IN (".implode(",", $chiefRoomIDs)."))";
		$sqlwhere .= $this->sqlwhere;

		$sql = "SELECT COUNT(*)" .
				" FROM {assignment_body} body".
				" INNER JOIN {assignment} name ON (body.assignment_id = name.assignment_id)".
				" INNER JOIN {assignment_block} block ON (body.assignment_id=block.assignment_id) ".
				" LEFT JOIN {assignment} assign ON (body.body_id = assign.body_id)".
				" LEFT JOIN {assignment_report} report ON (body.body_id = report.body_id)".
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
			$sql = "SELECT block.block_id, body.*, name.assignment_name, ".
					"name.insert_user_id AS assign_insert_user_id, ".
					"name.insert_user_name AS assign_insert_user_name, ".
					"name.insert_time AS assign_insert_time, ".
					"report.submit_id, report.report_id, " .
					"report.insert_user_id, report.insert_user_name, report.insert_time ".
				" FROM {assignment_body} body".
				" INNER JOIN {assignment} name ON (body.assignment_id = name.assignment_id)".
				" INNER JOIN {assignment_block} block ON (body.assignment_id=block.assignment_id) ".
				" LEFT JOIN {assignment} assign ON (body.body_id = assign.body_id)".
				" LEFT JOIN {assignment_report} report ON (body.body_id = report.body_id)".
				$sqlwhere.
				" ORDER BY body.body_id DESC";

			$this->results =& $this->db->execute($sql ,$this->params, $this->limit, $this->offset, true, array($this, '_fetchcallback'));
		}

		return 'success';
	}

	/**
	 * fetch時コールバックメソッド(blocks)
	 *
	 * @param result adodb object
	 * @access	private
	 */
	function _fetchcallback($result)
	{
		$ret = array();
		$i = 0;
		while ($row = $result->fetchRow()) {
			$ret[$i] = array();
			$ret[$i]['block_id'] =  $row['block_id'];
			$ret[$i]['pubDate'] = !empty($row['insert_time']) ? $row['insert_time'] : $row['assign_insert_time'];
			$ret[$i]['title'] = $row['assignment_name'];
    		$ret[$i]['url'] = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
								"&block_id=".$row['block_id'].
								"&active_action=assignment_view_main_init".
								"&assignment_id=".$row['assignment_id'].
    							(!empty($row["submit_id"]) ? "&submit_id=".$row["submit_id"]."&report_id=".$row["report_id"] : "").
								"#_".$row['block_id'];
			$ret[$i]['description'] = $row['body'];
			$ret[$i]['user_id'] = !empty($row['insert_user_id']) ? $row['insert_user_id'] : $row['assign_insert_user_id'];
			$ret[$i]['user_name'] = !empty($row['insert_user_name']) ? $row['insert_user_name'] : $row['assign_insert_user_name'];
			$i++;
		}
		return $ret;
	}
}
?>