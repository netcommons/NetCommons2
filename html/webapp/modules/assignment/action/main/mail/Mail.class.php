<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール送信アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Assignment_Action_Main_Mail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;
 	
    // 使用コンポーネントを受け取るため
 	var $mailMain = null;
 	var $assignmentView = null;
 	var $session = null;
 	var $usersView = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		$reportID = $this->session->getParameter("assignment_mail_report_id");
		$reportID = intval($reportID);

		if (empty($reportID)) {
			return "success";
		}
		
		$mail = $this->assignmentView->getMail($reportID);
		if ($mail === false) {
			return "error";
		}
		
		$this->mailMain->setSubject($mail["mail_subject"]);
		$this->mailMain->setBody($mail["mail_body"]);
		
		$tags["X-ASSIGNMENT_NAME"] = htmlspecialchars($mail["assignment_name"]);
		$tags["X-BODY"] = $mail["report_body"];
		$tags["X-USER"] = htmlspecialchars($mail["insert_user_name"]);
		$tags["X-TO_DATE"] = $mail["insert_time"];
		$tags["X-URL"] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=assignment_view_main_init".
							"&submit_id=". $mail["submit_id"].
							"&block_id=". $this->block_id.
							"#". $this->session->getParameter(array("assignment","id",$this->block_id));
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers($this->room_id, $mail["grade_authority"]);
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("assignment_mail_report_id");
		
		return "success";
    }
}
?>
