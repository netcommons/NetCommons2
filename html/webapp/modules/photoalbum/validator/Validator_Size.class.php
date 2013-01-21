<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * サイズの値チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Validator_Size extends Validator
{
    /**
     * サイズの値チェックバリデータ
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        if ($attributes["size_flag"] != _ON) {
        	return;
        }
        
        $min = $params[0];
        $max = $params[1];

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
        $errors = array();
        if ($attributes["width"] < $min
        		|| $attributes["width"] > $max) {
			$errors[] = sprintf($smartyAssign->getLang("_number_error"), $smartyAssign->getLang("photoalbum_slide_size_width"), $min, $max);
		}

        if ($attributes["height"] < $min
        		|| $attributes["height"] > $max) {
			$errors[] = sprintf($smartyAssign->getLang("_number_error"), $smartyAssign->getLang("photoalbum_slide_size_height"), $min, $max);
		}
		
		if (empty($errors)) {
			return;
		}
		
		$errorStr = implode("<br />", $errors);
		return $errorStr;
    }
}
?>
