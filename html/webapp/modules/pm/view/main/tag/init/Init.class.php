<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タグ一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Tag_Init extends Action 
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $request = null;

	// 値をセットするため
    var $tags = null;
	var $tags_noexist = null;
    var $scrolling = null;
	
	var $current_menu = null;
	var $search_flag = null;
	
	var $top_id_name = null;
	
    /**
     * タグ一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {  	
		$this->current_menu = PM_LEFTMENU_SETTING;
		
		$this->tags = &$this->pmView->getTags();
        $this->tags_noexist = 1;
		
		if ($this->tags === false) {
        	$this->tags_noexist = 0;
        }

		return 'success';
    }
}
?>