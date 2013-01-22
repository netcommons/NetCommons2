<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌詳細画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_View_Main_Detail extends Action
{
	// リクエストパラメータを受け取るため
	var $post_id = null;
	var $html_flag = null; //携帯で使用
	var $more_flag = null; //携帯で使用
	var $mobile_comment_flag = null; //携帯で使用
	var $comment_flag = null;
	var $trackback_flag = null;
	var $comment_href_flag = null;

	var $active_action = null;
	var $active_center = null;

	// バリデートによりセット
	var $journal_obj = null;

	// 使用コンポーネントを受け取るため
	var $journalView = null;
	var $session = null;
	var $db = null;
	var $mobileView = null;

	// 値をセットするため
	var $post = null;
	var $category = null;
	var $comments = null;
	var $trackback_url = null;
	var $trackbacks = null;

	/**
	 * 日誌詳細画面表示
	 *
	 * @access  public
	 */
	function execute()
	{
		$mobile_flag = $this->session->getParameter("_mobile_flag");

		$this->trackback_url = BASE_URL. INDEX_FILE_NAME.
								"?action=".JOURNAL_DEFAULT_TRACKBACK_URL.
								"&post_id=". $this->post_id;

		$this->category = $this->journalView->getCatByPostId($this->post_id);

		if ($mobile_flag == _OFF || ($this->journal_obj['comment_flag'] == _ON && $this->mobile_comment_flag == _ON)) {
			$this->comments = $this->journalView->getChildDetail($this->post_id);
			if($this->comments === false) {
				return 'error';
			}
		}

		if($mobile_flag == _OFF || $this->trackback_flag == _ON) {
			$this->trackbacks = $this->journalView->getChildDetail($this->post_id, JOURNAL_TRACKBACK_RECEIVE);
			if($this->trackbacks === false) {
				return 'error';
			}
		}
		// タイトル設定
		if(isset($this->active_action) || isset($this->active_center)) {
			$this->session->setParameter("_page_title", $this->post['title']);
		}

		if ($mobile_flag == _ON ) {
			if( $this->mobile_comment_flag == _ON) {
				return 'comment';
			}
			else {
				$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
				return 'success';
			}
		} else {
			return 'success';
		}
	}
}
?>