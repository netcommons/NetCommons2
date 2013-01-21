<?php
/**
 * モジュールテーブル表示用クラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Modules_View {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	var $_container = null;
	
	var $_module_name_list = array();
	
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Modules_View() {
		$this->_container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
    	$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * modulesテーブルの一覧を取得する
	 * @param array where_params
	 * @param array order_params
	 * @return array modules
	 * @access	public
	 */
	function &getModules($where_params = null, $order_params = array("{modules}.module_id"=>"ASC"), $limit = null, $offset = null, $func = array("Modules_View", "_fetchcallbackModules"), $func_param = null)
	{
		if (!isset($order_params)) {
        	$order_params = array("{modules}.module_id"=>"ASC");	
        }
        if (!isset($func_param)) {
        	$func_param = $this;	
        }
        
		$result = $this->_db->selectExecute("modules", $where_params, $order_params, $limit, $offset, $func, $func_param);
		
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		
		return $result;
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	public
	 */
	function _fetchcallbackModules($result, $func_param) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$pathList = explode("_", $row["action_name"]);
			$row["module_name"] = $func_param->loadModuleName($pathList[0]);
			$ret[] = $row;
		}
		return $ret;
	}
	
	/**
	 * DirNameからmodules_objectの配列を取得する
	 * @param string dir_name
	 * @return array
	 * @access	public
	 */
	function &getModuleByDirname($dir_name) 
	{
		$params = array(
			"action_name" => $dir_name."\_%"
		);
		
		$result = $this->_db->execute("SELECT * FROM {modules} " .
										" WHERE {modules}.action_name LIKE ? ",$params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if(isset($result[0])) {
			//モジュール名読み込み
			$pathList = explode("_", $result[0]["action_name"]);
			$result[0]["module_name"] = $this->loadModuleName($pathList[0]);
			return $result[0];
		}
		
		return $result;
	}
	
	/**
	 * System系で使用可能なmodules_objectの配列を取得する
	 * @param string role_authority_id
	 * @param int limit
	 * @param int start
	 * @return array
	 * @access	public
	 */
	function &getModulesByRoleAuthorityId($role_authority_id,$limit=0,$start=0) 
	{
		$params = array(
			"role_authority_id" => $role_authority_id
		);
		
		$result = $this->_db->execute("SELECT {modules}.* FROM {modules},{authorities_modules_link} " .
										" WHERE {modules}.system_flag = 1 AND {authorities_modules_link}.role_authority_id=? " .
										"AND {modules}.module_id = {authorities_modules_link}.module_id ORDER BY {modules}.display_sequence ",$params, $limit, $start);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if(isset($result[0])) {
			//モジュール名読み込み
	        $count = 0;
	        foreach($result as $module_obj) {
	        	$pathList = explode("_", $result[$count]["action_name"]);
	        	$result[$count]["module_name"] = $this->loadModuleName($pathList[0]);
	        	$count++;
	        }
	        return $result;
		}
		return $result;
	}
	
	/**
	 * System_flag毎のmodules_objectの件数を取得する
	 * @param string system_flag
	 * @return int 
	 * @access	public
	 */
	function getCountBySystemflag($system_flag) 
	{
		$params = array(
			"system_flag" => $system_flag
		);
		
		$result = $this->_db->execute("SELECT COUNT(*) FROM {modules} " .
										" WHERE {modules}.system_flag=? ",$params,null,null,false);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if(isset($result[0])) {
			return $result[0][0];
		}
		return 0;
	}
	
	/**
	 * System_flag毎のmodules_objectの配列を取得する
	 * @param string system_flag
	 * @param int limit
	 * @param int start
	 * @return array
	 * @access	public
	 */
	function &getModulesBySystemflag($system_flag,$limit=0,$start=0) 
	{
		$params = array(
			"system_flag" => $system_flag
		);
		
		$result = $this->_db->execute("SELECT * FROM {modules} " .
										" WHERE {modules}.system_flag=? ORDER BY display_sequence ",$params,$limit,$start);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if(isset($result[0])) {
			//モジュール名読み込み
	        $count = 0;
	        foreach($result as $module_obj) {
	        	$pathList = explode("_", $result[$count]["action_name"]);
	        	$result[$count]["module_name"] = $this->loadModuleName($pathList[0]);
	        	$count++;
	        }
	        return $result;
		}
		return $result;
	}
	
	/**
	 * moduleテーブルからmodule_objectの配列を取得する
	 * @param int module_id or array module_id_array
	 * @return array
	 * @access	public
	 */
	function &getModulesById($params, $func = null, $func_param = null) 
	{
		if(is_array($params)) {
			$count = count($params);
			if($count == 0) {
				$result= false;
				return $result;
			}
			$sql = "SELECT {modules}.* FROM {modules} " .
											" WHERE {modules}.module_id=?";
			for($i = 1; $i < $count; $i++) {
				$sql .= " OR {modules}.module_id=?";
			}
			$sql .= " ORDER BY {modules}.display_sequence, {modules}.module_id ";
			
			$result = $this->_db->execute($sql,$params,null,null,true,$func,$func_param);
		} else {
			$id = $params;
			$sql_params = array(
				"module_id" => $id
			);
			$result = $this->_db->execute("SELECT {modules}.* FROM {modules} " .
											" WHERE {modules}.module_id=?",$sql_params,null,null,true,$func,$func_param);	
		}
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if(count($result) == 0) {
			$result= false;
			return $result;
		} else {
			if($func == null) {
				if(isset($result[0])) {
					$count = count($result);
					for($i = 0; $i < $count; $i++) {
						//モジュール名読み込み
						$pathList = explode("_", $result[$i]["action_name"]);
						$result[$i]["module_name"] = $this->loadModuleName($pathList[0]);
					}
				}
			}
			if(is_array($params)) {
				return $result;	
			} else {
				return $result[0];	
			}
		}
	}
	
	/**
	 * page_idから追加可能な一般モジュール一覧を取得する
	 * @param int page_id
	 * @return array
	 * @access	public
	 */
	function &getModulesByUsed($page_id = null, $limit = 0, $start = 0,$key_flag = false) 
	{
		$params = array(
			"page_id" => $page_id
		);
		$result = $this->_db->execute("SELECT {modules}.* FROM {modules},{pages_modules_link} " .
									" WHERE {pages_modules_link}.room_id =? " .
									" AND {modules}.module_id={pages_modules_link}.module_id " .
									" ORDER BY {modules}.display_sequence",$params,$limit,$start, $key_flag, array($this, "fetchcallbackModulesByUsed"), array($key_flag));
	
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		
		return $result;
	}
	
	/**
	 * プレイベートスペースで使用できるモジュールの一覧を取得する
	 * @param  int  role_authority_id
	 * @param  int  limit
	 * @param  int  start
	 * @param  bool key_flag(連想配列として取得するかどうか)
	 * @return array authorities_modules_link + module_name
	 * @access	public
	 */
	function &getAuthoritiesModulesByUsed($_role_authority_id, $limit = null, $start = null,$key_flag = false)
	{
		$params = array(
			"authorities_modules_link" => $_role_authority_id
		);
		$result = $this->_db->execute("SELECT {modules}.* FROM {modules},{authorities_modules_link} " .
									" WHERE {authorities_modules_link}.role_authority_id =? " .
									" AND {modules}.system_flag = 0 AND {modules}.module_id={authorities_modules_link}.module_id " .
									" ORDER BY {modules}.display_sequence",$params,$limit,$start, $key_flag, array($this, "fetchcallbackModulesByUsed"), array($key_flag));
	
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		
		return $result;
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @param func_param
	 * @return array
	 * @access	public
	 */
	function fetchcallbackModulesByUsed($result, $func_param) {
		$key_flag = $func_param[0];
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(!$key_flag) {
				$pathList = explode("_", $row[3]);
			} else {
				$pathList = explode("_", $row['action_name']);
			}
			//モジュール名称セット
			$module_name = $this->loadModuleName($pathList[0]);
			if(!$key_flag) {
				$ret_arr = array();
				$ret_arr[] = $row[0];
				$ret_arr[] = $module_name;
				$ret_arr[] = $pathList[0];
				$ret[$module_name] = $ret_arr;
				//$row[1] = $module_name;
				//$ret[$row[1]] = $row;
			} else {
				$row['module_name'] = $module_name;
				//iconセット
				//なければnoimageを表示
				$image_path = HTDOCS_DIR  . "/images/" . $pathList[0] ."/" . $row['module_icon'];
				if(file_exists($image_path)) {
					$row['icon_path'] = $pathList[0]. "/" . $row['module_icon'];
				} else {
					$row['icon_path'] = "common/noimage.gif";	
				}
				$row['dir_name'] = $pathList[0];
				$ret[$row['module_id']] = $row;
			}
			
		}
		return $ret;
	}
	
	/**
	 * MAX表示順取得を取得する
	 * @return int $system_flag
	 * @access	public
	 */
	function getMaxDisplaySeq($system_flag)
	{
		$params = array(
					"system_flag" => intval($system_flag)
				);
		
		$result = $this->_db->execute("SELECT MAX(display_sequence) FROM {modules} WHERE system_flag=? ",$params,null,null,false);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		if(isset($result[0])) {
			return $result[0][0];
		}
		return $result;	
	}
	
	/**
     * Load the module install_info for this module
     * 
     * @param	string  $dirname    Module directory
     */
    function loadInfo($dirname)
    {
    	$file_path = MODULE_DIR."/".$dirname.'/install.ini';
    	if (file_exists($file_path)) {
	        if(version_compare(phpversion(), "5.0.0", ">=")){
	        	$initializer = DIContainerInitializerLocal::getInstance();
	        	$install_ini = $initializer->read_ini_file($file_path, true);
	        } else {
	 	        $install_ini = parse_ini_file($file_path, true);
	        }
	        if (count($install_ini) < 1) {
	        	return false;
	        }
       	} else {
       		return false;
       	}
       	$install_ini_def = $this->getDefaultInfoFileDefine();
		$install_ini = array_merge($install_ini_def,$install_ini);
		return $install_ini;
    }
     /**
     * install.iniファイルDEFAULT値取得
     * @return array
     */
    function getDefaultInfoFileDefine() {
    	return array(
			"version" => MODULE_DEFAULT_VARSION,
			"action_name" => "",
			"edit_action_name" => "",
			"edit_style_action_name" => "",
			"system_flag" => MODULE_DEFAULT_SYSTEM_FLAG,
			"disposition_flag" => MODULE_DEFAULT_DISPOSITION_FLAG,
			"default_enable_flag" => MODULE_DEFAULT_ENABLE_FLAG,
			"module_icon" => MODULE_DEFAULT_MODULEICON,
			"theme_name" => MODULE_DEFAULT_THEME_NAME,
			"temp_name" => MODULE_DEFAULT_TEMP_NAME,
			"min_width_size" => MODULE_DEFAULT_MIN_WIDTH_SIZE,
			"backup_action" => "auto",
			"restore_action" => "auto",
			"search_action" => "",
			"delete_action" => "auto",
			"block_add_action" => "",
			"block_delete_action" => "auto",
			"move_action" => "",
			"copy_action" => "",
			"shortcut_action" => "",
			"personalinf_action" => "",
			"whatnew_flag" => _OFF
		);
    }
    /**
     * install.iniファイルDEFAULT値設定
     * @param	array  $install_ini,array $module_obj,string $result_mes
     * @return boolean true or false
     */
    function setDefaultInfoFile(&$install_ini,&$module_obj,&$result_mes) {
    	$return_flag =true;
    	$install_ini_def = $this->getDefaultInfoFileDefine();
						
    	//DEFAULT値設定
       	foreach($install_ini_def as $key => $value) {
       		if(!isset($install_ini[$key])) {
       			if($value == "") {
       				//エラー
       				$result_mes .= $this->getShowMes(sprintf(MODULE_MES_RESULT_NODATAFILE_ER,$key),2);
	       			$return_flag =false;	
       			} else {
       				$install_ini[$key] = $value;
	       			$result_mes .= $this->getShowMes(sprintf(MODULE_MES_RESULT_NODATAFILE_WR,$key,$value),2);	
       			}
       		}
       		if(isset($install_ini[$key])) {
       			if ($module_obj[$key] != $install_ini[$key] && $install_ini[$key] != "") {
       				$result_mes .= $this->getShowMes(sprintf(MODULE_MES_RESULT_REGIST_ET,"[".$key."=".$install_ini[$key]."]"),2);
       			}
       		}
       	}
       	return $return_flag;
    }
    /**
     * モジュールインストール/アップデート/アンインストール結果文字列 表示関数
     * @param	string  $mes, int $tabLine タブの数
     * @return mes
     */
    function getShowMes($mes,$tabLine=0) {
    	$tabNum = $tabLine*20;	//20px
    	return "<div style='padding-left:".$tabNum."px;'>".$mes."</div>";
    }
    
    /**
     * Load the module info for this module
     * 
     * @param	string  $dirname    Module directory
     */
    function loadModuleInfo($dirname)
    {
    	$session =& $this->_container->getComponent("Session");
        $lang = $session->getParameter("_lang");
        $file_path = MODULE_DIR."/".$dirname."/language/".$lang.'/modinfo.ini';
     
        if (file_exists($file_path)) {
 	        $modinfo_ini = parse_ini_file($file_path);
	        if (count($modinfo_ini) < 1) {
	        	return false;
	        }
       	} else {
       		return false;
       	}
		return $modinfo_ini;
    }
    /**
     * モジュール名読み込み
     * 
     * @param	string $dir_name
     * @return string $module_name
     */
    function loadModuleName($dir_name) {
    	if(isset($this->_module_name_list[$dir_name]))
    		return $this->_module_name_list[$dir_name];
    	
		$modinfo_ini = $this->loadModuleInfo($dir_name);
		
        if($modinfo_ini && isset($modinfo_ini["module_name"])) {
        	$this->_module_name_list[$dir_name] = $modinfo_ini["module_name"];
        	return $modinfo_ini["module_name"];
        } else {
        	if(defined("_NONE_MODULE_NAME")) {
	        	$this->_module_name_list[$dir_name] = _NONE_MODULE_NAME;
    			return _NONE_MODULE_NAME;
			} else {
				$this->_module_name_list[$dir_name] = _NONE_MODULE_NAME;
    			return _NONE_MODULE_NAME;
				//$this->_module_name_list[$dir_name] = "Undefined";
    			//return "Undefined";
			}
    	}	
    }
}
?>
