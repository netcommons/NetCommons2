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
class Todo_View_Admin_Search extends Action
{
	// リクエストを受け取るため
	var $block_id = null;
	var $limit = null;
	var $offset = null;
	var $fm_target_time = null;
	var $to_target_time = null;
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
		$sqlwhere .= $this->sqlwhere;
		$sql = "SELECT COUNT(*)" .
				" FROM {todo_task} task" .
				" INNER JOIN {todo} todo ON (task.todo_id=todo.todo_id)" .
				" INNER JOIN {todo_block} block ON (task.todo_id=block.todo_id)" .
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
			$sql = "SELECT block.block_id, todo.room_id, todo.todo_name, page.page_name, task.*" .
					" FROM {todo_task} task" .
					" INNER JOIN {todo} todo ON (task.todo_id=todo.todo_id)" .
					" INNER JOIN {pages} page ON (todo.room_id=page.page_id)" .
					" INNER JOIN {todo_block} block ON (task.todo_id=block.todo_id)" .
					$sqlwhere.
					" ORDER BY task.insert_time DESC";
			$this->results =& $this->db->execute($sql ,$this->params, $this->limit, $this->offset, true, array($this, '_fetchcallback'));
		}
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド(block)
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
			$ret[$i]['block_id'] = $row['block_id'];
			$ret[$i]['pubDate'] = $row['insert_time'];
			$ret[$i]['action'] = "todo_view_main_init";
			$ret[$i]['title'] = $row['page_name']._SEARCH_SUBJECT_SEPARATOR.
								 $row['todo_name']._SEARCH_SUBJECT_SEPARATOR.
								 $row['task_value'];
			$ret[$i]['description'] = $row['task_value'];
			$ret[$i]['user_id'] = $row['insert_user_id'];
			$ret[$i]['user_name'] = $row['insert_user_name'];
			$i++;
		}
		return $ret;
	}
}
?>