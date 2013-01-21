<?php
//
// Authors: Ryuji Masukawa
//
// $Id: Filter_SmartyAssign.class.php,v 1.19 2008/07/14 12:47:13 Ryuji.M Exp $
//

require_once MAPLE_DIR.'/nccore/SmartyTemplate.class.php';

/**
 * 言語セットの定義ファイルをAssignするFilter
 * key:global,global_config,module,config
 * NOTE: Session,DbObjectのフィルターを通したあとで実行すること
 * @author	Ryuji Masukawa
 **/
class Filter_SmartyAssign extends Filter {
	/**
	 * @var	言語セット
	 *
	 * @access	private
	 **/
	var $_lang=array();
	var $_conf=array();
	var $action_name="";

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Filter_SmartyAssign() {
		parent::Filter();
	}

	/**
	 * 言語セットの定義ファイルをAssign,_langに保存
	 *
	 **/
	function execute() {
		$log =& LogFactory::getLog();
		$log->trace("Filter_SmartyAssignの前処理が実行されました", "Filter_SmartyAssign#execute");

		//
		// カレントのActionを取得
		//
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$session =& $container->getComponent("Session");

		//アクション名よりモジュール言語セットパスを取得
		$action_name = $actionChain->getCurActionName();
		$pathList = explode("_", $action_name);
		$filepath   = MODULE_DIR . "/" . $pathList[0]. "/language/";
		$conf_filepath   = MODULE_DIR . "/" . $pathList[0]. "/config/";
		$global_conf_filepath   = WEBAPP_DIR . "/config/";
		$this->action_name = $action_name;

		$commonArray = array();
		$moduleArray = array();
		$lang_arr = array();
		$config_arr = array();

	    //言語名称取得
    	$lang = $session->getParameter("_lang");

		//
		// 共通クラスを取得
		//
		//$common =& $container->getComponent("NcCommon");

		//
		// DBクラスを取得,共通クラスに設定
		//
		//$db =& $container->getComponent("DbObject");
		//$common->setDb(&$db);

		//
		// 属性取得
		//

		$attributes = $this->getAttributes();
		if (isset($attributes["global"])) {
			$commonArray = explode(",", $attributes["global"]);
			foreach ($commonArray as $key => $value) {
				$commonArray[$key] = trim($value);
				$filename = WEBAPP_DIR . "/language/" . $lang . "/" . $commonArray[$key];
				$this->_LangArrayMerge($lang_arr,$filename);
			}
		}
		//modules直下のmaple.iniで定義してあるため、上記の読み込み処理で設定される
		//global.iniは必ずインクルード
		//$filename = WEBAPP_DIR . "/language/" . $lang . "/" . "global.ini";
		//$this->_LangArrayMerge($lang_arr,$filename);

		// global-assign.iniは必ずインクルード
		//$filename = WEBAPP_DIR. "/config/global-assign.ini";
		//$this->_LangArrayMerge($config_arr,$filename);

		if (isset($attributes["module"])) {
			$moduleArray = explode(",", $attributes["module"]);
			foreach ($moduleArray as $key => $value) {
				$moduleArray[$key] = trim($value);
				$filename = $filepath.$lang."/".$moduleArray[$key];
				$this->_LangArrayMerge($lang_arr,$filename);
			}
		}

		if (isset($attributes["config"])) {
			$moduleArray = explode(",", $attributes["config"]);
			foreach ($moduleArray as $key => $value) {
				$moduleArray[$key] = trim($value);
				$filename = $conf_filepath.$moduleArray[$key];
				$this->_LangArrayMerge($config_arr,$filename);
			}
		}

		if (isset($attributes["global_config"])) {
			$moduleArray = explode(",", $attributes["global_config"]);
			foreach ($moduleArray as $key => $value) {
				$moduleArray[$key] = trim($value);
				$filename = $global_conf_filepath.$moduleArray[$key];
				$this->_LangArrayMerge($config_arr,$filename);
			}
		}

		$this->_setLang($lang_arr,$action_name);
		$this->_setLang($config_arr,$action_name,false,true);
		// -------------------------------------------------------------------------
		// include.inc.php書き込みチェック
		// 現状、SmartyAssignで行っているが
		// 別途、Filterを追加するほうがよいかも
		// 但し、SmartyAssignのFilterを通した後に実行すること
		// -------------------------------------------------------------------------
		if((is_writeable(INSTALL_INC_DIR . "/". "install.inc.php") && is_dir(BASE_DIR."/webapp/modules/install/")) && ($this->action_name == 'pages_view_main' || $this->action_name == 'control_view_main')) {
			$file_path = dirname(INSTALL_INC_DIR) . '/templates/main/installerror.php';
			if(file_exists($file_path)) {
				$content = "";
    			include $file_path;
    			echo $content;
				exit;
			}
		}

		$filterChain =& $container->getComponent("FilterChain");
		$filterChain->execute();

		$log->trace("Filter_SmartyAssignの後処理が実行されました", "Filter_SmartyAssign#execute");
	}

	function _LangArrayMerge(&$lang_arr,$filename) {
		if(@is_file($filename)) {
			if (version_compare(phpversion(), '5.0.0', '>=')) {
				$initializer =& DIContainerInitializerLocal::getInstance();
				$tmp_lang_arr = $initializer->read_ini_file($filename, true);
			} else {
				$tmp_lang_arr = parse_ini_file($filename, TRUE);
			}
			if(isset($tmp_lang_arr["Define"]) && isset($lang_arr["Define"])) {
				$lang_arr["Define"] = array_merge($lang_arr["Define"],$tmp_lang_arr["Define"]);
				unset($tmp_lang_arr["Define"]);
			}
			if(isset($tmp_lang_arr["Global_Force"]) && isset($lang_arr["Global_Force"])) {
				$lang_arr["Global_Force"] = array_merge($lang_arr["Global_Force"],$tmp_lang_arr["Global_Force"]);
				unset($tmp_lang_arr["Global_Force"]);
			}
			if(isset($tmp_lang_arr["Global"]) && isset($lang_arr["Global"])) {
				$lang_arr["Global"] = array_merge($lang_arr["Global"],$tmp_lang_arr["Global"]);
				unset($tmp_lang_arr["Global"]);
			}
			$lang_arr = array_merge_recursive($lang_arr,$tmp_lang_arr);
		}
	}
	/**
	 * 言語セットを設定
	 *
	 * @param	array	$lang	言語セットの配列
	 * @param	string	$action_name	アクション名
	 * @param	boolean	$loop_flag		再帰処理用
	 * @param	boolean	$conf_flag		コンフィグフラグ：assign先をconfにする
	 * @access	private
	 * [画面名称]
	 * key=value･･･
	 * global:key=value･･･
	 **/
	function _setLang($lang,$action_name,$loop_flag=false,$conf_flag=false) {
		if(!$conf_flag) {
			$assign_str = "lang";
			$this_lang =& $this->_lang;
		} else {
			$assign_str = "conf";
			$this_lang =& $this->_conf;
		}

		$log =& LogFactory::getLog();
		$pathList = explode("_", $action_name);
		static $global_arr = array();
		foreach ($lang as $key_arr => $value_arr) {
			if(strpos($key_arr, ":")!==false) {
			//if (preg_match("/:/", $key_arr)) {
				//[画面名称:画面名称]ならば
				$keyArray = explode(":", $key_arr);
				foreach ($keyArray as $subkey => $subvalue) {

					$sublang = array($subvalue => $value_arr);
					//再帰処理
					$this->_setLang($sublang,$action_name,true,$conf_flag);
				}
				continue;
			}
			foreach ($lang[$key_arr] as $key => $value) {
				//TODO:LANG_CODEについては使い道があるかどうかを含めて
				//検討しなければならない
				if (LANG_CODE != INTERNAL_CODE) {
					$value = mb_convert_encoding($value, INTERNAL_CODE, LANG_CODE);
				}
				$key_flag = 0;	//1:global 2:define 3:noconstant
				$key_len = 7;
				if(substr($key, 0, $key_len) == "global:") {
					$key_flag = 1;
					$key = substr($key, $key_len, strlen($key) - $key_len);
				} else if(substr($key, 0, $key_len) == "define:") {
					$key_flag = 2;
					$key = substr($key, $key_len, strlen($key) - $key_len);
				} else if(substr($key, 0, 8) == "noconst:") {
					$key_flag = 3;
					$key = substr($key, 8, strlen($key) - 8);
				}

				if($key_arr == "Define") {
					//常にdefine
					if (!defined($key)) {
						define($key, $value);
					}
				} else if($key_arr == "Global") {
					$global_arr[$key] = $value;
				} else if($key_arr == "Global_Force" || $key_arr == ucfirst($pathList[0])."_Global" || strpos(strtolower($action_name)."_", strtolower($key_arr)."_")!==false ) {
				//} else if($key_arr == "Global_Force" || $key_arr == ucfirst($pathList[0])."_Global" || preg_match("/".strtolower($key_arr)."_"."/", strtolower($action_name)."_")!=0 ) {
					if($key_flag == 1) {
						//global:key
						$gkey  = $key;
						if(isset($global_arr[$gkey])) {
							$this_lang[$gkey] = $global_arr[$gkey];
						}else if(!isset($this_lang[$key])){
							$log->warn("webapp/language/下に[GLOBAL]$gkey=valueが宣言されていません", "Filter_SmartyAssign#_setLang");
							$this_lang[$key] = $value;
						}
					}else if($key_flag == 2) {
						//define:ならば、defineに追加
						//既に定義済みならばdefinef不可
						if (!defined($key)) {
							$key  = preg_replace("/^define:/", "", $key);
							define($key, $value);
						}
					}else if($key_flag == 3) {
						$this_lang[$key] = $value;
					} else {
						if (defined($value))
							$value = constant($value);
						$this_lang[$key] = $value;
					}
				}
			}
		}
		if(!$loop_flag) {
			//Smartyインスタンス取得
			$renderer =& SmartyTemplate::getInstance();

			$temp_arr = $renderer->get_template_vars($assign_str);
			if(is_array($temp_arr))
				$this_lang = array_merge($temp_arr,$this_lang);

			$renderer->assign($assign_str,$this_lang); //assign_by_ref
		}
	}

	/**
	 * 言語セット情報をクリア
	 *
	 * @access	public
	 **/
	function clear() {
		$this->_lang = array();
		//Smartyインスタンス取得
		$renderer =& SmartyTemplate::getInstance();
		$renderer->clear_assign("lang");
	}

	/**
	 * 言語セット情報を返却
	 *
	 * @param	key	$key	言語セットのキー名称
	 * @return	array	言語セット情報の配列
	 * @access	public
	 **/
	function &getLang($key=null) {
		if(!isset($key))
			return $this->_lang;
		else {
			if(isset($this->_lang[$key]))
				return $this->_lang[$key];
			else {
				$log =& LogFactory::getLog();
				$log->warn(sprintf("%sが宣言されていません",$key), "Filter_SmartyAssign#_getLang");
				return $key;
			}
		}
	}

	/**
	 * Configセット情報を返却
	 *
	 * @param	key	$key	Configセットのキー名称
	 * @return	array	Configセット情報の配列
	 * @access	public
	 **/
	function &getConf($key=null) {
		if(!isset($key))
			return $this->_conf;
		else {
			if(isset($this->_conf[$key]))
				return $this->_conf[$key];
			else {
				$log =& LogFactory::getLog();
				$log->warn(sprintf("%sが宣言されていません",$key), "Filter_SmartyAssign#_getConf");
				return $key;
			}
		}
	}
}
?>
