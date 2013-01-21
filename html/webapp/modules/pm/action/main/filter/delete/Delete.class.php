<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フイルタ削除アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Action_Main_Filter_Delete extends Action
{
    // 使用コンポーネントを受け取るため
    var $pmAction = null;
	
    /**
     * フイルタ削除アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->pmAction->deleteFilter()) {
        	return "error";
        }
    	
    	return "success";
    }
}
?>