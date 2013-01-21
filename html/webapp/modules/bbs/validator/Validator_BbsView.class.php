<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 掲示板参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Validator_BbsView extends Validator
{
    /**
     * 掲示板参照権限チェックバリデータ
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
        $bbsView =& $container->getComponent("bbsView");
		if ($authID < _AUTH_CHIEF) {
			$bbsID = $bbsView->getCurrentBbsID();
			if ($bbsID != $attributes["bbs_id"]) {
				return $errStr;
			}
		}
		
		$request =& $container->getComponent("Request");
		if (strpos($attributes["prefix_id_name"], BBS_PREFIX_REFERENCE) === 0) {
			$request->setParameter("theme_name", "system");
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (empty($attributes["bbs_id"])) {
			$bbs = $bbsView->getDefaultBbs();
		} elseif ($actionName == "bbs_view_edit_entry"
					|| $actionName == "bbs_action_edit_entry"
					|| strpos($attributes["prefix_id_name"], BBS_PREFIX_REFERENCE) === 0) {
			$bbs = $bbsView->getBbs();
		} else {
			$bbs = $bbsView->getCurrentBbs();
		}

		if (empty($bbs)) {
        	return $errStr;
        }

		$request->setParameter("bbs", $bbs);
 
        return;
    }
}
?>