<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事詳細画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Main_Post extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $post_id = null;
	var $html_flag = null;

    // 使用コンポーネントを受け取るため
    var $bbsView = null;
    var $request = null; //携帯で使用
    var $session = null; //携帯で使用
    var $token = null;   //携帯で使用
	var $mobileView = null;

	// validatorから受け取るため
	var $bbs = null;

	// 値をセットするため
    var $viewableChildExists = null;

    /**
     * 記事詳細画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$topicID =  $this->bbsView->getTopicID($this->post_id);
		if ($topicID === false) {
			return "error";
		}

		$this->viewableChildExists = $this->bbsView->viewableChildExists($topicID);

	    if ($this->session->getParameter("_mobile_flag") == _ON) {
	    	$this->request->setParameter("_token", $this->token->getValue());
	    }
		$this->html_flag = $this->mobileView->getTextHtmlMode($this->html_flag);

		return "success";
    }
}
?>
