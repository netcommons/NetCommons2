<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ToDo一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Todo_View_Mobile_Init extends Action
{
	// 値をセットするため
	var $todos = null;

	/**
	 * ToDo一覧画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		return 'success';
	}
}
?>
