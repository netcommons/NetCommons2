<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 承認機能アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Confirm extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $post_id = null;
    
    // バリデートによりセット
	var $journal_obj = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $session = null;
	var $journalAction = null;

    // 値をセットするため
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {	
    	$post_before_update = $this->db->selectExecute("journal_post", array("post_id" => $this->post_id));
		if (empty($post_before_update)) {
			return 'error';
		}
		
		$params = array(
			"agree_flag" => JOURNAL_STATUS_AGREE_VALUE
		);
		$result = $this->db->updateExecute("journal_post", $params,  array("post_id" => $this->post_id), true);
		if($result === false) {
			return 'error';
		}
		$this->session->setParameter("journal_mail_post_id", null);
		$this->session->setParameter("journal_confirm_mail_post_id", null);
		if($this->journal_obj['mail_flag'] == _ON && empty($post_before_update[0]['parent_id'])) {
			$this->session->setParameter("journal_mail_post_id", array("post_id" => $this->post_id, "agree_flag" => JOURNAL_STATUS_AGREE_VALUE));
		}
		
		if((empty($post_before_update[0]['parent_id']) && $this->journal_obj['agree_mail_flag'] == _ON) 
				|| (!empty($post_before_update[0]['parent_id']) && $this->journal_obj['comment_agree_mail_flag'] == _ON && (empty($post_before_update[0]['direction_flag']) || (!empty($post_before_update[0]['direction_flag']) && strpos($post_before_update[0]['tb_url'], BASE_URL) !== false)))) {
			$this->session->setParameter("journal_confirm_mail_post_id", $this->post_id);
		}
		
		//トラックバックの処理
		$this->journalAction->setTrackBack($this->journal_obj, $this->post_id, $post_before_update[0]);
		
		//--新着情報関連 Start--
		if(empty($post_before_update[0]['parent_id'])) {
			$result = $this->journalAction->setWhatsnew($this->post_id);
		}else {
			$result = $this->journalAction->setCommentWhatsnew($this->post_id);
		}
		if($result === false) {
			return 'error';
		}
		//--新着情報関連 End--
		
		// --- 投稿回数更新 ---
		
		$before_post = isset($post_before_update[0]) ? $post_before_update[0] : null;
		$result = $this->journalAction->setMonthlynumber(_ON, JOURNAL_POST_STATUS_REREASED_VALUE, $before_post['status'], $before_post);
		if ($result === false) {
			return 'error';
		}

        return 'success';
    }
}
?>
