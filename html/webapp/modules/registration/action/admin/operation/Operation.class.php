<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュール操作時(move,copy,shortcut)に呼ばれるアクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Action_Admin_Operation extends Action
{
	var $mode = null;	//move or shortcut or copy
	// 移動元
	var $block_id = null;
	var $page_id = null;
	var $room_id = null;
	var $unique_id = null;
	
	// 移動先
	var $move_page_id = null;
	var $move_room_id = null;
	var $move_block_id = null;
	
	// コンポーネントを受け取るため
	var $db = null;
	var $commonOperation = null;
	
	function execute()
	{
		switch ($this->mode) {
			case "move":
				//存在チェック
				$where_params = array(
					"registration_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$result = $this->db->selectExecute("registration", $where_params);
				if($result === false || !isset($result[0])) {
					return "false";
				}
				
				//更新
				$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("registration", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$registration_blocks_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"registration_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("registration_block", $registration_blocks_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				$where_params = array(
					"registration_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$result = $this->db->updateExecute("registration_item", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("registration_data", $params, $where_params, false);
				if($result === false) {
					return "false";
				}

				$func = array($this, "_fetchcallbackData");
				$item_data_id_arr = $this->db->selectExecute("registration_item_data", $where_params, null, null, null, $func);
				if($item_data_id_arr === false) {
					return "false";
				}
				$result = $this->db->updateExecute("registration_item_data", $params, $where_params, false);
				if($result === false) {
					return "false";
				}

				if(is_array($item_data_id_arr)) {
					$where_str = implode("','", $item_data_id_arr);
					$where_params = array(
							"item_data_id IN ('". $where_str. "') " => null
						);
					
					$result = $this->db->updateExecute("registration_file", $params, $where_params, false);
					if($result === false) {
						return "false";
					}

					//
					// 添付ファイル更新処理
					// WYSIWYG
					//
					$registration_item_data = $this->db->selectExecute("registration_item_data", $where_params);
					if($registration_item_data === false) {
						return "false";
					}
					$upload_id_arr = $this->commonOperation->getWysiwygUploads("item_data_value", $registration_item_data);
					$result = $this->commonOperation->updWysiwygUploads($upload_id_arr, $this->move_room_id);
					if($result === false) {
						return "false";
					}
				}
				
				break;
			default:
				return "false";
		}
		return "true";
	}
	
	/**
	 * fetch時コールバックメソッド(config)
	 * @param result adodb object
	 * @access	private
	 */
	function &_fetchcallbackData($result) {
		$item_data_id_arr = array();
		while ($row = $result->fetchRow()) {
			$item_data_id_arr[] = $row['item_data_id'];
		}
		return $item_data_id_arr;
	}
}
?>