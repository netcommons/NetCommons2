<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の編集の表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_View_Mobile_Confirm_Delete extends Action
{
    // リクエストパラメータを受け取るため

    // 使用コンポーネントを受け取るため
	var $request = null;

    // 値をセットするため

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->request->setParameter("rrule", CALENDAR_PLAN_EDIT_THIS);
		return 'success';
    }
}
?>
