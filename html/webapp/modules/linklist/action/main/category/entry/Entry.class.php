<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * カテゴリ登録アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Action_Main_Category_Entry extends Action
{
    // リクエストパラメータを受け取るため
	var $category_id = null;
    
    // 使用コンポーネントを受け取るため
    var $linklistAction = null;
	
    /**
     * カテゴリ登録アクションクラス
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->linklistAction->setCategory()) {
        	return "error";
        }
    	
    	if (empty($this->category_id)) {
			return "create";
		}
		
		return "modify";
    }
}
?>
