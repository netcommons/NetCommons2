<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日の追加の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_View_Admin_Add extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $year = null;

    // 使用コンポーネントを受け取るため
	var $session = null;

    // 値をセットするため
	var $holiday_obj = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->holiday_obj = array(
    		"rrule_id" => 0,
    		"lang_dirname" => $this->session->getParameter("holiday_lang"),
    		"summary" => "",
    		"varidable_flag" => _OFF,
    		"substitute_flag" => _ON,
    		"start_time" => $this->year."0101",
    		"end_time" => $this->year."0102",
    		"rrule" => array("YEARLY"=>array("FREQ"=>"YEARLY","INTERVAL"=>1,"BYMONTH"=>array(1),"BYDAY"=>array("1SU")))
    	);
       	return 'success';
    }
}
?>