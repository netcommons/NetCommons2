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
class Reservation_Validator_CategorySequence extends Validator
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

		$drag_category = $reservationView->getCategory($attributes["drag_category_id"]);
		if (empty($drag_category)) {
			return $errStr;
		}
		$request->setParameter("drag_category", $drag_category);

		$drop_category = $reservationView->getCategory($attributes["drop_category_id"]);
		if (empty($drop_category)) {
			return $errStr;
		}
		$request->setParameter("drop_category", $drop_category);
    }
}
?>
