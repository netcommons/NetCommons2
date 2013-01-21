<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 本文とその本文が含まれるスレッドの画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Mobile_Post_Main extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $post_id = null;
	var $html_flag = null;

	// validatorから受け取るため
	var $bbs = null;
	var $post = null;
	var $expand = null;

	// 使用コンポーネントを受け取るため
	var $bbsView = null;
	var $abbreviateurlView = null; //--URL短縮形関連
	var $mobileView = null;

	// 値をセットするため
	var $topics = null;
	var $posts = null;
	var $short_url = ""; //--URL短縮形関連

	/**
	 * 本文画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		$topicID =  $this->bbsView->getTopicID($this->post_id);
		if ($topicID === false) {
			return 'error';
		}

		$this->html_flag = $this->mobileView->getTextHtmlMode($this->html_flag);
		//--URL短縮形関連
		$this->short_url = $this->abbreviateurlView->getAbbreviateUrl($this->post['post_id']);

		// スレッド表示用記事の設定
		if ($this->expand == BBS_EXPAND_THREAD_VALUE) {
			$this->topics = $this->bbsView->getThread($topicID);
			if ($this->topics === false) {
				return 'error';
			}
		}

		// フラット表示用記事の設定
		if ($this->expand == BBS_EXPAND_FLAT_VALUE) {
			$this->posts = $this->bbsView->getFlat($topicID);
			if ($this->posts === false) {
				return 'error';
			}
		}
		return 'success';
	}
}
?>
