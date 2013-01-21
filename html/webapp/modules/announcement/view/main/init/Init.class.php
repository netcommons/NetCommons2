<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * お知らせメイン表示
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Announcement_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $html_flag = null;
	var $more_flag = null;

	// 使用コンポーネントを受け取るため
	var $db = null;
	var $mobileView = null; // mod by AllCreator
	var $session = null;	// add by AllCreator

	// 値をセットするため
	var $announcement_obj=null;

	var $block_num = null;  // mod by AllCreator

	function execute()
	{
		$this->html_flag = $this->mobileView->getTextHtmlMode($this->html_flag);
		if($this->session->getParameter("_mobile_flag") == true) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		$result = $this->db->selectExecute("announcement", array("block_id"=>$this->block_id));
		if ($result == false || $result[0]['content'] == "" || $result[0]['content'] == "<br />") {
			return 'nonexistent';
		}
		$this->announcement_obj = $result[0];

		return 'success';
	}
}
?>