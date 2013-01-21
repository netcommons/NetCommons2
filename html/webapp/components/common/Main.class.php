<?php
 /**
 * Commonクラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Main {
	var $_className = "Common_Main";

	/**
	 * ディレクトリ再帰削除関数
	 *
	 * @param  string	$class_path
	 * @param  string	$class_name
	 * @param  string	$regist_name
	 * @return	object
	 **/
	function &registerClass($class_path, $class_name, $regist_name) {
		$container =& DIContainerFactory::getContainer();
		$object =& $container->getComponent($regist_name);
        if(!isset($object)) {
        	require_once $class_path;
        	$object =& new $class_name;
        	$container->register($object, $regist_name);
        }
        return $object;
	}

	/**
	 * 文字列"XXXX>=(<=,==,<,>)YYYYY"を実行した結果を返す
	 * @param string session_name==(== or >= or <= or > or <)constant
	 * @param int page_id
	 * @param int user_id
	 * @param string default_key
	 * @return	boolean
	 * @access	private
	 **/
	function isResultByOperatorString($cond_str, $page_id = null, $user_id = null, $default_key = "_auth_id")
	{
		$re_cond_str2 = '/([^=!<>]*)([=!<>]+)(.*)/i';
		$matches = array();
		preg_match($re_cond_str2,$cond_str,$matches);
		if(isset($matches[2])) {
			list($key, $value) = $this->_isResultByOperatorStringSub($matches[1], $matches[3], $default_key, $page_id, $user_id);
			switch($matches[2]) {
				case "<=":
					if($key <= $value) return true;
					break;
				case ">=":
					if($key >= $value) return true;
					break;
				case "<":
					if($key < $value) return true;
					break;
				case ">":
					if($key > $value) return true;
					break;
				case "===":
					if($key === $value) return true;
					break;
				case "!==":
					if($key !== $value) return true;
					break;
				case "!=":
					if($key != $value) return true;
					break;
				case "==":
					if($key == $value) return true;
					break;
				case "<>":
					if($key <> $value) return true;
					break;
			}
			//eval("if (".$key.$matches[1].$value.") { return true; }");
		}
		return false;
	}
	function _isResultByOperatorStringSub($key, $value, $default_key, $page_id, $user_id)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$cond_key = (!isset($key) || $key == "") ? $default_key : $key;
		if(isset($page_id) && $cond_key == "_auth_id") {
			$authCheck =& $container->getComponent("authCheck");
			//page_id指定
			if(!isset($user_id)) $user_id = $session->getParameter("_user_id");
			$key = $authCheck->getPageAuthId($user_id, $page_id);
		} else {
			$key = $session->getParameter($cond_key);
		}
		$key = intval($key);
		if (defined($value)) $value = constant($value);
		return array($key, $value);
	}
	/**
	 * ブロック表示時のSmartyキャッシュIDを取得する
	 * @return	string
	 * @access	public
	 **/
	function getCacheid() {
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$request =& $container->getComponent("Request");
		//$actionChain =& $container->getComponent("ActionChain");

		if(!isset($session)) {
			return false;
		}
		//
        //cache_idセット
        //
        $block_id = $request->getParameter("block_id");
        //$_ref_block_id = $request->getParameter("_ref_block_id");
		//if($_ref_block_id) {
		//	$block_id = $_ref_block_id;
		//}
        $page_id = $request->getParameter("page_id");
        $_user_id = $session->getParameter("_user_id");
        $_auth_id = $session->getParameter("_auth_id");
   	 	$_user_auth_id = $session->getParameter("_user_auth_id");
    	if($_user_auth_id == null) {
    		$_user_auth_id = _AUTH_GUEST;
    	}
    	$md_session_id =$session->getID();
        $parameters = $request->getStrParameters();
        //TODO:tokenのパラメータを削除しているが、最後にtokenがない場合消えないので、要チェック
    	$parameters = preg_replace("/&_token=.*$/", "", $parameters);
    	//$action_name = $actionChain->getCurActionName();
    	$action_name = $request->getParameter(ACTION_KEY);
        $lang_dirname = $session->getParameter("_lang");
    	
    	$cache_id = $block_id.",".$page_id.",".$_user_id.",".$_auth_id.",".$_user_auth_id.",".$lang_dirname.",".$md_session_id.",".$action_name.",".$parameters;

 		return $cache_id;
	}

	/**
     * Viewフィルタ共通Assign処理
     * @param object $renderer(Smarty object),object log(log object)
     * @return array(id,page_id,theme_name,temp_name,min_width_size,url)
     * @access private
     */
	function viewAssign(&$renderer) {
		//初期化
		$id = 0;
		$page_id = 0;
		$themeStr = "";
		$tempStr = "default";
		$min_width_size = 0;
		$url_htmlspecialchars = "";

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$getdata =& $container->getComponent("GetData");
		$session =& $container->getComponent("Session");
		$request =& $container->getComponent("Request");
		$pagesView =& $container->getComponent("pagesView");

		//アクション名よりテンプレートパスを取得
		$action_name = $actionChain->getCurActionName();
		$pathList = explode("_", $action_name);
		$block_id = intval($request->getParameter("block_id"));

		$module_id = ($request->getParameter("module_id")) ? $request->getParameter("module_id") : 0;
		//if ($block_id == 0) {
			//ブロックIDが取得できなくとも、管理モジュールならば表示可能
			$modules =& $getdata->getParameter("modules");
			if(isset($modules[$pathList[0]])) {
				$module_id = $modules[$pathList[0]]['module_id'];
				//$request->setParameter("module_id",$module_id);
			}
		//}
		//
		//idセット
		//
		$renderer->assign('id',$session->getParameter("_id"));

		$modules = $getdata->getParameter("modules");
		if(isset($modules[$pathList[0]])) {
			$module_id = $modules[$pathList[0]]['module_id'];
			$renderer->assign('module_obj',$modules[$pathList[0]]);
			$renderer->assign('module_id',$modules[$pathList[0]]['module_id']);
		} else {
			$module_id = 0;
			$renderer->assign('module_obj',"");
			$renderer->assign('module_id',0);
		}

		if($pathList[0] == "dialog" && !$request->getParameter("inside_flag")) {
			//共通ダイアログ
			$dialog_flag = true;
		} else if ($action_name == "login_view_main_init" && $block_id == 0) {
			$dialog_flag = true;
		} else {
			$dialog_flag = false;
		}
		$page_id = 0;
		if($session->getParameter("_system_flag")) {
			if(isset($modules[$pathList[0]]["action_name"])) {
				$themeStr = "system";	//$modules[$pathList[0]]['theme_name'];
				$tempStr = $modules[$pathList[0]]['temp_name'];
				$min_width_size = intval($modules[$pathList[0]]['min_width_size']);
			} elseif($dialog_flag) {
				//共通ダイアログ:固定値
				$themeStr = "system";
				$tempStr = "default";
				$min_width_size = 0;
			} else {
				//$log->error("モジュールオブジェクトの取得に失敗しました", "{$this->_className}#viewAssign");
				return false;
			}
			//管理系にもブロックIDを付与
			$renderer->assign('block_id',$block_id);

			$renderer->assign('page_id',0);
			$renderer->assign('display_position',0);

		} else {
			if($dialog_flag) {
				//共通ダイアログ:固定値
				$themeStr = "system";
				$tempStr = "default";
				$min_width_size = 0;
			} else if($block_id != 0) {
				$pages = $getdata->getParameter("pages");
				$blocks = $getdata->getParameter("blocks");
				if(!isset($blocks[$block_id])) {
					$block =& $container->getComponent("blocksView");
					$block_obj = $block->getBlockById($block_id);
				} else {
					$block_obj =& $blocks[$block_id];
				}
				$page_id = $session->getParameter("_main_page_id");//$block_obj['page_id'];
				if($page_id == null) {
					$page_id = $block_obj['page_id'];
				}
				//ブロックオブジェクトが取得できなければ、エラー
				//アクション名称とURLのアクションが同じでない場合、共通ダイアログでなければエラー
				if(!$block_obj) {
					//$log->error("ブロックオブジェクトの取得に失敗しました", "{$this->_className}#viewAssign");
					return false;
				}
				if(!isset($block_obj['theme_name']) || $block_obj['theme_name'] == "") {
					if(!isset($pages[$block_obj['page_id']])) {
						$pages[$block_obj['page_id']] =& $pagesView->getPageById($block_obj['page_id']);
					}
					$themeList = $session->getParameter("_theme_list");
					if($themeList[$pages[$block_obj['page_id']]['display_position']] != "") {
						$block_obj['theme_name'] = $themeList[$pages[$block_obj['page_id']]['display_position']];
					} else {
						$block_obj['theme_name'] = $pages[$page_id]['theme_name'];
					}
				}
				//ショートカットか否か
	    		//$page_shortcut_flag = false;
	    		//if($pages[$page_id]['shortcut_flag']) {
	    		//	$page_shortcut_flag = true;
	    		//}
	    		$themeStr = htmlspecialchars($block_obj['theme_name'], ENT_QUOTES);
				$tempStr = htmlspecialchars($block_obj['temp_name'], ENT_QUOTES);//$block_obj['temp_name'];
				$min_width_size = intval($block_obj['min_width_size']);
				$renderer->assign('block_obj',$block_obj);
				$renderer->assign('display_position',$pages[$block_obj['page_id']]['display_position']);
			}

			//最小広さ設定
			if ($dialog_flag) {
				$renderer->assign('min_width_size',"width:auto;");
			} else if ($block_id == 0) {
				if(!isset($modules[$pathList[0]]) || $modules[$pathList[0]]['min_width_size'] == 0)
					$renderer->assign('min_width_size',"width:auto;");
				else
					$renderer->assign('min_width_size',"width:".$modules[$pathList[0]]['min_width_size']."px;");
			} else {
				if($block_obj['min_width_size'] == 0)
					$renderer->assign('min_width_size',"width:auto;");
				else
					$renderer->assign('min_width_size',"width:".$block_obj['min_width_size']."px;");
			}
			$renderer->assign('block_id', $block_id);
			$renderer->assign('page_id', $page_id);
		}
		$renderer->assign('dir_name',$pathList[0]);
		$renderer->assign('action_name',$action_name);
		//URL-Assign
		//$url = CURRENT_URL;
		$url = BASE_URL.INDEX_FILE_NAME.$request->getStrParameters(false);
		$url_htmlspecialchars = htmlspecialchars($url, ENT_QUOTES); //preg_replace("/&amp;/i", '&', htmlspecialchars($url, ENT_QUOTES));

		$renderer->assign('url',$url_htmlspecialchars);
		$renderer->assign('encode_url',rawurlencode($url));
		return array(
			$page_id,
			$themeStr,
			$tempStr,
			$min_width_size,
			$url_htmlspecialchars
		);
	}

	function getTopId($block_id = 0, $module_id = 0, $prefix_id_name=null) {
		//
		//ブロック固有のidセット
		// パラメータにprefix_id_nameがあればid名称が、"_"+prefix_id_name+idとなる
		// ダイアログ等を表示する場合、画面に同じブロックIDが２つ存在してしまうため
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$session =& $container->getComponent("Session");

    	$id = "";
    	if(!isset($prefix_id_name)) {
    		$prefix_id_name = $request->getParameter("prefix_id_name");
    	}

		if(isset($prefix_id_name) && $prefix_id_name != "") {
			$prefix_id_name = htmlspecialchars($prefix_id_name, ENT_QUOTES)."_";
		} else {
			$prefix_id_name = "";
		}
		$actionChain =& $container->getComponent("ActionChain");
		$action_name = $actionChain->getCurActionName();
		$pathList = explode("_", $action_name);

		if($block_id == 0 && $pathList[0] != "login" && $pathList[0] != "pages" && $pathList[0] != "search") {
			$id = "_".$prefix_id_name.$module_id;
		} else {
			$id = "_".$prefix_id_name.$block_id;
		}
		$session->setParameter("_id", $id);
		return $id;
	}

	function _getThemeDirList($themeStr) {
		$themeStrList = explode("_", $themeStr);
		$count_len = count($themeStrList);
		if($count_len == 1) {
			$theme_first_name = $themeStr;
			$theme_second_name = "";
			$themeDir = "/themes/".$themeStr."/templates";
			$themeCssPath = "themes/".$themeStr."/css";
		} else {
			$count = 0;
			$themeDir = "";
			$themeCssPath = "";
			foreach($themeStrList as $themeStr) {
				if($count == 0) {
					$theme_first_name = $themeStr;
				} else {
					$theme_second_name = $themeStr;
					if($themeDir == "") {
						$themeDir = "/themes/".$theme_first_name."/templates/".$themeStr;
						$themeCssPath = "themes/".$theme_first_name."/css/".$themeStr;
					} else {
						$themeDir .= $themeStr;
						$themeCssPath .= $themeStr;
					}
					if($count_len != $count+1) {
						$themeDir .= "/";
						$themeCssPath .= "/";
					}
				}
				$count++;
			}
		}

		return array(
			$theme_first_name,
			$theme_second_name,
			$themeDir,
			$themeCssPath
		);
	}

	/**
     * テーマ関連Assign処理
     */
	function themeAssign(&$renderer, $block_id, $theme_name) {
		$container =& DIContainerFactory::getContainer();
		$getdata =& $container->getComponent("GetData");
		$session =& $container->getComponent("Session");
		//$request =& $container->getComponent("Request");

		//アクション名よりテンプレートパスを取得
		$actionChain =& $container->getComponent("ActionChain");
		$action_name = $actionChain->getCurActionName();
		$pathList = explode("_", $action_name);
		$blocks = $getdata->getParameter("blocks");

		$page_id = isset($blocks[$block_id]['page_id']) ? $blocks[$block_id]['page_id'] : 0;
		//
		// テーマDir取得
		//

		list($theme_first_name, $theme_second_name, $themeDir, $themeCssPath) = $this->_getThemeDirList($theme_name);

		//
		// テーマ存在チェック
		//
		$template_block = "block.html";

		if (!file_exists(STYLE_DIR .  $themeDir . "/" .$template_block)) {
			$theme_name = "";
		}

		if($theme_name == "") {
			// ブロックのテーマが削除されていた場合
			$pages = $getdata->getParameter("pages");

			if(!isset($pages[$page_id])) {
				$pagesView =& $container->getComponent("pagesView");
				$pages[$page_id] =& $pagesView->getPageById($page_id);
			}
			$themeList = $session->getParameter("_theme_list");
			if(isset($pages[$page_id]['display_position']) && $themeList[$pages[$page_id]['display_position']] != "") {
				$theme_name = $themeList[$pages[$page_id]['display_position']];
			} else {
				$theme_name = $pages[$page_id]['theme_name'];
			}
			list($theme_first_name, $theme_second_name, $themeDir, $themeCssPath) = $this->_getThemeDirList($theme_name);
		}

		$renderer->assign('_theme_name', $theme_name);
		$renderer->assign('_theme_first_name', $theme_first_name);
		$renderer->assign('_theme_second_name', $theme_second_name);
		//
		// icon_color取得
		//
		$color_ini_list = @parse_ini_file(STYLE_DIR. "/themes/" .$theme_first_name."/config/"._THEME_ICON_COLOR_INIFILE);
		if($color_ini_list) {
			if(isset($color_ini_list[$theme_second_name])) {
				$renderer->assign('_icon_color', $color_ini_list[$theme_second_name]);
			} else if(isset($color_ini_list["default"])) {
				$renderer->assign('_icon_color', $color_ini_list["default"]);
			} else {
				$renderer->assign('_icon_color', "default");
			}
		} else {
			$renderer->assign('_icon_color', $theme_second_name);
		}

		return array(
			$theme_second_name,
			$themeDir,
			$themeCssPath
		);
	}

	/**
	 * リダイレクトヘッダー
	 * @param string   $url
	 * @param int      $time
	 * @param string   $message
	 * @access	public
	 */
	function redirectHeader($url="", $time = 2, $message = "")
	{
		$container =& DIContainerFactory::getContainer();
		$config =& $container->getComponent("configView");
		$meta = $config->getMetaHeader();
		if($url == "") {
			$url = BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".DEFAULT_ACTION;
		}
		//$url = htmlspecialchars(str_replace("?action=","?_sub_action=",str_replace("&","@",BASE_URL.INDEX_FILE_NAME.$this->_request->getStrParameters(false))), ENT_QUOTES);

		$renderer =& SmartyTemplate::getInstance();
		$renderer->assign('header_field',$meta);
		$renderer->assign('time', $time);
		$renderer->assign('url',$url);
		$renderer->assign('lang_ifnotreload', sprintf(_IFNOTRELOAD,$url));
		if($message != "") {
			$renderer->assign('redirect_message', $message);
		} else {
			$renderer->assign('redirect_message', "");
		}
		$actionChain =& $container->getComponent("ActionChain");
		$errorList =& $actionChain->getCurErrorList();
		$renderer->setErrorList($errorList);
		$main_template_dir = WEBAPP_DIR . "/templates/"."main/";

		//template_dirセット
		$renderer->setTemplateDir($main_template_dir);

		$session =& $container->getComponent("Session");
		$mobile_flag = $session->getParameter("_mobile_flag");
		if (!isset($mobile_flag)) {
			$mobileCheck =& MobileCheck::getInstance();
			$mobile_flag = $mobileCheck->isMobile();
		}
		//$response =& $container->getComponent("Response");
		//$contentDisposition = $response->getContentDisposition();
		//$contentType = "text/html; charset=utf-8";	//$response->getContentType();
		//if ($contentDisposition != "") {
		//	header("Content-disposition: ${contentDisposition}");
		//}
		//if ($contentType != "") {
		//	header("Content-type: ${contentType}");
		//}
		//header("Content-type: ".$contentType);
		if ($mobile_flag == _ON) {
			$contentType = "text/html; charset=shift_jis";	//$response->getContentType();
			header("Content-type: ".$contentType);
			$result = $renderer->fetch("mobile_redirect.html", 'redirect');
			$convertHtml =& $this->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
			$result = $convertHtml->convertMobileHtml($result, true);
		} else {
			$contentType = "text/html; charset=utf-8";	//$response->getContentType();
			header("Content-type: ".$contentType);
			$result = $renderer->fetch("redirect.html",'redirect');
		}
		print $result;
		exit;
	}

	/**
	 * headerタグ以下にaddした文字列を追加するメソッド
	 * @param string $value  head以下に追加する文字列（例：<meta hoge1="hoge2" hoge3="hoge4" />）
	 *        注：同じvalueのものは追加しない。
	 * @access	public
	 */
	function addHeader($value) {
		$renderer =& SmartyTemplate::getInstance();
		$add_header_fields = $renderer->get_template_vars('add_header_fields');
		$add_header_fields[$value] = $value;
		$renderer->assign('add_header_fields', $add_header_fields);
	}

	/**
	 * addHeaderメソッドよりaddした文字列を削除するメソッド
	 * @param string $value  head以下に追加する文字列から指定したもののみ削除
	 * @access	public
	 */
	function delHeader($value = null) {
		$renderer =& SmartyTemplate::getInstance();
		$add_header_fields = $renderer->get_template_vars('add_header_fields');
		if(is_null($value)) {
			$add_header_fields = null;
		} else if(isset($add_header_fields[$value])) {
			unset($add_header_fields[$value]);
		}
		$renderer->assign('add_header_fields', $add_header_fields);
	}
}
?>
