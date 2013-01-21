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
class Quiz_Action_Main_Mail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;
 	var $quiz_id = null;

    // 使用コンポーネントを受け取るため
 	var $mailMain = null;
 	var $quizView = null;
 	var $session = null;
 	var $usersView = null;

	// 値をセットするため
	var $summary_id = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		$summaryID = $this->session->getParameter("quiz_mail_summary_id");
		$summaryID = intval($summaryID);
		$this->summary_id = $summaryID;

		if (empty($summaryID)) {
			return "success";
		}
		
		$mail = $this->quizView->getMail($summaryID);
		if ($mail === false) {
			return "error";
		}
		
		$this->mailMain->setSubject($mail["mail_subject"]);
		$this->mailMain->setBody($mail["mail_body"]);
		
		$tags["X-QUIZ_NAME"] = htmlspecialchars($mail["quiz_name"]);
		$tags["X-TO_DATE"] = $mail["answer_time"];
		$tags["X-URL"] = BASE_URL. 
							"?action=". DEFAULT_ACTION .
							"&active_action=quiz_view_edit_quiz_list".
							"&quiz_id=". $mail["quiz_id"].
							"&summary_id=". $summaryID.
							"&block_id=". $this->block_id;
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers($this->room_id, _AUTH_CHIEF);
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("quiz_mail_summary_id");
		
		return "success";
    }
}
?>
