<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * キャビネット参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Cabinet_Validator_CabView extends Validator
{
    /**
     * validate実行
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
		
		$request =& $container->getComponent("Request");
		$prefix_id_name = $request->getParameter("prefix_id_name");
		
		if ($authID < _AUTH_CHIEF &&
				$prefix_id_name == CABINET_REFERENCE_PREFIX_NAME.$attributes["cabinet_id"]) {
			return $errStr;
		}
		
        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($prefix_id_name) == _ON && $actionName == "cabinet_view_main_init") {
			$request =& $container->getComponent("Request");
			$request->setParameter("theme_name", "system");
		}

        $cabinetView =& $container->getComponent("cabinetView");
		if (empty($attributes["cabinet_id"]) && ($actionName == "cabinet_view_edit_create")) {
			$cabinet = $cabinetView->getDefaultCabinet();
		} elseif ($prefix_id_name == CABINET_REFERENCE_PREFIX_NAME.$attributes["cabinet_id"] || ($actionName == "cabinet_view_edit_modify")) {
			$cabinet = $cabinetView->getCabinet();
			$request->setParameter("reference", _ON);
		} else {
			$cabinet = $cabinetView->getCurrentCabinet();
			$request->setParameter("reference", _OFF);
		}

		if (empty($cabinet)) {
        	return $errStr;
        }

		$request->setParameter("cabinet", $cabinet);
 
        return;
    }
}
?>
