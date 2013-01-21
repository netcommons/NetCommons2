<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * お知らせ削除アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Announcement_Action_Edit_Delblock extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $uploadsAction = null;
	
	// 値をセットするため

	//Filter_Whatsnewに値をセットするため
	var $whatsnew = array();

	function execute()
	{
		$result = $this->db->selectExecute("announcement", array("block_id"=>$this->block_id));
    	if ($result === false) {
    		return 'error';
    	}
    	if(!isset($result[0])) {
    		return 'success';
    	}
		$announcement = $result[0];

		$result = $this->db->deleteExecute("announcement", array("block_id"=>$this->block_id));
    	if ($result === false) {
    		return 'error';
    	}
		
		//--新着情報関連 Start--
		$this->whatsnew = array(
			"unique_id" => $this->block_id
		);
		//--新着情報関連 End--
		return 'success';
	}
}
?>
