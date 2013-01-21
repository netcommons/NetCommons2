<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * デフォルトモジュールチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Validator_Module extends Validator
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
		$modulesView =& $container->getComponent("modulesView");

		$module_id = intval($attributes);
		$module_obj = $modulesView->getModulesById($module_id);
		if ($module_obj["search_action"] == "") {
			return $errStr;
		}

		$actionChain =& $container->getComponent("ActionChain");
  		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("module_obj"=>$module_obj));
    }
}
?>