<?php
/**
 * モジュールテーブル登録用クラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Modules_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Modules_Action() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * display_sequence更新処理
	 * @param int module_id,int display_sequence
	 * @return boolean true or false
	 * @access	public
	 **/
	function updModuleDisplayseq($module_id,$display_sequence)
	{
		$params = array(
			"display_sequence" => $display_sequence,
			"module_id" => $module_id
		);
		$result = $this->_db->execute("UPDATE {modules} SET display_sequence=? " .
									" WHERE module_id=?",$params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return true;
	}
	
	/**
	 * Modules Update
	 * @param array(install.iniの設定項目)
	 * @return boolean true or false
	 * @access	public
	 */
	function updModule($install_ini=array())
	{
		$params = array(
			"version" =>$install_ini["version"],
			"action_name" =>$install_ini["action_name"],
			"edit_action_name" =>$install_ini["edit_action_name"],
			"edit_style_action_name" =>$install_ini["edit_style_action_name"],
			"system_flag" =>$install_ini["system_flag"],
			"disposition_flag" =>$install_ini["disposition_flag"],
			"default_enable_flag" =>$install_ini["default_enable_flag"],
			"module_icon" =>$install_ini["module_icon"],
			"theme_name" =>$install_ini["theme_name"],
			"temp_name" =>$install_ini["temp_name"],
			"min_width_size" =>$install_ini["min_width_size"],
			"backup_action" =>$install_ini["backup_action"],
			"restore_action" =>$install_ini["restore_action"],
			"search_action" =>$install_ini["search_action"],
			"delete_action" =>$install_ini["delete_action"],
			"block_add_action" =>$install_ini["block_add_action"],
			"block_delete_action" =>$install_ini["block_delete_action"],
			"move_action" =>$install_ini["move_action"],
			"copy_action" =>$install_ini["copy_action"],
			"shortcut_action" =>$install_ini["shortcut_action"],
			"personalinf_action" =>$install_ini["personalinf_action"],
			"whatnew_flag" =>$install_ini["whatnew_flag"]
		);
		$result = $this->_db->updateExecute("modules", $params, array("module_id" => $install_ini["module_id"]), true);
        if ($result === false) {
	       	return $result;
		}
		
		return true;
	}
	
	/**
	 * Modules Insert
	 * @param array(install.iniの設定項目)
	 * @return boolean false or int $module_id
	 * @access	public
	 */
	function insModule($install_ini=array())
	{
		//MAX表示順取得
		$configView =& $this->_container->getComponent("modulesView");
		$display_sequence = $configView->getMaxDisplaySeq($install_ini["system_flag"]) + 1;    		
	    
        $params = array(
        	"version" =>$install_ini["version"],
			"display_sequence" =>$display_sequence,
			"action_name" =>$install_ini["action_name"],
			"edit_action_name" =>$install_ini["edit_action_name"],
			"edit_style_action_name" =>$install_ini["edit_style_action_name"],
			"system_flag" =>$install_ini["system_flag"],
			"disposition_flag" =>$install_ini["disposition_flag"],
			"default_enable_flag" =>$install_ini["default_enable_flag"],
			"module_icon" =>$install_ini["module_icon"],
			"theme_name" =>$install_ini["theme_name"],
			"temp_name" =>$install_ini["temp_name"],
			"min_width_size" =>$install_ini["min_width_size"],
			"backup_action" =>$install_ini["backup_action"],
			"restore_action" =>$install_ini["restore_action"],
			"search_action" =>$install_ini["search_action"],
			"delete_action" =>$install_ini["delete_action"],
			"block_add_action" =>$install_ini["block_add_action"],
			"block_delete_action" =>$install_ini["block_delete_action"],
			"move_action" =>$install_ini["move_action"],
			"copy_action" =>$install_ini["copy_action"],
			"shortcut_action" =>$install_ini["shortcut_action"],
			"personalinf_action" =>$install_ini["personalinf_action"],
			"whatnew_flag" =>$install_ini["whatnew_flag"]
		);
		
		$module_id = $this->_db->insertExecute("modules", $params, true, "module_id");
        if ($module_id === false) {
	       	return $result;
		}
		
		return $module_id;
	}
	
	/**
	 * module_idによるモジュール削除処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function delModuleById($module_id)
	{
		$params = array( 
			"module_id" => $module_id
		);
		
		$result = $this->_db->execute("DELETE FROM {modules} WHERE module_id=?" .
										" ",$params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		
		return true;
	}
	/**
	 * 表示順前詰め処理
	 * @param display_sequence,system_flag
	 * @return boolean true or false
	 * @access	public
	 */
	function decrementDisplaySequence($display_sequence,$system_flag = 0) {
	 	$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        
		$params = array(
			"update_time" =>$time,
			"update_user_id" => $user_id,
			"update_user_name" => $user_name,
			"system_flag" => $system_flag,
			"display_sequence" => $display_sequence
		);
		$result = $this->_db->execute("UPDATE {modules} SET display_sequence=display_sequence - 1,update_time=?, update_user_id=?,update_user_name=?" .
									" WHERE system_flag=?" .
									" AND display_sequence>?",$params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return true;
	}
}
?>