<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_View_Mobile_Details extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $whatsnew_module_id = null;
	var $whatsnew_unique_id = null;

    // 使用コンポーネントを受け取るため
	var $whatsnewView = null;

	// Filterによりセット
	var $room_arr_flat =null; 

    // 値をセットするため
	var $whatsnew = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->whatsnew = $this->whatsnewView->getResult($this->room_arr_flat, $this->whatsnew_module_id, $this->whatsnew_unique_id);
    	if ($this->whatsnew === false) {
    		return 'error';
    	}
		return 'success';
    }
}
?>
