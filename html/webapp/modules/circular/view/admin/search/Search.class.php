<?php

/**
 * 検索処理
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_View_Admin_Search extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $limit = null;
	var $offset = null;
	var $only_count = null;
	var $params = null;
	var $sqlwhere = null;

	// フィルターによりセット
	var $room_id_arr = null;
	var $block_id_arr = null;

	// 値をセットするため
	var $count = null;
	var $results = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;

	/**
	 * execute処理
	 *
	 * @return string アクション文字列
	 * @access  public
	 */
	function execute()
	{
		if ($this->block_id_arr) {
			$sqlwhere = ' WHERE block.block_id IN ('.implode(',', $this->block_id_arr).')';
		} else {
			return 'success';
		}

		$sqlwhere .= $this->sqlwhere;
		$sqlwhere .= ' AND (circular.post_user_id = ? OR user.receive_user_id = ?)';
		$user_id = $this->session->getParameter('_user_id');
		$this->params[] = $user_id;
		$this->params[] = $user_id;

		$sqlinner = ' INNER JOIN {circular_block} block ON (circular.room_id = block.room_id)';
		$sqlinner .= ' INNER JOIN {circular_user} user ON (circular.circular_id = user.circular_id)';

		$sql = 'SELECT DISTINCT circular.circular_id'.
				' FROM {circular} circular';

		$sql .= $sqlinner;
		$sql .= $sqlwhere;

		$circulars = $this->db->execute($sql, $this->params, null ,null, false);
		if ($circulars !== false) {
			$this->count = count($circulars);
		} else {
			$this->count = 0;
		}
		if ($this->only_count === _ON) {
			return 'count';
		}

		if ($this->count > 0) {
			$circular_id = array();
			foreach ($circulars as $circular) {
				$circular_id[] = $circular[0];
			}
			$sql = 'SELECT DISTINCT C.*'.
					' FROM {circular} C'.
					' WHERE C.circular_id IN ('.implode(',', $circular_id).')'.
					' ORDER BY C.insert_time DESC';

			$this->results =& $this->db->execute($sql ,array(), $this->limit, $this->offset, true, array($this, '_fetchcallback'));
		}
		return 'success';
	}

	/**
	 * Fetchコールバックメソッド
	 *
	 * @param  mixed $result 結果セット
	 * @return mixed データオブジェクト
	 * @access private
	 */
	function _fetchcallback($result)
	{
		$ret = array();
		$i = 0;
		while ($row = $result->fetchRow()) {
			if (empty($block_id) || $row['room_id'] != $room_id) {
				$params = array(
					'room_id'=>$row['room_id']
				);
				$block = $this->db->selectExecute('circular_block', $params, array('block_id'=>'ASC'), 1);
				if (!isset($block[0])) {
					return false;
				}
				$block_id = $block[0]['block_id'];
				$room_id = $row['room_id'];
			}
			$ret[$i] = array();
			$ret[$i]['block_id'] = $block_id;
			$ret[$i]['pubDate'] = $row['insert_time'];
			$ret[$i]['title'] = $row['circular_subject'];
			$ret[$i]['url'] = BASE_URL.INDEX_FILE_NAME.'?action='.DEFAULT_ACTION.
								'&active_action=circular_view_main_detail'.
								'&block_id='.$block_id.
								'&room_id='.$row['room_id'].
								'&circular_id='.$row['circular_id'].
								'#_'.$block_id;
			$ret[$i]['description'] = $row['circular_body'];
			$ret[$i]['user_id'] = $row['insert_user_id'];
			$ret[$i]['user_name'] = $row['insert_user_name'];
			$ret[$i]['guid'] = 'content_id='.$row['circular_id'];
			$i++;
		}
		return $ret;
	}
}
?>