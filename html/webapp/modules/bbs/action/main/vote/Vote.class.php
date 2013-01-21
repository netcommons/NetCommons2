<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 投票アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Main_Vote extends Action
{
	// パラメータを受け取るため
    var $block_id = null;
    var $bbs_id = null;
    var $post_id = null;

    // 使用コンポーネントを受け取るため
    var $bbsAction = null;

    /**
     * 投票アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->bbsAction->vote()) {
			return "error";
		}
		
        return "success";
    }
}
?>
