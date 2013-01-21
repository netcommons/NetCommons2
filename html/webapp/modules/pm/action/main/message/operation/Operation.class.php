<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メッセージ操作アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Action_Main_Message_Operation extends Action
{
    // 使用コンポーネントを受け取るため
	var $session = null;
	
	var $uploadsAction = null;
	var $pmAction = null;
	   
    /**
     * メッセージ操作アクション
     *
     * @access  public
     */
    function execute()
    {
    	if (!$this->pmAction->operation()) {
        	return "error";
        }
		
		$pm_delete_upload_ids = $this->session->getParameter("pm_delete_upload_ids");
		if(is_array($pm_delete_upload_ids) && sizeof($pm_delete_upload_ids) > 0){
			foreach($pm_delete_upload_ids as $pm_delete_upload_id){
				$this->uploadsAction->delUploadsById($pm_delete_upload_id);
			}
			
			$this->session->removeParameter("pm_delete_upload_ids");
		}
		
		return "success";
    }
}
?>
