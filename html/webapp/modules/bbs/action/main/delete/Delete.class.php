<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Main_Delete extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $post_id = null;
	
    // 使用コンポーネントを受け取るため
    var $bbsAction = null;
    var $bbsView = null;

    /**
     * 記事削除アクション
     *
     * @access  public
     */
    function execute()
    {
 		$topicID = $this->bbsView->getTopicID($this->post_id);

        if (!$this->bbsAction->deletePost($this->post_id)) {
	        return "error";
        }

		if ($topicID != $this->post_id) {
			$result = $this->bbsAction->updateChildNum($topicID);
        } else {
        	$result = $this->bbsAction->deleteTopic($topicID);
        }
    	if ($result === false) {
			return "error";
		}

    	return "success";
    }
}
?>
