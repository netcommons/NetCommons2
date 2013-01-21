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
class Reservation_Validator_CategoryView extends Validator
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
		$request =& $container->getComponent("Request");

		if (!isset($attributes["category_list"])) {
	    	$attributes["category_list"] = $reservationView->getCategories();
			if ($attributes["category_list"] === false) {
	        	return $errStr;
	        }
			$location_count_list = $reservationView->getCountLocationByCategory();
			$request->setParameter("location_count_list", $location_count_list);
		}

		$request->setParameter("category_list", $attributes["category_list"]);
    }
}
?>
