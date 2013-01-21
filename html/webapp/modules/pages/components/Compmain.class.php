<?php
/**
 * ページ表示用コンポーネント
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pages_Components_Compmain {
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	var $_session = null;
	var $_response = null;
	var $_renderer = null;
	var $_getdata = null;

	var $authCheck = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pages_Components_Compmain() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_session =& $this->_container->getComponent("Session");
		$this->_renderer =& SmartyTemplate::getInstance();
		$this->_lang = $this->_session->getParameter("_lang");
		$this->_response =& $this->_container->getComponent("Response");
		$this->_request =& $this->_container->getComponent("Request");
		$this->_getdata =& $this->_container->getComponent("GetData");

		$this->_common =& $this->_container->getComponent("commonMain");
	}

	/**
	 * カラム毎にフェッチ(Smarty)する
	 *
	 * @param object block_obj,object pages_obj,string html,string template_dir, int display_position,int parent_id,int thread_num, boolean grouptop_flag
	 * @return string html
	 *
	 * @access	public
	 **/
	function setPageFetch(&$blocks_obj,&$pages_obj,&$html, $template_dir, $display_position, $parent_id, $thread_num,$grouptop_flag=false)
	{
		//$_user_id = $this->_session->getParameter("_user_id");
    	//$_site_id = $this->_session->getParameter("_site_id");
		$result = "";
		if(isset($blocks_obj[$display_position][$parent_id])) {
			$set_columns = array();
			//Arr['display_position']['parent_id']['thread_num']['col_num']['row_num']
			foreach ($blocks_obj[$display_position][$parent_id] as $col_num => $columns) {
				foreach ($columns as  $row_num => $block) {
					if($block['action_name'] == "pages_view_grouping") {
						$content_field = $this->setPageFetch($blocks_obj,$pages_obj,$html, $template_dir,$display_position, intval($block['block_id']),$thread_num+1);
						//if($content_field != "") {
							//グループ化した子供が存在
							$theme_name = $block['theme_name'];
							list($images_dir, $template_sub_child_dir, $themeCssPath) = $this->_common->themeAssign($this->_renderer, $block['block_id'], $theme_name);
							/*

							if(isset($theme_name) && $theme_name != "") {
								$themeStrList = explode("_", $theme_name);
								if(count($themeStrList) == 1) {
									$template_sub_child_dir = "/themes/".$theme_name."/templates/";
								} else {
									$template_sub_child_dir = "/themes/".array_shift ( $themeStrList )."/templates/".implode("/", $themeStrList)."/";
								}
								$template_child = "block.html";
								if (!@file_exists(STYLE_DIR .  $themeDir . "/" .$template_block)) {
									$theme_name == "";
								}
							}


							if(!isset($theme_name) || $theme_name == "") {
								if(!isset($pages_obj[$block['page_id']])) {
									$pages_obj[$block['page_id']] =& $pagesView->getPageById($block['page_id']);
								}
								$theme_name = $pages_obj[$block['page_id']]['theme_name'];
							}
							$themeStrList = explode("_", $theme_name);
							if(count($themeStrList) == 1) {
								$template_sub_child_dir = "/themes/".$theme_name."/templates/";
							} else {
								$theme_name = array_shift ( $themeStrList );
								$template_sub_child_dir = "/themes/".$theme_name."/templates/".implode("/", $themeStrList)."/";
							}
							*/
							//$template_sub_child_dir = "/templates/".$theme_name."/";
							//$template_child_dir = MODULE_DIR."/blocks" . $template_sub_child_dir;
							$template_child_dir = STYLE_DIR. $template_sub_child_dir . "/";
							$template_child = "block.html";
							//
							//セッション
							//
							$this->authCheck->AuthCheck($block['action_name'],$block['page_id'],$block['block_id']);
							//$this->_renderer->setSession($this->_session);

							//blocks/theme/以下にblock.htmlがあれば優先的に使用
							if (!@file_exists($template_child_dir . $template_child)) {
								//レイアウトモードをセッションより取得
								$layoutmode = $this->_session->getParameter("_layoutmode");
								$_layoutmode_onetime =  $this->_request->getParameter("_layoutmode_onetime");
								if(isset($_layoutmode_onetime)) {
									$layoutmode = $_layoutmode_onetime;
								}
								if($this->_session->getParameter("_auth_id") >= _AUTH_CHIEF) {
									$template_child_dir .= $layoutmode ."/";
								} else {
									$template_child_dir .= "off" ."/";
								}
							}
							$block['full_path'] = htmlspecialchars($block['full_path'], ENT_QUOTES);	//preg_replace("/&amp;/i", '&', htmlspecialchars($block['full_path'], ENT_QUOTES));
							$this->_renderer->assign('block_obj',$block);
							$this->_renderer->assign('block_id',$block['block_id']);

							$this->_renderer->assign('action_name',$block['action_name']);
							$this->_renderer->assign('content_field',$content_field);
							$this->_renderer->assign('url',$block['full_path']);
							$this->_renderer->assign('encode_url',rawurlencode($block['full_path']));
							$this->_renderer->assign('id',"_".$block['block_id']);
							$this->_renderer->assign('module_obj',"");
							$this->_renderer->assign('headermenu',"");

							//最小広さ設定
							if($block['min_width_size']=="" || $block['min_width_size']==0) {
								$table_min_width_size = "100%";
								//$table_min_width_size = "auto";
							} else {
								$table_min_width_size = $block['min_width_size']."px";
							}
							$this->_renderer->assign('min_width_size',"width:".$table_min_width_size.";");

							$this->_renderer->setTemplateDir($template_child_dir);

							$column_html = $this->_renderer->fetch($template_child, "block_theme".$block['block_id'],$template_child_dir);
							//Blockテーマヘッダー情報を追加
							$set_columns[$col_num][$row_num] = $this->_setBlockHeader($column_html,$block['block_id'],$block['full_path'],$theme_name, $table_min_width_size);
						//}
					} else {
						$set_columns[$col_num][$row_num] = $html[$block['block_id']];
					}

					$set_style[$col_num][$row_num] = "padding:".$block['topmargin']."px ".$block['rightmargin']."px ".$block['bottommargin']."px ".$block['leftmargin']."px;";
				}
			}
			if(!$grouptop_flag) {
				$this->_renderer->setTemplateDir($template_dir);
				$template = "column.html";

				$this->_renderer->assign('columns',$set_columns);
				$this->_renderer->assign('style',$set_style);
				$result = $this->_renderer->fetch($template,"column_".$display_position."_".$parent_id."_".$thread_num,"/templates/main/");
			} else {
				$result = $set_columns[$col_num][$row_num];
			}
		} else {
			//ブロック追加に備えて、空のcolumnを挿入しておく。
			$this->_renderer->setTemplateDir($template_dir);
			$template = "column.html";
			$this->_renderer->assign('columns',"");
			$this->_renderer->assign('style',"");
			$result = $this->_renderer->fetch($template,"column_".$display_position."_".$parent_id."_".$thread_num,"/templates/main/");
		}
		return $result;
	}

	/**
	 * Blockテーマヘッダー情報を追加
	 * id=_$block_id class=ThemeName
	 * DIVタグにするとモジュールのみの表示した場合に全画面表示(Width:100%)で表示されるため、tableタグを現状用いる
	 *
	 * @param 　string $result:fetch結果
	 * 			 int $block_id:ブロックID
	 * 			 string $theme_name:ブロックテーマ名称
	 * 			 int $min_width_size:最小Width
	 *			 string $style:&style=visibility:hidden;width:100px;のように指定
	 * @return string html：fetch結果
	 *
	 * @access	private
	 **/
	function _setBlockHeader($result,$block_id,$url,$theme_name,$min_width_size,$style="")
	{
		$auth_id = $this->_session->getParameter("_auth_id");
		if($auth_id >= _AUTH_CHIEF) $chief_flag = _ON;
		else $chief_flag = _OFF;

		$script_str =& $this->_getdata->getParameter("script_str");
		//$token =& $this->_container->getComponent("Token");
		$url_htmlspecialchars = htmlspecialchars($url, ENT_QUOTES);	//preg_replace("/&amp;/i", '&', htmlspecialchars($url, ENT_QUOTES));
		//
		//パラメータのstyle属性付与
		//
		//$style = $this->_request->getParameter("style");
		//if($style != "") {
		//	//styleパラメータは、&style=visibility:hidden;width:100px;のように指定すること
		//	$style = " style=\"".$style."\"";
		//}

		//
		// Blockテーマヘッダー情報を追加
		// id=_$block_id class=ThemeName
		// DIVタグにするとモジュールのみの表示した場合に全画面表示(Width:100%)で表示されるため、tableタグを現状用いる
		$block_theme_header = "<table id=\"_" . $block_id . "\" class=\"blockstyle_" . $block_id . " module_box module_grouping_box " . $theme_name . "\" style=\"width:" . $min_width_size . ";\"><tr><td>";
		$layoutmode = $this->_session->getParameter("_layoutmode");
		if($layoutmode == "on") {
			$template_block_dir = "/templates/default/";		//TODO:default固定
			//block.html固定
			$template_block = "header.html";
			$this->_renderer->setTemplateDir(STYLE_DIR .  $template_block_dir);
			$heder_btn = $this->_renderer->fetch($template_block, "block_header".$block_id, $template_block_dir);
			$block_theme_header .= $heder_btn;
		}
		//if(is_object($token)){
		//	$token_url = "?prefix="."pages"."&block_id=".$block_id;
		//	$token->setName($token_url);
		//	$block_theme_header .= "<input type=\"hidden\" class=\"_token\" value=\"".$token->getValue()."\"/>";
		//}
		$block_theme_header .= "<input type=\"hidden\" class=\"_url\" value=\"".$url_htmlspecialchars."\"/>";
		//透過Gif挿入：min-width指定(IEが未対応のため画像とする:2006/02/02 By Ryuji Masukawa)
		$block_theme_footer = "";
		if($min_width_size != "auto") {
			$block_theme_footer .= "<img alt=\"\" src=\"".get_image_url()."/images/common/blank.gif\" style=\"height:0px;width:". $min_width_size .";\" />";
		}
		$block_theme_footer .= "</td></tr></table>";
		//$block_theme_footer .= "<script type=\"text/javascript\">commonCls.moduleInit(\"_" . $block_id."\");</script>";
		$script_str .= "commonCls.moduleInit(\"_" . $block_id."\",".$chief_flag.");";
		//header_btn
		$heder_btn = "";
		$result = $block_theme_header . $result . $block_theme_footer;
		return $result;
	}

	function setHeaderArray(&$res,&$obj,&$all_headerinc_arr)
	{
		$headerinc_arr = unserialize($res);

		/*
		 TODO:1つに統合したためコメント
		foreach (array_keys($headerinc_arr['js']) as $value) {
		    if($headerinc_arr['js'][$value]) {
		    	$headerinc_arr['js'][$value] = str_replace("{\$theme_name}",$obj['theme_name'],$headerinc_arr['js'][$value]);
		    	$headerinc_arr['js'][$value] = str_replace("{\$temp_name}",$obj['temp_name'],$headerinc_arr['js'][$value]);
		    	$headerinc_arr['js'][$value] = str_replace("{\$lang}",$this->_lang,$headerinc_arr['js'][$value]);
		    	$all_headerinc_arr['js'][$value] = $headerinc_arr['js'][$value];
		    }
		}
		foreach (array_keys($headerinc_arr['css']) as $value) {
			if($headerinc_arr['css'][$value]) {
		    	//TODO:コメント
		    	////$headerinc_arr['css'][$value] = str_replace("{\$theme_name}",$obj['theme_name'],$headerinc_arr['css'][$value]);
		    	$headerinc_arr['css'][$value] = str_replace("{\$temp_name}",$obj['temp_name'],$headerinc_arr['css'][$value]);
		    	$all_headerinc_arr['css'][$value] = $headerinc_arr['css'][$value];
		    }
		}
		*/
		$themeStrList = explode("_", $obj['theme_name']);
		if(count($themeStrList) == 1) {
			$theme_name = $obj['theme_name'];
		} else {
			$theme_name = $themeStrList[1];
		}
		foreach ($headerinc_arr as $key => $headerinc) {
			foreach (array_keys($headerinc_arr[$key]) as $value) {
				$include_file = str_replace("{\$theme_name}",$theme_name,$headerinc_arr[$key][$value]);
			    $include_file = str_replace("{\$temp_name}",$obj['temp_name'],$include_file);

			    if(!preg_match("/^themes/", $include_file)) {
					$pathList = explode("/", $include_file);
					$dir_name = $pathList[0];
					unset($pathList[0]);
					if(!file_exists(WEBAPP_DIR."/modules/" . $dir_name . "/files/css/" . implode("/", $pathList))) {
						$include_file = str_replace("{\$theme_name}","default",$headerinc_arr[$key][$value]);
						$include_file = str_replace("{\$temp_name}","default",$include_file);
					}
				}

			    //if(!file_exists(HTDOCS_DIR."/".$include_file)) {
			    //	$include_file = str_replace("{\$theme_name}","default",$headerinc_arr[$key][$value]);
			    //	$include_file = str_replace("{\$temp_name}","default",$include_file);
			    //}
			    $all_headerinc_arr['css']["/".$include_file] = "/".$include_file;
			    ////$all_headerinc_arr['css'][$value] = BASE_URL."/".$include_file;
			}
		}
	}
	/**
	 * ヘッダー表示
	 * @param array   page
	 * @param array   $all_headerinc_arr
	 * @param array   $include_dir_name_css_arr
	 * param   boolean login_flag(default=null)
	 *
	 * @access	public
	 **/
	function showHeader(&$page_obj, $include_dir_name_arr, &$all_headerinc_arr, $include_dir_name_css_arr, $include_block_id_css_arr, $login_flag=null)
	{
		$token =& $this->_container->getComponent("Token");
		if (is_object($token)) {
			$this->_renderer->setToken($token);
		}

		//if (is_object($this->_session)) {
		//	$this->_renderer->setSession($this->_session);
		//}

		//
		// ヘッダー、META情報取得
		//
		$config =& $this->_container->getComponent("configView");
		$headercolumn_page_id = $this->_session->getParameter('_headercolumn_page_id');
		$leftcolumn_page_id = $this->_session->getParameter('_leftcolumn_page_id');
		$rightcolumn_page_id = $this->_session->getParameter('_rightcolumn_page_id');
		$page_id_arr = array(
			$page_obj['page_id'],
			$leftcolumn_page_id,
			$rightcolumn_page_id,
			$headercolumn_page_id
		);
		$meta = $config->getMetaHeader($page_id_arr, $include_dir_name_arr);

		//ログイン
		//default固定
		if($login_flag) {
			$meta['css_header']["/login/default/style.css"] = "/login/default/style.css";
		}
		$meta['css_header_pagetheme'] = $page_obj['theme_name'];
		flush();
		/*
		 * TODO:一つに統合したため、コメント
		    if(isset($all_headerinc_arr['js'])) {
			foreach($all_headerinc_arr['js'] as $value) {
				if($value) {
					$meta['script_header'] .= "<script type=\"text/javascript\" src=\"".$value."\"></script>";
				}
			}
		}*/
		if(isset($all_headerinc_arr['css'])) {
			foreach($all_headerinc_arr['css'] as $key=>$value) {
				if($value) {
					$meta['css_header'][$value] = $value;
				}
			}
		}
		// カスタム用CSS
		foreach($include_dir_name_css_arr as $include_dir_name_css) {
			$meta['css_header'][$include_dir_name_css] = $include_dir_name_css;
		}
		if(count($include_dir_name_css_arr) >= 1) {
			$meta['css_header_block_id'] = $include_block_id_css_arr;
		}
		//キャッシュ処理をしない
		$caching = $this->_renderer->getCaching();
		$this->_renderer->setCaching(0);

		$this->_renderer->assign('header_field',$meta);

		$template_dir = WEBAPP_DIR . "/templates/"."main/";
		$template_name = "header.html";

		//template_dirセット
		$this->_renderer->setTemplateDir($template_dir);
		$result = $this->_renderer->fetch($template_name, "page_header","/templates/"."main/");
		print $result;
		flush();

		//キャッシュ処理を元に戻す
		$this->_renderer->setCaching($caching);

		//header_終了----------------------------------------------------

		//debug
		if($this->_renderer->debugging) {
			$template_dir = BASE_DIR . "/";
			$template_debug = "webapp/templates/main/debug.html";
			//キャッシュ処理をしない
			$caching = $this->_renderer->getCaching();
			$this->_renderer->setCaching(0);

			//template_dirセット
			$this->_renderer->setTemplateDir($template_dir);

			//キャッシュ処理を元に戻す
			$this->_renderer->setCaching($caching);
			//Debug部分
			print $this->_renderer->fetch($template_debug, "debug","/templates/"."main/");
			flush();
		}
		//UTF-8
		// 直接指定：safariが文字化けするため
		//$this->_response->setContentType("text/html; charset=utf-8");

		//$contentDisposition = $this->_response->getContentDisposition();
		//$contentType        = $this->_response->getContentType();
		//$result             = $this->_response->getResult();
		//$redirect           = $this->_response->getRedirect();
		//$redirect_script    = $this->_response->getRedirectScript();
		//$script    			= $this->_response->getScript();
	}

	/**
	 * フッター表示
	 * @param array   page_object
	 * @param array $page_obj
	 *
	 * @access	public
	 **/
	function showFooter(&$page_obj,$include_dir_name_arr)
	{
		$config =& $this->_container->getComponent("configView");
		//$footer_field =& $this->_getdata->getParameter("footer_field");
		//$timeout_time = $this->_session->getParameter('_session_gc_maxlifetime')*60;
		//$footer_field['script_footer'] .= "commonCls.commonInit('"._SESSION_TIMEOUT_ALERT."',".$timeout_time.");";
		//if($login_flag) {
        //	$footer_field['script_footer'] .= "loginCls['_0'] = new clsLogin(\"_0\");";
		//}
		//footer表示--------------------------------------------------
		//$footer_field['template_footer'] = "";

		$this->_renderer->assign('footer_field',$config->terminateFooter($include_dir_name_arr));

		$template_dir = WEBAPP_DIR . "/templates/"."main/";
		$template_name = "footer.html";

		//キャッシュ処理をしない
		$caching = $this->_renderer->getCaching();
		$this->_renderer->setCaching(0);

		//template_dirセット
		$this->_renderer->setTemplateDir($template_dir);
		$result = $this->_renderer->fetch($template_name, "page_footer","/templates/"."main/");
		print $result;

		//キャッシュ処理を元に戻す
		$this->_renderer->setCaching($caching);

		//footer_終了----------------------------------------------------	
	}

	/**
	 * ログイン画面用のHTMLをテンプレートにアサインする
	 *
	 * @return boolean	true:アサインした、false:アサインしてない
	 * @access	public
	 */
	function setLoginHtml() 
	{
		$userId = $this->_session->getParameter('_user_id');
		if (!empty($userId)) {
			return false;
		}

		$this->_common->getTopId('0', '0', '');
		$preexecute =& $this->_container->getComponent('preexecuteMain');
		$params = array('action' => 'login_view_main_init'
					, '_header' => _OFF
					, '_output' => _OFF);
		$loginHtml = $preexecute->preExecute('login_view_main_init', $params);
		$this->_renderer->assign('loginHtml', $loginHtml);

		return true;
	}
}
?>
