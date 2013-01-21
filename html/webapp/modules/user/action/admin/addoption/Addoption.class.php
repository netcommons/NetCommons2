<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目追加-リスト追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Addoption extends Action
{
	// リクエストパラメータを受け取るため
	var $iteration = null;
	
	// 値をセットするため
	var $option = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		//
		// リスト値
		//
		$this->iteration = intval($this->iteration) + 1;
    	$this->option = array();
    	$this->option['options'] = USER_DEFAULT_OPTIONS.$this->iteration;
    	$this->option['default_selected'] = _OFF;	//固定値
		return 'success';
	}
}
?>
