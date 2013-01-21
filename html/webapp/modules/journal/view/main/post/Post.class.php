<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Main_Post extends Action
{
    // リクエストパラメータを受け取るため
    var $post_id = null;
    
    // バリデートによりセット
	var $journal_obj = null;

    // 使用コンポーネントを受け取るため
    var $db = null;
    var $journalView = null;
	var $session = null;

    // 値をセットするため
    var $categories = null;
    var $edit_flag = false;
    var $post = null;
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
    	$this->categories = $this->journalView->getCatByJournalId($this->journal_obj['journal_id']);
    	if($this->categories === false) {
    		return 'error';
    	}
    	
    	if(!empty($this->post_id)) {
    		$this->edit_flag = _ON;
    		$result = $this->journalView->getPostDetail($this->post_id);
    		if($result === false || !isset($result[0])) {
    			return 'error';
    		}
    		$this->post = $result[0];

			$mobile_flag = $this->session->getParameter('_mobile_flag');
			if ($mobile_flag == _ON) {
				$this->session->removeParameter('journal_current_mobile_image');
				if (preg_match('/<img[^>]+class\s*=\s*["\'][^>]*' . MOBILE_IMAGE . '[^>]+>/u', $this->post['content'], $match) > 0) {
					$this->post['mobile_image'] = $match[0];
					$this->session->setParameter('journal_current_mobile_image', $match[0]);
				}
			}
    	}else {
    		$this->edit_flag = _OFF;
    		$this->post = array(
    			"journal_id" => $this->journal_obj['journal_id'],
    			"journal_date" => timezone_date(null, true, "YmdHis"),
    			"category_id" => 0,
    			"title" => "",
    			"content" => ""
    		);
    	}

        return 'success';
    }
}
?>
