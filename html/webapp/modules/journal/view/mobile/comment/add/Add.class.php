<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コメントの追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Mobile_Comment_Add extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $post_id = null;
	var $room_id = null;

	// コンポーネントを使用するため
	var $journalView = null;
	var $db = null;
	var $session = null;

    // Validateから値を受け取る

	// 値をセットするため
    var $journal_name = null;
    var $journal_id = null;
    var $post = null;
    var $category = null;
    var $journal_obj = null;
	var $comment = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	$this->journal_name = $this->session->getParameter("journal_name".$this->block_id);
    	$this->journal_id = $this->session->getParameter("journal_id".$this->block_id);
    	$journal = $this->db->selectExecute("journal", array("journal_id"=>$this->journal_id));
        if ($journal === false || empty($journal[0])) {
        	return 'error';
        }
        $this->journal_obj = $journal[0];
    	$post = $this->journalView->getPostDetail($this->post_id);
    	if ($post === false || !isset($post[0])) {
    		return "error";
    	}
		$this->post = $post[0];		
		$this->category = $this->journalView->getCatByPostId($this->post_id);
		$this->comment = array(
			"post_id" => 0,
			"journal_id" => $this->journal_id,
			"category_id" => 0,
			"root_id" => $this->post_id,
			"parent_id" => $this->post_id,
			"content" => ""
		);
		return 'success';
    }
}
?>
