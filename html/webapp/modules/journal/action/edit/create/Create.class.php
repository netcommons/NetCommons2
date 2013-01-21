<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Edit_Create extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
	var $module_id = null;
	var $journal_id = null;

	// バリデートによりセット
	var $journal_obj = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $configView = null;
	var $request = null;

	// 値をセットするため

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"journal_name" => $this->request->getParameter("journal_name"),
			"post_authority" => intval($this->request->getParameter("post_authority")),
			"mail_flag" => intval($this->request->getParameter("mail_flag")),
			"vote_flag" => intval($this->request->getParameter("vote_flag")),
			"comment_flag" => intval($this->request->getParameter("comment_flag")),
    		"sns_flag" => intval($this->request->getParameter("sns_flag")),
			"trackback_transmit_flag" => intval($this->request->getParameter("trackback_transmit_flag")),
			"trackback_receive_flag" => intval($this->request->getParameter("trackback_receive_flag")),
			"transmit_blogname" => $this->request->getParameter("transmit_blogname"),
			"mail_authority" => intval($this->request->getParameter("mail_authority")),
			"mail_subject" => $this->request->getParameter("mail_subject"),
			"mail_body" => $this->request->getParameter("mail_body"),
			"new_period" => intval($this->request->getParameter("new_period")),
			"agree_flag" => intval($this->request->getParameter("agree_flag")),
			"agree_mail_flag" => intval($this->request->getParameter("agree_mail_flag")),
			"agree_mail_subject" => $this->request->getParameter("agree_mail_subject"),
			"agree_mail_body" => $this->request->getParameter("agree_mail_body"),
    		"comment_agree_flag" => intval($this->request->getParameter("comment_agree_flag")),
			"comment_agree_mail_flag" => intval($this->request->getParameter("comment_agree_mail_flag")),
			"comment_agree_mail_subject" => $this->request->getParameter("comment_agree_mail_subject"),
			"comment_agree_mail_body" => $this->request->getParameter("comment_agree_mail_body")
		);

		if(empty($this->journal_id)) {
			$params = array_merge($params, array("active_flag" => $this->journal_obj['active_flag']));
			$journal_id = $this->db->insertExecute("journal", $params, true, "journal_id");
			if ($journal_id === false) {
	    		return 'error';
	    	}
			$default_categoris_array = explode("|", JOURNAL_DEFAULT_CATEGORIES);
	    	$display_seq = 0;
	    	foreach(array_keys($default_categoris_array) as $i) {
	    		$display_seq++;
	    		$params = array(
					"journal_id" => $journal_id,
					"category_name" => $default_categoris_array[$i],
					"display_sequence" => $display_seq
				);
				$result = $this->db->insertExecute("journal_category", $params, true, "category_id");
	    	}
	    	$count = $this->db->countExecute("journal_block", array("block_id"=>$this->block_id));
	    	if($count === false) {
	    		return 'error';
	    	}

			if ($count == 0) {
				$params = array(
					"journal_id" => $journal_id,
					"visible_item" => JOURNAL_DEFAULT_VISIBLE_ITEM
				);
		    	$result = $this->db->insertExecute("journal_block", array_merge(array("block_id" => $this->block_id), $params), true);
			}else {
				$params = array(
					"journal_id" => $journal_id
				);
		    	$result = $this->db->updateExecute("journal_block", $params,  array("block_id"=>$this->block_id), true);
	    	}
	    	if ($result === false) {
	    		return 'error';
	    	}

	    	return 'style';
		}else {
			$where_params = array("journal_id" => $this->journal_id);
			$result = $this->db->updateExecute("journal", $params, $where_params);
			if ($result === false) {
	    		return 'error';
	    	}

	    	return 'list';
		}
    }
}
?>