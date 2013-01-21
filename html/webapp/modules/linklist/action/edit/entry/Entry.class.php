<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Action_Edit_Entry extends Action
{
	// リクエストパラメータを受け取るため
	var $linklist_id = null;

    // 使用コンポーネントを受け取るため
    var $linklistAction = null;
	
	/**
     * リンクリスト登録アクション
     *
     * @access  public
     */
    function execute()
	{
		if (!$this->linklistAction->setLinklist()) {
        	return "error";
        }

		if (empty($this->linklist_id)) {
			return "create";
		}
		
		return "modify";
	}
}
?>