<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システムConfig登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
 require_once MAPLE_DIR.'/nccore/Action.class.php';

class Security_Action_Displaylog extends Action
{
	// リクエストパラメータを受け取るため
	var $displaylog_lids = null;

	//使用コンポーネント
	var $actionChain = null;
	var $db = null;
	
	// 値をセットするため
	var $errorList = null;

	/**
     * セキュリティ・ログの削除
     *
     * @access  public
     */
    function execute()
    { 
        $this->errorList =& $this->actionChain->getCurErrorList();

        if (count($this->displaylog_lids) != 0) {
	        foreach($this->displaylog_lids as $displaylog_deletelid) {
				$deleteExecute_return = $this->db->deleteExecute("security_log", array("lid" => $displaylog_deletelid));
	        	if ($deleteExecute_return == false) {
	        	}
	        }
        }
                
        return 'success';
    }
}
?>
