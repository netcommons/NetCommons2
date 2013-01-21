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
class Cabinet_View_Admin_Search extends Action
{
	// リクエストを受け取るため
	var $block_id = null;
	var $room_id = null;
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

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		if ($this->block_id_arr) {
			$sqlwhere = " WHERE c_block.block_id IN (".implode(",", $this->block_id_arr).")";
		} else {
			return 'success';
		}
		$sqlwhere .= $this->sqlwhere;
		$sql = "SELECT COUNT(*)" .
				" FROM {cabinet_file} file" .
				" INNER JOIN {cabinet_manage} cabinet ON (file.cabinet_id=cabinet.cabinet_id)" .
				" INNER JOIN {cabinet_block} c_block ON (cabinet.cabinet_id=c_block.cabinet_id)" .
				" LEFT JOIN {cabinet_comment} comment ON (file.file_id=comment.file_id)". 
				$sqlwhere;
		$result = $this->db->execute($sql, $this->params, null ,null, false);
		if ($result !== false) {
			$this->count = $result[0][0];
		} else {
			$this->count = 0;
		}

		if ($this->count > 0) {
			$sql = "SELECT c_block.block_id, cabinet.room_id, file.*, comment.comment" .
					" FROM {cabinet_file} file" .
					" INNER JOIN {cabinet_manage} cabinet ON (file.cabinet_id=cabinet.cabinet_id)" .
					" INNER JOIN {cabinet_block} c_block ON (cabinet.cabinet_id=c_block.cabinet_id)" .
					" LEFT JOIN {cabinet_comment} comment ON (file.file_id=comment.file_id)". 
					$sqlwhere.
					" ORDER BY file.insert_time DESC";
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
			$ret[$i]['pubDate'] = $row['insert_time'];
			$ret[$i]['title'] = $row['file_name'];
    		$ret[$i]['url'] = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
								"&block_id=".$row['block_id'].
								"&active_action=cabinet_view_main_init".
								"&cabinet_id=".$row['cabinet_id'].
								"&folder_id=".$row['parent_id'].
								"#_".$row['block_id'];
			$ret[$i]['description'] = $row['comment'];
			$ret[$i]['user_id'] = $row['insert_user_id'];
			$ret[$i]['user_name'] = $row['insert_user_name'];
			$i++;
		}
		return $ret;
	}
}
?>