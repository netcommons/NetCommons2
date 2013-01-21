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
class Whatsnew_View_Main_Rss extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $room_id = null;
	var $display_type = null;
	var $display_days = null;
	var $_header = null;

    // 使用コンポーネントを受け取るため
	var $whatsnewView = null;

	// Filterによりセット
	var $room_arr_flat =null; 

 	// validatorから受け取るため
	var $whatsnew_obj = null;

    // 値をセットするため
	var $channel = null;
	var $results = null;
	var $count = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->whatsnew_obj["display_type"] = WHATSNEW_DEF_RSS;
    	$this->results = $this->whatsnewView->getResults($this->whatsnew_obj, $this->room_arr_flat);
    	if ($this->results === false) {
    		return 'error';
    	}
		$this->count = count($this->results);
		$this->channel = $this->whatsnewView->getChannel($this->whatsnew_obj);

   		return 'success';
    }
}
?>