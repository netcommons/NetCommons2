<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フイルタ一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Filter_Init extends Action 
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;

	// 値をセットするため
	var $filters = null;
	var $current_menu = null;
	
    /**
     * フイルタ一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->current_menu = PM_LEFTMENU_SETTING;
		
		$this->filters = &$this->pmView->getFilters();
		return 'success';
    }
}
?>