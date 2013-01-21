<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RSSメイン画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Rss_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $rssView = null;
	var $rssAction = null;
	var $rssParse = null;
	var $requestMain = null;
	var $session = null;
	var $mobileView = null;

    // 値をセットするため
    var $rss = false;
	var $block_num = null;

    /**
     * RSSメイン画面表示
     *
     * @access  public
     */
    function execute()
    {
    	$this->rss = $this->rssView->getRss();
        if (empty($this->rss)) {
        	return "error";
        }

		// キャッシュ時間を過ぎていた場合再取得
		if ($this->rss["cache_time"] <= time() - $this->rss["update_time_sec"]) {
			$xml = $this->requestMain->getResponseHtml($this->rss["url"]);
	    	if (empty($xml)) {
	            return "error";
	        }

        	$this->rss["xml"] = $this->rssParse->parse($xml, $this->rss["encoding"]);
			if ($this->rss["xml"] == false) {
		    	return "error";
		    }
			$params = array("block_id" => $this->block_id,
								"xml" => $this->rss["xml"],
								"update_time_sec" => time());
	    	$result = $this->rssAction->update($params);
	    	if($result === false) {
	    		return "error";
	    	}
 		}

 		// 表示件数分のみ設定
		if (!empty($this->rss["xml"]["item"])
				&& $this->rss["visible_row"] != 0 ) {
			$this->rss["xml"]["item"] = array_slice($this->rss["xml"]["item"], 0, $this->rss["visible_row"]);
		}

		if( $this->session->getParameter( "_mobile_flag" ) == true ) {
			$this->block_num = $this->mobileView->getCountForBlockInPageWithBlock( $this->block_id );
		}

		return "success";
    }
}
?>
