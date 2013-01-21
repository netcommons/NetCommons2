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
class Pm_Action_Main_Mail extends Action
{
    // リクエストパラメータを受け取るため
    var $module_id = null;
 	var $room_id = null;
 	var $block_id = null;
	var $page_id = null;

    // 使用コンポーネントを受け取るため
    var $session = null;
	var $configView = null;
	var $pmView = null;
 	var $mailMain = null;
	var $usersView = null;
	var $snscommunityView = null;
	
    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
		$message_id = $this->session->getParameter("pm_mail_message_id");
		$message_id = intval($message_id);
		if ($message_id == 0) {
			return 'success';
		}

		$message = $this->pmView->getMessageById($message_id);
    	if ($message === false) {
			return 'error';
    	}
    	
    	//セキュリティーの為、ユーザーのチェック
		$userID = $this->session->getParameter('_user_id');
		if(empty($userID) || $userID != $message["insert_user_id"]) {
			return 'error';
		}

		$mail_subject = sprintf(PM_SUBJECT_FORMAT, '', $message["subject"]);
		$mail_body = preg_replace("/\\\\n/s", "\n", PM_MAIL_BODY);
		$this->mailMain->setSubject($mail_subject);
		$this->mailMain->setBody($mail_body);

		$tags["X-TITLE"] = htmlspecialchars($message["subject"]);
		$tags["X-USER"] = htmlspecialchars($message["insert_user_name"]);
		$tags["X-INPUT_TIME"] = timezone_date($message["insert_time"], false, _FULL_DATE_FORMAT);
		$tags["X-BODY"] = $message["body"];
		
		$active_action_name = "active_center";
		
		$tags["X-URL"] = BASE_URL. INDEX_FILE_NAME.
						 "?action=". DEFAULT_ACTION .
						 "&" . $active_action_name . "=pm_view_main_message_detail".
						 "&message_id=". $message["message_id"];
			 
		$this->mailMain->assign($tags);

		$mail_filters = $this->session->getParameter("pm_mail_filters");
		$mail_forwards = $this->session->getParameter("pm_mail_forwards");
		if(!is_array($mail_filters)){ $mail_filters = array(); }
		if(!is_array($mail_forwards)){ $mail_forwards = array(); }

		if(sizeof($mail_filters) || sizeof($mail_forwards)){
			$mail_users = array_merge($mail_forwards, $mail_filters);
		} else {
			return 'error';
		}

		$email_item_id = $this->pmView->getEmailItemId();
		if (!empty($email_item_id)) {
			$user_item =& $this->usersView->getUserItemLinkById($userID,$email_item_id);
		}
		if (empty($user_item)) {
			$user_email = "";
			$public_flag = _OFF;
		} else {
			$user_email = $user_item['content'];
			$public_flag = $user_item['public_flag'];
		}
		if (empty($user_email) || $public_flag == _OFF) {
			$open_users = array();
			$close_users = $mail_users;
		} else {
			list($open_users, $close_users) = $this->pmView->divideMailFrom($mail_users, $email_item_id);
		}
		if(!$open_users && !$close_users){
			return 'error';
		}
		if (!empty($open_users)) {
			$open_sender = $this->pmView->getFromInfo(_ON);
			$this->mailMain->setFromName($open_sender["from_name"]);
			$this->mailMain->setFromEmail($user_email);
			$this->mailMain->setToUsers($open_users);
			$this->mailMain->send();
		}
		if (!empty($close_users)) {
			$close_sender = $this->pmView->getFromInfo(_OFF);
			$this->mailMain->setFromName($close_sender["from_name"]);
			$this->mailMain->setFromEmail($close_sender["from_email"]);
			$this->mailMain->setToUsers($close_users);
			$this->mailMain->send();
		}

		$this->session->removeParameter("pm_mail_forwards");
		$this->session->removeParameter("pm_mail_filters");
		$this->session->removeParameter("pm_mail_message_id");
        return 'success';
    }
}
?>