<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 権限の値チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Validator_AuthorityValue extends Validator
{
    /**
     * 権限の値チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (!is_array($attributes["contents_authority"])||
				!is_array($attributes["mail_authority"])) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		$contents_authority = min(array_keys($attributes["contents_authority"]));
		$request->setParameter("contents_authority", $contents_authority);
		
		$mail_authority = min(array_keys($attributes["mail_authority"]));
		$request->setParameter("mail_authority", $mail_authority);
    }
}
?>