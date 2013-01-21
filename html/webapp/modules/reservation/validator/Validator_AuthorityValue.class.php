<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予約を追加できる権限チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Reservation_Validator_AuthorityValue extends Validator
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
		if (!is_array($attributes["add_authority"])) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		$authorityID = min(array_keys($attributes["add_authority"]));
		$request->setParameter("add_authority", intval($authorityID));
    }
}
?>
