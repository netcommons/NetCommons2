<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 投稿アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Main_Post extends Action
{
	// パラメータを受け取るため
    var $block_id = null;
    var $bbs_id = null;

    // 使用コンポーネントを受け取るため
    var $bbsAction = null;

    /**
     * 投稿アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->bbsAction->setPost()) {
	        return "error";
        }

    	return "success";
    }
}
?>
