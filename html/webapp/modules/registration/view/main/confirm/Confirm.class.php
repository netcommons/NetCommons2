<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * データ入力確認画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_View_Main_Confirm extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $accept = null;
	var $dataID = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $mobileView = null;

	// validatorから受け取るため
	var $registration = null;
	var $items = null;

	// 値をセットするため
	var $entryDatas = true;

	var $block_num = null;

	/**
	 * データ入力確認画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->entryDatas =& $this->session->getParameter("registration_entry_datas". $this->block_id);

		if (empty($this->accept)) {
			return "confirm";
		}

		$this->session->removeParameter("registration_entry_datas". $this->block_id);

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		return "accept";
	}
}
?>