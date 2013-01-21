<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員登録(会員編集)-閉じるボタン押下時
 * セッションデータクリア
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Registclose extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		
    	//
		// 登録時使用　セッション初期化
		//
		$this->session->removeParameter(array("user", "regist", $this->user_id));
		$this->session->removeParameter(array("user", "regist_public", $this->user_id));
		$this->session->removeParameter(array("user", "regist_reception", $this->user_id));
		$this->session->removeParameter(array("user", "regist_auth", $this->user_id));
		$this->session->removeParameter(array("user", "regist_role_auth", $this->user_id));
		$this->session->removeParameter(array("user", "regist_confirm", $this->user_id));
		$this->session->removeParameter(array("user", "selroom", $this->user_id));
		$this->session->removeParameter(array("user", "selauth", $this->user_id));
		
		return 'success';
	}
}
?>
