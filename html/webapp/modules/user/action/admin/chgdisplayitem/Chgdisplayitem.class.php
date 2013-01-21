<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Chgdisplayitem extends Action
{
	// リクエストパラメータを受け取るため
	var $item_id = null;
	var $display_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $usersAction = null;
	
	// バリデートによりセットするため
	var $items = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$item_id = intval($this->item_id);
		$display_flag = intval($this->display_flag) == _ON ? _ON : _OFF;
		//ログインＩＤ、パスワード、ハンドルは、ＯＦＦにできないようにする
		if($this->items['tag_name'] == "login_id" || $this->items['tag_name'] == "password" ||
			 $this->items['tag_name'] == "handle") {
			$display_flag = _ON;
		}
		
		$result = $this->usersAction->updItem(array("display_flag" => $display_flag), array("item_id" => $item_id));
		if ($result === false) {
			return 'error';
		}
		return 'success';
	}
}
?>
