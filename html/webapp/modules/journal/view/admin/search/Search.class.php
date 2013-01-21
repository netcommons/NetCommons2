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
class Journal_View_Admin_Search extends Action
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
		$sqlwhere .= " AND post.status = ".JOURNAL_POST_STATUS_REREASED_VALUE;
		$sqlwhere .= $this->sqlwhere;
		$sql = "SELECT COUNT(*)" .
				" FROM {journal_post} post" .
				" INNER JOIN {journal} journal ON (post.journal_id=journal.journal_id)" .
				" INNER JOIN {journal_block} block ON (post.journal_id=block.journal_id)". 
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
			$sql = "SELECT block.block_id, journal.room_id, post.*" .
					" FROM {journal_post} post" .
					" INNER JOIN {journal} journal ON (post.journal_id=journal.journal_id)" .
					" INNER JOIN {journal_block} block ON (post.journal_id=block.journal_id)". 
					$sqlwhere.
					" ORDER BY post.insert_time DESC";
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
			$ret[$i]['title'] = $row['title'];
    		$ret[$i]['url'] = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION.
								"&block_id=".$row['block_id'].
								"&active_action=journal_view_main_detail".
								"&post_id=".($row['parent_id'] > 0 ? $row['parent_id'] : $row['post_id']).
								($row['parent_id'] > 0 ? "&comment_flag="._ON : "").
								"#_".$row['block_id'];
			$ret[$i]['description'] = $row['content']."<br /><br />".$row['more_content'];
			$ret[$i]['user_id'] = $row['insert_user_id'];
			$ret[$i]['user_name'] = $row['insert_user_name'];
			$i++;
		}
		return $ret;
	}
}
?>