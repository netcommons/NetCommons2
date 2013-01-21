<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Delitem extends Action
{
	// リクエストパラメータを受け取るため
	var $item_id = null;
	
	// 使用コンポーネントを受け取るため
	var $usersAction = null;
	var $db = null;
	
	// バリデートによりセットするため
	var $items = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$item_id = intval($this->item_id);
		$result = $this->usersAction->delItemById($item_id);
		if ($result === false) {
			return 'error';
		}
		
		// 表示順-前詰め処理
		$where_params = array(
								"col_num" => $this->items['col_num']
							);
		$sequence_param = array("row_num" => $this->items['row_num']);
		$result = $this->db->seqExecute("items", $where_params, $sequence_param);
		if ($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
