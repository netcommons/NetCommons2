<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Action_Edit_Item_Entry extends Action
{
    // 使用コンポーネントを受け取るため
    var $registrationAction = null;

    /**
     * 項目登録アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->registrationAction->setItem()) {
        	return "error";
        }
		
		return "success";
    }
}
?>