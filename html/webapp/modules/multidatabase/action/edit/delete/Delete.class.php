<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $multidatabase_id = null;
	
	// 使用コンポーネントを受け取るため
	var $mdbAction = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$result = $this->mdbAction->deleteMdb($this->multidatabase_id);
		if($result === false) {
			return 'error';
		}
		
		return 'success';
	}
}
?>
