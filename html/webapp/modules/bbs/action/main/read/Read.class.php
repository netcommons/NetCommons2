<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 既読アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Main_Read extends Action
{
	// リクエストパラメータを受け取るため
    var $block_id = null;
    var $bbs_id = null;
    var $post_id = null;

    // 使用コンポーネントを受け取るため
    var $bbsAction = null;

    /**
     * 既読アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->bbsAction->read($this->post_id)) {
			return "error";
		}
		
        return "success";
    }
}
?>
