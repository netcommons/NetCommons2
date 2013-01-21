<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Bbs_Action_Edit_Style extends Action
{
    // 使用コンポーネントを受け取るため
    var $bbsAction = null;

    /**
     * 表示方法変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->bbsAction->setBlock()) {
        	return "error";
        }

		return "success";
    }
}
?>
