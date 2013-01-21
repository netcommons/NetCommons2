<?php

//require_once MAPLE_DIR.'/nccore/GetData.class.php';
//require_once MAPLE_DIR.'/nccore/SessionExtra.class.php';

/**
 * Configファイルの設定を行うFilter
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_SetConfig extends Filter {

    var $_container;

    var $_log;

    var $_filterChain;

    var $_actionChain;

    var $_request;

    var $_response;

    var $_session;

    var $_className;

    var $_errorList;

    var $_languagesView;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Filter_SetConfig() {
		parent::Filter();
	}

	/**
	 * Configファイルの設定を行うFilter
	 *
	 * @access	public
	 **/
	function execute() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_log =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_request =& $this->_container->getComponent("Request");
        $this->_response =& $this->_container->getComponent("Response");
        $this->_session =& $this->_container->getComponent("Session");
		$this->_languagesView =& $this->_container->getComponent('languagesView');
		if (empty($this->_languagesView)) {
			$common =& $this->_container->getComponent('commonMain');
			$this->_languagesView =& $common->registerClass(COMPONENT_DIR.'/languages/View.class.php', 'Languages_View', 'languagesView');
		}
        $this->_errorList =& $this->_actionChain->getCurErrorList();
        $this->_className = get_class($this);

    	$this->_prefilter();

        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");


        $this->_filterChain->execute();

        $this->_postfilter();
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}

	/**
     * プレフィルタ
     * 初期処理を行う
     * @access private
     */
    function _prefilter()
    {
    	// NetCommonsバージョンファイル読み込み
    	if (@file_exists(WEBAPP_DIR . "/config/version.php")) {
    		include_once WEBAPP_DIR . "/config/version.php";
        }

    	$page_id = $this->_request->getParameter("page_id");
    	$block_id = $this->_request->getParameter("block_id");
    	$_redirect_url =  $this->_request->getParameter("_redirect_url");
    	$action_name = $this->_request->getParameter(ACTION_KEY);
    	$attributes = $this->getAttributes();

    	if(!$action_name) {
    		$action_name = DEFAULT_ACTION;
    		$this->_request->setParameter(ACTION_KEY,DEFAULT_ACTION);
    	}

    	// http -> https
		if ($action_name == DEFAULT_ACTION && ( !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on')
			&& preg_match("/^https:\/\//i", BASE_URL) ) {
			$url = 'https://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ;
			header( 'Location: '.$url );
			exit();
		}

    	//
    	//block_id page_id設定
    	//
    	if($page_id == null) {
    		$page_id = 0;
    		$this->_request->setParameter("page_id",0);
    	}
    	if($block_id == null || $block_id < 0) {
    		$block_id = 0;
    		$this->_request->setParameter("block_id",0);
    	}

    	if($_redirect_url) {
    		$_redirect_url =  str_replace("@@","#",$_redirect_url);
    		$_redirect_url =  str_replace("@","&",$_redirect_url);
    		$_redirect_url =  str_replace("?_sub_action=","?action=",$_redirect_url);
    		$this->_request->setParameter("_redirect_url",$_redirect_url);
    	}

    	// ----------------------------------------------
		// --- getDataクラス取得     				  ---
		// ----------------------------------------------
    	//$getdata =& new GetData;
    	//$this->_container->register($getdata, "GetData");
    	$getdata =& $this->_container->getComponent("GetData");

		// ----------------------------------------------
		// --- configデータ取得     				  ---
		// ----------------------------------------------
		$config =& $this->_container->getComponent("configView");
	    $config_obj =& $config->getConfig(_SYS_CONF_MODID);

		//
		// _PAGESTYLE_CONF_CATID,_SECURITY_CONF_CATIDのみ保存
		//
		$buf_config = $config_obj;
		//unset($buf_config[_GENERAL_CONF_CATID]);
		unset($buf_config[_SERVER_CONF_CATID]);
		unset($buf_config[_MAIL_CONF_CATID]);
		unset($buf_config[_META_CONF_CATID]);
		unset($buf_config[_ENTER_EXIT_CONF_CATID]);
		unset($buf_config[_DEBUG_CONF_CATID]);
		//if(isset($buf_config[_SECURITY_CONF_CATID]['security_level']) &&
		//	$buf_config[_SECURITY_CONF_CATID]['security_level']['conf_value'] == _SECURITY_LEVEL_NONE) {
		//	// チェックしない場合、セキュリティ項目unset
		//	unset($buf_config[_SECURITY_CONF_CATID]);
		//}
		$getdata->setParameter("config", $buf_config);

	    // ------------------------------------------------------------------------------------------
		// --- Session情報           				                                              ---
		// --- configデータよりSession情報を登録するため、Filter.Sessionではsession_start()しない ---
		// ------------------------------------------------------------------------------------------
	    if ($config_obj[_SERVER_CONF_CATID]['use_mysession']['conf_value'] && $config_obj[_SERVER_CONF_CATID]['session_name']['conf_value'] != '') {
            $this->_session->setName($config_obj[_SERVER_CONF_CATID]['session_name']['conf_value']);
        }

        //1.1と同様の処理：後に削除
	    //$sslpost_name = $this->_request->getParameter($config_obj[_GENERAL_CONF_CATID]['sslpost_name']['conf_value']);
		//if ($config_obj[_GENERAL_CONF_CATID]['use_ssl']['conf_value'] &&
		//	isset($sslpost_name) && $sslpost_name != '') {
		//	session_id($sslpost_name);
		//} else if ($config_obj[_SERVER_CONF_CATID]['use_mysession']['conf_value'] &&
		//	$config_obj[_SERVER_CONF_CATID]['session_name']['conf_value'] != '') {
		//	if (isset($_COOKIE[$config_obj[_SERVER_CONF_CATID]['session_name']['conf_value']])) {
		//		session_id($_COOKIE[$config_obj[_SERVER_CONF_CATID]['session_name']['conf_value']]);
		//	} else {
		//		// no custom session cookie set, destroy session if any
		//		$this->_session->close();
		//	}
		//}

		//if(isset($config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'])){
			ini_set('session.gc_maxlifetime', $config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'] * 60);
		//}
		//-------------------------------------------------------------------------------
		// ---Cookieパラメータセット                                                  ---
		//-------------------------------------------------------------------------------
		$path = $config_obj[_SERVER_CONF_CATID]['cookie_path']['conf_value'];
		if($path == "") {
			$path = "/";
			$pathList = explode("_", $action_name);
			if(!(isset($pathList[0]) && $pathList[0] == "install")) {
				$path = strstr(preg_replace("/^(http:\/\/|https:\/\/)/i","", BASE_URL), "/");
				if($path === false) $path = '/';
				else {
					$buf_path = preg_replace('/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/').'/i', "", BASE_DIR);
					if($buf_path != BASE_DIR && $buf_path != "") {
						if(strstr($path, $buf_path)) {
							$path = (substr($buf_path, 0,1) == '/') ? $buf_path : "/".$buf_path;
						}
					}

				}
			}
			//$path = strstr(preg_replace("/^(http:\/\/|https:\/\/)/i","", BASE_URL), "/");
		}
		$domain = $config_obj[_SERVER_CONF_CATID]['cookie_domain']['conf_value'];
		$secure = intval($config_obj[_SERVER_CONF_CATID]['cookie_secure']['conf_value']);

		session_set_cookie_params(0 , $path,  $domain,  $secure);
		//session_set_cookie_params($config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'] * 60, $path,  $domain,  $secure);
		//ini_set("session.cookie_path", $path);
		//ini_set("session.cookie_domain", $domain);
		//ini_set("session.cookie_secure", $secure);
		//ini_set("session.cookie_lifetime", $config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'] * 60);
		//setcookie($this->_session->getName(), $this->_session->getID(), time()+($config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'] * 60));

		// ----------------------------------------------
		// ---  セッションスタート           		  ---
		// ----------------------------------------------
		if (Net_UserAgent_Mobile::isMobile()) {
			ini_set('session.use_only_cookies', _OFF);
		}
		if(isset($attributes["regenerate_flag"]) && $attributes["regenerate_flag"]==_OFF) {
			$this->_session->start(_OFF);
		} else {
			$this->_session->start(intval($config_obj[_SERVER_CONF_CATID]['session_regenerate']['conf_value']));
		}
		$user_id = $this->_session->getParameter("_user_id");
		if(!isset($user_id)) {
    		$user_id = "0";
			$this->_session->setParameter("_user_id", "0");
			$this->_session->setParameter("_handle",'');
		}

		// ----------------------------------------------
		// ---  固定リンク　　　　           		  ---
		// ----------------------------------------------
		if(isset($config_obj[_SERVER_CONF_CATID]['use_permalink']['conf_value']) && $config_obj[_SERVER_CONF_CATID]['use_permalink']['conf_value'] == _ON) {
			$this->_session->setParameter("_permalink_flag", _ON);
		} else {
			$this->_session->setParameter("_permalink_flag", _OFF);
		}

		// 言語セット
		$_lang = $this->_request->getParameter('lang');
		if(!empty($_lang)) {
			$languages = $this->_languagesView->getLanguages(array("lang_dirname"=>$_lang));
			if (!isset($languages[0])) {
				$_lang = null;
			}else {
				$this->_session->setParameter('_lang', $_lang);
			}
		}
		if(empty($_lang)) {
			$_lang = $this->_session->getParameter('_lang');
			if(empty($_lang)) {
				//システム管理のシステム標準使用言語の「自動」で選択している場合、自動で判断する。
				if(empty($config_obj[_GENERAL_CONF_CATID]['language']['conf_value'])) {
					$this->_session->setParameter('_lang', $this->_getAcceptLang());
				} else {
					$this->_session->setParameter('_lang', $config_obj[_GENERAL_CONF_CATID]['language']['conf_value']);
				}
			}
		}
    	if($action_name == "pages_view_main") {
	    	$header_menu_flag = _ON;
	    	if(isset($config_obj[_PAGESTYLE_CONF_CATID]['header_menu_flag']['conf_value'])) {
	    		$header_menu_flag = $config_obj[_PAGESTYLE_CONF_CATID]['header_menu_flag']['conf_value'];
	    	}
	    	$this->_session->setParameter("_header_menu_flag", $header_menu_flag);
    	}
		// ----------------------------------------------
		// --- 自動ログイン設定           		      ---
		// ----------------------------------------------
		$autologin_login_cookie_name = $config_obj[_GENERAL_CONF_CATID]['autologin_login_cookie_name']['conf_value'];
		$autologin_pass_cookie_name = $config_obj[_GENERAL_CONF_CATID]['autologin_pass_cookie_name']['conf_value'];
		if(($user_id == "0" || $user_id == null) && $autologin_login_cookie_name != "" && $autologin_pass_cookie_name != "") {
    		$login_id = isset($_COOKIE[$autologin_login_cookie_name]) ? $_COOKIE[$autologin_login_cookie_name] : null;		//TODO:stripslashesする必要があるかも
			$pass = isset($_COOKIE[$autologin_pass_cookie_name]) ? $_COOKIE[$autologin_pass_cookie_name] : null;			//TODO:stripslashesする必要があるかも

			if( empty($login_id) || empty($pass) || is_numeric($pass) ) $html = "error" ;
			else {
				if($config_obj[_GENERAL_CONF_CATID]['autologin_use']['conf_value'] == _AUTOLOGIN_OK) {
					$params = array(
										"action" =>"login_action_main_init",
										"login_id" =>$login_id,
										"password" =>$pass,
										"md5" =>"1",
										"_header" =>"0",
										"_output" =>"0"
										);
					if($config_obj[_GENERAL_CONF_CATID]['autologin_use']['conf_value'] == _AUTOLOGIN_OK) {
						$params['rememberme'] = _OFF;
					}
					$preexecuteMain =& $this->_container->getComponent("preexecuteMain");
					$html = $preexecuteMain->preExecute("login_action_main_init", $params, true);
					$user_id = $this->_session->getParameter("_user_id");
				} else {
					$html = "error";
				}
				//$usersView =& $this->_container->getComponent("usersView");
				//$user =& $usersView->getUsers(array("login_id" => $login_id,"password" => $pass));
			}
			//$cookie_path = '/' ;
			if ($html == "error") {
				if($config_obj[_GENERAL_CONF_CATID]['autologin_use']['conf_value'] == _AUTOLOGIN_NO) {
					setcookie($autologin_login_cookie_name, '', time() - 3600, $path, $domain, $secure);
				}
				setcookie($autologin_pass_cookie_name, '', time() - 3600, $path, $domain, $secure);
			}

		}
    	$user_auth_id = $this->_session->getParameter("_user_auth_id");
    	if($user_auth_id == _AUTH_OTHER || $user_auth_id == null) {
    		//ログイン前
    		$this->_session->setParameter("_allow_attachment_flag", _ALLOW_ATTACHMENT_NO);
    		$this->_session->setParameter("_allow_htmltag_flag", _ALLOW_ATTACHMENT_NO);
    		$this->_session->setParameter("_allow_video_flag", _OFF);
    		$this->_session->setParameter("_allow_layout_flag", _OFF);
    		$this->_session->setParameter("_private_max_size", -1);
    	}

		// セッション情報をクッキーに保存する
		//if ($user_id > 0 && $config_obj[_SERVER_CONF_CATID]['use_mysession']['conf_value'] &&
		//	$config_obj[_SERVER_CONF_CATID]['session_name']['conf_value'] != '') {
		//	setcookie($config_obj[_SERVER_CONF_CATID]['session_name']['conf_value'], session_id(), time()+(60 * $config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value']), '/',  '', 0);
		//}

		//
		// config設定
		//
		if($action_name == "encryption_view_publickey" || $action_name == "headerinc_view_main") {
	    	//公開鍵取得アクション,またはヘッダー取得処理なのでチェックしない
	    	return;
	    /*
    	} else if($_redirect_url && !preg_match("{^".BASE_URL."}", $_redirect_url)) {
    		//
    		// 他サーバ
    		//
    		// TODO:他サーバ処理は現状、未実装のためコメント
    		// 他サーバからの場合、DBの値に関わらずDEBUGはOFF
    		if (isset($attributes["debug"])) {
    			$this->_session->setParameter("_php_debug",0);
	    		$this->_session->setParameter("_sql_debug",0);
	    		$this->_session->setParameter("_smarty_debug",0);
	    		$this->_session->setParameter("_maple_debug",0);
	    		$this->_session->setParameter("_trace_log_level",LEVEL_TRACE);
    		}

    		//データセット
    		//$getdata->setParameter("mysite",false);
    	*/
	    } else {
	    	//
    		// 自サーバ
    		//
	    	//
	    	// ----------------------------------------------
			// ---デバッグ関連　　　           		      ---
			// ----------------------------------------------
	    	if (isset($attributes["debug"])) {
	    		// DBから取得
	    		foreach($config_obj[_DEBUG_CONF_CATID] as $conf_rec) {
    				switch ($conf_rec["conf_name"]) {
    					case "php_debug":
    						$php_debug = $conf_rec["conf_value"];
    						break;
    					case "sql_debug":
    						$sql_debug = $conf_rec["conf_value"];
    						break;
    					case "smarty_debug":
    						$smarty_debug = $conf_rec["conf_value"];
    						break;
    					case "maple_debug":
    						$maple_debug = $conf_rec["conf_value"];
    						break;
    					case "trace_log_level":
    						$trace_log_level = $conf_rec["conf_value"];
    						break;
    					case "use_db_debug":
    						$use_db_debug = $conf_rec["conf_value"];
    						break;
    				}
    			}
	    		if($use_db_debug) {
	    			$this->_session->setParameter("_php_debug",$php_debug);
					if ($php_debug
						&& version_compare(phpversion(), '5.3.0', '>=')) {
						error_reporting(E_ALL ^ E_DEPRECATED);
					} elseif ($php_debug) {
						error_reporting(E_ALL);
					} else {
						error_reporting(0);
					}
					$this->_session->setParameter("_sql_debug",$sql_debug);
					$this->_session->setParameter("_smarty_debug",$smarty_debug);
					$this->_session->setParameter("_maple_debug",$maple_debug);
					$this->_session->setParameter("_trace_log_level",$trace_log_level);
					$this->_session->setParameter("_use_db_debug",$use_db_debug);
	    		}
	    	}

    		//データセット
    		//$getdata->setParameter("mysite",true);

	    }

	    //debugモード
		if (isset($attributes["debug"])) {
			$sql_debug = $this->_session->getParameter("_sql_debug");
			$db =& $this->_container->getComponent("DbObject");
			$db->setDebugMode($sql_debug);
		}

		// ----------------------------------------------
		// ---タイムゾーン　　　           		      ---
		// ----------------------------------------------
		if(isset($config_obj[_GENERAL_CONF_CATID]['server_TZ'])) {
			$this->_session->setParameter("_server_TZ",$config_obj[_GENERAL_CONF_CATID]['server_TZ']['conf_value']);
		} else {
			// 日本時間
			$this->_session->setParameter("_server_TZ", 9);
		}
		if(isset($config_obj[_GENERAL_CONF_CATID]['default_TZ'])) {
			$this->_session->setParameter("_default_TZ",$config_obj[_GENERAL_CONF_CATID]['default_TZ']['conf_value']);
		} else {
			// 日本時間
			$this->_session->setParameter("_default_TZ", 9);
		}

		if(($user_id == "0" || $user_id == null)) {
			$this->_session->setParameter("_timezone_offset", $this->_session->getParameter("_default_TZ"));
		}
		// main_action_name
		$this->_session->setParameter('_main_action_name', $action_name);

		$this->_session->setParameter('_session_gc_maxlifetime', $config_obj[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value']);

    	// ----------------------------------------------
		// ---metaタグ設定　　　                      ---
		// ----------------------------------------------
		$sitename = $config->getConfigByConfname(_SYS_CONF_MODID, 'sitename');
		$language = $this->_languagesView->getLanguages(array("lang_dirname"=>$this->_session->getParameter('_lang')));
    	$meta = array(
    		'sitename'=>$sitename['conf_value'],
			'meta_language'=>$language[0]['language'],
			'meta_robots'=>$config_obj[_META_CONF_CATID]['meta_robots']['conf_value'],
			'meta_keywords'=>$config_obj[_META_CONF_CATID]['meta_keywords']['conf_value'],
			'meta_description'=>$config_obj[_META_CONF_CATID]['meta_description']['conf_value'],
			'meta_rating'=>$config_obj[_META_CONF_CATID]['meta_rating']['conf_value'],
			'meta_author'=>$config_obj[_META_CONF_CATID]['meta_author']['conf_value'],
			'meta_copyright'=>$config_obj[_META_CONF_CATID]['meta_copyright']['conf_value'],
			'meta_footer'=>$config_obj[_META_CONF_CATID]['meta_footer']['conf_value']
		);

		//
    	// footer初期化
    	//
    	$footer_field = array(
			'template_footer'=>"",
			'script_footer'=>""
    	);

		// データセット
    	$this->_session->setParameter("_meta",$meta);

    	// プライベートスペースを公開するかどうか
    	$this->_session->setParameter("_open_private_space", $config_obj[_GENERAL_CONF_CATID]['open_private_space']['conf_value']);

    	// デフォルト参加ロール権限
    	$_default_entry_role_auth_public = $config_obj[_GENERAL_CONF_CATID]['default_entry_role_auth_public']['conf_value'];
    	$_default_entry_role_auth_group = $config_obj[_GENERAL_CONF_CATID]['default_entry_role_auth_group']['conf_value'];
    	//プライベートスペースを公開した場合、使用
    	if($config_obj[_GENERAL_CONF_CATID]['open_private_space']['conf_value'] != _OFF) {
    		$_default_entry_role_auth_private = $config_obj[_GENERAL_CONF_CATID]['default_entry_role_auth_private']['conf_value'];
    	} else {
    		$_default_entry_role_auth_private = _ROLE_AUTH_OTHER;
    	}
    	//
    	// デフォルトで参加する場合のベース権限と権限の階層をセッションにセット
    	//
    	// システム管理のデフォルトで参加するルームでのロール権限の設定を一般、あるいはゲストのみとしてdefine値をセット
    	// システム管理でモデレータのロール権限を設定させる場合、authoritiesテーブルからuser_authority_id,hierarchyを
    	// 取得しなければならない（実行時間の節約のため行わない）。
    	//
    	if($_default_entry_role_auth_public == _ROLE_AUTH_GENERAL) {
    		$this->_session->setParameter("_default_entry_auth_public", _AUTH_GENERAL);
    		$this->_session->setParameter("_default_entry_hierarchy_public", _HIERARCHY_GENERAL);
    	} else {
    		$this->_session->setParameter("_default_entry_auth_public", _AUTH_GUEST);
    		$this->_session->setParameter("_default_entry_hierarchy_public", _HIERARCHY_GUEST);
    	}
    	if($_default_entry_role_auth_group == _ROLE_AUTH_GENERAL) {
    		$this->_session->setParameter("_default_entry_auth_group", _AUTH_GENERAL);
    		$this->_session->setParameter("_default_entry_hierarchy_group", _HIERARCHY_GENERAL);
    	} else {
    		$this->_session->setParameter("_default_entry_auth_group", _AUTH_GUEST);
    		$this->_session->setParameter("_default_entry_hierarchy_group", _HIERARCHY_GUEST);
    	}
    	if($_default_entry_role_auth_private == _ROLE_AUTH_GENERAL) {
    		$this->_session->setParameter("_default_entry_auth_private", _AUTH_GENERAL);
    		$this->_session->setParameter("_default_entry_hierarchy_private", _HIERARCHY_GENERAL);
    	} else if($_default_entry_role_auth_private == _ROLE_AUTH_OTHER){
    		$this->_session->setParameter("_default_entry_auth_private", _AUTH_OTHER);
    		$this->_session->setParameter("_default_entry_hierarchy_private", _HIERARCHY_OTHER);
    	} else {
    		$this->_session->setParameter("_default_entry_auth_private", _AUTH_GUEST);
    		$this->_session->setParameter("_default_entry_hierarchy_private", _HIERARCHY_GUEST);
    	}
    	/*
    	$this->_session->setParameter("_default_entry_auth_public", $config_obj[_GENERAL_CONF_CATID]['default_entry_auth_public']['conf_value']);
    	$this->_session->setParameter("_default_entry_auth_group", $config_obj[_GENERAL_CONF_CATID]['default_entry_auth_group']['conf_value']);
    	//プライベートスペースを公開した場合、使用
    	if($config_obj[_GENERAL_CONF_CATID]['open_private_space']['conf_value'] != _OFF) {
    		$this->_session->setParameter("_default_entry_auth_private", $config_obj[_GENERAL_CONF_CATID]['default_entry_auth_private']['conf_value']);
    	} else {
    		$this->_session->setParameter("_default_entry_auth_private", _ROLE_AUTH_OTHER);
    	}
    	*/

    	// gzip_compression
    	$this->_session->setParameter("_gzip_compression", $config_obj[_DEBUG_CONF_CATID]['gzip_compression']['conf_value']);

    	// ----------------------------------------------
		// ---Smarty関連設定　　　                    ---
		// ----------------------------------------------
    	$renderer =& SmartyTemplate::getInstance();
    	if(array_key_exists('smarty_caching',$config_obj[_GENERAL_CONF_CATID])) {
    		if($config_obj[_GENERAL_CONF_CATID]['smarty_caching']['conf_value']=="true")
    			$smarty_caching = 2;
    		else
    			$smarty_caching = 0;

    		$renderer->setCaching($smarty_caching);
    	}
    	if(array_key_exists('smarty_force_compile',$config_obj[_GENERAL_CONF_CATID])) {
    		if($config_obj[_GENERAL_CONF_CATID]['smarty_force_compile']['conf_value']=="true")
    			$smarty_force_compile = true;
    		else
    			$smarty_force_compile = false;
    		$renderer->setCompile($smarty_force_compile);
    	}

    	if(array_key_exists('smarty_lifetime',$config_obj[_GENERAL_CONF_CATID])) {
    		$renderer->setCacheLifetime(intval($config_obj[_GENERAL_CONF_CATID]['smarty_lifetime']['conf_value']));
    	}

		// ----------------------------------------------
		// ---メモリ最大サイズ設定　　　              ---
		// ----------------------------------------------
		ini_set('memory_limit',$config_obj[_SERVER_CONF_CATID]['memory_limit']['conf_value']);

		// ----------------------------------------------
		// ---改行コードの判別有無の設定　　　        ---
		// ----------------------------------------------
		ini_set('auto_detect_line_endings', _ON);

		//UTF-8
		// 直接指定：safariが文字化けするため
		$this->_response->setContentType("text/html; charset=utf-8");

		// ----------------------------------------------
		// ---サイトIDセット　　　                    ---
		// ----------------------------------------------
		$_site_id = $this->_session->getParameter("_site_id");
		if(!isset($_site_id) || $_site_id == 0) {
			$sitesView =& $this->_container->getComponent("sitesView");
			$site = $sitesView->getSelfSite();
			if(isset($site['site_id'])) {
				$this->_session->setParameter("_site_id", $site['site_id']);
			}
		}

		// ----------------------------------------------
		// ---サイトClose　　　                      ---
		// ----------------------------------------------
		$this->_session->setParameter("_closesite", _OFF);
		if($config_obj[_GENERAL_CONF_CATID]['closesite']['conf_value'] == _ON) {
			$this->_session->setParameter("_closesite", _ON);
			$_lang = $this->_session->getParameter("_lang");
			if($user_id != "0" && $user_auth_id != _AUTH_ADMIN) {
				// 強制ログアウト
				$this->_session->close();
			    $user_id = "0";
			}
			if($user_id == "0") {
				$this->_session->setParameter("_lang", $_lang);
				$allow_action_name_arr = explode("|", _CLOSESITE_ALLOW_ACTION);
				if(!in_array($action_name, $allow_action_name_arr)) {
					// サイト閉鎖画面表示
					$preexecute =& $this->_container->getComponent("preexecuteMain", array(), false, $this->_className);
					$preexecute->preExecute("pages_view_closesite");
					exit;
				}
			}
		}
    }

	/**
     * ポストフィルタ
     * @access private
     */
    function _postfilter()
    {
    }

	/**
     * ブラウザの指定言語を取得
     * @access private
     */
	function _getAcceptLang()
	{
		$used_language = null;
		$lang_arr = array();
		$maximal_num = 0;
		$languages = $this->_languagesView->getLanguages();
		foreach($languages as $language) {
			$lang_arr[$language['language']] = $language['lang_dirname'];
		}
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			foreach (explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $value) {
				$pri = explode(";", trim($value));
				$num = (isset($pri[1])) ? (float) preg_replace("/^q=/", "", $pri[1]) : 1;
				if($num > $maximal_num) {
					if(array_key_exists($pri[0], $lang_arr)) {
						$maximal_num = $num;
						$used_language = $lang_arr[$pri[0]];
					}else if(strpos($pri[0], '-') != false) {
						$pri_key = substr($pri[0], 0, strpos($pri[0], '-'));
						if(array_key_exists($pri_key , $lang_arr)) {
							$maximal_num = $num;
							$used_language = $lang_arr[$pri_key];
						}
					}
				}
			}
		}
		if(!$used_language) {
			$used_language = $languages[0]['lang_dirname'];
		}
		return $used_language ;
	}
}
?>
