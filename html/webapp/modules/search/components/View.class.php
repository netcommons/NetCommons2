<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Components_View
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
	 * @var モジュールオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_modulesView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Search_Components_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_modulesView =& $this->_container->getComponent("modulesView");
	}

	/**
	 * ブロックのデータを取得
	 *
	 * @access	public
	 */
	function &getBlock($block_id, $show_mode=null, $default_target_module=null, $detail_flag=null) 
	{
		$block_id = intval($block_id);
    	$result =& $this->_db->selectExecute("search_blocks", array("block_id"=>$block_id));
        if (empty($result)) {
        	return $result;
        }
        $search_blocks_obj = $result[0];
		if (isset($show_mode)) {
			$search_blocks_obj["show_mode"] = $show_mode;
		}
		if (isset($default_target_module)) {
			$search_blocks_obj["default_target_module"] = $default_target_module;
		}
		$search_blocks_obj["default_target_module_arr"] = explode(",", $search_blocks_obj["default_target_module"]);
		if (isset($detail_flag)) {
			$search_blocks_obj["detail_flag"] = $detail_flag;
		}
        return $search_blocks_obj;
	}

	/**
	 * モジュールのデータを取得
	 *
	 * @access	public
	 */
	function &getModules($search_blocks_obj=null) 
	{
		$result =& $this->_modulesView->getModules(null, array("display_sequence"=>"ASC"), null, null, array($this, "_callbackModules"), array($search_blocks_obj));
		if ($result === false) {
        	return $result;
        }
        return $result;
	}

	/**
	 * モジュールのデータを取得
	 *
	 * @access	private
	 */
	function _callbackModules(&$recordSet, &$params) 
	{
		$request = $actionChain =& $this->_container->getComponent("Request");
		$block_id = $request->getParameter("block_id");
		
		$target_modules = $this->_session->getParameter(array("search_select", $block_id, "target_modules"));
		
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		
		$search_blocks_obj = $params[0];
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			if ($row["search_action"] == "") { continue; }
			$pathList = explode("_", $row["action_name"]);
			$row["dir_name"] = $pathList[0];
			if ($actionName == "search_view_main_init" && $search_blocks_obj["show_mode"] == SEARCH_SHOW_MODE_SIMPLE &&
				!in_array($row["dir_name"], $search_blocks_obj["default_target_module_arr"])) {
				continue;
			}
			$row["module_name"] = $this->_modulesView->loadModuleName($row["dir_name"]);
			if (isset($search_blocks_obj)) {
				if (isset($target_modules) && in_array($row["module_id"], $target_modules) || !isset($target_modules) && in_array($row["dir_name"], $search_blocks_obj["default_target_module_arr"])) {
					$row["target_module_flag"] = true;
				} else {
					$row["target_module_flag"] = false;
				}
			}
			$ret[] = $row;
		}
		return $ret;
	}
}
?>
