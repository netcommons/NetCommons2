<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員削除(選択して削除)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Seldelete extends Action
{
	// リクエストパラメータを受け取るため
	var $select_user = null;
	var $delete_users = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $usersAction = null;
	var $db = null;
	var $authoritiesView = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// 検索結果から削除会員を特定
		$order_str = "";
		$session_params =& $this->session->getParameter(array("user", "selected_params"));
		//$select_str = "SELECT {users}.user_id ";
		$select_str = "SELECT {users}.*, ".
						"{authorities}.role_authority_id,".
						"{authorities}.role_authority_name,".
						"{authorities}.system_flag AS authority_system_flag,".
						"{authorities}.user_authority_id,".
						"{authorities}.public_createroom_flag,".
						"{authorities}.group_createroom_flag,".
						"{authorities}.private_createroom_flag,".
						"{authorities}.myroom_use_flag";

		$sql = $select_str.
				$this->session->getParameter(array("user", "selected_where_str")) . $order_str;
		$users =& $this->db->execute($sql, $session_params, null, null, true, array($this, "_fetchcallback"));
		if($users === false) {
			$this->db->addError();
			return 'error';
		}

		$login_user_id = $this->session->getParameter("_user_id");
		$_system_user_id = $this->session->getParameter("_system_user_id");
		$_user_auth_id = $this->session->getParameter("_user_auth_id");

		$canUseSysytemModule = false; 
		$roleId = $this->session->getParameter('_role_auth_id');
		$fetchMethod = array($this, '_canUseSysytemModule');
		$whereArray = array('system_flag' => _ON);
		if($_user_auth_id == _AUTH_ADMIN) {
			// 管理者ならば、システムコントロールモジュール、サイト運営モジュールの選択の有無で上下を判断
			$canUseSysytemModule = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($roleId, $whereArray, null, $fetchMethod);
		}

		$targetUsers = array();
		foreach($users as $user_id => $user) {
			if($user_id == $login_user_id || $user_id == $_system_user_id) {
				// 自分自身 or システム管理者
				continue;
			}
			if($_user_auth_id != _AUTH_ADMIN && $user['user_authority_id'] >= $_user_auth_id) {
				// ログイン会員よりベース権限が未満のものしか削除できない
				continue;
			}

			if (empty($canUseSysytemModule)) {
				$canUseSysytemModuleTarget = $this->authoritiesView->getAuthoritiesModulesLinkByAuthorityId($user['role_authority_id'], $whereArray, null, $fetchMethod);
				if ($canUseSysytemModuleTarget === true) {
					continue;
				}
			}

			if (!empty($this->delete_users[$user_id])
					|| !empty($this->select_user)) {
				$targetUsers[] = $user_id;
			} else {
				continue;
			}
		}

		if (!$this->usersAction->deleteUser($targetUsers)) {
			return 'error';
		}

		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['user_id']] = $row;
		}
		return $ret;
	}

	/**
	 * システム管理用モジュールが利用できるかどうか判断する。
	 * 
	 * @param array $recordSet 利用可能モジュール
	 * @return true or null
	 * @access private
	 */
	function _canUseSysytemModule($result) {
		$site_modules_dir_arr = explode("|", AUTHORITY_SYS_DEFAULT_MODULES_ADMIN);
		while ($obj = $result->fetchRow()) {
			if($obj["authority_id"] === null) continue;
			$module_id = $obj["module_id"];

			$pathList = explode("_", $obj["action_name"]);
			if(!in_array($pathList[0], $site_modules_dir_arr)) {
				return true;
			}
		}
		return null;
	}
}
?>
