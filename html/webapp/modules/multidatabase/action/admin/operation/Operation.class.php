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
class Multidatabase_Action_Admin_Operation extends Action
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
					"multidatabase_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("multidatabase", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}
				
    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("multidatabase", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$multidatabase_block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"multidatabase_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("multidatabase_block", $multidatabase_block_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				$where_params = array(
					"multidatabase_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				
				$metadata_content_id_arr = "";
				$func = array($this, "_fetchcallbackContents");
    			$content_id_arr = $this->db->selectExecute("multidatabase_content", $where_params, null, null, null, $func);
    			if(is_array($content_id_arr) && count($content_id_arr) > 0) {
    				$where_str = implode("','", $content_id_arr);
    				$where_params_in = array(
							"content_id IN ('". $where_str. "') " => null
					);
					$func = array($this, "_fetchcallbackMetaContents");
    				$metadata_content_id_arr = $this->db->selectExecute("multidatabase_metadata_content", $where_params_in, null, null, null, $func);
    				if($metadata_content_id_arr === false) {
						return "false";
					}
    			}
    			
				$result = $this->db->updateExecute("multidatabase_content", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("multidatabase_metadata", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				if(!empty($where_params_in)) {
					$result = $this->db->updateExecute("multidatabase_comment", $params, $where_params_in, false);
					if($result === false) {
						return "false";
					}
					$result = $this->db->updateExecute("multidatabase_metadata_content", $params, $where_params_in, false);
					if($result === false) {
						return "false";
					}
				}
				if(is_array($metadata_content_id_arr) && count($metadata_content_id_arr) > 0) {
					
					$where_str = implode("','", $metadata_content_id_arr);
					
					//
	    			// 添付ファイル更新処理
	    			// WYSIWYG
	    			//
	    			$where_params = array(
							"metadata_content_id IN ('". $where_str. "') " => null
						);
					$result = $this->db->updateExecute("multidatabase_file", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
					
					$func = array($this, "_fetchcallbackUploads");
					$upload_id_arr = $this->db->selectExecute("multidatabase_file", $where_params, null, null, null, $func);
					if($upload_id_arr === false) {
						return "false";
					}
					if(is_array($upload_id_arr) && count($upload_id_arr) > 0) {
	    				$where_str = implode("','", $upload_id_arr);
	    				$where_params_in = array(
								"upload_id IN ('". $where_str. "') " => null
						);
						$result = $this->db->updateExecute("uploads", $params, $where_params_in, false);
						if($result === false) {
							return "false";
						}
					}
					
	    			$multidatabase_metadata_content = $this->db->selectExecute("multidatabase_metadata_content", $where_params);
					if($multidatabase_metadata_content === false) {
						return "false";
					}
	    			$upload_id_arr = $this->commonOperation->getWysiwygUploads("content", $multidatabase_metadata_content);
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
				if(is_array($content_id_arr) && count($content_id_arr) > 0) {
					$whatsnew = array(
						"unique_id" => $content_id_arr,
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
	function &_fetchcallbackMetaContents($result) {
		$metadata_content_id_arr = array();
		while ($row = $result->fetchRow()) {
			$metadata_content_id_arr[] = $row['metadata_content_id'];
		}
		return $metadata_content_id_arr;
	}
	
	function &_fetchcallbackContents($result) {
		$content_id_arr = array();
		while ($row = $result->fetchRow()) {
			$content_id_arr[] = $row['content_id'];
		}
		return $content_id_arr;
	}
	
	function &_fetchcallbackUploads($result) {
		$content_id_arr = array();
		while ($row = $result->fetchRow()) {
			$content_id_arr[] = $row['upload_id'];
		}
		return $content_id_arr;
	}
}
?>