<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Main_Plan_Delete extends Action
{
    // リクエストパラメータを受け取るため
	var $calendar_id = null;
	var $edit_rrule = null;

 	// validatorから受け取るため
	var $calendar_obj = null;

    // 使用コンポーネントを受け取るため
	var $calendarPlanAction = null;
	var $calendarAction = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->edit_rrule = intval($this->edit_rrule);
        $plan_id = $this->calendar_obj["plan_id"];

		$result = $this->calendarPlanAction->deletePlan($this->calendar_id, $this->edit_rrule);
		if ($result === false) {
			return 'error';
		}

    	$params = array("plan_id"=>$plan_id);
		$result = $this->calendarAction->setWhatsnew($params);
        return 'success';
    }
}
?>