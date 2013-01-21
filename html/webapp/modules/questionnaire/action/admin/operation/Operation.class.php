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
class Questionnaire_Action_Admin_Operation extends Action
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
					"questionnaire_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
    			$result = $this->db->selectExecute("questionnaire", $where_params);
    			if($result === false || !isset($result[0])) {
					return "false";
				}
				
    			//更新
    			$params = array(
					"room_id"=> intval($this->move_room_id)
				);
				$result = $this->db->updateExecute("questionnaire", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("questionnaire_answer", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("questionnaire_choice", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$result = $this->db->updateExecute("questionnaire_summary", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				$questionnaire_block_params = array(
					"block_id"=> intval($this->move_block_id),
					"room_id"=> intval($this->move_room_id)
				);
				$where_params = array(
					"block_id"=> intval($this->block_id),
					"questionnaire_id"=> intval($this->unique_id)
				);
				$result = $this->db->updateExecute("questionnaire_block", $questionnaire_block_params, $where_params, false);
				if($result === false) {
					return "false";
				}
				
				$where_params = array(
					"questionnaire_id"=> intval($this->unique_id),
					"room_id"=> intval($this->room_id)
				);
				$func = array($this, "_fetchcallbackQuestion");
    			$question_id_arr = $this->db->selectExecute("questionnaire_question", $where_params, null, null, null, $func);
    			if($question_id_arr === false) {
					return "false";
				}
				$result = $this->db->updateExecute("questionnaire_question", $params, $where_params, false);
				if($result === false) {
					return "false";
				}
				if(is_array($question_id_arr)) {
					$where_str = implode("','", $question_id_arr);
					
					//
	    			// 添付ファイル更新処理
	    			// WYSIWYG
	    			//
	    			$where_params = array(
							"question_id IN ('". $where_str. "') " => null
						);
	    			$questionnaire_question = $this->db->selectExecute("questionnaire_question", $where_params);
					if($questionnaire_question === false) {
						return "false";
					}
	    			$upload_id_arr = $this->commonOperation->getWysiwygUploads("question_value,description", $questionnaire_question);
	    			$result = $this->commonOperation->updWysiwygUploads($upload_id_arr, $this->move_room_id);
	    			if($result === false) {
						return "false";
					}
				}
				
				//--新着情報関連 Start--
				$whatsnew = array(
					"unique_id" => $this->unique_id,
					"room_id" => $this->move_room_id
				);
				$result = $this->whatsnewAction->moveUpdate($whatsnew);
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
	
	/**
	 * fetch時コールバックメソッド(config)
	 * @param result adodb object
	 * @access	private
	 */
	function &_fetchcallbackQuestion($result) {
		$question_id_arr = array();
		while ($row = $result->fetchRow()) {
			$question_id_arr[] = $row['question_id'];
		}
		return $question_id_arr;
	}
}
?>