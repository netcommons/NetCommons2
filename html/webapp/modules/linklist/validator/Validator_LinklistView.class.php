<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_LinklistView extends Validator
{
    /**
     * リンクリスト参照権限チェックバリデータ
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
        $linklistView =& $container->getComponent("linklistView");
		if ($authID < _AUTH_CHIEF) {
			$linklistID = $linklistView->getCurrentLinklistID();
			if ($linklistID != $attributes["linklist_id"]) {
				return $errStr;
			}
		}
		
		if (strpos($attributes["prefix_id_name"], LINKLIST_PREFIX_REFERENCE) === 0) {
			$request =& $container->getComponent("Request");
			$request->setParameter("theme_name", "system");
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (empty($attributes["linklist_id"])) {
			$linklist = $linklistView->getDefaultLinklist();
		} elseif ($actionName == "linklist_view_edit_entry"
					|| $actionName == "linklist_action_edit_entry"
					|| strpos($attributes["prefix_id_name"], LINKLIST_PREFIX_REFERENCE) === 0) {
			$linklist = $linklistView->getLinklist();
		} else {
			$linklist = $linklistView->getCurrentLinklist();
		}
		if (empty($linklist)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("linklist", $linklist);
 
        return;
    }
}
?>