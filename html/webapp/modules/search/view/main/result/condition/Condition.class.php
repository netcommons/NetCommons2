<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索条件
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_View_Main_Result_Condition extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $init_flag = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	
	// 値をセットするため
	var $fm_target_date = null;
	var $to_target_date = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		$fm_target_date = $this->session->getParameter(array("search_select", $this->block_id, "fm_target_date"));
		if ($fm_target_date) {
			$timestamp = mktime(0, 0, 0, substr($fm_target_date,4,2), substr($fm_target_date,6,2), substr($fm_target_date,0,4));
			$this->fm_target_date = date(_DATE_FORMAT, $timestamp);
		}

		$to_target_date = $this->session->getParameter(array("search_select", $this->block_id, "to_target_date"));
		if ($to_target_date) {
			$timestamp = mktime(0, 0, 0, substr($to_target_date,4,2), substr($to_target_date,6,2), substr($to_target_date,0,4));
			$this->to_target_date = date(_DATE_FORMAT, $timestamp);
		}
		return 'success';
	}
}
?>