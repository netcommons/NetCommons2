<?php

/**
 * 言語チェック
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_Validator_Language extends Validator
{
    /**
     * 言語チェック
     *   Filterとして行うべきだが、実装していないためValidatorとして実装
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     (使用しない)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
		$languagesView =& $container->getComponent("languagesView");
		$languages =& $languagesView->getLanguagesList();
		if ($languages === false) {
			return $errStr;
		}
		
		$request =& $container->getComponent("Request");
		$request->setParameter('languages', $languages);

    	return;
    }
}
?>