<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 投票機能アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Vote extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $post_id = null;

    // バリデートによりセット
	var $journal_obj = null;
	var $post = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $journalView = null;
    var $session = null;

    // 値をセットするため

    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
		$user_id = $this->session->getParameter("_user_id");
		if (empty($user_id)) {
			$votes = $this->session->getParameter("journal_votes");
			$votes[] = $this->post_id;
			$this->session->setParameter("journal_votes", $votes);
			$user_id = -1;
		}

    	$vote = $this->post['vote'];
    	if (!empty($vote)) {
			$vote_update = $vote."|".$user_id;
		}else{
			$vote_update = $user_id;
		}

		$params = array(
			"vote" => $vote_update
		);
		$result = $this->db->updateExecute("journal_post", $params,  array("post_id"=>$this->post_id), true);
		if($result === false) {
			return 'error';
		}

        return 'success';
    }
}
?>
