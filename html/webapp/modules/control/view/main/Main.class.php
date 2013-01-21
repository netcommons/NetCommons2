<?php
/**
 * 管理画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
include_once MODULE_DIR.'/headerinc/view/main/Main.class.php';

class Control_View_Main extends Action
{
	// リクエストパラメータを受け取るため
	var $current_page_id = null;
	
	// 使用コンポーネントを受け取るため
	var $blocks = null;
	var $modules = null;
	var $session = null;
	var $getdata = null;
	var $db = null;
	
	var $_lang = null;
	
	function execute()
	{
		//出力用バッファをクリア(消去)し、出力のバッファリングをオフ
		//$ob_buffer = ob_get_contents();
		//ob_end_clean();
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$renderer =& SmartyTemplate::getInstance();
		$script_str =& $this->getdata->getParameter("script_str");
		$script_str .= "controlCls.controlInit();";
        $this->getdata->setParameter("script_str", $script_str);
		
		$this->current_page_id = intval($this->current_page_id);
		if($this->session->getParameter("_permalink_flag") && $this->current_page_id > 0) {
			$result = $this->db->selectExecute("pages", array("page_id" => $this->current_page_id), null, 1, 0);
			if(isset($result[0]) && $result[0]['permalink'] != "") {
				$edit_end_url = BASE_URL.'/'.$result[0]['permalink'];
				if($result[0]['permalink'] != "") $edit_end_url .= '/';
			} else {
				$edit_end_url = BASE_URL.'/';
			}
		} else {
			$edit_end_url = BASE_URL;
			if($this->current_page_id > 0) {
				$edit_end_url .= "/?page_id=".$this->current_page_id;
			}
		}
		$renderer->assign('edit_end_url',$edit_end_url);
		
		//$session =& $container->getComponent("Session");
        $this->_lang = $this->session->getParameter("_lang");
        //$_user_id = $session->getParameter("_user_id");
    	//$_site_id = $session->getParameter("_site_id");
        
        //$main_cache_id = "main_control_panel";
    	//$user_main_cache_id = "control_panel" . md5($session->getID()). "_" .$_user_id . "_" . $_site_id;
    	
		
		$token =& $container->getComponent("Token");
		if (is_object($token)) {
			$renderer->setToken($token);
		}

		//$session =& $container->getComponent("Session");
		$this->session->setParameter("_page_title",CONTROL_TITLE);
		//if (is_object($this->session)) {
		//	$renderer->setSession($this->session);
		//}
		$url = BASE_URL.INDEX_FILE_NAME."?action=control_view_main";
        //URL-Assign
		$renderer->assign('url',$url);
		
		//header表示--------------------------------------------------
		//
		// ヘッダー、META情報取得
		//
		$config =& $container->getComponent("configView");
		
		$themeStr = "default";
					
		//コントロールjs
		//$meta['script_header'] .= "<script type=\"text/javascript\" src=\"./js/control/control.js\"></script>";
		//
		//データ取得
		//
		$modules_obj =& $this->modules->getModulesByRoleAuthorityId($this->session->getParameter("_role_auth_id"));
        //$modules_obj =& $this->modules->getModulesBySystemflag(1);

        $headerInc = new Headerinc_View_Main();
        $all_headerinc_arr = array();
        $include_dir_name_arr = array();
        foreach($modules_obj as $key=>$module_obj) {
        	$pathList = explode("_", $module_obj['action_name']);
        	$modules_obj[$key]['dir_name'] = $pathList[0];
        
        	//include_header読み込み
			$headerInc->setParams($module_obj['action_name'],null,$module_obj['theme_name'],$module_obj['temp_name']);
			$res = $headerInc->execute();
			if($res) {
				$this->_setHeaderArray($res,$all_headerinc_arr,$module_obj);
			}
			//iconセット
			//なければnoimageを表示
			$image_path = HTDOCS_DIR  . "/images/" . $pathList[0] ."/" . $modules_obj[$key]['module_icon'];
			if(file_exists($image_path)) {
				$modules_obj[$key]['icon_path'] = $pathList[0]. "/" . $modules_obj[$key]['module_icon'];
			} else {
				$modules_obj[$key]['icon_path'] = "common/noimage.gif";	
			}
			$include_dir_name_arr[] = $pathList[0];
		}
		$renderer->assign('modules_obj',$modules_obj);
		$meta = $config->getMetaHeader(null, $include_dir_name_arr, true);
		//コントロールCSS
		////$meta['css_header'] .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"./css/control/style.css\" />";
		
		//if(isset($all_headerinc_arr['js'])) {
		//	foreach($all_headerinc_arr['js'] as $value) {
		//		if($value) {
		//			$meta['script_header'] .= "<script type=\"text/javascript\" src=\"".$value."\"></script>";
		//		}
		//	}
		//}

		if(isset($all_headerinc_arr['css'])) {
			foreach($all_headerinc_arr['css'] as $key=>$value) {
				if($value) {
					$meta['css_header'][$value] = $value;
				}
			}
		}
		
		// モジュールのJavascriptインクルード
		$meta['template_header'] = "";
		if(isset($all_headerinc_arr['template'])) {
			foreach($all_headerinc_arr['template'] as $value) {
				if($value)
					$meta['template_header'] .= $value;
			}
		}
		
		$renderer->assign('header_field',$meta);
		
		$template_dir = WEBAPP_DIR . "/templates/"."main/";
		$template_name = "header.html";
		
		//キャッシュ処理をしない
		$caching = $renderer->getCaching();
		$renderer->setCaching(0);
		
		//template_dirセット
		$renderer->setTemplateDir($template_dir);
		$result = $renderer->fetch($template_name,"control_header","/templates/"."main/");
		print $result;
		flush();
		
		//キャッシュ処理を元に戻す
		$renderer->setCaching($caching);
		
		//$token =& $container->getComponent("Token");
		//$timeout_time = $this->session->getParameter('_session_gc_maxlifetime')*60;
		//$footer_field['script_footer'] .= "commonCls.commonInit('"._SESSION_TIMEOUT_ALERT."',".$timeout_time.");";
        //$footer_field['script_footer'] .= "loginCls['_0'] = new clsLogin(\"_0\");";
        
        flush();
        //header終了----------------------------------------------------
        //if($ob_buffer) {
        //	print $ob_buffer;
		//	flush();
        //}
		//debug
		if($renderer->debugging) {
			$template_dir = BASE_DIR . "/";
			$template_debug = "webapp/templates/main/debug.html";
			//キャッシュ処理をしない
			$caching = $renderer->getCaching();
			$renderer->setCaching(0);
			
			//template_dirセット
			$renderer->setTemplateDir($template_dir);
			//Debug部分
			print $renderer->fetch($template_debug,"control_debug","/templates/"."main/");
			flush();
			
			//キャッシュ処理を元に戻す
			$renderer->setCaching($caching);
		}
        //
        //cache_idセット
        //
        $common =& $container->getComponent("commonMain");
    	$cache_id = $common->getCacheid();
    	
        $template_dir = MODULE_DIR . "/control/templates/default/";	//default固定
		$template_name = "control.html";
		
		//キャッシュ処理をしない
		$caching = $renderer->getCaching();
		$renderer->setCaching(0);
		
		//template_dirセット
		$renderer->setTemplateDir($template_dir);
		$result = $renderer->fetch($template_name,$cache_id,"/control/templates/default/");
		print $result;
		flush();
        
		//キャッシュ処理を元に戻す
		$renderer->setCaching($caching);
		
        //footer表示--------------------------------------------------
        
        //$footer_field['template_footer'] = "";
		$renderer->assign('footer_field',$config->terminateFooter($include_dir_name_arr, true));
		
		$template_dir = WEBAPP_DIR . "/templates/"."main/";
		$template_name = "footer.html";
		
		//キャッシュ処理をしない
		$caching = $renderer->getCaching();
		$renderer->setCaching(0);
		
		//template_dirセット
		$renderer->setTemplateDir($template_dir);
		$result = $renderer->fetch($template_name, "control_footer","/templates/"."main/");
		print $result;
		
		//キャッシュ処理を元に戻す
		$renderer->setCaching($caching);
		
		//footer終了----------------------------------------------------
		return 'success';
	}
	
	function _setHeaderArray(&$res,&$all_headerinc_arr,&$module_obj)
	{
		$headerinc_arr = unserialize($res);
		/*
		foreach (array_keys($headerinc_arr['js']) as $value) {
		    if($headerinc_arr['js'][$value]) {
		    	$headerinc_arr['js'][$value] = str_replace("{\$theme_name}",$module_obj['theme_name'],$headerinc_arr['js'][$value]);
		    	$headerinc_arr['js'][$value] = str_replace("{\$temp_name}",$module_obj['temp_name'],$headerinc_arr['js'][$value]);
		    	$headerinc_arr['js'][$value] = str_replace("{\$lang}",$this->_lang,$headerinc_arr['js'][$value]);
		    	$all_headerinc_arr['js'][$value] = $headerinc_arr['js'][$value];
		    }
		}
		foreach (array_keys($headerinc_arr['css']) as $value) {
		    if($headerinc_arr['css'][$value]) {
		    	$headerinc_arr['css'][$value] = str_replace("{\$theme_name}",$module_obj['theme_name'],$headerinc_arr['css'][$value]);
		    	$headerinc_arr['css'][$value] = str_replace("{\$temp_name}",$module_obj['temp_name'],$headerinc_arr['css'][$value]);
		    	$all_headerinc_arr['css'][$value] = $headerinc_arr['css'][$value];
		    }
		}
		*/
		$themeStrList = explode("_", $module_obj['theme_name']);
		if(count($themeStrList) == 1) {
			$theme_name = $module_obj['theme_name'];
		} else {
			$theme_name = $themeStrList[1];
		}
		//foreach (array_keys($headerinc_arr['css']) as $value) {
		//	$headerinc_arr['css'][$value] = str_replace("{\$theme_name}",$theme_name,$headerinc_arr['css'][$value]);
		//    $headerinc_arr['css'][$value] = str_replace("{\$temp_name}",$module_obj['temp_name'],$headerinc_arr['css'][$value]);
		//    $all_headerinc_arr['css'][$value] = $headerinc_arr['css'][$value];
		//}
		foreach ($headerinc_arr as $key => $headerinc) {
			foreach (array_keys($headerinc_arr[$key]) as $value) {
				$include_file = str_replace("{\$theme_name}",$theme_name,$headerinc_arr[$key][$value]);
			    $include_file = str_replace("{\$temp_name}",$module_obj['temp_name'],$include_file);
			   
			    if(!file_exists(HTDOCS_DIR."/".$include_file)) {
			    	$include_file = str_replace("{\$theme_name}","default",$headerinc_arr[$key][$value]);
			    	$include_file = str_replace("{\$temp_name}","default",$include_file);
			    }
			    $all_headerinc_arr['css']["/".$include_file] = "/".$include_file;
			    ////$all_headerinc_arr['css'][$value] = BASE_URL."/".$include_file;
			}
		}
		//foreach (array_keys($headerinc_arr['template']) as $value) {
		//	if($headerinc_arr['template'][$value]) {
		//		$all_headerinc_arr['template'][$value] = $headerinc_arr['template'][$value];
		//	}
		//}
	}
}
?>