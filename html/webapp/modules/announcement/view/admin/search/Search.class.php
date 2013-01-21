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
class Announcement_View_Admin_Search extends Action
{
	// リクエストを受け取るため
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
	var $block_id_arr =null; 

	// コンポーネントを受け取るため
	var $db = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		//// ブロックID ////
		if ($this->block_id_arr) {
			$sqlwhere = " WHERE block_id IN (".implode(",", $this->block_id_arr).")";
		} else {
			return 'success';
		}
		$sqlwhere .= $this->sqlwhere;
		
		$sql = "SELECT COUNT(*) FROM {announcement} ". $sqlwhere;
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
			$sql = "SELECT * FROM {announcement} " . $sqlwhere . " ORDER BY insert_time DESC";
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
		$count = 0;
		while ($row = $result->fetchRow()) {
			$ret[$count] = array();
			$ret[$count]['block_id'] =  $row['block_id'];
			$ret[$count]['pubDate'] = $row['insert_time'];
			//$ret[$count]['title'] = "";
			//$ret[$count]['url'] = "#";
			$ret[$count]['action'] = "announcement_view_main_init";
			$ret[$count]['description'] = $row['content'];
			$ret[$count]['user_id'] = $row['insert_user_id'];
			$ret[$count]['user_name'] = $row['insert_user_name'];
			$count++;
		}
		return $ret;
	}
}
?>