<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 初期表示の設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action_Admin_Default extends Action
{
    // リクエストパラメータを受け取るため
	var $target_module_id = null;
	var $module_id = null;

    // 使用コンポーネントを受け取るため
	var $configAction = null;
	var $modulesView = null;

    // 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->target_module_id > 0) {
	    	$module = $this->modulesView->getModulesById($this->target_module_id);
	    	if ($module === false) {
	    		return 'error';
	    	}
	    	$pathList = explode("_", $module["action_name"]);
	    	$dir_name = $pathList[0];
    	} else {
    		$dir_name = "";
    	}
    	$result = $this->configAction->updConfigValue($this->module_id, "default_module", $dir_name);
    	if ($result === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>