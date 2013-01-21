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
class Announcement_Action_Admin_Operation extends Action
{
	var $mode = null;	//move or shortcut or copy
	// 移動元
	var $block_id = null;
	//var $page_id = null;
	//var $module_id = null;
	//var $unique_id = null;

	// 移動先
	var $move_page_id = null;
	var $move_room_id = null;
	var $move_block_id = null;

	// コンポーネントを受け取るため
	var $db = null;
	var $commonOperation = null;
	var $whatsnewAction = null;
	var $session = null;

	function execute()
	{
		switch ($this->mode) {
    		case "move":
    			//お知らせ更新
				$params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id)
				);
				$result = $this->db->updateExecute("announcement", $params, $where_params, false);
				if($result === false) {
					return "false";
				}

    			//
    			// 添付ファイル更新処理
    			// WYSIWYG
    			//
    			$announcement = $this->db->selectExecute("announcement", $where_params);
				if($announcement === false) {
					return "false";
				}
    			$upload_id_arr = $this->commonOperation->getWysiwygUploads("content,more_content", $announcement);
    			$result = $this->commonOperation->updWysiwygUploads($upload_id_arr, $this->move_room_id);
    			if($result === false) {
					return "false";
				}

				//--新着情報関連 Start--
				$whatsnew = array(
					"unique_id" => $this->block_id,
					"room_id" => $this->move_room_id
				);
				$result = $this->whatsnewAction->moveUpdate($whatsnew);
				if ($result === false) {
					return false;
				}
				//--新着情報関連 End--

				break;
			case "copy":
				$params = array(
					"block_id"=> intval($this->block_id)
				);
				$announcement = $this->db->selectExecute("announcement", $params);
				if($announcement === false) {
					return "false";
				}
				if (!isset($announcement[0])) {
					return "true";
				}

				//
    			// 添付ファイルコピー処理
    			// WYSIWYG
    			//
				$upload_id_arr = $this->commonOperation->getWysiwygUploads("content,more_content", $announcement);
				if(count($upload_id_arr) > 0) {
					$new_upload_id_arr = $this->commonOperation->copyWysiwygUploads($upload_id_arr, $this->move_room_id);
	    			if($new_upload_id_arr === false) {
						return "false";
					}
					// upload_id振替
					$count = 0;
					foreach($new_upload_id_arr as $new_upload_id) {
						$pattern = _REGEXP_PRE_TRANSFER_UPLOAD_ID.$upload_id_arr[$count]._REGEXP_POST_TRANSFER_UPLOAD_ID;
						$replacement = '${1}'.$new_upload_id.'${2}';
						$announcement[0]['content'] = preg_replace($pattern, $replacement, $announcement[0]['content'], 1);
		        		$announcement[0]['more_content'] = preg_replace($pattern, $replacement, $announcement[0]['more_content'], 1);

						$count++;
					}
				}
	        	$announcement[0]['block_id'] = intval($this->move_block_id);
	        	$announcement[0]['room_id'] = intval($this->move_room_id);

				$user_id = $this->session->getParameter("_user_id");
				$announcement[0]['insert_user_id'] = $user_id;
				$announcement[0]['update_user_id'] = $user_id;

				$user_name = $this->session->getParameter("_handle");
				$announcement[0]['insert_user_name'] = $user_name;
				$announcement[0]['update_user_name'] = $user_name;

				$time = timezone_date();
	        	$announcement[0]['insert_time'] = $time;
	        	$announcement[0]['update_time'] = $time;

	        	//
	        	// Insert
	        	//
				$result = $this->db->insertExecute("announcement", $announcement[0], false);
				if($result === false) {
					return "false";
				}

				//--新着情報関連 Start--
				$whatsnew = array(
					"unique_id" => $this->move_block_id,
					"room_id" => $this->move_room_id,
					"title" => "",
					"description" => $announcement[0]["content"],
					"parameters" => "block_id=".$this->move_block_id."#_".$this->move_block_id,
					"insert_time" => $time,
					"insert_user_id" => $user_id,
					"insert_user_name" => $user_name,
					"update_time" => $time,
					"update_user_id" => $user_id,
					"update_user_name" => $user_name
				);
				$result = $this->whatsnewAction->insert($whatsnew,_ON);
				if ($result === false) {
					return false;
				}
				//--新着情報関連 End--

				break;
			default:
				return "false";
		}

		return "true";
	}
}
?>