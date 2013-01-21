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
class Reservation_Action_Main_Reserve_Mail extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
 	var $room_id = null;

    // 使用コンポーネントを受け取るため
    var $session = null;
    var $request = null;
	var $reservationView = null;
 	var $mailMain = null;
	var $usersView = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$config = $this->reservationView->getMailConfig();
		if ($config === false) {
    		return 'error';
    	}
    	if ($config["mail_send"] == _OFF) {
    		return 'success';
    	}

		$reserve_id = $this->session->getParameter("reservation_mail_reserve_id");
		$reserve_id = intval($reserve_id);
		if ($reserve_id == 0) {
			return 'success';
		}

		$reserve = $this->reservationView->getReserve($reserve_id);
    	if ($reserve === false) {
    		return 'error';
    	}

		$location = $this->reservationView->getLocation($reserve["location_id"]);
    	if ($location === false) {
    		return 'error';
    	}

		$this->mailMain->setSubject($config["mail_subject"]);
		$this->mailMain->setBody($config["mail_body"]);

		$tags["X-LOCATION_NAME"] = htmlspecialchars($reserve["location_name"]);
		$tags["X-TITLE"] = htmlspecialchars($reserve["title"]);
		if ($reserve["reserve_flag"] == RESERVATION_MEMBERS) {
			$tags["X-RESERVE_FLAG"] = RESERVATION_NO_RESERVE_FLAG;
		} else { 
			$tags["X-RESERVE_FLAG"] = htmlspecialchars($reserve["page_name"]);
    	}
		if ($reserve["start_date_view"] == $reserve["end_date_view"]) {
			$tags["X-RESERVE_TIME"] = $reserve["start_date_str"]." ".sprintf(RESERVATION_TIME_FMTO_FORMAT, $reserve["start_time_str"], $reserve["end_time_str"]);
		} else {
			$tags["X-RESERVE_TIME"] = sprintf(RESERVATION_TIME_FMTO_FORMAT, $reserve["start_date_str"]." ".$reserve["start_time_str"], $reserve["end_date_str"]." ".$reserve["end_time_str"]);
		}
		$tags["X-CONTACT"] = htmlspecialchars($reserve["contact"]);
		$tags["X-USER"] = htmlspecialchars($reserve["insert_user_name"]);
		$tags["X-INPUT_TIME"] = timezone_date($reserve["insert_time"], false, _FULL_DATE_FORMAT);
		$tags["X-BODY"] = ($reserve["description"] == "" ? RESERVATION_MAIL_NO_DISCRIPTION : $reserve["description"]);

		$rrule_str = $this->reservationView->stringRRule($reserve["rrule"]);
		$tags["X-RRULE"] = ($rrule_str == "" ? RESERVATION_MAIL_NO_RRULE : $rrule_str);

		$this->request->setParameter("reserve_room_id", $reserve["room_id"]);
		$block_id = $this->reservationView->getBlockIdByWhatsnew();

		$tags["X-URL"] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=reservation_view_main_init".
							"&reserve_id=". $reserve_id.
							"&view_date=".$reserve["start_date"].
							"&block_id=". $block_id.
							"#_". $block_id;
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers($reserve["room_id"], $config["mail_authority"]);
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("reservation_mail_reserve_id");
        return 'success';
    }
}
?>