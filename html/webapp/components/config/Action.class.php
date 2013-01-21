<?php
/**
 * ConfigActionクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Config_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Config_Action() {
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
	}

	/**
	 * ConfigValue登録処理
	 * @return boolean true or false
	 * @access	public
	 **/
	function insConfigValue($conf_modid, $conf_name, $conf_value)
	{
		$regs = array();
		if (preg_match("/^([^\[\]>]+)(\[([0-9]*)\])?$/", $conf_name, $regs)) {
			$conf_name = $regs[1];
		}
		$conf_catid = isset($regs[3]) ? intval($regs[3]) : 0;
		
        $params = array(
			"conf_modid" =>$conf_modid,
			"conf_catid" =>$conf_catid,
			"conf_name" =>$conf_name,
			"conf_value" =>$conf_value
		);
		
		$result = $this->_db->insertExecute("config", $params, true, "conf_id");
		if($result === false) {
			return false;
		}
		return true;
	}
	
	/**
	 * ConfigValue更新処理
	 * @return boolean true or false
	 * @access	public
	 **/
	function updConfigValue($conf_modid, $conf_name, $conf_value, $conf_catid=_MAIN_CONF_CATID)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$configView =& $container->getComponent("configView");
		$lang_items = explode(',', _MULTI_LANG_CONFIG_ITEMS);
		$session_lang = $session->getParameter('_lang');
		$config_lang =& $configView->getConfigByConfname(_SYS_CONF_MODID, 'language');
		
		if(in_array($conf_name, $lang_items) && $configView->isMultiLanguage) {
			$result = $this->_db->updateExecute('config_language', array('conf_value' => $conf_value), array('lang_dirname' => $session_lang,'conf_name' => $conf_name));
			if($result === false) return false;
		}

		if(!$configView->isMultiLanguage || !in_array($conf_name, $lang_items) || $config_lang['conf_value'] == $session_lang) {
			$params = array(
				"conf_catid" => $conf_catid,
				"conf_value" => $conf_value
			);
			$where_params = array(
							"conf_modid" => $conf_modid, 
							"conf_name" => $conf_name
						);
			
			$result = $this->_db->updateExecute("config", $params, $where_params, true);
	        if ($result === false) {
		       	return $result;
			}
		}
		return true;
	}
	
	
	/**
	 * Config更新処理
	 * @param array $params
	 * @param array $where_params
	 * @return boolean
	 * @access	public
	 */
	function updConfig($params=array(), $where_params=array(), $footer_flag = true)
	{
		$result = $this->_db->updateExecute("config", $params, $where_params, $footer_flag);
        if ($result === false) {
	       	return false;
		}
		return true;
	}

	/**
	 * ConfigValue削除処理
	 * @return boolean true or false
	 * @access	public
	 **/
	function delConfigValueByModid($conf_modid)
	{
		$params = array(
			"conf_modid" => $conf_modid
		);
		$result = $this->_db->deleteExecute("config", $params);
		if ($result === false) {
	       	return $result;
		}
		return true;
	}

	/**
	 * ConfigValue削除処理
	 * @return boolean true or false
	 * @access	public
	 **/
	function delConfigValue($conf_modid, $conf_name)
	{
		$params = array(
			"conf_modid" => $conf_modid, 
			"conf_name" => $conf_name
		);
		$result = $this->_db->deleteExecute("config", $params);
		if ($result === false) {
	       	return $result;
		}
		return true;
	}
}
?>