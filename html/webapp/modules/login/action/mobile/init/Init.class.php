<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯ログインアクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Login_Action_Mobile_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $login_id = null;
	var $password = null;
	var $mobile_auto_login = null;

	// コンポーネントを使用するため
	var $mobileView = null;
	var $request = null;

	/**
	 * 携帯ログインアクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if ($this->mobile_auto_login != _ON) {
			return 'success';
		}

		if (strlen($this->login_id) != 0
			&& strlen($this->password) != 0) {
			return 'success';
		}

		$loginInfo = $this->mobileView->getAutoLogin();
		if (empty($loginInfo)) {
			return 'success';
		}

		$this->request->setParameter('login_id', $loginInfo['login_id']);
		$this->request->setParameter('password', $loginInfo['password']);
		$this->request->setParameter('md5', _ON);

		return 'success';
	}
}
?>