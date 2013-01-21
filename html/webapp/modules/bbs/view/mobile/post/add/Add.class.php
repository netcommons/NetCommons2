<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事一覧画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Mobile_Post_Add extends Action
{
    // validatorから受け取るため
	var $bbs = null;

    // 値をセットするため
	var $post = null;
	
    /**
     * 記事一覧画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->post = array(
			"post_id" => 0,
			"bbs_id" => $this->bbs["bbs_id"],
			"subject" => "",
			"body" => ""
		);
		return "success";
    }
}
?>
