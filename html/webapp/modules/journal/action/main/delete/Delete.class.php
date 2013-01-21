<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事削除処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Delete extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $post_id = null;
    var $comment_id = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
 	var $session = null;
	var $whatsnewAction = null;
	var $request = null;

    // 値をセットするため
    var $comment_flag = null;
    var $location_action = null;

    /**
     * 記事削除処理
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
    	if (!empty($this->comment_id)) {
    		$post_id = $this->comment_id;
	    	if ($mobile_flag == _ON) {
				$this->comment_flag = _ON;
				$this->location_action = "journal_view_main_detail";
				$success = 'success';
	    	}else {
	    		$success = 'comment';
	    	}
    	} else {
    		$post_id = $this->post_id;
	    	if ($mobile_flag == _ON) {
				$this->comment_flag = _OFF;
				$this->location_action = "journal_view_main_init";
				$success = 'success';
	    	}else {
	    		$topid = $this->session->getParameter("_id");
	    		$this->request->setParameter('_redirect_url', BASE_URL.INDEX_FILE_NAME."?active_action=journal_view_main_init&block_id=$this->block_id&post_id=$this->post_id#$topid");
	    		$success = 'post';
	    	}
    	}

    	// 自記事削除
		$result = $this->db->deleteExecute("journal_post", array("post_id"=>$post_id));
		if($result === false) {
			return 'error';
		}

		// 子記事削除
		$result = $this->db->deleteExecute("journal_post", array("parent_id"=>$post_id));
		if($result === false) {
			return 'error';
		}

		//--新着情報関連 Start--
		if (empty($this->comment_id)) {
			$result = $this->whatsnewAction->delete($post_id);
			if($result === false) {
				return 'error';
			}
		} else {
			// コメント削除
			$count = $this->db->countExecute("journal_post", array("parent_id"=>$this->post_id));
			if ($count === false) {
				return 'error';
			}
			if($count == 0) {
				$result = $this->whatsnewAction->delete($this->post_id, _ON);
			} else {
				$whatsnew = array(
					"unique_id" => $this->post_id,
					"count_num" => $count,
					"child_flag" => _ON
				);
				$result = $this->whatsnewAction->auto($whatsnew);
				if($result === false) {
					return 'error';
				}
			}
		}
		//--新着情報関連 End--

		//--URL短縮形関連 Start--
		$container =& DIContainerFactory::getContainer();
		$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
		$result = $abbreviateurlAction->deleteUrl($post_id);
		if ($result === false) {
			return 'error';
		}
		//--URL短縮形関連 End--

        return $success;
    }
}
?>
