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
class Bbs_Action_Main_Mail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;
 	
    // 使用コンポーネントを受け取るため
 	var $mailMain = null;
 	var $bbsView = null;
 	var $session = null;
 	var $usersView = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		$postID = $this->session->getParameter("bbs_mail_post_id");
		$postID = intval($postID);

		if (empty($postID)) {
			return "success";
		}
		
		$mail = $this->bbsView->getMail($postID);
		if ($mail === false) {
			return "error";
		}
		
		$this->mailMain->setSubject($mail["mail_subject"]);
		$this->mailMain->setBody($mail["mail_body"]);
		
		$tags["X-BBS_NAME"] = htmlspecialchars($mail["bbs_name"]);
		$tags["X-SUBJECT"] = htmlspecialchars($mail["subject"]);
		$tags["X-BODY"] = $mail["body"];
		$tags["X-USER"] = htmlspecialchars($mail["insert_user_name"]);
		$tags["X-TO_DATE"] = $mail["insert_time"];
		$tags["X-URL"] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=bbs_view_main_post".
							"&post_id=". $postID.
							"&block_id=". $this->block_id.
							"#". $this->session->getParameter("_id");
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers($this->room_id, $mail["mail_authority"]);
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("bbs_mail_post_id");
		
		return "success";
    }
}
?>
