<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日設定の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_View_Admin_Init extends Action
{
    // リクエストパラメータを受け取るため
	var $block_id = null;
	var $year = null;
	var $lang = null;

    // 使用コンポーネントを受け取るため
	var $languagesView = null;
	var $holidayView = null;
	var $session = null;

    // 値をセットするため
	var $year_list = null;
	var $lang_list = null;
	var $holiday_list = null;
	var $count = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if (!isset($this->year)) {
    		$this->year = timezone_date(null, false, "Y");
    	}
    	$this->session->setParameter("holiday_year", $this->year);

    	if (!isset($this->lang)) {
			$this->lang = $this->session->getParameter("_lang");
    	}
    	$this->session->setParameter("holiday_lang", $this->lang);
		$this->lang_list = $this->languagesView->getLanguagesList();
		$this->holiday_list = $this->holidayView->getYear($this->year, $this->lang);
        if ($this->holiday_list === false) {
        	return 'error';
        }
    	$this->count = count($this->holiday_list);
       	return 'success';
    }
}
?>