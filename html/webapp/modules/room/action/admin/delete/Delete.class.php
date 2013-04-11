<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム削除処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Delete extends Action
{
	// パラメータを受け取るため
	var $edit_current_page_id = null;

	// 使用コンポーネントを受け取るため
	var $pagesAction = null;

	/**
	 * ルーム削除処理
	 *
	 * @access  public
	 */
	function execute()
	{
		if (!$this->pagesAction->deleteRoom($this->edit_current_page_id)) {
			return 'error';
		}

		return 'success';
	}
}
?>