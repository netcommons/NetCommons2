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
class Calendar_Action_Mobile_Plan_Modify extends Action
{
    // リクエストパラメータを受け取るため
	var $regist = null;
	var $cancel = null;
	var $cal_id = null;

	// コンポーネントを受け取るため

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	if (isset($this->regist)) {
	    	return 'regist';
    	} else {
	    	return 'cancel';
    	}
    }
}
?>
