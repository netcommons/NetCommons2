<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * お知らせ編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Announcement_View_Edit_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	
	// 使用コンポーネントを受け取るため
	var $db = null;

	// 値をセットするため
	var $announcement_obj=null;
	
	function execute()
	{
		$result = $this->db->selectExecute("announcement", array("block_id"=>$this->block_id));
		if ($result != false) {
			$this->announcement_obj = $result[0];
		}
		return 'success';
	}
}
?>