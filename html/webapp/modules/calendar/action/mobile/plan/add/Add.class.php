<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定追加処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Mobile_Plan_Add extends Action
{
    // リクエストパラメータを受け取るため
	var $regist = null;
	var $cancel = null;
	var $details = null;
	var $d_flag = null;
	var $s_year = null;
	var $s_month = null;
	var $s_day = null;

	// コンポーネントを受け取るため
	var $request = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if (isset($this->regist)) {
    		$this->d_flag = intval($this->d_flag);
    		if ($this->d_flag == _OFF) {
    			$this->request->setParameter("e_year", $this->s_year);
    			$this->request->setParameter("e_month", $this->s_month);
    			$this->request->setParameter("e_day", $this->s_day);
    		}
			$this->request->setParameter("icon_name", "");
	    	return 'regist';
    	} elseif (isset($this->details)) {
			$this->request->setParameter("details_flag", _ON);
    		return 'details';
    	} else {
	    	return 'cancel';
    	}
    }
}
?>
