<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォーム存在チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_RegistrationExists extends Validator
{
    /**
     * 登録フォーム存在チェックバリデータ
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

		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();		
		if (empty($attributes["registration_id"]) &&
				($actionName == "registration_view_edit_registration_entry" ||
					$actionName == "registration_action_edit_registration_entry")) {
			return;
		}

        $registrationView =& $container->getComponent("registrationView");
		$request =& $container->getComponent("Request");
		if (empty($attributes["registration_id"])) {
        	$session =& $container->getComponent("Session");
			$session->removeParameter("registration_edit". $attributes["block_id"]);
        	
        	$attributes["registration_id"] = $registrationView->getCurrentRegistrationID();
        	$request->setParameter("registration_id", $attributes["registration_id"]);
		}

		if (empty($attributes["registration_id"])) {
			return $errStr;
		}

		if (empty($attributes["block_id"])) {
        	$block = $registrationView->getBlock();
			if ($attributes["room_id"] != $block["room_id"]) {
				return $errStr;
			}

			$attributes["block_id"] = $block["block_id"];
        	$request->setParameter("block_id", $attributes["block_id"]);
		}
		
        if (!$registrationView->registrationExists()) {
			return $errStr;
		}
		
        return;
    }
}
?>