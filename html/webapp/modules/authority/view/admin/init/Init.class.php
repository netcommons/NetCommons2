<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限管理(一覧)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_View_Admin_Init extends Action
{
	// 使用コンポーネントを受け取るため
	var $authoritiesView = null;
	var $session = null;
	
	// 値をセットするため
	var $authorities = null;
	var $maxNum = 0;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$order_params = array();
		$order_params["user_authority_id"] = "DESC";
		$order_params["hierarchy"] = "DESC";
		$order_params["system_flag"] = "DESC";
		$order_params["role_authority_name"] = "ASC";

		$this->authorities = $this->authoritiesView->getAuthorities(null, $order_params);
		$this->maxNum = count($this->authorities);
		
		// セッションの初期化
		$this->session->removeParameter(array("authority"));

		return 'success';
	}
}
?>
