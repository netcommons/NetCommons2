<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク順序変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Action_Main_Link_Sequence extends Action
{
    // 使用コンポーネントを受け取るため
    var $linklistAction = null;

    /**
     * リンク順序変更アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->linklistAction->updateLinkSequence()) {
			return "error";
		}
		
		return "success";
    }
}
?>