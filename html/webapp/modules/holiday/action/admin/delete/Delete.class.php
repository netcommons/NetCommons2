<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_Action_Admin_Delete extends Action
{
    // リクエストパラメータを受け取るため
	var $rrule_id = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $request = null;

    // 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->db->deleteExecute("holiday_rrule", array("rrule_id"=>$this->rrule_id));
    	if($result === false) {
    		return 'error';
    	}
    	$result = $this->db->deleteExecute("holiday", array("rrule_id"=>$this->rrule_id));
    	if($result === false) {
    		return 'error';
    	}

		$lang = $this->session->getParameter("holiday_lang");
		$year = $this->session->getParameter("holiday_year");
		$this->request->setParameter("lang", $lang);
		$this->request->setParameter("year", $year);

		return 'success';
    }
}
?>