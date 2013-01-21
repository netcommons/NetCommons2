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
class Reservation_View_Main_Movedate extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
	var $view_date = null;
	var $display_type = null;
	var $category_id = null;
	var $location_id = null;
	var $location_count_list = null;

    // 使用コンポーネントを受け取るため
	var $session = null;

	// validatorから受け取るため
	var $location_list = null;
	var $category_list = null;
	var $today = null;

    // 値をセットするため
	var $input_date = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$_id = $this->session->getParameter("_id");
    	$this->session->setParameter(array("reservation", "popup_move", "_id", $this->block_id), $_id);

		$this->input_date = date(_INPUT_DATE_FORMAT, 
								mktime(0,0,0,substr($this->view_date,4,2),substr($this->view_date,6,2),substr($this->view_date,0,4)));
        return 'success';
    }
}
?>
