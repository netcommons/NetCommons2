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

class Security_Action_Prefixmanager extends Action
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
        
        if (!empty($this->db_prefix_new) && !empty($this->db_prefix_old) && strcmp($this->db_prefix_new,  $this->db_prefix_old)) {
			$this->dbtables = $this->SearchTables->SearchTables();
			if ($this->dbtables == false) {
				$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_COPYTABLE, "search table"));
	       		return 'error';
	       	}
			
			$copy_dbtables = "";
	       	foreach($this->dbtables as $dbtable) {
	       		if (!strcmp($dbtable['prefix'], $this->db_prefix_old)) {
	       			$copy_dbtables = $dbtable;
	       			break;
	       		}
	       	}
	       	if (empty($copy_dbtables)) {
				$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_COPYTABLE, "table not found"));
	       		return 'error';
	       	}
	       	
	       	foreach($copy_dbtables['tables'] as $old_dbtable) {
	       		$old_dbtable_name = $old_dbtable['Name'];
		       	if (!strcmp($this->db_prefix_old, "(none)")) {
					$new_dbtable = $this->db_prefix_new . $old_dbtable_name;
				} else {
					$new_dbtable = $this->db_prefix_new . substr($old_dbtable_name, strlen($this->db_prefix_old));       	
				}
				$create_sql = $this->db->execute("show create table ". $old_dbtable_name);
				if ($create_sql == false) {
					$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_COPYTABLE, "show table"));
					return 'error';
				}
				$sql_cmd = preg_replace( "/^CREATE TABLE `$old_dbtable_name`/", "CREATE TABLE `$new_dbtable`", $create_sql[0]['Create Table'], 1 ) ;

				$sqlcmd_return = $this->db->execute($sql_cmd);
				if ($sqlcmd_return == false) {
					$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_COPYTABLE, $new_dbtable));
					return 'error';
				}
				
				$sql_cmd = "INSERT INTO `$new_dbtable` SELECT * FROM `$old_dbtable_name`";
				$sqlcmd_return = $this->db->execute($sql_cmd);
				if ($sqlcmd_return == false) {
					$this->errorList->add(get_class($this), sprintf(SECURITY_INVALID_COPYTABLE, "insert table"));
					return 'error';
				}
	       	}
//		       	$this->db->setPrefix($this->db_prefix_new);
		}

        return 'success';
        
    }
}
?>
