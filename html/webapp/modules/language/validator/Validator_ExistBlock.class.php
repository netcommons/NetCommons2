<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ブロックに配置してあるかどうか
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Language_Validator_ExistBlock extends Validator
{
    /**
     * ブロックに配置してあるかどうか
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
		$languageView =& $container->getComponent("languageView");
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
			
		$block_id = $attributes;
		if(empty($block_id) || $actionName == "language_action_edit_create") {
			$lang_obj = $languageView->getDefaultLanguage();
		}else {
			$lang_obj = $languageView->getLanguageById($block_id);
		}		
		if($lang_obj === false || !isset($lang_obj)) {
			return $errStr;
		}

		
		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("lang_obj"=>$lang_obj));
		return;
	}
}
?>
