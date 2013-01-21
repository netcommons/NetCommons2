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
class Registration_Action_Main_Mail extends Action
{
	// リクエストパラメータを受け取るため
	var $room_id = null;
	var $block_id = null;

	// 使用コンポーネントを受け取るため
	var $registrationView = null;
	var $mailMain = null;
	var $session = null;
	var $usersView = null;
	var $filterChain = null;

	/**
	 * メール送信アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$dataID = $this->session->getParameter("registration_mail_data_id");
		$dataID = intval($dataID);

		if (empty($dataID)) {
			return "success";
		}

		$mail = $this->registrationView->getMail($dataID);
		if ($mail === false) {
			return "error";
		}

		$this->mailMain->setSubject($mail["mail_subject"]);
		$this->mailMain->setBody($mail["mail_body"]);

		$tags["X-REGISTRATION_NAME"] = htmlspecialchars($mail["registration_name"]);
		$tags["X-DATA"] = $mail["data"];
		$tags["X-TO_DATE"] = $mail["insert_time"];
		$tags["X-URL"] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=registration_view_edit_registration_list".
							"&registration_id=". $mail["registration_id"].
							"&block_id=". $this->block_id.
							"#". $this->session->getParameter("_id");
		$this->mailMain->assign($tags);

		$users = array();
		if ($mail['regist_user_send']
			&& !empty($mail['regist_user_email'])) {
			$users[]['email'] = $mail['regist_user_email'];
			$this->mailMain->setToUsers($users);
			$this->mailMain->send();
		}

		$smartyAssign =& $this->filterChain->getFilterByName('SmartyAssign');
		$mailBodyToChief = $smartyAssign->getLang('registration_mail_body_to_chief');
		$this->mailMain->setBody($mail['mail_body'] . $mailBodyToChief);
		$url = BASE_URL. INDEX_FILE_NAME.
				"?action=". DEFAULT_ACTION .
				"&active_action=registration_view_edit_registration_list".
				"&registration_id=". $mail["registration_id"].
				"&block_id=". $this->block_id.
				"#". $this->session->getParameter("_id");
		$this->mailMain->assign('X-URL', $url);
		$users = array();
		if ($mail["chief_send"] ==  _ON) {
			$users = $this->usersView->getSendMailUsers($this->room_id, _AUTH_CHIEF);
		}
		$rcptToes = explode(REGISTRATION_RCPT_TO_SEPARATOR, $mail["rcpt_to"]);
		foreach ($rcptToes as $rcptTo) {
			$users[]["email"] = $rcptTo;
		}

		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("registration_mail_data_id");

		return "success";
	}
}
?>