<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * URLの存在チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Rss_Validator_UrlCheck extends Validator
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
		if (!@parse_url($attributes)) {
			return $errStr;
		}
		
		$container =& DIContainerFactory::getContainer();
		$requestMain =& $container->getComponent("requestMain");
		$xml = $requestMain->getResponseHtml($attributes);
    	if (empty($xml)) {
            return $errStr;
        }

        $request =& $container->getComponent("Request");
		$request->setParameter("xml", $xml);

		return;
    }
}
?>
