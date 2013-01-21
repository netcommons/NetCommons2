<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリー存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_CategoryExists extends Validator
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
		$reservationView =& $container->getComponent("reservationView");
		
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		
		if ($actionName == "reservation_action_edit_addblock") {
			$count = $reservationView->getCountCategory();
			
			$request =& $container->getComponent("Request");
			$request->setParameter("category_count", $count);

		} elseif ($actionName == "reservation_action_edit_category_sequence") {
			if (!$reservationView->categoryExists($attributes["drag_category_id"])) {
				return $errStr;
			}
			if (!$reservationView->categoryExists($attributes["drop_category_id"])) {
				return $errStr;
			}

		} elseif ($actionName == "reservation_action_edit_location_sequence") {
			if (isset($attributes["drop_category_id"]) && 
					!$reservationView->categoryExists($attributes["drop_category_id"])) {
				return $errStr;
			}

		} elseif ($actionName == "reservation_view_edit_style_switchcate" || $actionName == "reservation_action_edit_style") {
			if (!empty($attributes["category_id"]) && !$reservationView->categoryExists($attributes["category_id"])) {
				return $errStr;
			}

		} else {
			if (!$reservationView->categoryExists($attributes["category_id"])) {
				return $errStr;
			}
		}
    }
}
?>
