<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ詳細画面表示アクションクラス
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Multidatabase_View_Main_Detail extends Action
{
	// リクエストパラメータを受け取るため
	var $multidatabase_id = null;
	var $content_id = null;
	var $comment_id = null;
	var $clear_comment = null;
	var $block_id = null;
	var $html_flag = null;
	var $search = null;

	// バリデートによりセット
	var $mdb_obj = null;
	var $detail = null;

	// 使用コンポーネントを受け取るため
	var $abbreviateurlView = null; //--URL短縮形関連
	var $mobileView = null;

	// 値をセットするため
	var $short_url = ""; //--URL短縮形関連

	/**
	 * コンテンツ詳細画面表示アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		//--URL短縮形関連
		$this->short_url = $this->abbreviateurlView->getAbbreviateUrl($this->content_id);
		$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		$this->search = intval($this->search);
		return 'success';
	}
}
?>