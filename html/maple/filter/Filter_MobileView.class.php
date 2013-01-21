<?php
//
// $Id: Filter_MobileView.class.php,v 1.12 2008/07/31 08:37:55 snakajima Exp $
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
class Filter_MobileView extends Filter
{
	var $_container;

    var $_log;

    var $_filterChain;

    var $_actionChain;

    var $_request;

    var $_response;

    var $_session;

    var $_className = "Filter_MobileView";

    var $_getdata;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Filter_MobileView() {
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
        if ($mobile_flag == _ON) {
        	$this->_prefilter();
        }

        $this->_filterChain->execute();

        if ($mobile_flag == _ON) {
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
		// add by AllCreator
		$_reader_flag = $this->_session->getParameter('_reader_flag');
		$_smartphone_flag = $this->_session->getParameter('_smartphone_flag');
		if ($_reader_flag == _OFF && $_smartphone_flag == _OFF) {
			header("Content-Type:application/xhtml+xml;");
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
        $action =  ($this->_request->getParameter(ACTION_KEY)) ? $this->_request->getParameter(ACTION_KEY) : DEFAULT_MOBILE_ACTION;

		$_smartphone_flag = $this->_session->getParameter('_smartphone_flag');

    	$renderer =& SmartyTemplate::getInstance();
		$action_name = $this->_actionChain->getCurActionName();
		$pathList = explode("_", $action_name);

        $common =& $this->_container->getComponent("commonMain");

		$view = $this->_response->getView();

		$token =& $this->_container->getComponent("Token");
		if (is_object($token)) {
			$renderer->setToken($token);
		}
		$id = $this->_session->getParameter("_id");
		$theme = $this->getAttribute("define:theme", _OFF);
		$themeList = explode(",", $theme);

		$template = $this->getAttribute($view);
		if ($template == "") {
			if($view == USE_CACHE) {
				//キャッシュを使用するため、ファイル名が未指定
			} elseif($view == TOKEN_ERROR_TYPE || $view == VALIDATE_ERROR_TYPE || $view == UPLOAD_ERROR_TYPE) {
				$template = $this->getAttribute("error", "main:mobile_error.html");
				$theme = _ON;
				$themeList = explode(",", $theme);
			} else {
				$this->_log->error(sprintf("テンプレートファイルの取得に失敗しました(%s)",$view), "{$this->_className}#_postfilter");
				exit;
			}
		}

		$_reader_flag = $this->_session->getParameter("_reader_flag");
		if ($_reader_flag == _OFF) {
			$session_param = "&".session_name()."=".session_id();
			$form_session = "<input type=\"hidden\" name=\"".session_name()."\" value=\"".session_id()."\">";
		} else {
			$session_param = "";
			$form_session = "";
		}

		if (preg_match("/^(action:)/", $template)) {
			$actionList = preg_replace("/action:/", "", $template);
			$actionList = explode(",", $actionList);
			$action = trim(array_shift($actionList));

			$this->_actionChain->add($action);

		} elseif (preg_match("/^(location:)/", $template)) {
			$action_obj =& $this->_actionChain->getCurAction();
			$toArray = get_object_vars($action_obj);
			$pattern = array("/location:/", "/{session_param}/", "/{DEFAULT_ACTION}/", "/{DEFAULT_MOBILE_ACTION}/");
			$replace = array("", $session_param, DEFAULT_ACTION, DEFAULT_MOBILE_ACTION);

			foreach ($toArray as $key=>$obj) {
				$type = strtolower(gettype($obj));
				if ($type != "string" && $type != "integer") { continue; }
				$pattern[] = "/{".$key."}/";
				$replace[] = $obj;
			}
			$parameter = preg_replace($pattern, $replace, $template);

			$mobile_modules = $this->_getdata->getParameter("mobile_modules");

			$url = BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".(!empty($parameter) ? $parameter : DEFAULT_ACTION);
			if ($action_name == "login_action_main_init") {
				$mobile_redirect_url = $this->_session->getParameter("_mobile_redirect_url");
				if ($mobile_redirect_url != "") {
					$this->_session->removeParameter("_mobile_redirect_url");

					$url = BASE_URL.INDEX_FILE_NAME."?".substr($mobile_redirect_url, 1).$session_param;
				}
			}
       		header('Location: '.$url);
			return;
		} else {
			$themeStr = "";
			$tempStr = "";
			$retAssign = $common->viewAssign($renderer);
			if (is_array($retAssign)) {
				list($page_id, $themeStr, $tempStr, $min_width_size, $url_htmlspecialchars) = $retAssign;
			}
			$mobile_modules = $this->_getdata->getParameter("mobile_modules");
			if ($action == DEFAULT_MOBILE_ACTION) {
				$current_dir_name = $this->_session->getParameter("_mobile_default_module");
			} else {
				$current_dir_name = $pathList[0];
			}
			$module_name = "";
			if (isset($mobile_modules[_DISPLAY_POSITION_HEADER]) && isset($mobile_modules[_DISPLAY_POSITION_HEADER][$current_dir_name])) {
				$module_name = $mobile_modules[_DISPLAY_POSITION_HEADER][$current_dir_name]["module_name"];
			}
			if (isset($mobile_modules[_DISPLAY_POSITION_CENTER]) && isset($mobile_modules[_DISPLAY_POSITION_CENTER][$current_dir_name])) {
				$module_name = $mobile_modules[_DISPLAY_POSITION_CENTER][$current_dir_name]["module_name"];
			}

			$action_obj =& $this->_actionChain->getCurAction();
			$renderer->setAction($action_obj);

			$errorList =& $this->_actionChain->getCurErrorList();
			$renderer->setErrorList($errorList);

			$renderer->setScriptName($_SERVER['SCRIPT_NAME']);

			$config =& $this->_container->getComponent("configView");
			$meta = $config->getMetaHeader();
			$renderer->assign('header_field', $meta);

			$main_action_name = $this->_session->getParameter('_main_action_name');
			$renderer->assign('main_action_name', $main_action_name);
			$renderer->assign('session_param', $session_param);

			if(is_object($token) && $token->getValue() != ''){
				$token_param = "<input type=\"hidden\" name=\"_token\" value=\"".$token->getValue()."\">";
				$renderer->assign('token_form', $token_param);
			}

			$renderer->assign('session_form', $form_session);

			$renderer->assign('current_module_name', $module_name);

			$block_id = intval($this->_request->getParameter("block_id"));
			$page_id = $this->_session->getParameter("_mobile_page_id");
			$room_id = $this->_session->getParameter("_mobile_room_id");

			$renderer->assign('block_id', $block_id);
			$renderer->assign('page_id', $page_id);
			$renderer->assign('room_id', $room_id);

			$lang = $renderer->get_template_vars("lang");
			$conf = $renderer->get_template_vars("conf");

			// add by AllCreator
			$modulesView =& $this->_container->getComponent("modulesView");
			$mobile_obj = $modulesView->getModuleByDirname("mobile");
			$mobile_config = $config->getConfig($mobile_obj["module_id"], false);
			$renderer->assign('color_back', $mobile_config['mobile_color_back']['conf_value']);
			$renderer->assign('color_text', $mobile_config['mobile_color_text']['conf_value']);
			$renderer->assign('color_link', $mobile_config['mobile_color_link']['conf_value']);
			$renderer->assign('color_vlink', $mobile_config['mobile_color_vlink']['conf_value']);
			$renderer->assign('smartphone_theme_color', $mobile_config['mobile_smt_theme_color']['conf_value']);

			if (preg_match("/^(main:mobile_dialog.html)/", $template)) {
				$method = $this->getAttribute("method", "post");
				$renderer->assign('method', $method);

				$regist_action = $this->getAttribute("regist_action", "");
				$renderer->assign('regist_action', $regist_action);

				$cancel_action = $this->getAttribute("cancel_action", "");
				$renderer->assign('cancel_action', $cancel_action);

				$messages = $this->getAttribute('message', "");
				$messages = explode(",", $messages);

				$confirm_message = $messages[0];
		    	if (preg_match("/^define:/", $confirm_message)) {
		    		$confirm_message = preg_replace("/^define:/", "", $confirm_message);
					$confirm_message = defined($confirm_message) ? constant($confirm_message) : $confirm_message;
    			} elseif (preg_match("/^lang./", $confirm_message)) {
    				$confirm_message = preg_replace("/^lang./", "", $confirm_message);
	    			$confirm_message = isset($lang[$confirm_message]) ? $lang[$confirm_message] : $confirm_message;
    			} elseif (preg_match("/^conf./", $confirm_message)) {
    				$confirm_message = preg_replace("/^conf./", "", $confirm_message);
	    			$confirm_message = isset($conf[$confirm_message]) ? $conf[$confirm_message] : $confirm_message;
    			} else {
	    			$confirm_message = isset($toArray[$confirm_message]) ? $toArray[$confirm_message] : $confirm_message;
    			}
    			if (count($messages) > 1) {
	    			$messages = array_slice($messages, 1);
    				foreach ($messages as $i=>$message) {
				    	if (preg_match("/^define:/", $message)) {
				    		$message = preg_replace("/^define:/", "", $message);
							$message = defined($message) ? constant($message) : $message;
		    			} elseif (preg_match("/^lang./", $message)) {
		    				$message = preg_replace("/^lang./", "", $message);
			    			$message = isset($lang[$message]) ? $lang[$message] : $message;
		    			} elseif (preg_match("/^conf./", $message)) {
		    				$message = preg_replace("/^conf./", "", $message);
			    			$message = isset($conf[$message]) ? $conf[$message] : $message;
		    			} else {
			    			$message = isset($toArray[$message]) ? $toArray[$message] : $message;
		    			}
		    			$messages[$i] = $message;
    				}
    				$confirm_message = vsprintf($confirm_message, $messages);
    			}

				$patterns = array("/\\\\n/s");
				$replacements = array("<br>");
				$confirm_message = preg_replace($patterns, $replacements, $confirm_message);
				$renderer->assign('message', $confirm_message);

				$request_params = $this->_request->getParameters();
				$params = array();
	    		foreach ($request_params as $key=>$val) {
	    			if ($key == "action") { continue; }
	    			if (is_object($val) || is_array($val)) { continue; }
	    			if (preg_match("/^_/", $val)) { continue; }
	    			if (preg_match("/^".session_name()."/", $key)) { continue; }

	    			$params[$key] = $val;
	    		}
				$renderer->assign('params', $params);
				$themeList[0] = _ON;
			}
			if ($themeList[0] == _ON) {
				$templateList = explode(",", $template);
				$result = "";
				foreach ($templateList as $i=>$template) {
					if (preg_match("/^(error_location:)/", $template)) {
						$action_obj =& $this->_actionChain->getCurAction();
						$toArray = get_object_vars($action_obj);
						$pattern = array("/error_location:/", "/{session_param}/", "/{DEFAULT_ACTION}/", "/{DEFAULT_MOBILE_ACTION}/");
						$replace = array("", $session_param, DEFAULT_ACTION, DEFAULT_MOBILE_ACTION);

						foreach ($toArray as $key=>$obj) {
							$type = strtolower(gettype($obj));
							if ($type != "string" && $type != "integer") { continue; }
							$pattern[] = "/{".$key."}/";
							$replace[] = $obj;
						}
						$parameter = preg_replace($pattern, $replace, $template);
						$renderer->assign('error_location', BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".(!empty($parameter) ? $parameter : DEFAULT_MOBILE_ACTION));
						if (count($templateList) == 1) {
							$template = "main:mobile_error.html";
						} else {
							continue;
						}
					}
					$result .= $this->_mobileFetch($renderer, $template, $pathList[0]);
				}
		        if ($result != "") {
		            $this->_response->setResult($result);
		        } else {
		        	$this->_response->setResult(null);
		        }
		        //_outputは、pages_view_main等を呼び出した場合にprintさせないように制御するためのパラメータ
				$output = $this->_request->getParameter("_output");
				if (isset($output) && $output == _OFF) {
					return;
		    	}

				//キャッシュ処理をしない
				$caching = $renderer->getCaching();
				$renderer->setCaching(0);

				if (!empty($mobile_modules)) {
					$renderer->assign('header_modules', $mobile_modules[_DISPLAY_POSITION_HEADER]);
				}
				$renderer->assign('current_dir_name', $current_dir_name);
				$renderer->assign('contents', $result);

				$template_dir = WEBAPP_DIR . "/templates/"."main/";
				$template_name = "mobile.html";

				$renderer->assign('session_param', $session_param);

				//template_dirセット
				$renderer->setTemplateDir($template_dir);
				$result = $renderer->fetch($template_name, "mobile_template", "/templates/"."main/");
				$convertHtml =& $common->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
				if ($_smartphone_flag == _ON) {
					$convert_sjis_flag = false;
				} else {
					$convert_sjis_flag = true;
				}
				$result = $convertHtml->convertMobileHtml($result, $convert_sjis_flag);
				print $result;
				flush();
			}
		}
    }

    /**
     * ポストフィルタ
     * Viewの処理を実行
     * @access private
     */
    function &_mobileFetch(&$renderer, $template, $dir_name)
    {
		$themeStr = "";
		$tempStr = "";
        $common =& $this->_container->getComponent("commonMain");
		$retAssign = $common->viewAssign($renderer);

		$main_action_name = $this->_session->getParameter('_main_action_name');
		if (is_array($retAssign)) {
			list($page_id, $themeStr, $tempStr, $min_width_size, $url_htmlspecialchars) = $retAssign;
		}
		if (preg_match("/main:/", $template)) {
			//main:ならば、mainのテンプレートを使用する
			$template = preg_replace("/main:/", "", $template);
			$sub_main_template_dir = "/templates/main/";
			$main_template_dir = WEBAPP_DIR . $sub_main_template_dir;
		} elseif (preg_match("/common:/", $template)) {
			//common:ならば、モジュールの共通テンプレート
			$template = preg_replace("/common:/", "", $template);
			$sub_main_template_dir = "/" . $dir_name. "/templates/";
			$main_template_dir = MODULE_DIR . $sub_main_template_dir;
		} else {
			if ($tempStr != "") {
				$sub_main_template_dir = "/" . $dir_name. "/templates/".$tempStr."/";
			} else {
				$sub_main_template_dir = "/" . $dir_name. "/templates/default/";	//default固定とする
			}
			$main_template_dir = MODULE_DIR . $sub_main_template_dir;
		}
		$renderer->setTemplateDir($main_template_dir);
		$result = $renderer->fetch($template, $main_action_name, $sub_main_template_dir);
		if (preg_match("/^".ERROR_MESSAGE_PREFIX."/", $result)) {
			$result = preg_replace("/^".ERROR_MESSAGE_PREFIX."/", "", $result);
		}
		return $result;
    }
}
?>
