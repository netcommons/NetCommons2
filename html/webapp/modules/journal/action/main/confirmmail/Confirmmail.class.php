<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 承認メール送信アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Confirmmail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
 	var $mailMain = null;
 	var $journalView = null;
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
		$post_id = $this->session->getParameter("journal_confirm_mail_post_id");
		$post_id = intval($post_id);

		if (empty($post_id)) {
			return 'success';
		}
		
		$mail = $this->journalView->getMail($post_id);
		if ($mail === false) {
			return 'error';
		}

		if(empty($mail['parent_id'])) {
			$this->mailMain->setSubject($mail['agree_mail_subject']);
			$this->mailMain->setBody($mail['agree_mail_body']);
			$comment_url = "&comment_flag="._OFF;
			$comment_href_flag = _OFF;
		}else {
			$post_id = $mail['parent_id'];
			$this->mailMain->setSubject($mail['comment_agree_mail_subject']);
			$this->mailMain->setBody($mail['comment_agree_mail_body']);
			empty($mail['direction_flag']) ? $comment_url = "&comment_flag="._ON:$comment_url = "&trackback_flag="._ON;
			$comment_href_flag = _ON;
		}
		
		$tags['X-URL'] = BASE_URL. INDEX_FILE_NAME.
							"?action=". DEFAULT_ACTION .
							"&active_action=journal_view_main_detail".
							"&post_id=". $post_id.
							"&block_id=". $this->block_id.
							$comment_url.
							"&comment_href_flag=". $comment_href_flag.
							"#". $this->session->getParameter("_id");
		$this->mailMain->assign($tags);
		
		$users = $this->usersView->getSendMailUsers(null, null, null, array("{users}.user_id" => $mail['insert_user_id']));
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("journal_confirm_mail_post_id");
		
		return 'success';
    }
}
?>
