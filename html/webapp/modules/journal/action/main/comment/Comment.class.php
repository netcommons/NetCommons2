<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメント追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Comment extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $post_id = null;
    var $comment_id = null;
    var $content = null;

    // バリデートによりセット
	var $journal_obj = null;
	var $post = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
 	var $request = null;
 	var $session = null;
 	var $journalView = null;
	var $journalAction = null;

    // 値をセットするため
    var $comment_flag = null;

    /**
     * コメント追加
     *
     * @access  public
     */
    function execute()
    {
    	$_auth_id = $this->session->getParameter("_auth_id");
    	if($_auth_id < _AUTH_CHIEF && $this->journal_obj['comment_agree_flag'] == _ON) {
			$agree_flag = JOURNAL_STATUS_WAIT_AGREE_VALUE;
		}else {
			$agree_flag = JOURNAL_STATUS_AGREE_VALUE;
		}
		
    	if(empty($this->comment_id)) {
	    	$params = array(
				"journal_id" => $this->journal_obj['journal_id'],
				"root_id" => $this->post_id,
				"parent_id" => $this->post_id,
				"title" => "Re:".$this->post['title'],
				"icon_name" => "",
				"content" => $this->content,
	    		"agree_flag" => $agree_flag
			);
			$this->comment_id = $this->db->insertExecute("journal_post", $params, true, "post_id");
			if($this->comment_id === false) {
				return 'error';
			}
    	}else {
	    	$comment_before_update = $this->db->selectExecute("journal_post", array("post_id" => $this->comment_id));
			if (empty($comment_before_update)) {
				return 'error';
			}
    		$params = array(
				"content" => $this->content,
    			"agree_flag" => $agree_flag
			);
    		$result = $this->db->updateExecute("journal_post", $params,  array("post_id"=>$this->comment_id), true);
    		if($result === false) {
				return 'error';
			}
			
    		if($comment_before_update[0]['agree_flag'] == JOURNAL_STATUS_WAIT_AGREE_VALUE && 
    			$agree_flag == JOURNAL_STATUS_AGREE_VALUE &&
    			$this->journal_obj['comment_agree_mail_flag'] == _ON) {
				$this->session->setParameter("journal_confirm_mail_post_id", $this->comment_id);
			}
    	}
    	// 承認メール送信データ登録
    	if($this->journal_obj['comment_agree_flag'] == _ON && $agree_flag == JOURNAL_STATUS_WAIT_AGREE_VALUE) {
			$this->session->setParameter("journal_mail_post_id", array("post_id" => $this->comment_id, "agree_flag" => JOURNAL_STATUS_WAIT_AGREE_VALUE));
    	}
		//--新着情報関連 Start--
    	$result = $this->journalAction->setCommentWhatsnew($this->comment_id);
		if($result === false) {
			return 'error';
		}
		//--新着情報関連 End--
    	$this->comment_flag = _ON;
    	$this->request->setParameter("comment_flag", $this->comment_flag);
        return 'success';
    }
}
?>
