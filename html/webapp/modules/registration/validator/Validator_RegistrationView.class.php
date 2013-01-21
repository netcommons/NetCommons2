<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォーム参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_RegistrationView extends Validator
{
    /**
     * 登録フォーム参照権限チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		$container =& DIContainerFactory::getContainer();

		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		$edit = $session->getParameter("registration_edit". $attributes["block_id"]);
		if ($authID < _AUTH_CHIEF &&
				$edit == _ON) {
			return $errStr;
		}
		
		$registrationView =& $container->getComponent("registrationView");
		if (empty($attributes["registration_id"])) {
			$registration = $registrationView->getDefaultRegistration();
		} elseif ($edit == _ON) {
			$registration = $registrationView->getRegistration();
		} else {
			$registration = $registrationView->getCurrentRegistration();
		}

		if (empty($registration)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("registration", $registration);
 
        return;
    }
}
?>
