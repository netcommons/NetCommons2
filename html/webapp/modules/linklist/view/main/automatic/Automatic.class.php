<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * タイトル、詳細の自動取得アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_View_Main_Automatic extends Action
{
    // リクエストパラメータを受け取るため
    var $url = null;

	// コンポーネントを受け取るため
	var $requestMain = null;

	// validatorから受け取るため
    var $html = null;

    // 値をセットするため
	var $title = null;
	var $description = null;

    /**
     * タイトル、詳細の自動取得アクション
     *
     * @access  public
     */
    function execute()
    {
		$pattern = "/<title>(.*)<\/title>/i";
		if (preg_match($pattern, $this->html, $matches)) {
			$this->title = mb_convert_encoding($matches[1], "utf-8", "auto");
		}

		$pattern = "/<meta[^\"'<>\]]*name=(['\"]?)description\\1[^\"'<>\]]*content=(['\"]?)([^\"'<>\]]*)\\2[^\"'<>\]]*>/i";
		if (preg_match($pattern, $this->html, $matches)) {
			$this->description = mb_convert_encoding($matches[3], "utf-8", "auto");
		}

    	return "success";
    }
}
?>
