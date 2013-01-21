<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日の編集の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_View_Admin_Edit extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $holiday_id = null;

    // 使用コンポーネントを受け取るため
	var $holidayView = null;

    // 値をセットするため
	var $holiday_obj = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->holidayView->getRRule($this->holiday_id);
        if ($result === false) {
        	return 'error';
        }
        $this->holiday_obj = $result[0];
        $this->holiday_obj["rrule"] = $this->holidayView->parseRRule($this->holiday_obj["rrule"]);
       	return 'success';
    }
}
?>