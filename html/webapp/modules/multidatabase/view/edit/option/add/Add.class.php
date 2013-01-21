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
class Multidatabase_View_Edit_Option_Add extends Action
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
    	$this->option = array();
    	$this->option = MULTIDATABASE_DEFAULT_OPTIONS.(intval($this->iteration) + 1);
		return 'success';
	}
}
?>
