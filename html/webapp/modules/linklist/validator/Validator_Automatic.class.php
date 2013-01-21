<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 自動取得チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_Automatic extends Validator
{
    /**
     * 自動取得の値チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
		if (!@parse_url($attributes["url"])) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
		$requestMain =& $container->getComponent("requestMain");
		$html = $requestMain->getResponseHtml($attributes["url"]);
		if (empty($html)) {
			return $errStr;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter("html", $html);
    }
}
?>