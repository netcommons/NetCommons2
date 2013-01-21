<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム参照権限チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_PhotoalbumView extends Validator
{
    /**
     * フォトアルバム参照権限チェックバリデータ
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
		$photoalbumView =& $container->getComponent("photoalbumView");
		if ($authID < _AUTH_CHIEF) {
			$photoalbumID = $photoalbumView->getCurrentPhotoalbumID();
			if ($photoalbumID != $attributes["photoalbum_id"]) {
				return $errStr;
			}
		}
		
		if (!empty($attributes["prefix_id_name"])) {
			$request =& $container->getComponent("Request");
			$request->setParameter("theme_name", "system");
		}

        $actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (empty($attributes["photoalbum_id"])) {
			$photoalbum = $photoalbumView->getDefaultPhotoalbum();
		} elseif ($actionName == "photoalbum_view_edit_entry"
					|| $actionName == "photoalbum_action_edit_entry"
					|| strpos($attributes["prefix_id_name"], PHOTOALBUM_PREFIX_REFERENCE) === 0
					|| strpos($attributes["prefix_id_name"], PHOTOALBUM_PREFIX_ALBUM_LIST) === 0) {
			$photoalbum = $photoalbumView->getPhotoalbum();
		} else {
			$photoalbum = $photoalbumView->getCurrentPhotoalbum();
		}

		if (empty($photoalbum)) {
        	return $errStr;
        }

		$request =& $container->getComponent("Request");
		$request->setParameter("photoalbum", $photoalbum);
 
        return;
    }
}
?>