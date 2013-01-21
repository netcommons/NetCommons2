<?php


/**
 * ログインできるかどうかチェック
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Login_Validator_Logincheck extends Validator
{
	/**
	 * ログインできるかどうかチェック
	 *
	 * @param   mixed   $attributes チェックする値(配列の場合あり)
	 * @param   string  $errStr	 エラー文字列
	 * @param   array   $params	 (使用しない)
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 * @since   3.0.0
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();

		$session =& $container->getComponent("Session");
		$configView =& $container->getComponent("configView");
		$authoritiesView =& $container->getComponent("authoritiesView");

		$login_id = $session->getParameter("_login_id");

		if (isset($login_id) && $login_id != 0) {
			//既にログイン済
			return;
		}

		//DBオブジェクト取得
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		$db =& $container->getComponent("DbObject");

		if (is_array($attributes)) {
			$md5 = $attributes[2];
			if($md5 == 1) {
				// 自動ログイン時
				$params = array(
								"login_id" => $attributes[0],
								"password" => $attributes[1]
							);
			} else{
				$params = array(
								"login_id" => $attributes[0],
								"password" => md5($attributes[1])
							);
			}
			if ($this->ldapCheck($attributes[0], $attributes[1]) == false) {
				$result = $db->execute("SELECT user_id,handle,role_authority_id,timezone_offset,last_login_time,system_flag,lang_dirname FROM {users} WHERE login_id=? AND password=? AND active_flag="._USER_ACTIVE_FLAG_ON,$params,0,null,false);
			} else {
				$result = $db->execute("SELECT user_id,handle,role_authority_id,timezone_offset,last_login_time,system_flag,lang_dirname FROM {users} WHERE login_id=? AND active_flag="._USER_ACTIVE_FLAG_ON,array($attributes[0]),0,null,false);
			}
			if(is_array($result)) {
				if(isset($result[0][0])) {
					$authorities =& $authoritiesView->getAuthorityById($result[0][2]);
					if($authorities === false || !isset($authorities['user_authority_id'])) return $errStr;

					$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
					if($config['closesite']['conf_value'] == _ON && $authorities['user_authority_id'] < $config['closesite_okgrp']['conf_value']) {
						return LOGIN_ACTION_CLOSESITE;
					}

					$mobileAction =& $container->getComponent("mobileAction");
					$mobileAction->setLogin($result[0][0], $attributes[0], $params['password'], $result[0][1]);

					BeanUtils::setAttributes($action, array("user_id"=>$result[0][0]));
					BeanUtils::setAttributes($action, array("handle"=>$result[0][1]));
					BeanUtils::setAttributes($action, array("role_authority_id"=>$result[0][2]));
					BeanUtils::setAttributes($action, array("timezone_offset"=>$result[0][3]));
					BeanUtils::setAttributes($action, array("last_login_time"=>$result[0][4]));
					BeanUtils::setAttributes($action, array("system_flag"=>$result[0][5]));
					BeanUtils::setAttributes($action, array("lang_dirname"=>$result[0][6]));

					BeanUtils::setAttributes($action, array("role_authority_name"=>$authorities['role_authority_name']));
					BeanUtils::setAttributes($action, array("user_authority_id"=>$authorities['user_authority_id']));
					BeanUtils::setAttributes($action, array("allow_attachment"=>$authorities['allow_attachment']));
					BeanUtils::setAttributes($action, array("allow_htmltag_flag"=>$authorities['allow_htmltag_flag']));
					BeanUtils::setAttributes($action, array("allow_video"=>$authorities['allow_video']));
					BeanUtils::setAttributes($action, array("allow_layout_flag"=>$authorities['allow_layout_flag']));
					BeanUtils::setAttributes($action, array("max_size"=>$authorities['max_size']));

				} else {

					return $errStr;
				}
			} else {
				return $db->ErrorMsg();
			}
		} else {
			return $errStr;
		}
	}

	/**
	 * ユーザ情報を集めてログインを行う
	 *
	 * @param	stirng	$ldap_user	ユーザ
	 * @param	stirng	$ldap_pass	パスワード
 	 * @return boolean	true or false
	 * @access	public
	 */
	function ldapCheck($ldap_user, $ldap_pass) {
		$result = false;
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
//		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfigByCatid(0, _SERVER_CONF_CATID);

		if ($config === false) {
			return $result;
		}
		if ($config['ldap_uses']['conf_value'] == _OFF) {
			return $result;
    	}
		$ldapServer = $config['ldap_server']['conf_value'];

		$ldapDomain = $config['ldap_domain']['conf_value'];

		$domainUsername = $ldap_user . "@" . $ldapDomain;

		$ldapConn = ldap_connect($ldapServer);

		if (!ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
			ldap_close($ldapConn);
			die("Could not set LDAP Protocol version");
		}
		$result = @ldap_bind($ldapConn, $domainUsername, $ldap_pass);

		ldap_close($ldapConn);
		return $result;
	}
}
?>
