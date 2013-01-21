<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * お知らせ登録処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Announcement_Action_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $content = null;
	var $more_checked = null;
	var $more_content = null;
	var $more_title = null;
	var $hide_more_title = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	//var $uploadsAction = null;

	// 値をセットするため

	//Filter_Whatsnewに値をセットするため
	var $whatsnew = array();

	function execute()
	{
		if(intval($this->more_checked) == _OFF) {
    		$this->more_title = "";
    		$this->more_content = "";
    		$this->hide_more_title = "";
    	}else {
    		if($this->more_title == "") {
    			$this->more_title = ANNOUNCEMENT_MORE_TITLE;
    		}
    		if($this->hide_more_title == "") {
    			$this->hide_more_title = ANNOUNCEMENT_HIDE_MORE_TITLE;
    		}
    	}

		$announcement = $this->db->selectExecute("announcement", array("block_id"=>$this->block_id));
		if (!empty($announcement)) {
			$params = array(
				"content" => $this->content,
				"more_content" => $this->more_content,
				"more_title" => $this->more_title,
				"hide_more_title" => $this->hide_more_title
			);
			$result = $this->db->updateExecute("announcement", $params, array("block_id"=>$this->block_id), true);
	    	if ($result === false) {
	    		return 'error';
	    	}
		} else {
			$params = array(
				"block_id" =>$this->block_id,
				"content" => $this->content,
				"more_content" => $this->more_content,
				"more_title" => $this->more_title,
				"hide_more_title" => $this->hide_more_title
			);
	    	$result = $this->db->insertExecute("announcement", $params, true);
	    	if ($result === false) {
	    		return 'error';
	    	}
		}
		//$this->uploadsAction->setGarbageflag("0", $this->content);

		//--新着情報関連 Start--
		$this->whatsnew = array(
			"unique_id" => $this->block_id,
			"title" => "",
			"description" => $this->content
		);
		if (!empty($announcement)) {
			$this->whatsnew["insert_time"] = $announcement[0]["insert_time"];
			$this->whatsnew["insert_user_id"] = $announcement[0]["insert_user_id"];
			$this->whatsnew["insert_user_name"] = $announcement[0]["insert_user_name"];
		}
		//--新着情報関連 End--
		return 'success';
	}
}
?>
