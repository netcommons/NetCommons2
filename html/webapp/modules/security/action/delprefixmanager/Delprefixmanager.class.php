<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DBテーブルの削除
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
 require_once MAPLE_DIR.'/nccore/Action.class.php';

class Security_Action_Delprefixmanager extends Action
{
	// リクエストパラメータを受け取るため
	var $db_prefix_new = null;
	var $db_prefix_old = null;
	var $SearchTables = null;
			
	//使用コンポーネント
	var $actionChain = null;
	var $db = null;
	
	// 値をセットするため
	var $errorList = null;
	var $dbtables = null;
	
    /**
     * DBテーブルのコピー／削除
     *
     * @access  public
     */
    function execute()
    { 
        $this->errorList =& $this->actionChain->getCurErrorList();
        
        // DBテーブルを削除
        if (!empty($this->db_prefix_old)) {
			$this->dbtables = $this->SearchTables->SearchTables();
        
			$delete_dbtables = "";
	       	foreach($this->dbtables as $dbtable) {
	       		if (!strcmp($dbtable['prefix'], $this->db_prefix_old)) {
	       			$delete_dbtables = $dbtable;
	       			break;
	       		}
	       	}
	       	if (empty($delete_dbtables)) {
				$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_DELETETABLE, "table not found"));
	       		return 'error';
	       	}
	
	       	foreach($delete_dbtables['tables'] as $old_dbtable) {
	   	    	$sql_cmd = "drop table " . $old_dbtable['Name'];
	   	    	$sqlcmd_return = $this->db->execute($sql_cmd);
	   	    	if ($sqlcmd_return == false) {
					$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_DELETETABLE, "delete table"));
	   	    		return 'error';
				}
	       	}
		}

        return 'success';
    }
}
?>
