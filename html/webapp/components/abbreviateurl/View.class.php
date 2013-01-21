<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * URL短縮用表示クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Abbreviateurl_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Abbreviateurl_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
	}

	/**
	 * abbreviate_urlから取得
	 *
	 * @param string $dir_name
	 * @param string $unique_id
	 *
	 * @return boolean
	 * @access  public
	 */
	function getAbbreviateUrl($unique_id, $dir_name=null)
	{
		//dir_nameが省略されている場合、実行アクションから取得
		if (!isset($dir_name)) {
			$dir_name = $this->getDefaultUniqueKey();
		}

		$params = array(
			'dir_name' => $dir_name,
			'unique_id' => $unique_id
		);
		$result = $this->_db->selectExecute('abbreviate_url', $params);
		if ($result === false) {
			return $result;
		}
		if (isset($result[0])) {
			return $result[0]["short_url"];
		}
		return "";
	}

	/**
	 * install.iniからアクションを取得
	 *
	 * @return string
	 * @access  public
	 */
	function getIniFile($module_name, $key)
	{
		// install.iniチェック
		// [Abbreviateurl]
		$file_path = MODULE_DIR."/".$module_name.'/install.ini';
		if (file_exists($file_path)) {
			if(version_compare(phpversion(), "5.0.0", ">=")){
	        	$initializer =& DIContainerInitializerLocal::getInstance();
	        	$install_ini = $initializer->read_ini_file($file_path, true);
	        } else {
	 	        $install_ini = parse_ini_file($file_path, true);
	        }
	        if (isset($install_ini['Abbreviateurl']) && is_array($install_ini['Abbreviateurl']) && isset($install_ini['Abbreviateurl'][$key])) {
	        	return $install_ini['Abbreviateurl'][$key];
	        }
		}

		return "";
	}

	/**
	 * ユニークキーの初期値
	 *
	 * @return string
	 * @access  public
	 */
	function getDefaultUniqueKey()
	{
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		$pathList = explode("_", $actionName);
		$dir_name = $pathList[0];
		
		return $dir_name;
	}

	/**
	 * モジュールID取得
	 *
	 * @return string
	 * @access  public
	 */
	function getDefaultModuleId($module_name)
	{
		$getdata = $this->_container->getComponent("GetData");
		$modules = $getdata->getParameter("modules");
		if (isset($modules[$module_name])) {
			$module = $modules[$module_name];
		} else {
			$modulesView = $this->_container->getComponent("modulesView");
			$module = $modulesView->getModuleByDirname($module_name);
		}
		$module_id = $module['module_id'];
		
		return $module_id;
	}

	/**
	 * 英数字のランダム文字列を作成
	 *
	 * @param int $len
	 * @param string $prefix
	 *
	 * @return string
	 * @access  public
	 */
	function randString($len=8, $prefix=null)
	{
		$pt = _ABBREVIATE_URL_PATTERN;
		$st = '';
		if (isset($prefix)) {
			$st .= $prefix;
		} else {
			$rd = rand(0, strlen($pt)-1);
			$st .= $pt[$rd];
		}
		$set_len = strlen($st);
		for ($i=0; $i<$len-$set_len; $i++) {
			$rd = rand(0, strlen($pt)-1);
			$st .= $pt[$rd];
		}
		return $st;
	}
}
?>
