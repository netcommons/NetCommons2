<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カレント登録フォーム更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Action_Edit_Registration_Current extends Action
{
    // 使用コンポーネントを受け取るため
    var $registrationAction = null;

    /**
     * カレント登録フォーム更新アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!$this->registrationAction->setBlock()) {
        	return "error";
        }

		return "success";
    }
}
?>
