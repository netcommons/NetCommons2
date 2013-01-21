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
class Journal_Action_Main_Mail extends Action
{
    // リクエストパラメータを受け取るため
 	var $room_id = null;
 	var $block_id = null;

    // 使用コンポーネントを受け取るため
 	var $mailMain = null;
 	var $journalView = null;
 	var $session = null;
 	var $usersView = null;
 	var $db = null;
 	var $request = null;
 	var $commonMain = null;

	// validatorから受け取るため
	var $mail = null;

    // 値をセットするため
	var $post_id = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
    	$post_mail = $this->session->getParameter("journal_mail_post_id");
		$post_id = intval($post_mail['post_id']);
		$this->post_id = $post_id;
		if (empty($post_id)) {
			return 'success';
		}

		$mail = $this->journalView->getMail($post_id);
		if ($mail === false) {
			return 'error';
		}

		$this->request->setParameter("room_id", $mail['room_id']);
		$this->room_id = $mail['room_id'];

		$this->mailMain->setSubject($mail['mail_subject']);
		$this->mailMain->setBody($mail['mail_body']);

		$tags['X-JOURNAL_NAME'] = htmlspecialchars($mail['journal_name']);
		$tags['X-CATEGORY_NAME'] = !empty($mail['category_name']) ? htmlspecialchars($mail['category_name']) : JOURNAL_NOCATEGORY;
		$tags['X-SUBJECT'] = htmlspecialchars($mail['title']);
		$tags['X-USER'] = htmlspecialchars($mail['insert_user_name']);
		if(empty($mail['parent_id'])) {
			$tags['X-TO_DATE'] = $mail['journal_date'];
			$tags['X-BODY'] = $mail['content'];
			$comment_url = "&comment_flag="._OFF;
			$comment_href_flag = _OFF;
		}else {
			$tags['X-TO_DATE'] = $mail['insert_time'];
			if(empty($this->block_id)) {
				$block = $this->db->selectExecute("journal_block", array("journal_id" => $mail['journal_id']));
				if($block === false || !isset($block[0])) {
					return 'error';
				}
				$this->block_id = $block[0]['block_id'];
				$this->commonMain->getTopId($this->block_id);
			}
			if(empty($mail['direction_flag'])) {
				$comment_url = "&comment_flag="._ON;
				$tags['X-BODY'] = nl2br(htmlspecialchars($mail['content']));
			}else {
				$comment_url = "&trackback_flag="._ON;
				$tags['X-BODY'] = $mail['content'];
			}
			$comment_href_flag = _ON;
			$post_id = $mail['parent_id'];
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

		if($post_mail['agree_flag'] == JOURNAL_STATUS_WAIT_AGREE_VALUE) {
			$users = $this->usersView->getSendMailUsers($this->room_id, _AUTH_CHIEF);
		}else if($post_mail['agree_flag'] == JOURNAL_STATUS_AGREE_VALUE) {
			$users = $this->usersView->getSendMailUsers($this->room_id, $mail['mail_authority']);
		}
		$this->mailMain->setToUsers($users);
		$this->mailMain->send();
		$this->session->removeParameter("journal_mail_post_id");

		return 'success';
    }
}
?>