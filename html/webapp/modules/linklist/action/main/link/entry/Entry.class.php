<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Action_Main_Link_Entry extends Action
{
    // リクエストパラメータを受け取るため
	var $link_id = null;

    // 使用コンポーネントを受け取るため
    var $linklistAction = null;

    /**
     * リンク登録アクションクラス
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->linklistAction->setLink()) {
        	return "error";
        }
    	
    	if (empty($this->link_id)) {
			return "create";
		}
		
		return "modify";
    }
}
?>
