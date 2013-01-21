<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール送信
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Action_Main_Plan_Mail extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
	var $room_id = null;

    // 使用コンポーネントを受け取るため
    var $session = null;
    var $request = null;
	var $configView = null;
	var $calendarView = null;
	var $mailMain = null;
	var $usersView = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$config = $this->configView->getConfig($this->module_id, false);
		if ($config === false) {
    		return false;
    	}
    	if (defined($config["mail_send"]["conf_value"])) {
    		$mail_send = constant($config["mail_send"]["conf_value"]);
    	} else {
    		$mail_send = intval($config["mail_send"]["conf_value"]);
    	}
    	if ($mail_send == _OFF) {
    		return 'success';
    	}
    	if (defined($config["mail_authority"]["conf_value"])) {
    		$mail_authority = constant($config["mail_authority"]["conf_value"]);
    	} else {
    		$mail_authority = intval($config["mail_authority"]["conf_value"]);
    	}
    	if (defined($config["mail_subject"]["conf_value"])) {
    		$mail_subject = constant($config["mail_subject"]["conf_value"]);
    	} else {
    		$mail_subject = $config["mail_subject"]["conf_value"];
    	}
    	if (defined($config["mail_body"]["conf_value"])) {
    		$mail_body = preg_replace("/\\\\n/s", "\n", constant($config["mail_body"]["conf_value"]));
    	} else {
    		$mail_body = $config["mail_body"]["conf_value"];
    	}
    	
		$calendar_id = $this->session->getParameter("calendar_mail_calendar_id");
		$calendar_id = intval($calendar_id);
		if ($calendar_id == 0) {
			return 'success';
		}
		
		$calendar_obj = $this->calendarView->getCalendar($calendar_id);
    	if ($calendar_obj === false) {
    		return 'error';
    	}

		$this->request->setParameter("plan_room_id", $calendar_obj["room_id"]);
		$block_id = $this->calendarView->getBlockIdByWhatsnew();

		$this->mailMain->setSubject($mail_subject);
		$this->mailMain->setBody($mail_body);
		
		$tags["X-TITLE"] = htmlspecialchars($calendar_obj["title"]);
		$tags["X-PLAN_FLAG"] = htmlspecialchars($calendar_obj["page_name"]);
		$tags["X-START_TIME"] = $calendar_obj["start_time_str"];
		$tags["X-END_TIME"] = $calendar_obj["end_time_str"];
		$tags["X-LOCATION"] = htmlspecialchars($calendar_obj["location"]);
		$tags["X-CONTACT"] = htmlspecialchars($calendar_obj["contact"]);
		$tags["X-USER"] = htmlspecialchars($calendar_obj["insert_user_name"]);
		$tags["X-INPUT_TIME"] = timezone_date($calendar_obj["insert_time"], false, _FULL_DATE_FORMAT);
		$tags["X-BODY"] = ($calendar_obj["description"] == "" ? CALENDAR_MAIL_NO_DISCRIPTION : $calendar_obj["description"]);
		$rrule_str = $this->calendarView->stringRRule($calendar_obj["rrule"]);
		$tags["X-RRULE"] = ($rrule_str == "" ? CALENDAR_MAIL_NO_RRULE : $rrule_str);
		$tags["X-URL"] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=calendar_view_main_init".
							"&calendar_id=". $calendar_id.
							"&date=".$calendar_obj["start_date"].
							"&block_id=". $block_id.
							"#_". $block_id;
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers($calendar_obj["room_id"], $mail_authority);
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("calendar_mail_calendar_id");
        return 'success';
    }
}
?>