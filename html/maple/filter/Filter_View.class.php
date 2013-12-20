<?php
//
// $Id: Filter_View.class.php,v 1.165 2008/08/15 08:11:08 Ryuji.M Exp $
//

require_once MAPLE_DIR.'/nccore/SmartyTemplate.class.php';

/**
 * Viewの実行準備および実行を行うFilter
 * @TODO:内部にechoのみのモジュールだと出力されないと思うので呼び出す順番を修正する必要あり
 *  TODO:AssignをSmatryクラスで行うように変更するかも
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_View extends Filter {
	var $_container;

    var $_log;

    var $_filterChain;

    var $_actionChain;

    var $_request;

    var $_response;

    var $_session;

    var $_className;

    var $_getdata;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Filter_View() {
		parent::Filter();
	}

	/**
	 * Viewの処理を実行
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

        $this->_getdata =& $this->_container->getComponent("GetData");

        $this->_className = get_class($this);
		$mobile_flag = $this->_session->getParameter("_mobile_flag");

        $this->_log->trace("{$this->_className}の前処理が実行されました", "{$this->_className}#execute");
		if ($mobile_flag == _OFF) {
	        $this->_prefilter();
		}
        $this->_filterChain->execute();
		if ($mobile_flag == _OFF) {
	        $this->_postfilter();
		}
        $this->_log->trace("{$this->_className}の後処理が実行されました", "{$this->_className}#execute");
	}

	/**
     * プレフィルタ
     *
     * @access private
     */
    function _prefilter()
    {
    	$nouse = $this->getAttribute("define:nouse");
        if(isset($nouse) && $nouse == 1) {
        	return;
        }
    	ob_start();
    }

    /**
     * ポストフィルタ
     * Viewの処理を実行
     * @access private
     */
    function _postfilter()
    {
    	$renderer =& SmartyTemplate::getInstance();
    	$nouse = $this->getAttribute("define:nouse");
		if(isset($nouse) && $nouse == 1) {
			return;
		}
    	//ブロック情報読み込み
		$block_id = intval($this->_request->getParameter("block_id"));

		//アクション名よりテンプレートパスを取得
		$action_name = $this->_actionChain->getCurActionName();
		$pathList = explode("_", $action_name);

    	//
		// リクエスト値を取得
		//
        $action =  ($this->_request->getParameter(ACTION_KEY)) ? $this->_request->getParameter(ACTION_KEY) : DEFAULT_ACTION;

        //
        //cache_idセット
        //
        $common =& $this->_container->getComponent("commonMain");
    	$cache_id = $common->getCacheid();

		$view = $this->_response->getView();

		$result = "";
		$header = _OFF;
		$action_flag = false;
		$attachment = $this->getAttribute("define:attachment");
		if(!isset($attachment)) {
			//default:ファイル添付していない
			$attachment = _OFF;
		}

		//
		// 印刷アイコンをつけるかどうか
		//
		$print_html = "";
		$print = $this->getAttribute("define:print");
		if(isset($print)) {
			$printList = explode(",", $print);
			$print_len = count($printList);
			if($print_len == 1) {
				$print = intval($printList[0]);
			} else {
				$print = _OFF;
				for($i = 1; $i < $print_len; $i++) {
					if($view == $printList[$i]) {
						$print = intval($printList[0]);
						break;
					}
				}
			}
			if($print == _ON) {
				$_themes_images_path = get_themes_image("print.gif");
				$print_html = "<div class=\"align-right print_preview_none\">".
								"<a class=\"link\" href=\"#\" onclick=\"commonCls.print(Element.getParentElement(this, 2)); return false;\">".
									"<img title=\""._PRINT_ICON."\" alt=\""._PRINT_ICON."\" src=\"".$_themes_images_path."\"/>".
								"</a>".
							 "</div>";
			}
		}

		//if ($view != "" && $attachment == 0) {
		if ($view != "" && gettype($view) == "string") {
			$template = $this->getAttribute($view);
			if($template == "") {
				if($view == USE_CACHE) {
					//キャッシュを使用するため、ファイル名が未指定
				} elseif($view == TOKEN_ERROR_TYPE || $view == VALIDATE_ERROR_TYPE || $view == VALIDATE_ERROR_NONEREDIRECT_TYPE || $view == UPLOAD_ERROR_TYPE) {
					//Error_default
					$template = "main:error.html";
				} else {
					$this->_log->error(sprintf("テンプレートファイルの取得に失敗しました(%s)",$view), "{$this->_className}#_postfilter");
					exit;
				}
			}
			if(strncmp("location_script:", $template, 16) == 0) {
			//if (preg_match("/^(location_script:)/", $template)) {
				//もし、パラメータに_redirect_urlがあれば
				//優先的に_redirect_urlへリダイレクト
				$redirect_url = $this->_request->getParameter("_redirect_url");
				if($redirect_url)
					$sub_url = preg_replace("/&amp;/i", '&', htmlspecialchars($redirect_url, ENT_QUOTES));
				else
					$sub_url = preg_replace("/location_script:/", "", $template);
				$sub_url = trim($sub_url);

				if(preg_match("/^".preg_quote(BASE_URL, '/')."/i", $sub_url)) {
					$sub_url = preg_replace("/^".preg_quote(BASE_URL, '/')."/i", "", $sub_url);
					$sub_url_strlen = strlen($sub_url);
					if(substr($sub_url, 0, 1) == "/") {
						// 最初の「/」を除去
						$sub_url = substr($sub_url, 1, $sub_url_strlen - 1);
					}
					$base_url = BASE_URL."/";
				} else if(preg_match("/^".preg_quote("http", '/')."/i", $sub_url)) {
					$base_url = "";
				} else {
					$base_url = BASE_URL."/";
				}
				// SSLログインの場合、HTTPSからHTTPに戻す
				// TODO:ログイン先もSSLで表示するページならば、HTTPSで表示。現状。未対応
				//if($url == "") $url = "?action=".DEFAULT_ACTION;
				$sub_url = urlencode($sub_url);
				$sub_url = str_replace(array("%3d","%3D","%26","%2F","%2f","%3f", "%3F","%23"), array("=","=","&","/","/","?","?","#"), $sub_url);
				$url = $base_url.$sub_url;

				$this->_response->setRedirectScript($url);
			} else if(strncmp("location:", $template, 9) == 0) {
			//} else if (preg_match("/^(location:)/", $template)) {
				//もし、パラメータに_redirect_urlがあれば
				//優先的に_redirect_urlへリダイレクト
				$redirect_url = $this->_request->getParameter("_redirect_url");
				if($redirect_url)
					$url = preg_replace("/&amp;/i", '&', htmlspecialchars($redirect_url, ENT_QUOTES));
				else
					$url = preg_replace("/location:/", "", $template);

				$url = trim($url);
				if($url == "") $url = "?action=".DEFAULT_ACTION;
				$this->_response->setRedirect($url);
			} else if(strncmp("action:", $template, 7) == 0) {
			//} else if (preg_match("/^(action:)/", $template)) {
				$actionList = preg_replace("/action:/", "", $template);
				$not_remove_requests = explode(",", $actionList);
				$action = trim(array_shift($not_remove_requests));
				if(count($not_remove_requests) > 0) {
					// 削除しないで残しておくパラメータの指定あり。
					// それ以外は削除(block_id, page_id,module_id,action以外)
					$request_params = array();
					foreach($not_remove_requests as $not_remove_request) {
						$request_params[$not_remove_request] = $this->_request->getParameter($not_remove_request);
					}
					$this->_request->removeParameters();
					$this->_request->setParameters($request_params);
				}
				$this->_actionChain->add($action);
				$action_flag = true;
			} else {
				//$renderer =& SmartyTemplate::getInstance();
				//
				// define情報読み込み
				//
				$theme = $this->getAttribute("define:theme");
				if(!isset($theme) || ($this->_request->getMethod() == "POST" &&
					($template == "main:error.html" || $template == "main:key_error.html"))) {
					//default:テーマを表示しない
					$theme = 0;
				} else {
					$themeList = explode(",", $theme);
					$theme_len = count($themeList);
					if($theme_len == 1) {
						$theme = intval($themeList[0]);
					} else {
						//default:テーマを表示しない
						$theme = 0;
						for($i = 1; $i < $theme_len; $i++) {
							if($view == $themeList[$i]) {
								$theme = intval($themeList[0]);
								break;
							}
						}
					}
				}

				//
				// 主担かどうかセット
				//
				$auth_id = $this->_session->getParameter("_auth_id");
				if($auth_id >= _AUTH_CHIEF) $chief_flag = _ON;
				else $chief_flag = _OFF;

				if($theme == 1) {
					$header_type = $this->getAttribute("define:header_type");

					//header_typeの値は、main、editのいずれかが入り
					//ブロックヘッダーのボタンの種別を指定する(編集ボタンか、編集終了ボタンか)
					//設定されていなければ、フォルダ構成より自動判断。
					if(!isset($header_type) && ($header_type != "main" && $header_type != "edit")) {
						$header_type = null;
					}
					if($header_type == null && count($pathList) >= 3) {
						//自動判断
						switch ($pathList[2]) {
						   case "edit":
						       $header_type = "edit";
						       break;
						   default:
						       $header_type = "main";
						}
					} else if($header_type == null) {
						$header_type = "main";
					}
					$renderer->assign('header_type',$header_type);
					if($chief_flag) {
						$renderer->assign('dblclick_edit',_DBLCLICK_EDIT);
						$renderer->assign('draganddrop_move',_DRAGANDDROP_MOVE);
					} else {
						$renderer->assign('dblclick_edit','');
						$renderer->assign('draganddrop_move','');
					}
				}

				//
				//ヘッダー表示・非表示
				//
				$header = $this->_request->getParameter("_header");
				if(!isset($header)) {
					//default:ヘッダーを表示する
					if(substr($template, -4, 4) == ".xml") {
						$header = _OFF;
					} else {
						$header = _ON;
					}
				}
				//
				//javascriptを読み込むかどうか
				//
				$noscript = $this->_request->getParameter("_noscript");
				if(!isset($noscript)) {
					//default:javascriptを読み込む
					$noscript = _OFF;
				}

				//TODO:パラメータ「_theme」廃止
				//ブロックテーマ表示：非表示
				//$theme = $this->_request->getParameter("_theme");
				//if(!isset($theme)) {
				//	//default:ヘッダーを表示する
				//	$theme = 1;
				//}


				//
				//共通Assign処理
				//
				//TODO:後に第2引数を無くす
				$id = $this->_session->getParameter("_id");
				$themeStr = "";
				$tempStr = "";
				$retAssign = $common->viewAssign($renderer, $this->_log);
				if(is_array($retAssign)) {
					list($page_id, $themeStr, $tempStr, $min_width_size, $url_htmlspecialchars) = $retAssign;
					if($header && $block_id == 0 && $themeStr == "") {
						$themeStr = "default";
					}
					$renderer->assign('_url',$url_htmlspecialchars);
					if($action_name != DEFAULT_ACTION && $header == _ON) {
						//ヘッダー表示ありの場合、リクエストパラメータのpage_idをセット
						$req_page_id = $this->_request->getParameter("page_id");
						if($req_page_id) $page_id = $req_page_id;
					}
				}

				//close_popup_confirm
				//close_popup_func
				$close_popup_func = $this->getAttribute("define:close_popup_func");
				if(isset($close_popup_func)) {
					//閉じる場合の関数定義
					$renderer->assign('_close_popup_func',$close_popup_func);
				} else {
					$close_popup_confirm = $this->getAttribute("define:close_popup_confirm");
					if(isset($close_popup_confirm)) {
						//閉じる場合、確認メッセージ)
						$renderer->assign('_close_popup_confirm',$close_popup_confirm);
					}
				}
				//theme_name
				$theme_name = $this->getAttribute("define:theme_name");
				if(isset($theme_name)) {
					$themenameList = explode(",", $theme_name);
					$theme_len = count($themenameList);
					if($theme_len == 1) {
						$themeStr = $themenameList[0];
					} else {
						//default:テーマを表示しない
						for($i = 1; $i < $theme_len; $i++) {
							if($view == $themenameList[$i]) {
								$themeStr = $themenameList[0];
								break;
							}
						}
					}
					//$themeStr = $theme_name;
				}
				if($this->_session->getParameter("_system_flag") == _OFF && ($themeStr == "system" || $this->_request->getParameter("theme_name") == "system")) {
					$min_width_size = 0;
				}

				$def_min_width_size = $this->getAttribute("define:min_width_size");
				if(isset($def_min_width_size)) {
					//ポップアップサイズ指定用
					$min_width_size = intval($def_min_width_size);
				}

				if(!$header){
					//ヘッダーなしで、リクエストパラメータtheme_name指定あり
					$reqest_theme_name = $this->_request->getParameter("theme_name");
					if($reqest_theme_name == "none") {
						$themeStr = "";
						$theme = _OFF;
					} else if($reqest_theme_name && $themeStr != "system") $themeStr = $reqest_theme_name;
				}
				if($themeStr != "") {
					$themeStr = basename($themeStr);
					list($block_color, $themeDir, $themeCssPath) = $common->themeAssign($renderer, $block_id, $themeStr);

				}
				$action_obj =& $this->_actionChain->getCurAction();
				$error_template_flag = false;
				if(strncmp("main:", $template, 5) == 0) {
				//if (preg_match("/main:/", $template)) {
					//main:ならば、mainのテンプレートを使用する
					$template = preg_replace("/main:/", "", $template);
					$sub_main_template_dir = "/templates/"."main/";
					$main_template_dir = WEBAPP_DIR . $sub_main_template_dir;
					//$main_flag = true;
					if ($this->_request->getMethod() == "POST" && ($template == "error.html" || $template == "key_error.html")) {
						$error_template_flag = true;
					}
				} else if(strncmp("common:", $template, 7) == 0) {
				//} else if (preg_match("/common:/", $template)) {
					//common:ならば、モジュールの共通テンプレート
					$template = preg_replace("/common:/", "", $template);
					$sub_main_template_dir = "/" . $pathList[0]. "/templates/";
					$main_template_dir = MODULE_DIR . $sub_main_template_dir;
				} else {
					if($tempStr != "") {
						$sub_main_template_dir = "/" . $pathList[0]. "/templates/".$tempStr."/";
						//$main_template_dir = MODULE_DIR . "/" . $pathList[0]. "/templates/".$tempStr."/";
					} else {
						$sub_main_template_dir = "/" . $pathList[0]. "/templates/default/";	//default固定とする
						//$sub_main_template_dir = "/" . $pathList[0]. "/templates/";
						//$main_template_dir = MODULE_DIR . "/" . $pathList[0]. "/templates/";
					}
					$main_template_dir = MODULE_DIR . $sub_main_template_dir;
					//$main_flag = false;
				}

				if(!is_dir($main_template_dir)) {
					$sub_main_template_dir = "/" . $pathList[0]. "/templates/default/";
					$main_template_dir = MODULE_DIR . $sub_main_template_dir;
					//$main_template_dir = MODULE_DIR . "/" . $pathList[0]. "/templates/default/";
				}

				//強制的に外枠なしに設定するパラメータ
				//TODO:廃止
				//if (preg_match("/noneouter:/", $template)) {
				//	$template = preg_replace("/noneouter:/", "", $template);
				//	$theme = 0;
				//}
				$script_str =& $this->_getdata->getParameter("script_str");
				$script_str_all =& $this->_getdata->getParameter("script_str_all");

				$renderer->setAction($action_obj);

				$errorList =& $this->_actionChain->getCurErrorList();
				$renderer->setErrorList($errorList);

				$token =& $this->_container->getComponent("Token");
				//if (is_object($token)) {
				//	$renderer->setToken($token);
				//}

				//if (is_object($this->_session)) {
				//	$renderer->setSession($this->_session);
				//}

				$renderer->setScriptName($_SERVER['SCRIPT_NAME']);

				//main_action_nameをassign
				$main_action_name = $this->_session->getParameter('_main_action_name');
				$renderer->assign('main_action_name',$main_action_name);
				//$renderer->assign('main_block_id',$this->_session->getParameter('main_block_id'));
				//template_dirセット
				$renderer->setTemplateDir($main_template_dir);
				//if($main_flag) {
				//	//mainのテンプレートを使用する場合、キャッシュを使用しない
				//	//キャッシュ処理をしない
				//	$caching = $renderer->getCaching();
    			//	$renderer->setCaching(false);
				//}
				$result = $print_html . $renderer->fetch($template,$cache_id,$sub_main_template_dir);
				if($theme && strncmp(ERROR_MESSAGE_PREFIX, $result, strlen(ERROR_MESSAGE_PREFIX)) == 0) {
				//if ($theme && preg_match("/^".ERROR_MESSAGE_PREFIX."/", $result)) {
					$result = preg_replace("/^".ERROR_MESSAGE_PREFIX."/", "", $result);
				}
				//Scriptを保管
				if($noscript == _OFF && _SCRIPT_OUTPUT_POS == "footer") {
				//if($header || $main_action_name == DEFAULT_ACTION) {
					$footer_module_script = "";

					$re_script = '/<script class=([\'"]?)nc_script.*\\1(.*)>(.*)<\/script>/isU';
					$matches = array();
					preg_match_all($re_script,$result,$matches);
					if(isset($matches[0])) {
						$result = preg_replace($re_script,"",$result);
						$script_result = "";
						$script_result_all = "";
						$match_count = 0;
						foreach($matches[3] as $match) {
							if($match == "" && strtolower($matches[2][$match_count]) != ' type="text/javascript"') {
								$script_result_all .= '<script class="nc_script" ' . $matches[2][$match_count] . '></script>';
							} else {
								$script_result .= $match;
							}
							$match_count++;
						}

						//if(!isset($footer_field['script_footer'])) $footer_field['script_footer'] = "";
						$script_str .= $script_result;
						$script_str_all .= $script_result_all;
						//if($header) {
						//	$this->_response->setScript($script_result);
						//} else {
						//	$footer_module_script = $script_result;
						//}
					}
				}

				//if($main_flag) {
				//	//mainのテンプレートを使用する場合、キャッシュを使用しない
				//	//キャッシュ処理を元に戻す
				//	$renderer->setCaching($caching);
				//}
				//
				// コンテンツがfetchされていない場合、レスポンスクラスからコンテンツ取得
				// ob_get_contentsより取得し連結
				//
				//$ob_buffer = ob_get_contents();
				//$result = $ob_buffer . $result;
				//@ob_end_clean();
				//ブロックテーマを追加
				if($theme && ($block_id != 0 || isset($themeDir))) {
					//ヘッダーメニュー
					$headerMenuLists = null;
					if($this->_filterChain->hasFilterByName("HeaderMenu")) {
						$headerMenu =& $this->_filterChain->getFilterByName("HeaderMenu");
						if($headerMenu) {
							$headerMenuLists = $headerMenu->getMenu();
							$headerMenu->clear();
						}
					}
					$renderer->assign("headermenu",$headerMenuLists);

					//block.html固定
					$template_block = "block.html";

					$layoutmode = "";
					//if (!@file_exists(MODULE_DIR . $themeDir . $template_block)) {
						//レイアウトモードをセッションより取得
						//$pages = $this->_getdata->getParameter("pages");
						$_layoutmode_centercolumn = $this->_session->getParameter("_layoutmode_centercolumn");
						$layoutmode = isset($_layoutmode_centercolumn) ? $_layoutmode_centercolumn : $this->_session->getParameter("_layoutmode");
						$_layoutmode_onetime =  $this->_request->getParameter("_layoutmode_onetime");
						if(isset($_layoutmode_onetime) && $chief_flag) {
							$layoutmode = $_layoutmode_onetime;
						}
					//}
					//template_dirセット
					if(!isset($themeDir)) {
						$common->redirectHeader("", 2, sprintf(_ACCESS_FAILURE, CURRENT_URL));
						exit;
					}
					$renderer->setTemplateDir(STYLE_DIR .  $themeDir);
					//content情報assign
					$renderer->assign('content_field',$result);

					//キャッシュ処理をしない
					$caching = $renderer->getCaching();
					$renderer->setCaching(0);
					$result = $renderer->fetch($template_block, $cache_id."block_theme=".$themeStr."_".$layoutmode,$themeDir);
					//$result = $renderer->fetch($template_block, "block_theme_".$themeStr."_".$layoutmode,$themeDir);

					//header_btn
					$heder_btn = "";
					if($layoutmode == "on" && $themeStr != "system") {
						$template_block_dir = "/templates/default/";	//TODO:default固定
						$template_block = "header.html";
						$renderer->setTemplateDir(STYLE_DIR .  $template_block_dir);
						$heder_btn = $renderer->fetch($template_block, $cache_id, $template_block_dir);
					}

					//キャッシュ処理を元に戻す
					$renderer->setCaching($caching);

					//
					//パラメータのstyle属性付与
					//
					//TODO:セキュリティ上（うまく動作するかも含めて）問題にならないか後にチェック。
					//$style = htmlspecialchars($this->_request->getParameter("style"), ENT_QUOTES);
					//if($style != "") {
					//	//styleパラメータは、&style=visibility:hidden;width:100px;のように指定すること
					//	$style = " style=\"".$style."\"";
					//}

					//
					// Blockテーマヘッダー情報を追加
					// id=_$block_id class=ThemeName
					// DIVタグにするとモジュールのみの表示した場合に全画面表示(Width:100%)で表示されるため、tableタグを現状用いる
					//$block_theme_header = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"  id=\"_" . $url . "\" class=\"module_box " . $themeStr . "\"".$style."><tr><td>";
					if($min_width_size==0) {
						if($themeStr != "system") {
							$table_min_width_size = "100%";
						} else {
							$table_min_width_size = "auto";
						}
					} else {
						$table_min_width_size = $min_width_size."px";
					}
					$top_class_name = "blockstyle".$id." module_box";
					if($action_name == "pages_action_grouping") $top_class_name .=" module_grouping_box";
					$block_theme_header = "<table id=\"" . $id . "\" class=\"".$top_class_name." " . htmlspecialchars($themeStr, ENT_QUOTES) . "\" style=\"width:" . $table_min_width_size . "\"><tr><td>";
					$block_theme_header .= $heder_btn;

					//$block_theme_header .= "<div style=\"width:" . $div_min_width_size . "\" >";
					//$block_theme_header .= "<div>";

					if(is_object($token) && $token->getValue() != ''){
						$block_theme_header .= "<input type=\"hidden\" id=\"_token" . $id . "\" class=\"_token\" value=\"".$token->getValue()."\"/>";
					}
					//if($block_id != 0) {
					//	$block_theme_header .= "<input type=\"hidden\" class=\"_block_id\" value=\"".$block_id."\"/>";
					//	$block_theme_header .= "<input type=\"hidden\" class=\"_page_id\" value=\"".$block_obj['page_id']."\"/>";
					//} else {
					//	$block_theme_header .= "<input type=\"hidden\" class=\"_module_id\" value=\"".$module_id."\"/>";
					//}
					$block_theme_header .= "<input type=\"hidden\" id=\"_url" . $id . "\" class=\"_url\" value=\"".$url_htmlspecialchars."\"/>";
					//透過Gif挿入：min-width指定(IEが未対応のため画像とする:2006/02/02 By Ryuji Masukawa)
					$block_theme_footer = "";
					if($min_width_size!=0) {
						$block_theme_header .= "<img alt=\"\" src=\"".get_image_url()."/images/common/blank.gif\" style=\"height:0px;width:". $min_width_size ."px;\" />";
					}
					//$block_theme_footer .= "</div>";
					$block_theme_header .= "<a id=\"_href".$id."\" name=\"" . $id . "\"></a>";
					$block_theme_footer .= "</td></tr></table>";
					//ページに表示してある場合、モジュールInit（イベントを付与）
					//TODO:移動する権限があるかどうかのチェックが必要。左カラム等はとくに考える必要性あり。
					//if(!$header) {
						// メニューの場合、主担でなくても編集ボタンをクリックさせるため、対応
						$edit_btn_flag = ($chief_flag == true || (isset($action_obj->headerbtn_edit) && $action_obj->headerbtn_edit == true)) ? _ON :  _OFF;
						$script_str = "commonCls.moduleInit(\"". $id."\",".$edit_btn_flag.");".$script_str;

						//$block_theme_footer .= "<script type=\"text/javascript\">commonCls.moduleInit(\""."_" . $url."\");</script>";
					//}

					$result = $block_theme_header . $result . $block_theme_footer;
				} else if(($theme && $block_id == 0 && !isset($themeDir)) ||
							 ($this->_request->getParameter("active_center") != "" && $this->_session->getParameter("_main_action_name") == DEFAULT_ACTION)) {
					// ブロックセンター表示
					$block_theme_header = "<div id=\"".$id."\">";
					if(is_object($token)){
						$block_theme_header .= "<input type=\"hidden\" id=\"_token" . $id . "\" class=\"_token\" value=\"".$token->getValue()."\"/>";
					}
					$block_theme_header .= "<input type=\"hidden\" id=\"_url" . $id . "\" class=\"_url\" value=\"".$url_htmlspecialchars."\"/>";
					$block_theme_header .= "<a id=\"_href".$id."\" name=\"" . $id . "\"></a>";
					$block_theme_footer = "</div>";
					$result = $block_theme_header . $result . $block_theme_footer;
				} else if(is_object($token) && $token->inbuild() && $token->getValue() != "") {
					// Tokenがbuildされていれば、書き換える
					$script_str .= "commonCls.setToken('_token".$id."','".$token->getValue()."');";
				}
				//初期化
				//$script_string = "";
				if($header) {
					//$themeCommonStr = "default";

					//
					// configクラスを取得
					//
					$config =& $this->_container->getComponent("configView");

					//
					// ヘッダー、META情報取得
					//
					if(isset($page_id)) {
						$meta = $config->getMetaHeader($page_id, array($pathList[0]));
					} else {
						$meta = $config->getMetaHeader(null, array($pathList[0]));
					}
					//$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./css/common/".$themeCommonStr."/style.css\" />";
					if($themeStr != "" && $themeStr != "system") {
						$meta['css_header']["/" . $themeCssPath. "/style.css"] = "/" . $themeCssPath. "/style.css";
						// custum css
						$meta['css_header'][$themeStr] = $themeStr;
						$meta['css_header_block_id'][$themeStr] = $block_id;
					}
					$timeout_time = $this->_session->getParameter('_session_gc_maxlifetime')*60;
					if($this->_session->getParameter("_user_id") != "0" && defined("_SESSION_TIMEOUT_ALERT")) {
						// 現状、ログインしたもののみcommonInitを行う
						$script_str .= "commonCls.commonInit('"._SESSION_TIMEOUT_ALERT."',".$timeout_time.");";
					}
					if(isset($block_color)) {
						$css_header_str = $this->_getheaderInc($block_color, $tempStr);
						$meta['css_header'][$css_header_str] = $css_header_str;
						////$meta['css_header'] .= $this->_getheaderInc($block_color, $tempStr, $header);
					}
					//meta情報assign
					$renderer->assign('header_field',$meta);

					//キャッシュ処理をしない
					$caching = $renderer->getCaching();
					$renderer->setCaching(0);

					$template_dir = WEBAPP_DIR . "/templates/"."main/";
					$template_header = "header.html";
					//template_dirセット
					$renderer->setTemplateDir($template_dir);
					$result_header = $renderer->fetch($template_header,$cache_id,"/templates/"."main/");
					//print $result;
					//flush();

					//footer表示--------------------------------------------------
					$template_dir = WEBAPP_DIR . "/templates/"."main/";
					$template_footer = "footer.html";

					$moduleNmaes = array($pathList[0]);
					if ($action_name == 'pages_view_closesite') {
						$moduleNmaes[] = 'login';
					}
					$footer_arr = $config->terminateFooter($moduleNmaes);
					$renderer->assign('footer_field', $footer_arr);

					//template_dirセット
					$renderer->setTemplateDir($template_dir);
					$result_footer = $renderer->fetch($template_footer,$cache_id,"/templates/"."main/");
					//キャッシュ処理を元に戻す
					$renderer->setCaching($caching);

				} else if(!$error_template_flag && $main_action_name != "pages_view_grouping" &&
							$main_action_name != DEFAULT_ACTION && $script_str != '' && $noscript != _ON &&
							substr($template, -4, 4) != ".xml") {
					if(!isset($block_color)) {
						$block_color = "default";
					}
					$css_script_dir_name = $this->_getheaderInc($block_color, $tempStr);
					if($css_script_dir_name != "") {
						$css_script = "commonCls.addCommonLink(\"".$css_script_dir_name."\");";
					} else {
						$css_script = "";
					}
					$result = $result."<script class=\"nc_script\" type=\"text/javascript\">" . $css_script . "</script>".
								$script_str_all."<script class=\"nc_script\" type=\"text/javascript\">" . $script_str. "</script>";
				//} else if($script_result_all != "") {
				//	add_script_footerをterminateFooterでセットするように修正
				//	$renderer->assign('add_script_footer', $script_result_all);
				}
				//javascriptからheaderの項目を追加しても、
				//jsファイルのインクルードがうまく動作しない
				//今後、jsファイルのインクルードが正常に動くようにブラウザが対応した場合、
				//以下の処理を使用可能
				/*******
				else if($this->_session->getParameter('main_action_name') != DEFAULT_ACTION){
					//ページ表示処理から呼ばれていないならば
					//ページ・ブロックのCSS、JSファイルをヘッダーにインクルード
					list($css_str, $js_str) = $this->_getheaderInc($themeDir, $tempStr, true);
					$script_string = "<script type=\"text/javascript\">" . $css_str . $js_str . "</script>";
				}
				*****/
				//scriptタグ追加
				//$result .= $footer_module_script;
				//$result = $script_string . $result;

				//$renderer->clearAction();
				//$renderer->clearErrorList();
				//$renderer->clearToken();
				//$renderer->clearScriptName();
				//$renderer->clear_assign('lang');
				//$renderer->clear_assign('conf');
			}


		}

		//
		//debug
		//
		if(isset($renderer) && $renderer->debugging && $main_action_name == $action_name) {
			$template_dir = BASE_DIR . "/";
			$template_debug = "webapp/templates/main/debug.html";
			//キャッシュ処理をしない
			$caching = $renderer->getCaching();
			$renderer->setCaching(0);

			//template_dirセット
			$renderer->setTemplateDir($template_dir);
			//Debug部分
			$result = $result.$renderer->fetch($template_debug, "debug",SMARTY_DIR);

			//キャッシュ処理を元に戻す
			$renderer->setCaching($caching);
		}

		//
		//attachment
		//
		if($attachment) {
			if($this->_filterChain->hasFilterByName("FileUpload")) {
				$fileUpload =& $this->_container->getComponent("FileUpload");
				//$count        = $fileUpload->count();
				$download_action_name = $fileUpload->getDownLoadactionName();
				$uploadId     = $fileUpload->getUploadid();
				$extension    = $fileUpload->getExtension();
				$insert_time  = $fileUpload->getInserttime();

		        $originalNames = $fileUpload->getOriginalName();
		        $mimeType     = $fileUpload->getMimeType();
		        $file_size     = $fileUpload->getFilesize();
		        $errormes     = $fileUpload->getErrorMes();
		        $filelist = array();
				foreach($originalNames as $i => $originalName) {
		        //for ($i = 0; $i < $count; $i++) {
//$test = $originalName[$i];
		        	$filelist[$i] = array(
		                'upload_id'           => isset($uploadId[$i]) ? $uploadId[$i] : '',
		                'file_name' => isset($originalName) ? $originalName : '',
		                'action_name'     => $download_action_name,
		                'file_size'     => isset($file_size[$i]) ? $file_size[$i] : '',
		                'mimetype'     => isset($mimeType[$i]) ? $mimeType[$i] : '',
		                'extension'     => isset($extension[$i]) ? $extension[$i] : '',
		                'insert_time'     => isset($insert_time[$i]) ? $insert_time[$i] : '',
		                'error_mes'     => isset($errormes[$i]) ? $errormes[$i] : ''
		            );
				}
//mb_convert_encoding($filename, "UTF-8")
				$template_dir = WEBAPP_DIR . "/templates/"."main/";
				$template_attachment = "attachment_result.html";
				//キャッシュ処理をしない
				$caching = $renderer->getCaching();
				$renderer->setCaching(0);

				//template_dirセット
				$renderer->setTemplateDir($template_dir);

				$renderer->assign("_attachment_list",$filelist);
				$renderer->assign("_attachment_result",$result);
				//<div class='test_class_name'>ファイルのアップロードがすべて終了しました</div>

				$errorList =& $this->_actionChain->getCurErrorList();
				$renderer->setErrorList($errorList);

				//attachment部分
				$result = $renderer->fetch($template_attachment, "attachment_result","/templates/"."main/");
//$test .= " ".var_dump($renderer->get_template_vars("_attachment_list"))." KOKO ";
//$result = $test.$result;
				$renderer->clear_assign("_attachment_list");
				$renderer->clear_assign("_attachment_callback");
				$renderer->clear_assign("_attachment_result");
				//キャッシュ処理を元に戻す
				$renderer->setCaching($caching);
			}
		}

		// コンテンツがfetchされていない場合、レスポンスクラスからコンテンツ取得
		// ob_get_contentsより取得し連結
		$result = ob_get_contents() . $result;



        if ($result != "") {
            $this->_response->setResult($result);
        } else {
        	$this->_response->setResult(null);
        }
        //_outputは、pages_view_main等を呼び出した場合にprintさせないように制御するためのパラメータ
		$output = $this->_request->getParameter("_output");
		if(isset($output) && $output == _OFF) {
			return;
    	}

    	if(isset($template) && substr($template, -4, 4) == ".xml") {
			// xml用のヘッダを設定
			$this->_response->setContentType("text/xml");
		}

		$contentDisposition = $this->_response->getContentDisposition();
		$contentType        = $this->_response->getContentType();
		$result             = $this->_response->getResult();
		$redirect           = $this->_response->getRedirect();
		$redirect_script    = $this->_response->getRedirectScript();
		$script    			= $this->_response->getScript();
		@ob_end_clean();

		if ($redirect) {
			header("Location: ${redirect}");
			return;
		} else if($redirect_script){
			// SSLのログインアクションの場合は、parent.location.hrefを更新
			if(!preg_match("/^http/i", $redirect_script)) {
				$redirect_script = BASE_URL."/".$redirect_script;
			}
			if($action_name == "login_action_main_init") {
				print "<script class=\"nc_script\" type=\"text/javascript\">" . "top.location.href = '$redirect_script';" . "</script>";
			} else {
				print "<script class=\"nc_script\" type=\"text/javascript\">" . "location.href = '$redirect_script';" . "</script>";
			}
			//flush();
			return;
		} else if($result != ""){
			if ($contentDisposition != "") {
				header("Content-disposition: ${contentDisposition}");
			}
			if ($contentType != "") {
				header("Content-type: ${contentType}");
			}
		}

		// debugがonの場合、register_shutdown_functionでログを出力する可能性があるため
		// （gzipしたものと、していないものを出力される）
		// debug中は、gzipしないで出力する
		if($action == $action_name) {
			if(extension_loaded('zlib') && $this->_session->getParameter("_gzip_compression") &&
				($this->_session->getParameter("_php_debug") == _OFF && $this->_session->getParameter("_sql_debug") == _OFF)) {
	    		ob_start('ob_gzhandler');
	    	} else {
	    		ob_start();
	    	}
		}
		if($header) {
			//header表示--------------------------------------------------
			print $result_header;
			//flush();

			print $result;
			//flush();
			print $script;
			//flush();
			//footer表示--------------------------------------------------
			print $result_footer;
			//flush();
		} else {
			print $result;

		}

		//action:指定ではなければflushする
		//if(!$action_flag) {
		if($action == $action_name) {
			ob_end_flush();
			//flush();
		}
    }
    /**
     * ヘッダーインクルード文字列取得
     * @param string $block_color
     * @param string $tempStr
     * @param boolean header_flag
     * @return string css_string
     * @access private
     */
    function _getheaderInc($block_color, $tempStr) {
    	$retcss = "";
    	$css_header = array();
     	if($this->_filterChain->hasFilterByName("HeaderInc")) {
			$HeaderInc =& $this->_filterChain->getFilterByName("HeaderInc");
			if (isset($HeaderInc) && is_object($HeaderInc)) {
				$include_pathList = $HeaderInc->getCssInc();
				if(is_array($include_pathList)) {
					foreach($include_pathList as $include_file_buf){
						//$default_include_file = str_replace("{\$theme_name}","default",$include_file);
						//$default_include_file = str_replace("{\$temp_name}","default",$default_include_file);
						$include_file = str_replace("{\$theme_name}",$block_color,$include_file_buf);
						$include_file = str_replace("{\$temp_name}",$tempStr,$include_file);
						if(strncmp("themes", $include_file, 6) != 0) {
						//if(!preg_match("/^themes/", $include_file)) {
							$pathList = explode("/", $include_file);
							$dir_name = $pathList[0];
							unset($pathList[0]);
							if(!file_exists(WEBAPP_DIR."/modules/" . $dir_name . "/files/css/" . implode("/", $pathList))) {
								$include_file = str_replace("{\$theme_name}","default",$include_file_buf);
								$include_file = str_replace("{\$temp_name}","default",$include_file);
							}
						}
						$css_header[$include_file] = "/".$include_file;

						/*
						if(file_exists(HTDOCS_DIR."/css/" . $include_file)) {
							$css_header[$include_file] = $include_file;
							if($header_flag) {
								$retcss .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"". BASE_URL."/css/" . $include_file ."\" />";
							} else {
								$retcss .= "commonCls.addLink(\"".BASE_URL."/css/" . $include_file."\");";
							}
						}else if(file_exists(HTDOCS_DIR."/css/" . $default_include_file)) {
							if($header_flag) {
								$retcss .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"". BASE_URL."/css/" . $default_include_file ."\" />";
							} else {
								$retcss .= "commonCls.addLink(\"".BASE_URL."/css/" . $default_include_file."\");";
							}
						} else {
							$this->_log->info(sprintf("設定ファイルで指定したファイルが存在しません(%s)",HTDOCS_DIR."/css/" . $include_file), "{$this->_className}#_postfilter");
						}
						*/
					}
				}
				$retcss = implode("|", $css_header);
			}
		}
		return $retcss;
     }
}
?>
