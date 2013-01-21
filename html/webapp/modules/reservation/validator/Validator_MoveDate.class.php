<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_MoveDate extends Validator
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
		$request =& $container->getComponent("Request");
		
		if (empty($attributes["move_year"]) && empty($attributes["move_date"])) {
			return;
		}
		
		if ($attributes["display_type"] == RESERVATION_DEF_MONTHLY) {
    		$move_date = $attributes["move_year"]. $attributes["move_month"]. "01";
    	} else {
    		$move_date = $attributes["move_date"];
    	}
		if (empty($move_date)) {
			return $errStr;
		}
		
		$request->setParameter("view_date", $move_date);
    }
}
?>
