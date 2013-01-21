<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 詳細画面での記事一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Main_Post_List extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $post_id = null;

    // 使用コンポーネントを受け取るため
    var $bbsView = null;
	var $filterChain = null;
	var $session = null;

    // validatorから受け取るため
	var $bbs = null;
	var $expand = null;

    // 値をセットするため
    var $topics = null;
    var $posts = null;

    /**
     * 詳細画面での記事一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$mobile_flag = $this->session->getParameter("_mobile_flag");
		$topicID =  $this->bbsView->getTopicID($this->post_id);
		if ($topicID === false) {
			return "error";
		}
		
		// スレッド表示用記事の設定
		if ($this->expand == BBS_EXPAND_THREAD_VALUE) {
			$this->topics = $this->bbsView->getThread($topicID);
			if ($this->topics === false) {
				return "error";
			}
		}

		// フラット表示用記事の設定
		if ($this->expand == BBS_EXPAND_FLAT_VALUE) {
			$this->posts = $this->bbsView->getFlat($topicID);
			if ($this->posts === false) {
				return "error";
			}
		}
		
		if ($mobile_flag == _ON) {
			$view =& $this->filterChain->getFilterByName("View");
			$view->setAttribute("define:theme", _ON);
		}
		return "success";
    }
}
?>
