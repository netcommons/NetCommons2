<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * レポート画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_View_Main_Modulesinfo extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    
    // コンポーネントを使用するため
    var $modulesView = null;
    var $preexecute = null;
    
    // バリデートによりセット
    var $user = null;
    
    // 値をセットするため
    var $personalinf = null;
    var $modules = null; 
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
    	$modules =& $this->modulesView->getModules();
    	$this->modules = null;
    	foreach($modules as $module) {
    		if($module['personalinf_action'] != "") {
    			// 	personalinf_action指定あり
    			$preexecute_params = array(
		    							"mode" => "personalinf",
		    							"action" =>$module['personalinf_action'], 
		    							"module_id" => $module['module_id'],
		    							"user_id" => $this->user_id,
		    							"_header" =>_OFF,
		    							"_output" =>_OFF
		    						);
    			$result = $this->preexecute->preExecute($module['personalinf_action'], $preexecute_params);
    			$result = preg_replace("/^".ERROR_MESSAGE_PREFIX."/i","", $result);
    			$this->personalinf[$module['module_id']] = $result;
    			$this->modules[$module['module_id']] = $module;
    		}
    	}
    	
    	return 'success';
    }
}
?>
