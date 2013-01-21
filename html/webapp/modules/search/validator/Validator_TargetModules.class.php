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
class Search_Validator_TargetModules extends Validator
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
    	if (empty($attributes["target_modules"])) {
    		return $errStr;
    	}
    	$container =& DIContainerFactory::getContainer();
    	$modulesView =& $container->getComponent("modulesView");
    	$modules = $modulesView->getModules(array("system_flag" => _OFF), null, null, null, $func = array($this, "_fetchcallbackModules"));
    	$target_modules_arr = $attributes["target_modules"];
    	
    	foreach($target_modules_arr as $target_module) {
    		if(!in_array($target_module, $target_modules_arr)) {
    			return _INVALID_INPUT;
    		}
    	}
    }
    
    
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackModules($result, $func_param) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
			$row["dir_name"] = $pathList[0];
			//$row["module_name"] = $func_param->loadModuleName($pathList[0]);
			$ret[] = $row["dir_name"];
		}
		return $ret;
	}
}
?>
