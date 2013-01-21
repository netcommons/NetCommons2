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
class Photoalbum_Action_Admin_Operation extends Action
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
					"photoalbum_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("photoalbum", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}
				
    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("photoalbum", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$photoalbum_block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"photoalbum_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("photoalbum_block", $photoalbum_block_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$where_params = array(
					"photoalbum_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$func = array($this, "_fetchcallbackPhotoalbum");
    			$album_id_arr = $this->db->selectExecute("photoalbum_album", $where_params, null, null, null, $func);
    			if($album_id_arr === false) {
					return "false";
				}
				$result = $this->db->updateExecute("photoalbum_album", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				if(is_array($album_id_arr)) {
					$where_str = implode("','", $album_id_arr);
					$where_params = array(
						"album_id IN ('". $where_str. "') " => null
					);
					
					$func = array($this, "_fetchcallbackPhoto_id");
					$photo_id_arr = $this->db->selectExecute("photoalbum_photo", $where_params, null, null, null, $func);
	    			if($photo_id_arr === false) {
						return "false";
					}
					
					$result = $this->db->updateExecute("photoalbum_photo", $params, $where_params, false);
					if($result === false) {
						return "false";
					}
					
					if(is_array($photo_id_arr)) {
						$photo_where_str = implode("','", $photo_id_arr);
						$photo_where_params = array(
							"photo_id IN ('". $photo_where_str. "') " => null
						);
						$result = $this->db->updateExecute("photoalbum_comment", $params, $photo_where_params, false);
						if($result === false) {
							return "false";
						}
						$result = $this->db->updateExecute("photoalbum_user_photo", $params, $photo_where_params, false);
						if($result === false) {
							return "false";
						}
					}
					
					//
	    			// 添付ファイル更新処理
	    			// WYSIWYG
	    			//
	    			$where_params = array(
							"album_id IN ('". $where_str. "') " => null
						);
	    			$photoalbum_album = $this->db->selectExecute("photoalbum_album", $where_params);
					if($photoalbum_album === false) {
						return "false";
					}
	    			$upload_id_arr = $this->commonOperation->getTextUploads("album_jacket", $photoalbum_album);
	    			
	    			$photoalbum_photo = $this->db->selectExecute("photoalbum_photo", $where_params);
					if($photoalbum_photo === false) {
						return "false";
					}
	    			$upload_id_photo_arr = $this->commonOperation->getTextUploads("photo_path", $photoalbum_photo);
	    			$upload_id_arr =  array_merge($upload_id_arr, $upload_id_photo_arr);
	    			
	    			$result = $this->commonOperation->updWysiwygUploads($upload_id_arr, $this->move_room_id);
	    			if($result === false) {
						return "false";
					}
				}
				
				//--新着情報関連 Start--
				if(is_array($album_id_arr) && count($album_id_arr) > 0) {
					$whatsnew = array(
						"unique_id" => $album_id_arr,
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
	function &_fetchcallbackPhotoalbum($result) {
		$album_id_arr = array();
		while ($row = $result->fetchRow()) {
			$album_id_arr[] = $row['album_id'];
		}
		return $album_id_arr;
	}
	
	
	/**
	 * fetch時コールバックメソッド(config)
	 * @param result adodb object
	 * @access	private
	 */
	function &_fetchcallbackPhoto_id($result) {
		$photo_id_arr = array();
		while ($row = $result->fetchRow()) {
			$photo_id_arr[] = $row['photo_id'];
		}
		return $photo_id_arr;
	}
}
?>