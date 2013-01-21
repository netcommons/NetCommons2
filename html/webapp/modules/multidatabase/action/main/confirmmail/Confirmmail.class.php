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
class Multidatabase_Action_Main_Confirmmail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
 	var $mailMain = null;
 	var $mdbView = null;
 	var $session = null;
 	var $usersView = null;

	// validatorから受け取るため
	var $mail = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		$content_id = $this->session->getParameter("multidatabase_confirm_mail_content_id");
		$content_id = intval($content_id);

		if (empty($content_id)) {
			return "success";
		}
		$params = array(
			"content_id" => $content_id
		);
		$contents = $this->db->selectExecute("multidatabase_content", $params);
		if($contents == false || !isset($contents[0])) {
			return 'error';
		}
		
		$multidatabase_id = $contents[0]['multidatabase_id'];
		
		$mail = $this->mdbView->getMultidatabase($multidatabase_id);
		if ($mail === false || empty($mail)) {
			return 'error';
		}

		$this->mailMain->setSubject($mail[0]['agree_mail_subject']);
		$this->mailMain->setBody($mail[0]['agree_mail_body']);
		
		$tags['X-URL'] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=multidatabase_view_main_detail".
							"&content_id=". $content_id.
							"&multidatabase_id=". $multidatabase_id.
							"&block_id=". $this->block_id.
							"#". $this->session->getParameter("_id");
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers(null, null, null, array("{users}.user_id" => $contents[0]['insert_user_id']));
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("multidatabase_confirm_mail_content_id");
		
		return 'success';
    }
}
?>
