<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RSS登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Rss_Action_Edit_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $site_name = null;
    var $url = null;
    var $encoding = null;
    var $cache_time = null;
    var $visible_row = null;
    var $imagine = null;

    // validatorから受け取るため
    var $xml = null;
    
    // 使用コンポーネントを受け取るため
    var $rssView = null;
    var $rssAction = null;
    var $rssParse = null;
    var $request = null;

    /**
     * RSS登録アクション
     *
     * @access  public
     */
    function execute()
    {
		$xml = $this->rssParse->parse($this->xml, $this->encoding);
	    if ($xml == false) {
	    	return "error";
	    }
	    
		$params = array("block_id" => $this->block_id,
							"site_name" => $this->site_name,
							"url" => $this->url,
							"encoding" => $this->encoding,
							"cache_time" => intval($this->cache_time),
							"visible_row" => intval($this->visible_row),
							"imagine" => intval($this->imagine),
							"xml" => $xml,
							"update_time_sec" => time());
		
		if ($this->rssView->rssExists($this->block_id)) {
			$result = $this->rssAction->update($params);
		} else {
			$result = $this->rssAction->insert($params);
		}
		if ($result === false) {
			return "error";
		}
		
		// 初期化
		$this->request->removeParameters();

		return "success";
    }
}
?>