<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日リストの表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_View_Admin_List extends Action
{
    // リクエストパラメータを受け取るため

    // 使用コンポーネントを受け取るため
	var $holidayView = null;
	var $session = null;
    var $limit = null;
    var $offset = null;
    var $sort_col = null;
    var $sort_dir = null;

    // 値をセットするため
	var $holiday_list = null;
    var $year = null;
    var $lang = null;
	
    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->year = $this->session->getParameter("holiday_year");
    	$this->lang = $this->session->getParameter("holiday_lang");
    	$this->holiday_list = $this->holidayView->getYear($this->year, $this->lang, $this->sort_col, $this->sort_dir);
        if ($this->holiday_list === false) {
        	return 'error';
        }
        return 'success';
    }
}
?>
