<?php
//
// $Id: SmartyTemplate.class.php,v 1.29 2008/06/30 06:49:19 Ryuji.M Exp $
//

require_once SMARTY_DIR . "Smarty.class.php";
require_once MAPLE_DIR.'/core/BeanUtils.class.php';

/**
 * Smartyクラスを拡張して使用する
 *
 * @author	Ryuji Masukawa
 **/
class SmartyTemplate extends Smarty {
	var $template_dir;
	var $compile_dir;
	var $config_dir;
	var $cache_dir;
	var $caching;
	var $cache_lifetime;
	var $compile_check;
	var $force_compile;

	/**
	 * コンストラクター
	 *
	 * SmartyTemplateクラスはSingletonとして使うので直接newしてはいけない
	 *
	 */
	function SmartyTemplate() {
		$this->Smarty();

		//$this->template_dir	     = SMARTY_TEMPLATE_DIR;
		$this->compile_dir	     = SMARTY_COMPILE_DIR;
		//$this->config_dir	     = SMARTY_CONFIG_DIR;
		//$this->cache_dir	     = SMARTY_CACHE_DIR;
		$this->caching           = SMARTY_CACHING;
		$this->cache_lifetime    = SMARTY_CACHE_LIFETIME;
		$this->compile_check     = SMARTY_COMPILE_CHECK;
		$this->force_compile     = SMARTY_FORCE_COMPILE;
		$this->default_modifiers = array(SMARTY_DEFAULT_MODIFIERS);

		$this->cache_handler_func = "smartyTemplateCacheHandler";


		//キャッシュを必ず使う場合、false
		//$this->compile_check = false;

		//プラグインのPath
		$this->plugins_dir = array(SMARTY_DIR.'plugins');

		//取得に失敗した際に呼び出す関数
		$this->default_template_handler_func = 'smartyTemplateCreate';

		$this->left_delimiter =  '<{';
		$this->right_delimiter =  '}>';

		if (TEMPLATE_CODE != INTERNAL_CODE) {
			// プリフィルタを登録
			$this->register_prefilter('smartytemplate_prefilter');
		}
		if (OUTPUT_CODE != INTERNAL_CODE) {
			// アウトプットフィルタを登録
			$this->register_outputfilter('smartytemplate_outputfilter');
		}
		if (defined("SMARTY_DEBUGGING")) {
			if(SMARTY_DEBUGGING)
				$debugging = 1;
			else
				$debugging = 0;
		} else {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			if($session) {
				$debugging = $session->getParameter("_smarty_debug");
			} else {
				$debugging = 0;
			}
		}
		$this->debugging = $debugging;

		//$this->debug_tpl = "debug.tpl";

		//$this->assign(array('base_url' => BASE_URL));

		//
    	// timezone関数
    	//
    	//if (function_exists("timezone_date_format")) {
    	//	$this->register_function("timezone_date_format", "timezone_date_format");
    	//}

    	//
    	// get_themes_image,get_modules_image関数
    	//
    	//if (function_exists("get_themes_image")) {
    	//	$this->register_function("get_themes_image", "get_themes_image");
    	//}
    	//if (function_exists("get_modules_image")) {
    	//	$this->register_function("get_modules_image", "get_modules_image");
    	//}

		//
    	// html_to_text関数
    	//
    	//if (function_exists("html_to_text")) {
    	//	$this->register_function("html_to_text", "html_to_text");
    	//}
	}
	/**
	 * cache_lifetime設定
     *
     * @param   int
	 **/
	function setCacheLifetime($int_value)
	{
		$this->cache_lifetime = $int_value;
	}

	/**
	 * cache_lifetime取得
     *
     * @return   int
	 **/
	function getCacheLifetime()
	{
		return $this->cache_lifetime;
	}

	/**
	 * compile設定
     *
     * @param   boolean
	 **/
	function setCompile($bool_value)
	{
		$this->force_compile = $bool_value;
		$this->compile_check = $bool_value;
	}

	/**
	 * compile取得
     *
     * @return   boolean
	 **/
	function getCompile()
	{
		return $this->force_compile;
	}

	/**
	 * caching設定
     *
     * @param   boolean
	 **/
	function setCaching($bool_value)
	{
		$this->caching = $bool_value;
	}

	/**
	 * caching取得
     *
     * @return   boolean
	 **/
	function getCaching()
	{
		return $this->caching;
	}

	/**
	 * templatesDIR設定
     *
     * @param   string  $dirname
	 **/
	function setTemplateDir($dirname)
	{
		$this->template_dir = $dirname;
	}

	/**
	 * templatesDIR取得
	 *
	 * @return  string
	 **/
	function getTemplateDir()
	{
		return $this->template_dir;
	}

	/**
	 * SmartyTemplateクラスの唯一のインスタンスを返却
	 *
	 * @return	Object	SmartyTemplateクラスのインスタンス
	 * @access	public
	 **/
	function &getInstance() {
		static $instance;
		if ($instance === NULL) {
			$instance = new SmartyTemplate();
		}
		return $instance;
	}

	/**
	 * コンパイルディレクトリの中身を全て破棄する
	 *
	 * @access	public
	 **/
	function clearTemplates_c() {
		$result = $this->clear_compiled_tpl();

		if ($result) {
			//echo "Clear";
			return true;
		} else {
			//echo "NG";
			return false;
		}
		//return true;
	}

	/**
	 * テンプレートのキャッシュを破棄する
	 *
	 * @param	string	$tpl	テンプレート名
	 * @access	public
	 **/
	function clearCache($tpl = "") {
		$result = $this->clear_cache($tpl);

		if ($result) {
			//echo "Cache Clear";
			return true;
		} else {
			// "NG";
			return false;
		}

		//return true;
	}

	/**
	 * Actionをセットする
	 *
	 * @param	Object	$action	Actionのインスタンス
	 * @access	public
	 **/
	function setAction(&$action) {
		$this->register_object("action", $action);

		//
        // default_modifiersでescapeがかかっているかをチェック
        //
        $needOfEscape = true;
        foreach ($this->default_modifiers as $modifier) {
            if (preg_match('|escape|', $modifier)) {
                $needOfEscape = false;
                break;
            }
        }

        //
        // プロパティーがあるものを取得
        //
        $attributes = array();
        $util =& new BeanUtils;
        $util->toArray(get_object_vars($action), $attributes, $action, $needOfEscape);

        $this->assign('action', $attributes);

		/*$attributes = array();
		$classVars = get_class_vars(get_class($action));
		foreach ($classVars as $name => $value) {
			if (preg_match('/^_/', $name)) {
				continue;
			}

			$getter = "get" . ucfirst($name);
			if (method_exists($action, $getter)) {
				$attributes[$name] = $action->$getter();
			} else {
				$attributes[$name] = $action->$name;
			}
		}

		$this->assign('action', $attributes);*/
	}

	/**
	 * ErrorListをセットする
	 *
	 * @param	Object	$errorList	ErrorListのインスタンス
	 * @access	public
	 **/
	function setErrorList(&$errorList) {
		$this->register_object("errorList", $errorList);
	}

	/**
	 * Tokenをセットする
	 *
	 * @param	Object	$token	Tokenのインスタンス
	 * @access	public
	 **/
	function setToken(&$token) {
		$this->register_object("token", $token);
	}

	/**
	 * Sessionをセットする
	 *
	 * @param	Object	$session	Sessionのインスタンス
	 * @access	public
	 **/
	function setSession(&$session) {
		$this->register_object("session", $session);
	}

	/**
     * ScriptNameをセットする
     *
     * @param   string  $scriptName ScriptName
     * @access  public
     * @since   3.1.0
     */
    function setScriptName($scriptName)
    {
        $scriptName = htmlspecialchars($scriptName, ENT_QUOTES);
        $this->assign('scriptName', $scriptName);
    }

    /**
	 * Actionをクリアする
	 *
	 * @param	Object	$action	Actionのインスタンス
	 * @access	public
	 **/
	function clearAction() {
		$this->unregister_object("action");
		$this->clear_assign('action');
	}

	/**
	 * ErrorListをクリアする
	 *
	 * @param	Object	$errorList	ErrorListのインスタンス
	 * @access	public
	 **/
	function clearErrorList() {
		$this->unregister_object("errorList");
	}

	/**
	 * Tokenをクリアする
	 *
	 * @param	Object	$token	Tokenのインスタンス
	 * @access	public
	 **/
	function clearToken() {
		$this->unregister_object("token");
	}

	/**
     * ScriptNameをクリアする
     *
     * @param   string  $scriptName ScriptName
     * @access  public
     * @since   3.1.0
     */
    function clearScriptName()
    {
        $this->clear_assign('scriptName');
    }
}

/**
 * プリフィルタ
 **/
function smartytemplate_prefilter($source, &$Smarty) {
	return mb_convert_encoding($source, INTERNAL_CODE, TEMPLATE_CODE);
}

/**
 * ポストフィルタ
 **/
function smartytemplate_postfilter($source, &$Smarty) {
	return mb_convert_encoding($source, OUTPUT_CODE, INTERNAL_CODE);
}

/**
 * アウトプットフィルタ
 **/
function smartytemplate_outputfilter($source, &$Smarty) {
	return mb_convert_encoding($source, OUTPUT_CODE, INTERNAL_CODE);
}

function smartyTemplateCreate ($resource_type, $resource_name, &$template_source, &$template_timestamp, &$smarty_obj)
{
	//DBから取得できるようにするかも by R.Masukawa
	//現状、未定義
	//if ( $resource_type == 'db' ) {
		//DBから取得
		//$template_source = "";
		//$template_timestamp = "";
		//return true;
	//} else {
	//}
	return false;
}

function smartyTemplateCacheHandler($action, &$smarty_obj, &$cache_content, $tpl_file=null, $cache_id=null, $compile_id=null, $exp_time=null)
{
	$use_gzip = false;

    $container =& DIContainerFactory::getContainer();
    $session =& $container->getComponent("Session");
    $request =& $container->getComponent("Request");
    $filterChain =& $container->getComponent("FilterChain");

    //if(preg_match("/^pages_/",$action_name)) {
    //	$cache_key = "page_id=".$request->getParameter("page_id");
    //} else if(preg_match("/^control_/",$action_name)) {
    //	$cache_key = "";
    //} else {
    //	$cache_key = "block_id=".$request->getParameter("block_id");
    //}
    if(!isset($session)) {
		return false;
	}
	//DBオブジェクト取得
    $db =& $container->getComponent("DbObject");
    if(!is_object($db)) {
    	return false;
    }
    $adodb =& $db->getAdoDbObject();
    if(!isset($adodb)) {
    	return false;
    }

	$time = timezone_date();
	//TODO:$parameters等に「,」が含まれている場合、動作がおかしくなる可能性あり
	//要検証
	$cache_id_arr = explode(",", $cache_id);
	if(count($cache_id_arr) >= 8) {
		$block_id = $cache_id_arr[0];
		$page_id = $cache_id_arr[1];
		$_user_id = $cache_id_arr[2];
		$_auth_id = $cache_id_arr[3];
	    $_user_auth_id = $cache_id_arr[4];
	    $lang_dirname = $cache_id_arr[5];
	    $md_session_id =$cache_id_arr[6];
	    $action_name = $cache_id_arr[7];
	    $parameters = $cache_id_arr[8];
	} else {
		$block_id = $request->getParameter("block_id");
		$page_id = $request->getParameter("page_id");
		$_user_id = $session->getParameter("_user_id");
        $_auth_id = $session->getParameter("_auth_id");
   	 	$_user_auth_id = $session->getParameter("_user_auth_id");
    	if($_user_auth_id == null) {
    		$_user_auth_id = _AUTH_GUEST;
    	}
    	$md_session_id =$session->getID();//md5($session->getID());
    	$actionChain =& $container->getComponent("ActionChain");
		$action_name = $actionChain->getCurActionName();
		$lang_dirname = $session->getParameter("_lang");
		$parameters = $request->getStrParameters();
        //TODO:tokenのパラメータを削除しているが、最後にtokenがない場合消えないので、要チェック
    	$parameters = preg_replace("/&_token=.*$/", "&", $parameters);
	}
	$_mobile_flag = $session->getParameter("_mobile_flag");
	$_ssl_flag = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? _ON : _OFF;
	$_meta = $session->getParameter("_meta");
	if (isset($_meta["permalink"])) {
		$permalink = "&permalink=" . $_meta["permalink"];
	} else {
		$permalink = "";
	}

	//$actionChain =& $container->getComponent("ActionChain");
	//$action_name = $actionChain->getCurActionName();
	//$pathList = explode("_", $action_name);
	//$dir_name = $pathList[0];

	//TODO:一定時間経過したキャッシュを削除するしくみを後に追加しなければならない→一定時間はシステム管理等にもたす
	$cache = null;
	if($filterChain->hasFilterByName("Cache")) {
		$cache =& $filterChain->getFilterByName("Cache");
	}

	//キャッシュフィルターがない場合、キャッシュを使用しない
	if(!is_object($cache)) return false;

	//if()
	//nocache
	$cache_arr = null;
	$allcache_flag = false;

	$cache_expire = $cache->getCacheExpire();
	$cache_expire_time = date("YmdHis",timezone_date(null, true, "U") + $cache_expire*60);

    switch ($action) {
    	case 'read':
    	case 'write':
    		$cache_arr =& $cache->getReadCache();
    	case 'clear':
    		if(!is_array($cache_arr)){
    			$cache_arr =& $cache->getClearCache();
    		}
    		$count = 0;
    		$or_where_str = "";
    		if($action != "clear" && $tpl_file) {
    			$where_str = "WHERE tpl_file=? AND compile_id=? ";
	    		$params = array(
	    			"tpl_file" => $tpl_file,
	    			"compile_id" => $compile_id
    			);
    		} else {
    			$where_str = "WHERE 1=1 ";
	    		$params = array();
    		}
    		if($action == "read") {
    			$where_str .= "AND expire_time >=? ";
    			$params["expire_time"] = $time;
    		}
    		if($action != "clear") {
    			$where_str .= "AND _mobile_flag=? ";
    			$params['_mobile_flag'] = $_mobile_flag;	// _mobile_flag追加
				$where_str .= "AND _ssl_flag=? ";
    			$params['_ssl_flag'] = $_ssl_flag; // _ssl_flag追加
    		}
    		$or_params = array();
			if(is_array($cache_arr)) {
	    		foreach($cache_arr as $value) {
	    			$value = trim($value);
	    			$parameters_flag = false;
	    			switch ($value) {
	    			 	case "nocache":
	    			 		//キャッシュを使用しない、作成もしない
	    			 		return false;
	    			 	case "tpl_file":
	    			 		$where_value = $tpl_file;
	    			 		break;
	    			 	case "compile_id":
	    			 		$where_value = $compile_id;
	    			 		break;
	    			 	case "page_id":
	    			 		$where_value = $page_id;
	    			 		$value = "page_id";
	    			 		break;
	    			 	case "_main_page_id":
	    			 		$page_id = $session->getParameter("_main_page_id");
	    			 		$where_value = $page_id;
	    			 		$value = "page_id";
	    			 		break;
	    			 	case "_main_room_id":
	    			 		$page_id = $session->getParameter("_main_room_id");
	    			 		$where_value = $page_id;
	    			 		$value = "page_id";
					    	//page_idの削除
					    	$parameters = preg_replace("/&page_id=[0-9]+/", "&page_id=", $parameters);
					    	$permalink = "";
	    			 		break;
	    			 	case "block_id":
	    			 		$where_value = $block_id;
	    			 		break;
	    			 	case "session_id":
	    			 		$where_value = $md_session_id;
	    			 		break;
	    			 	case "_user_id":
	    			 		$where_value = $_user_id;
	    			 		break;
	    			 	case "_auth_id":
	    			 		$where_value = $_auth_id;
	    			 		break;
	    			 	case "_user_auth_id":
	    			 		$where_value = $_user_auth_id;
	    			 		break;
	    			 	case "action_name":
							$where_value = $action_name;
	    			 		break;
	    			 	case "module_dir":
	    			 		$pathList = explode("_", $action_name);
	    			 		$where_value = $pathList[0];
	    			 		break;
	    			 	case "lang_dirname":
	    			 		$where_value = $lang_dirname;
	    			 		break;
	    			 	case "parameters":
							$where_value = $parameters.$permalink;
	    			 		break;
	    			 	case "allcache":
	    			 		if($action == "clear") {
	    			 			$allcache_flag = true;
	    			 		}
	    			 		break;
	    			 	case "_myportal_room_id":
	    			 		//researchmapのみ(clearのみ)
    			 			$_self_my_page = $session->getParameter("_self_my_page");
    			 			if (!empty($_self_my_page)) {
				    			$page_id = $_self_my_page["room_id"];
    			 			}
	    			 		$where_value = $page_id;
	    			 		$value = "page_id";
					    	//page_idの削除
					    	$parameters = preg_replace("/&page_id=[0-9]+/", "&page_id=", $parameters);
					    	$permalink = "";
	    			 		break;
	    			 	default:
	    			 		if($action != "clear")
	    			 			continue;
	    			 		else {
	    			 			//module_id or module_dir or action_name
	    			 			$parameters_flag = true;
	    			 			$where_value = "?action=".$value."%";
	    			 		}
	    			}

	    			if ($value == "module_dir") {
	    				$where_str .= "AND ";
	    				$params += array(
							$value => $where_value."_%",
						);
	    				$where_str .= "action_name LIKE ? ";
	    			} elseif(!$parameters_flag) {
	    				//if($where_str == "")
		    			//	$where_str = "WHERE ";
		    			//else
		    				$where_str .= "AND ";

	    				$params += array(
							$value => $where_value,
						);
	    				$where_str .= $value . "= ? ";
	    			} else {
	    				$or_params += array(
							$value => $where_value,
						);
	    				if($or_where_str != "")
	    					$or_where_str .= " OR ";

	    				$or_where_str .= "parameters" . " LIKE ?";
	    			}
	    			$count++;
	    		}
			}
    		if($where_str == "" && $or_where_str != "")
    			$where_str = "WHERE " . $or_where_str;
    		else if($or_where_str != "")
    			$where_str = $where_str ." AND (".$or_where_str.")";

    		$params = array_merge($params, $or_params);

    		break;
	}
    switch ($action) {
    	case 'read':
            // キャッシュをデータベースから読み込む
            $result = $db->execute("SELECT cache_content FROM {smarty_cache} " .
											$where_str ,$params,null,null,false);

			if(!isset($result[0])) {
				return false;
			}
            if($use_gzip && function_exists("gzuncompress")) {
                $cache_content = gzuncompress($result[0][0]);
            } else {
                $cache_content = $result[0][0];
            }

            $return = $cache_content;
            break;
        case 'write':
            // キャッシュをデータベースに保存する
            $result = $db->execute("SELECT cache_content FROM {smarty_cache} " .
											$where_str,$params,null,null,false);
			if($use_gzip && function_exists("gzcompress")) {
                // 記憶効率のために内容を圧縮する
                $contents = gzcompress($cache_content);
            } else {
                $contents = $cache_content;
            }
			if(!isset($result[0])) {
				//INSERT
				/// キャッシュIDを作成
    			//$CacheID = md5($tpl_file.$cache_id.$compile_id);
    			//$CacheID = $db->GenID('smarty_cache_cache_id_seq');
				$params = array(
					"tpl_file" => $tpl_file,
    				"compile_id" => $compile_id,
					//"cache_id" => $CacheID,
					"block_id" => $block_id,
					"page_id" => $page_id,
					"_user_id" => $_user_id,
					"_auth_id" => $_auth_id,
					"_user_auth_id" => $_user_auth_id,
					"_mobile_flag" => $_mobile_flag,
					"_ssl_flag" => $_ssl_flag,
					"lang_dirname" => $lang_dirname,
					"session_id" => $md_session_id,
					"action_name" => $action_name,
					"parameters" => $parameters.$permalink,
					"cache_content" => $contents,
					"expire_time" => $cache_expire_time,
					"insert_time" => $time,
					"update_time" => $time
				);
				$result = $db->insertExecute("smarty_cache", $params, false);
			} else {
				//UPDATE
				$params_upd = array(
					"upd_tpl_file" => $tpl_file,
    				"upd_compile_id" => $compile_id,
					"upd_block_id" => $block_id,
					"upd_page_id" => $page_id,
					"upd_user_id" => $_user_id,
					"upd_auth_id" => $_auth_id,
					"upd_user_auth_id" => $_user_auth_id,
					"upd_mobile_flag" => $_mobile_flag,
					"upd_ssl_flag" => $_ssl_flag,
					"upd_lang_dirname" => $lang_dirname,
					"upd_session_id" => $md_session_id,
					"upd_action_name" => $action_name,
					"upd_parameters" => $parameters.$permalink,
					"upd_cache_content" => $contents,
					"upd_expire_time" => $cache_expire_time,
					"upd_update_time" => $time,
				);
				$params = array_merge($params_upd, $params);
				$sql = "UPDATE {smarty_cache} SET ".
						"tpl_file=?,".
						"compile_id=?,".
						"block_id=?,".
						"page_id=?,".
						"_user_id=?,".
						"_auth_id=?,".
						"_user_auth_id=?,".
						"_mobile_flag=?,".
						"_ssl_flag=?,".
						"lang_dirname=?,".
						"session_id=?,".
						"action_name=?,".
						"parameters=?,".
						"cache_content=?,".
						"expire_time=?,".
						"update_time=? ".
					$where_str;
				$result = $db->execute($sql,$params);
			}
            if(!$result) {
            	return false;
                //$smarty_obj->_trigger_error_msg("cache_handler: query failed.");
            }
            $return = $result;
            break;
        case 'clear':
            // キャッシュ情報を破棄する
            //if(empty($cache_id) && empty($compile_id) && empty($tpl_file)) {
            if($allcache_flag) {
                // 全てのキャッシュを破棄する
                $result = $db->execute("DELETE FROM {smarty_cache}");
            } else {
            	$renderer =& SmartyTemplate::getInstance();
            	if($renderer->cache_lifetime != -1 && rand(1, 10) == 1) {
            		//10回に一度の確立で古いキャッシュの削除処理を動かす
            		$time = date("YmdHis",timezone_date(null, true, "U") - $renderer->cache_lifetime);

            		$result = $db->execute("DELETE FROM {smarty_cache} " .
						$where_str." OR update_time <'".$time."' ",$params);
            	} else {
                	$result = $db->execute("DELETE FROM {smarty_cache} " .
						$where_str,$params);
            	}
            }

			if(!$result) {
				return false;
                //$smarty_obj->_trigger_error_msg("cache_handler: query failed.");
            }
            $return = $result;
            break;
        default:
            // エラー・未知の動作
            //$smarty_obj->_trigger_error_msg("cache_handler: unknown action \"$action\"");
            $return = false;
            break;
    }

    return $return;

}

?>
