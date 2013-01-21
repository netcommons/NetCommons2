<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * データ入力画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Registration_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $back = null;

	// 使用コンポーネントを受け取るため
	var $registrationView = null;
	var $session = null;
	var $mobileView = null;


	// validatorから受け取るため
	var $registration = null;
	var $items = null;

	// 値をセットするため
	var $imageAuthenticationGenerator = null;
	var $entryDatas = null;
	var $block_num = null;
	
	/**
	 * データ入力画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		$this->imageAuthenticationGenerator = time();
		if ($this->back != _ON) {
			$this->session->removeParameter("registration_entry_datas". $this->block_id);
			return "success";
		}
		$this->entryDatas =& $this->session->getParameter("registration_entry_datas". $this->block_id);

		return "success";
	}
}
?>