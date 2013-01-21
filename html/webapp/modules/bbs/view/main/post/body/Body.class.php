<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 本文画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Main_Post_Body extends Action
{
    // パラメータを受け取るため
    var $html_flag = null;

    // validatorから受け取るため
    var $bbs = null;
    var $post = null;

	// 使用コンポーネントを受け取るため
	var $abbreviateurlView = null; //--URL短縮形関連
	var $mobileView = null;

	// 値をセットするため
	var $short_url = ""; //--URL短縮形関連

    /**
     * 本文画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$this->html_flag = $this->mobileView->getTextHtmlMode( $this->html_flag );
		//--URL短縮形関連
		$this->short_url = $this->abbreviateurlView->getAbbreviateUrl($this->post["post_id"]);
        return "success";
    }
}
?>
