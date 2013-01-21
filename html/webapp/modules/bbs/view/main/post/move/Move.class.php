<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 記事移動アイコン表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_View_Main_Post_Move extends Action
{
    // リクエストパラメータを受け取るため
    var $post_id = null;
 
    // 使用コンポーネントを受け取るため
    var $bbsView = null;

    // validatorから受け取るため
	var $bbs = null;

    // 値をセットするため
    var $parent_id = null;
    var $child_id = null;
    var $older_id = null;
    var $newer_id = null;

    /**
     * 記事移動アイコン表示アクション
     *
     * @access  public
     */
    function execute()
    {
		$moveArray =  $this->bbsView->getMoveData($this->post_id);
		if ($moveArray === false) {
			return "error";
		}
		
		$this->parent_id = $moveArray[0];
		$this->child_id = $moveArray[1];
		$this->older_id = $moveArray[2];
		$this->newer_id = $moveArray[3];
		
		return "success";
    }
}
?>
