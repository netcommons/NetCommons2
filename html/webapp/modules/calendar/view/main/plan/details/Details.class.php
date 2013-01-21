<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の詳細の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_Plan_Details extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $calendar_id = null;
	var $display_type = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;
    var $session = null;
	var $mobileView = null;
 
  	// validatorから受け取るため
	var $calendar_obj = null;
	var $rrule_calendar_id = null;
 
    // 値をセットするため
	var $calendar_block = null;
	var $html_flag = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	if ($mobile_flag == _OFF) {
	    	$this->display_type = intval($this->display_type);
			$this->calendar_block = $this->calendarView->getBlock($this->display_type);
	    	if ($this->calendar_block === false) {
	    		return 'error';
	    	}
    	}
		else {
			$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		}
		
        return 'success';
    }
}
?>
