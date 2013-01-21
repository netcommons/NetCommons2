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
class Bbs_Action_Admin_Operation extends Action
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
    			//掲示板チェック
				$where_params = array(
					"bbs_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("bbs", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}
				
    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("bbs", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$bbs_block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"bbs_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("bbs_block", $bbs_block_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				$where_params = array(
					"bbs_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$func = array($this, "_fetchcallbackBbsPost");
    			$post_id_arr = $this->db->selectExecute("bbs_post", $where_params, null, null, null, $func);
    			if($post_id_arr === false) {
					return "false";
				}
				$result = $this->db->updateExecute("bbs_post", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				if(is_array($post_id_arr)) {
					$where_str = implode("','", $post_id_arr);
					$where_params = array(
						"post_id IN ('". $where_str. "') " => null
					);
					$result = $this->db->updateExecute("bbs_post_body", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
					$result = $this->db->updateExecute("bbs_user_post", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
					$where_params = array(
						"topic_id IN ('". $where_str. "') " => null
					);
					$result = $this->db->updateExecute("bbs_topic", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
					
					//
	    			// 添付ファイル更新処理
	    			// WYSIWYG
	    			//
	    			$where_params = array(
							"post_id IN ('". $where_str. "') " => null
						);
	    			$bbs_post_body = $this->db->selectExecute("bbs_post_body", $where_params);
					if($bbs_post_body === false) {
						return "false";
					}
	    			$upload_id_arr = $this->commonOperation->getWysiwygUploads("body", $bbs_post_body);
	    			$result = $this->commonOperation->updWysiwygUploads($upload_id_arr, $this->move_room_id);
	    			if($result === false) {
						return "false";
					}
				}

				//--URL短縮形関連 Start--
				$container =& DIContainerFactory::getContainer();
				$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
				$result = $abbreviateurlAction->moveRoom($this->unique_id, $this->room_id, $this->move_room_id);
				if ($result === false) {
					return "false";
				}
				//--URL短縮形関連 End--
				
				//--新着情報関連 Start--
				if(is_array($post_id_arr) && count($post_id_arr) > 0) {
					$whatsnew = array(
						"unique_id" => $post_id_arr,
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
	function &_fetchcallbackBbsPost($result) {
		$post_id_arr = array();
		while ($row = $result->fetchRow()) {
			$post_id_arr[] = $row['post_id'];
		}
		return $post_id_arr;
	}
}
?>