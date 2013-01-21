<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Edit_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;

    // 使用コンポーネントを受け取るため
	var $calendarView = null;
	var $session = null;

    // 値をセットするため
	var $calendar_block = null;
	
    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->calendar_block = $this->calendarView->getBlock();
    	if ($this->calendar_block === false) {
    		return 'error';
    	}

		$this->session->removeParameter(array("calendar", "not_enroll_room", $this->block_id));
		$this->session->removeParameter(array("calendar", "enroll_room", $this->block_id));
		$this->session->removeParameter(array("calendar", "myroom_flag", $this->block_id));

       	return 'success';
    }
}
?>