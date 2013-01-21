<?php
/**
 * 権限管理共通コンポーネント
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Authority_Components_Compmain {
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $session = null;
	var $modulesView = null;
	var $commonMain = null;
	var $request = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Authority_Components_Compmain() {
	}
	
	
	/**
	 * module_object(authority_module_link_object)の一覧を取得する
	 * @return object module_object(authority_module_link_object)
	 * @access	public
	 */
	function setModules(&$result, $func_params)
	{
		$sys_modules = $func_params[0];
		if(isset($func_params[1])) {
			$first_flag = $func_params[1];
		} else {
			$first_flag = false;
		}
		if(isset($func_params[2])) {
			$format_flag = $func_params[2];
		} else {
			$format_flag = false;
		}
		
		$sysdata = array("sys_modules_id"=>array(), "sys_modules_name"=>array(), "sys_modules_dir"=>array(), "enroll_sysmodules_id"=>array(), "enroll_sysmodules_name"=>array());
		$data = array("sys_modules_id"=>array(), "sys_modules_name"=>array(), "sys_modules_dir"=>array(), "enroll_sysmodules_id"=>array(), "enroll_sysmodules_name"=>array());
		$site_modules_dir_arr = explode("|", AUTHORITY_SYS_DEFAULT_MODULES_ADMIN);
		
		while ($obj = $result->fetchRow()) {
			$module_id = $obj["module_id"];
			
			$pathList = explode("_", $obj["action_name"]);
			$name = $this->modulesView->loadModuleName($pathList[0]);
			
			if(in_array($pathList[0], $site_modules_dir_arr)) {
				$buf_data =& $data;
			} else {
				$buf_data =& $sysdata;
			}
			
			$buf_data["sys_modules_id"][$module_id] = $obj["module_id"];
			$buf_data["sys_modules_name"][$module_id] = $name;
			$buf_data["sys_modules_dir"][$module_id] = $pathList[0];
			if(!in_array("all", $sys_modules['disabled']) && !in_array($pathList[0], $sys_modules['disabled'])) {
				$buf_data["disabled"][$module_id] =  " disabled='true'";
			}
			$modselect = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "modselect"));
			if(isset($modselect)) {
				if((in_array("all", $sys_modules['default']) && isset($buf_data["disabled"][$module_id])) || (isset($modselect[$pathList[0]]) && $modselect[$pathList[0]] == _ON)) {
					$buf_data["default"][$module_id] =  " checked='checked'";
				}
			} else if ($format_flag == false && $first_flag && isset($obj["authority_id"])) {
				$buf_data["default"][$module_id] =  " checked='checked'";
				////$buf_data["authority_id"][$module_id] =  $obj["authority_id"];
				//$buf_data["enroll_sysmodules_id"][] = $obj["module_id"];
				//$buf_data["enroll_sysmodules_name"][] = $name;
			} else if(($format_flag == true || $first_flag == false || $this->request->getParameter("role_authority_id") == 0) && (in_array("all", $sys_modules['default']) || in_array($pathList[0], $sys_modules['default']))) {
				$buf_data["default"][$module_id] =  " checked='checked'";
			}
		}
		return array($sysdata, $data);
	}
	
	/**
	 * config.iniの値の配列を取得
	 * 		default値、disabledかどうか
	 * @param  int    $role_authority_id
	 * @param  int    $user_authority_id
	 * @return array  
	 * @access  public
	 */
	function &getConfig($role_authority_id, $user_authority_id)
	{
		$config = array();
		$select_index = $this->_getArrayIndex($user_authority_id);
		
		$config['myroom_use_flag']['default'][$this->_getValue('myroom_use_flag', AUTHORITY_MYROOM_USE_FLAG, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_MYROOM_USE_FLAG_DISABLED, $select_index)) {
			$config['myroom_use_flag']['disabled'] = " disabled='true'";
		}
		$config['allow_htmltag_flag']['default'][$this->_getValue('allow_htmltag_flag', AUTHORITY_ALLOW_HTMLTAG_FLAG, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_ALLOW_HTMLTAG_FLAG_DISABLED, $select_index)) {
			$config['allow_htmltag_flag']['disabled'] = " disabled='true'";
		}
		$config['allow_layout_flag']['default'][$this->_getValue('allow_layout_flag', AUTHORITY_ALLOW_LAYOUT_FLAG, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_ALLOW_LAYOUT_FLAG_DISABLED, $select_index)) {
			$config['allow_layout_flag']['disabled'] = " disabled='true'";
		}
		$config['allow_attachment']['default'][$this->_getValue('allow_attachment', AUTHORITY_ALLOW_ATTACHMENT, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_ALLOW_ATTACHMENT_DISABLED, $select_index)) {
			$config['allow_attachment']['disabled'] = " disabled='true'";
		}
		$config['allow_video']['default'][$this->_getValue('allow_video', AUTHORITY_ALLOW_VIDEO, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_ALLOW_VIDEO_DISABLED, $select_index)) {
			$config['allow_video']['disabled'] = " disabled='true'";
		}
		
		$config['max_size']['default'][$this->_getValue('max_size', AUTHORITY_MAX_SIZE, $select_index)] = " selected='selected'";
		//if($this->_getValue(null, AUTHORITY_MAX_SIZE_DISABLED, $select_index)) {
		if(!isset($config['myroom_use_flag']['default'][1]) || $config['myroom_use_flag']['default'][1] != " checked='checked'") {
			$config['max_size']['disabled'] = " disabled='true'";
		}
		$config['max_size']['list'] = explode("|", AUTHORITY_MAX_SIZE_LIST);
		$fileView =& $this->commonMain->registerClass(WEBAPP_DIR.'/components/file/View.class.php', "File_View", "fileView");
        foreach($config['max_size']['list'] as $list) {
			$config['max_size']['list_value'][] = $fileView->formatSize($list);
		}
		
		$config['public_createroom_flag']['default'][$this->_getValue('public_createroom_flag', AUTHORITY_PUBLIC_CREATEROOM_FLAG, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_PUBLIC_CREATEROOM_FLAG_DISABLED, $select_index)) {
			$config['public_createroom_flag']['disabled'] = " disabled='true'";
		}
		
		$config['group_createroom_flag']['default'][$this->_getValue('group_createroom_flag', AUTHORITY_GROUP_CREATEROOM_FLAG, $select_index)] = " checked='checked'";
		if($this->_getValue(null, AUTHORITY_GROUP_CREATEROOM_FLAG_DISABLED, $select_index)) {
			$config['group_createroom_flag']['disabled'] = " disabled='true'";
		}
		
		$config['sys_modules'] = $this->_getSysModulesArray($role_authority_id, $user_authority_id);
		
		return $config;
	}
	
	
	/**
	 * 権限カラムの初期化
	 * @param  int    $authority
	 * @param  int    $user_authority_id
	 * @access  public
	 */
	function formatAuth(&$authority, $user_authority_id)
	{
		$select_index = $this->_getArrayIndex($user_authority_id);
		
		$authority['myroom_use_flag'] = $this->_getValue('myroom_use_flag', AUTHORITY_MYROOM_USE_FLAG, $select_index);
		$authority['public_createroom_flag'] = $this->_getValue('public_createroom_flag', AUTHORITY_PUBLIC_CREATEROOM_FLAG, $select_index);
		$authority['group_createroom_flag'] = $this->_getValue('group_createroom_flag', AUTHORITY_GROUP_CREATEROOM_FLAG, $select_index);
		$authority['allow_htmltag_flag'] = $this->_getValue('allow_htmltag_flag', AUTHORITY_ALLOW_HTMLTAG_FLAG, $select_index);
		$authority['allow_layout_flag'] = $this->_getValue('allow_layout_flag', AUTHORITY_ALLOW_LAYOUT_FLAG, $select_index);
		$authority['allow_attachment'] = $this->_getValue('allow_attachment', AUTHORITY_ALLOW_ATTACHMENT, $select_index);
		$authority['allow_video'] = $this->_getValue('allow_video', AUTHORITY_ALLOW_VIDEO, $select_index);
		$authority['max_size'] = $this->_getValue('max_size', AUTHORITY_MAX_SIZE, $select_index);
	}
	/**
	 * config配列index取得
	 * @param  int    $user_authority_id
	 * @return array  
	 * @access  private
	 */
	function _getArrayIndex($user_authority_id)
	{
		switch($user_authority_id) {
			case _AUTH_ADMIN:
				$select_index = 0;
				break;
			case _AUTH_CHIEF:
				$select_index = 1;
				break;
			case _AUTH_MODERATE:
				$select_index = 2;
				break;
			case _AUTH_GENERAL:
				$select_index = 3;
				break;
			default:
				$select_index = 4;
				break;
		}
		return $select_index;
	}
	/**
	 * システムモジュールのデフォルト値、変更可能かどうかの配列取得
	 * @param  int    $role_authority_id
	 * @param  int    $user_authority_id
	 * @return array  
	 * @access  private
	 */
	function &_getSysModulesArray($role_authority_id, $user_authority_id)
	{
		$ret = array();
		switch($user_authority_id) {
			case _AUTH_ADMIN:
				if($role_authority_id == _ROLE_AUTH_ADMIN) {
					$def_default_name = AUTHORITY_SYS_DEFAULT_MODULES_SYSADMIN;
					$def_disabled_name = AUTHORITY_SYS_DISABLED_MODULES_SYSADMIN;
				} else {
					$def_default_name = AUTHORITY_SYS_DEFAULT_MODULES_ADMIN;
					$def_disabled_name = AUTHORITY_SYS_DISABLED_MODULES_ADMIN;
				}
				break;
			case _AUTH_CHIEF:
				$def_default_name = AUTHORITY_SYS_DEFAULT_MODULES_CHIEF;
				$def_disabled_name = AUTHORITY_SYS_DISABLED_MODULES_CHIEF;
				break;
			case _AUTH_MODERATE:
				$def_default_name = AUTHORITY_SYS_DEFAULT_MODULES_MODERATE;
				$def_disabled_name = AUTHORITY_SYS_DISABLED_MODULES_MODERATE;
				break;
			case _AUTH_GENERAL:
				$def_default_name = AUTHORITY_SYS_DEFAULT_MODULES_GENERAL;
				$def_disabled_name = AUTHORITY_SYS_DISABLED_MODULES_GENERAL;
				break;
			default:
				$def_default_name = AUTHORITY_SYS_DEFAULT_MODULES_GUEST;
				$def_disabled_name = AUTHORITY_SYS_DISABLED_MODULES_GUEST;
				break;
		}
		$ret['default'] = explode("|", $def_default_name);
		$ret['disabled'] = explode("|", $def_disabled_name);
		return $ret;
	}
	
	/**
	 * config.iniの値を取得
	 * @param  string    $name
	 * @param  string    $def_name
	 * @param  int       $select_index
	 * @return string    value 
	 * @access  private
	 */
	function _getValue($name, $def_name, $select_index)
	{
		$sess_value = null;
		switch($name) {
			case "myroom_use_flag":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "myroom_use_flag"));
				break;
			case "allow_htmltag_flag":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_htmltag_flag"));
				break;
			case "allow_layout_flag":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_layout_flag"));
				break;
			case "allow_attachment":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_attachment"));
				break;
			case "allow_video":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_video"));
				break;
			case "max_size":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "max_size"));
				if(isset($sess_value)) {
					$max_size_list = explode("|", AUTHORITY_MAX_SIZE_LIST);
					if($sess_value != 0) {
						$count = 0;
						foreach($max_size_list as $list) {
							if($sess_value == $list) {
								$sess_value = $count;
								break;
							}
							$count++;
						}
					} else {
						$sess_value = count($max_size_list);
					}
				}
				break;
			case "public_createroom_flag":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "public_createroom_flag"));
				break;
			case "group_createroom_flag":
				$sess_value = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "group_createroom_flag"));
				break;
			default:
				break;
		}
		if(isset($sess_value)) {
			return 	$sess_value;
		}
		$def_arr = explode("|", $def_name);
		return $def_arr[$select_index];
	}
	/**
	 * Detailの値があればSessionへセット
	 *   myroom_use_flagをdisabledになるとうまく動かない（現状、myroom_use_flagはdisabledになることはない）
	 * @param  array    $detail_arr 詳細情報配列
	 * @access  public
	 */
	function setSessionDetail($detail_arr, $modselect_flag = true)
	{
		if(isset($detail_arr['myroom_use_flag'])) {
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "myroom_use_flag"), $this->_getRequest($detail_arr, "myroom_use_flag"));
			if(isset($detail_arr['myroom_use_flag']) && $detail_arr['myroom_use_flag'] == _OFF) {
				$this->session->removeParameter(array("authority", $this->request->getParameter("role_authority_id"), "enroll_modules"));
			}
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_htmltag_flag"), $this->_getRequest($detail_arr, "allow_htmltag_flag"));
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_layout_flag"), $this->_getRequest($detail_arr, "allow_layout_flag"));
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_attachment"), $this->_getRequest($detail_arr, "allow_attachment"));
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "allow_video"), $this->_getRequest($detail_arr, "allow_video"));
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "max_size"), $this->_getRequest($detail_arr, "max_size"));
			$this->session->removeParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "modselect"));
			if($modselect_flag == true) {
				if(isset($detail_arr['modselect'])) {
					$this->session->removeParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "modselect"));
					foreach($detail_arr['modselect'] as $dirname => $modselect) {
						$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "modselect", $dirname), $modselect);
					}
				} else {
					$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "modselect"), array());
				}
			}	
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "usermodule_auth"), $this->_getRequest($detail_arr, "usermodule_auth"));	
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "public_createroom_flag"), $this->_getRequest($detail_arr, "public_createroom_flag"));
			$this->session->setParameter(array("authority", $this->request->getParameter("role_authority_id"), "detail", "group_createroom_flag"), $this->_getRequest($detail_arr, "group_createroom_flag"));
		}
	}
	
	/**
	 * Value取得
	 * @param  array    $detail_arr 詳細情報配列
	 * @param  string   $name
	 * @access  private
	 */
	function _getRequest(&$detail_arr, $name)
	{
		if(isset($detail_arr[$name])) {
			return $detail_arr[$name];
		} else if($name == "public_createroom_flag" || $name == "group_createroom_flag") {
			return _OFF;
		} else {
			return null;	
		}
	}
	
	
	/**
	 * module_object(authority_module_link_object)の一覧を取得する
	 * @return array module_object(authority_module_link_object)
	 * @access	public
	 */
	function setAuthoritiesModules(&$result, $func_params)
	{
		$role_authority_id = intval($func_params[0]);
		
		$enroll_modules = $this->session->getParameter(array("authority", $this->request->getParameter("role_authority_id"), "enroll_modules"));
		
		$data = array("not_enroll_id"=>array(), "not_enroll_name"=>array(), "enroll_id"=>array(), "enroll_name"=>array());
		while ($obj = $result->fetchRow()) {
			$pathList = explode("_", $obj["action_name"]);
			if(isset($enroll_modules)) {
				// セッションからセット
				if(in_array($obj["module_id"], $enroll_modules)) {
					$data["enroll_id"][] = $obj["module_id"];
					$data["enroll_name"][] = $this->modulesView->loadModuleName($pathList[0]);
				} else {
					$data["not_enroll_id"][] = $obj["module_id"];
					$data["not_enroll_name"][] = $this->modulesView->loadModuleName($pathList[0]);
				}
			} else if (isset($obj["authority_id"]) || $role_authority_id == 0) {
				$data["enroll_id"][] = $obj["module_id"];
				$data["enroll_name"][] = $this->modulesView->loadModuleName($pathList[0]);
			} else {
				$data["not_enroll_id"][] = $obj["module_id"];
				$data["not_enroll_name"][] = $this->modulesView->loadModuleName($pathList[0]);
			}
		}
		return $data;
	}
}
?>
