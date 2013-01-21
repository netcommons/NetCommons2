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
class Linklist_Action_Main_Count extends Action
{
    // 使用コンポーネントを受け取るため
    var $linklistAction = null;
    var $linklistView = null;

	// validatorから受け取るため
    var $linklist = null;
    
    // 値をセットするため
    var $link = null;

    /**
     * リンク登録アクションクラス
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->linklistAction->incrementViewCount()) {
        	return "error";
        }
    	
    	$this->link = $this->linklistView->getLinkViewCount();
    	if (empty($this->link)) {
    		return "error";
    	}
    	
    	return "success";
    }
}
?>
