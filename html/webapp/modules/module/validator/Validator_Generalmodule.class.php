<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 一般モジュールかどうかチェック
 * ログインモジュールのメニューのアンインストールも禁止する
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Module_Validator_Generalmodule extends Validator
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
    	$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		
    	$module_id = $attributes;
		
        $module =& $container->getComponent("modulesView");
        $module_obj =& $module->getModulesById($module_id);
        // module_idが0ならば、念のためアインストールを実行させる
        if(intval($module_id) != 0) {
	        if($module_obj['system_flag']) {
	        	//システムモジュール
	        	return $errStr;	
	        }
	        $pathList = explode("_", $module_obj["action_name"]);
	        if($pathList[0] == "login" || $pathList[0] == "menu") {
	        	//ログインモジュール
	        	return $errStr;	
	        }
        }
        //アクションにmodule_objセット
        BeanUtils::setAttributes($action, array("module_obj"=>$module_obj));
    }
}
?>
