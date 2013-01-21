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
class Search_Validator_KeywordHandle extends Validator
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
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "search_action_main_result_condition"){
	    	if (trim($attributes["keyword"],"\t \n\0　") == "" && 
	    			trim($attributes["handle"], "\t \n\0　") == "") {
	    		return $errStr;
	    	}
		} elseif ($actionName == "search_action_main_result_easy"){
			if (trim($attributes["keyword"],"\t \n\0　") == ""){
				return $errStr;
			}
		} else {
			return $errStr;
		}
		
		return;   
    }
        
}

?>
