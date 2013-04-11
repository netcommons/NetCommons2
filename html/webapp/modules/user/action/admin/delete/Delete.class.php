<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員削除
 * 自分自身の削除-システム管理者の削除は不可（バリデート）
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;

	// 使用コンポーネントを受け取るため
	var $usersAction = null;
	var $actionChain = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if (!$this->usersAction->deleteUser($this->user_id)) {
			return 'error';
		}

		$recursive_action_name = $this->actionChain->getRecursive();
		if (!empty($recursive_action_name)) {
			return 'recursive_success';
		}

		return 'success';
	}
}
?>
