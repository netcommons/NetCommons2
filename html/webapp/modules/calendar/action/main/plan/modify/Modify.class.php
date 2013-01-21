<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 予定の編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Main_Plan_Modify extends Action
{
    // リクエストパラメータを受け取るため
	var $calendar_id = null;
	var $title = null;
	var $icon_name = null;
	var $plan_room_id = null;
	var $notification_mail = null;
	var $edit_rrule = null;
	var $timezone_offset = null;

	var $allday_flag = null; 
	var $start_time_full = null;
	var $end_time_full = null;
	var $location = null;		//詳細登録のみ
	var $contact = null;		//詳細登録のみ
	var $description = null;	//詳細登録のみ
	var $rrule = null;			//詳細登録のみ

    // 使用コンポーネントを受け取るため
	var $session = null;
	var $calendarPlanAction = null;
	var $uploadsAction = null;
	var $calendarAction = null;

	// 値をセットするため
	var $date = null;

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$plan_params = array(
			"room_id" => $this->plan_room_id,
    		"title" => $this->title,
			"title_icon" => $this->icon_name,
			"allday_flag" => intval($this->allday_flag),
			"start_time_full" => $this->start_time_full,
			"end_time_full" => $this->end_time_full,
			"timezone_offset" => $this->timezone_offset,
			"location" => $this->location,
			"contact" => $this->contact,
			"description" => $this->description,
			"rrule" => $this->calendarPlanAction->concatRRule($this->rrule),
		);
		$this->date = timezone_date($this->start_time_full, false, "Ymd");

		$result = $this->calendarPlanAction->updatePlan($this->calendar_id, $plan_params, $this->edit_rrule);
		if ($result === false) {
			return 'error';
		}

		$upload_id_arr = $this->uploadsAction->getUploadId($this->description);
		if (!empty($upload_id_arr)) {
			$params = array(
				"room_id" => $plan_params["room_id"]
			);
			$where_params = array(
				"upload_id IN (". implode(",", $upload_id_arr) .")" => null
			);
	    	$result = $this->uploadsAction->updUploads($params, $where_params);
	    	if ($result === false) {
	    		return false;
	    	}
		}

		$result = $this->calendarAction->setWhatsnew($plan_params);
		
		if ($this->notification_mail == _ON) {
			$this->session->setParameter("calendar_mail_calendar_id", $this->calendar_id);
		} else {
			$this->session->setParameter("calendar_mail_calendar_id", 0);
		}
        return 'success';
    }
}
?>