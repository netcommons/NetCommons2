<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メッセージ検索画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Search_Init extends Action 
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// パラメータを受け取るため

	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $session = null;
	var $request = null;

	// 値をセットするため
	var $current_menu = null;
	var $tags = null;
	
    /**
     * メッセージ検索画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->current_menu = PM_LEFTMENU_SEARCH;
		$this->tags = $this->pmView->getTags();
		
		$this->session->removeParameter("search");
		
		return 'success';
    }
}
?>