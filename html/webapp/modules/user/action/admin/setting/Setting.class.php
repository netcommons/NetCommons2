<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報-詳細の登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Setting extends Action
{
	// リクエストパラメータを受け取るため
	var $drag_item_id = null;
	var $drop_item_id = null;
	
	var $position = null;
	var $addnew_col_num = null;
	
	// バリデートによりセット
	var $drag_items = null;
	var $drop_items = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $usersAction = null;
	var $usersView = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		//
		// 移動元
		//
		$where_params = array(
								"col_num" => $this->drag_items['col_num']
							);
		$sequence_param = array("row_num" => $this->drag_items['row_num']);
		$result = $this->db->seqExecute("items", $where_params, $sequence_param);
		if ($result === false) {
			return 'error';
		}
		if($this->drag_items['col_num'] == $this->drop_items['col_num'] &&
			$this->drag_items['row_num'] < $this->drop_items['row_num']) {
			$this->drop_items['row_num']--;	
		}
		//
		// 移動先
		//
		$addnew_col_num = $this->drop_items['col_num'];
		$position = $this->position;
		if($position != "top" && $position != "bottom") $position = "top";
		if($this->drag_item_id == $this->drop_item_id) {
			//新規行
			$addnew_col_num = intval($this->addnew_col_num);
			if($addnew_col_num > 2 || $addnew_col_num < 1) $addnew_col_num = 2;
			$row_num = 1;
		} else if($position == "top"){
			$row_num = $this->drop_items['row_num'];
		}else {
			$row_num = $this->drop_items['row_num'] + 1;
		}
		$where_params = array(
								"col_num" => $addnew_col_num
							);
		$sequence_param = array("row_num" => $row_num);
		$result = $this->db->seqExecute("items", $where_params, $sequence_param, 1);
		if ($result === false) {
			return 'error';
		}
		//
		// items更新
		//
		$params = array(
			"col_num" => $addnew_col_num,
			"row_num" => $row_num
		);
		$where_params = array("item_id" => $this->drag_item_id);
		$result = $this->usersAction->updItem($params, $where_params);
		if ($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
