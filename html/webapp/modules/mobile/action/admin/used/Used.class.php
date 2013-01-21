<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * OnOffの設定
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Action_Admin_Used extends Action
{
    // リクエストパラメータを受け取るため
	var $target_module_id = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $mobileView = null;

    // 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_module = $this->mobileView->getModules($this->target_module_id);
    	if ($mobile_module === false) {
    		return 'error';
    	}
    	if ($mobile_module["use_flag"] == _ON) {
    		$params = array("use_flag" => _OFF);
    	} else {
    		$params = array("use_flag" => _ON);
    	}
		$result = $this->db->updateExecute("mobile_modules", $params, array("module_id" => $this->target_module_id));
    	if ($result === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>