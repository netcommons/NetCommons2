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
class Cabinet_Action_Admin_Operation extends Action
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
	var $whatsnewAction = null;
	
	function execute()
	{
		switch ($this->mode) {
    		case "move":
    			//存在チェック
				$where_params = array(
					"cabinet_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("cabinet_manage", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}
				
    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("cabinet_manage", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$cabinet_block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"cabinet_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("cabinet_block", $cabinet_block_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				$where_params = array(
					"cabinet_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$func = array($this, "_fetchcallbackCabinetFile");
    			$file_list = $this->db->selectExecute("cabinet_file", $where_params, null, null, null, $func);
    			if($file_list === false) {
					return "false";
				}
				list($file_id_arr, $upload_id_arr) = $file_list;
				
				$result = $this->db->updateExecute("cabinet_file", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				if(is_array($file_id_arr)) {
					$where_str = implode("','", $file_id_arr);
					$where_params = array(
						"file_id IN ('". $where_str. "') " => null
					);
					$result = $this->db->updateExecute("cabinet_comment", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
					
					//
	    			// 添付ファイル更新処理
	    			//
	    			$where_str = implode("','", $upload_id_arr);
					$where_params = array(
						"upload_id IN ('". $where_str. "') " => null
					);
	    			$result = $this->db->updateExecute("uploads", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
				}
				
				//--新着情報関連 Start--
				if(is_array($file_id_arr) && count($file_id_arr) > 0) {
					$whatsnew = array(
						"unique_id" => $file_id_arr,
						"room_id" => $this->move_room_id
					);
					$result = $this->whatsnewAction->moveUpdate($whatsnew);
					if ($result === false) {
						return false;
					}
				}
				//--新着情報関連 End--
				
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
	function _fetchcallbackCabinetFile($result) {
		$file_id_arr = array();
		$upload_id_arr = array();
		while ($row = $result->fetchRow()) {
			$file_id_arr[] = $row['file_id'];
			$upload_id_arr[] = $row['upload_id'];
		}
		return array($file_id_arr, $upload_id_arr);
	}
}
?>