<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日付移動の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Main_Movedate extends Action
{
    // リクエストパラメータを受け取るため
	var $date = null;
	var $display_type = null;
	var $format_date = null;

    // 使用コンポーネントを受け取るため
    var $session = null;

    // 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$this->today = timezone_date(null, false, "Ymd");
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
		if ($mobile_flag == _ON) {
			$this->display_type = $this->session->getParameter(array("calendar_mobile", "display_type"));
		}
		$this->display_type = intval($this->display_type);
		$this->format_date = date(_INPUT_DATE_FORMAT, mktime(0,0,0,substr($this->date,4,2),substr($this->date,6,2),substr($this->date,0,4)));
        return 'success';
    }
}
?>
