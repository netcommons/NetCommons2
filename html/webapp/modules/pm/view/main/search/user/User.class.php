<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 宛先ユーザー検索画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Search_User extends Action 
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	
	// パラメータを受け取るため

	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $session = null;
	var $request = null;

	// 値をセットするため
	var $dialog_name = null;
	
    /**
     * メッセージ検索画面表示アクション
     *
     * @access  public
     */
    function execute()
    {	
		$this->dialog_name = PM_USER_SEARCH;
    	return 'success';
    }
}
?>