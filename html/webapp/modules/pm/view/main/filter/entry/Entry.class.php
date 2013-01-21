<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フイルタ登録画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_View_Main_Filter_Entry extends Action
{
	// Filterによりセット
	var $block_id = null;
	var $room_id = null;
	var $room_arr = null;
	
	// 使用コンポーネントを受け取るため
	var $pmView = null;
	var $request = null;

    //  値をセットするため
    var $filter = null;
	var $top_id_name = null;

    /**
     * フイルタ登録画面表示アクション
     *
     * @access  public
     */
    function execute()
	{
		$this->filter = &$this->pmView->getFilter();
		return "success";
    }
}
?>
