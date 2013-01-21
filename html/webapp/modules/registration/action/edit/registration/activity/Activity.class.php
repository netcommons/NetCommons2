<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 動作フラグ更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Action_Edit_Registration_Activity extends Action
{
    // 使用コンポーネントを受け取るため
    var $registrationAction = null;
	
    /**
     * 動作フラグ更新アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->registrationAction->setActivity()) {
        	return 'error';
        }

		return 'success';
    }
}
?>
