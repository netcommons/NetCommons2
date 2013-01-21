<?php
/**
 * ページ表示クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
include_once MODULE_DIR.'/headerinc/view/main/Main.class.php';

class Pages_View_Main extends Action
{
	// 使用コンポーネントを受け取るため
	var $blocksView = null;
	var $pagesView = null;
	var $modulesView = null;
	var $requestMain = null;
	var $preexecute = null;
	var $getData = null;
	var $pagesCompmain = null;
	var $session = null;
	var $commonMain = null;
	var $configView = null;
	var $actionChain = null;
	var $request = null;
	var $authCheck = null;

	var $_renderer = null;

	//var $page_obj = array();

	//リクエストパラメータを受け取るため
	var $page_id = null;
	var $active_action = null;
	var $block_id = null;

	var $active_center = null;
	var $active_block_id = null;

	var $_space_type = null;
	var $blocks_obj = array();

	//ページスタイル
	var $active_tab = null;
	var $pagestyle_x = null;
	var $pagestyle_y = null;

	function execute()
	{
		//出力用バッファをクリア(消去)し、出力のバッファリングをオフ
		//$ob_buffer = ob_get_contents();
		//ob_end_clean();

        $this->_renderer =& SmartyTemplate::getInstance();
        $errorList =& $this->actionChain->getCurErrorList();

        //$_user_id = $this->session->getParameter("_user_id");
    	//$_site_id = $this->session->getParameter("_site_id");
    	$pages_obj = $this->getData->getParameter("pages");
 		$buf_layoutmode = $this->session->getParameter("_layoutmode");
    	$this->session->setParameter("_layoutmodePage", $buf_layoutmode);
    	$css = array();
        $js = array();
        $block_objs = array();

        $headerInc = new Headerinc_View_Main();
        //$pages_obj[$this->page_id] =& $this->pages->getPageById($this->page_id);

        //TODO:未使用？
        $this->_space_type = $pages_obj[$this->page_id]['space_type'];

        $this->_renderer->assign('align',$pages_obj[$this->page_id]['align']);
        $this->_renderer->assign('leftmargin',$pages_obj[$this->page_id]['leftmargin']);
        $this->_renderer->assign('rightmargin',$pages_obj[$this->page_id]['rightmargin']);
        $this->_renderer->assign('topmargin',$pages_obj[$this->page_id]['topmargin']);
        $this->_renderer->assign('bottommargin',$pages_obj[$this->page_id]['bottommargin']);

        $headercolumn_page_id = $this->session->getParameter('_headercolumn_page_id');
        $leftcolumn_page_id = $this->session->getParameter('_leftcolumn_page_id');
        $rightcolumn_page_id = $this->session->getParameter('_rightcolumn_page_id');

        //メイン表示位置設定
        if($leftcolumn_page_id == $this->page_id)
        	$main_display_position = _DISPLAY_POSITION_LEFT;
        else if($rightcolumn_page_id == $this->page_id)
        	$main_display_position = _DISPLAY_POSITION_RIGHT;
        else if($headercolumn_page_id == $this->page_id)
        	$main_display_position = _DISPLAY_POSITION_HEADER;
        else
        	$main_display_position = _DISPLAY_POSITION_CENTER;

        //
        // active_centerがある場合、センターカラムに$this->active_centerのアクションを実行したものを
        // 表示する
        //
        $active_center_action = null;
		if($this->active_center != null) {
			$active_center_action = $this->active_center;
		}

		if($active_center_action != null) {
			//センターブロックに表示
			$active_block_error_flag = false;
			if($this->active_block_id != null) {
				$ret = $this->blocksView->getBlockById($this->active_block_id);
				if(isset($ret['block_id'])) {
					$center_auth_id = $this->authCheck->getPageAuthId($this->session->getParameter("_user_id"), $ret['page_id']);
					if($center_auth_id != _AUTH_OTHER) {
						$center_block = $ret;
						$center_block['prefix_id_name'] = "active_center";
					}
				}
			}
			if(!isset($center_block)) {
				$center_block = array();
				$centerPathList = explode("_", $active_center_action);
				$centerModule = $this->modulesView->getModuleByDirname($centerPathList[0]);
				if($centerModule === false || !isset($centerModule['module_id'])) {
					header("Content-type: text/html;charset="._CHARSET.";");
					echo _INVALID_INPUT;
					return 'error';
				}
				$center_block['block_id'] = 0;
				$center_block['page_id'] = $this->page_id;
				$center_block['module_id'] = $centerModule['module_id'];
				$center_block['theme_name']=$centerModule['theme_name'];
				$center_block['temp_name'] = $centerModule['temp_name'];
				$center_block['url'] = "";
				$center_block['action_name'] = $active_center_action;
				$center_block['prefix_id_name'] = "active_center";
				$this->request->setParameter("prefix_id_name", $center_block['prefix_id_name']);
			}
			 //else {
			//	$active_block_error_flag = true;
			//}
			if($center_block !== false && isset($center_block['block_id'])) {
				$params = array(
									"action" =>$active_center_action,
									"temp_name" =>$center_block['temp_name'],
									"page_id" =>$center_block['page_id'],
									"block_id" =>$center_block['block_id'],
									"module_id" =>$center_block['module_id'],
									"prefix_id_name" =>$center_block['prefix_id_name'],
									"theme_name" =>"none",
									"_header" =>"0",
									"_output" =>"0"
								);
			} else {
				$active_block_error_flag = true;
			}
			if(!$active_block_error_flag) {
				ob_start();
				$this->commonMain->getTopId($center_block['block_id'], $center_block['module_id'],$center_block['prefix_id_name']);
				$html_center = $this->preexecute->preExecute($active_center_action, $params, true);
				$this->request->removeParameters(array("prefix_id_name"));
				//$html_center = $this->response->getResult();

				$this->_renderer->assign('content_center_field', $html_center);

				//include_header読み込み
				$headerInc->setParams($active_center_action, $center_block['block_id'],$center_block['theme_name'],$center_block['temp_name']);
				$res = $headerInc->execute();
				if($res) {
					$center_block['action_name'] = $active_center_action;
					$this->pagesCompmain->setHeaderArray($res,$center_block, $all_headerinc_arr);
				}
				$headerinc_buf[$center_block['url'].":".$center_block['action_name'].":".$center_block['theme_name'].":".$center_block['temp_name']] = true;
			} else {
				$active_center_action = null;
			}
		}

        //ブロック情報取得
        if($active_center_action == null) {
	        $page_id_arr = array(
				$this->page_id,
				$leftcolumn_page_id,
				$rightcolumn_page_id,
				$headercolumn_page_id
			);
        } else {
        	$page_id_arr = array(
				$leftcolumn_page_id,
				$rightcolumn_page_id,
				$headercolumn_page_id
			);
        }
		$blocks = $this->blocksView->getBlockByPageId($page_id_arr);

		//$auth_id = $this->session->getParameter("_auth_id");
		//$active_center_actionをassign
		$this->_renderer->assign('active_center_action',$active_center_action);
		//main_action_nameをassign
		$this->_renderer->assign('main_action_name',"pages_view_main");
		//main_display_positionをassign
		$this->_renderer->assign('main_display_position',$main_display_position);

		//add_blockしたブロックID取得
		$_editing_block_id = $this->session->getParameter("_editing_block_id");

		// ページテーマ----------------------------------------------------
		$page_theme_render = "";

		$leftcolumn_flag = $pages_obj[$this->page_id]['leftcolumn_flag'];
		$rightcolumn_flag = $pages_obj[$this->page_id]['rightcolumn_flag'] ;
		$header_flag = $pages_obj[$this->page_id]['header_flag'];
		$footer_flag = $pages_obj[$this->page_id]['footer_flag'];
		$theme_name = $pages_obj[$this->page_id]['theme_name'];
		$temp_name = $pages_obj[$this->page_id]['temp_name'];

		$this->_renderer->assign('page_id',intval($this->page_id));
        $this->_renderer->assign('leftcolumn_flag',intval($leftcolumn_flag));
        $this->_renderer->assign('rightcolumn_flag',intval($rightcolumn_flag));
        $this->_renderer->assign('header_flag',intval($header_flag));
        $this->_renderer->assign('footer_flag',intval($footer_flag));
        $this->_renderer->assign('theme_name',$theme_name);

        $this->_renderer->assign('body_style',$pages_obj[$this->page_id]['body_style']);
        $this->_renderer->assign('header_style',$pages_obj[$this->page_id]['header_style']);
        $this->_renderer->assign('footer_style',$pages_obj[$this->page_id]['footer_style']);
        $this->_renderer->assign('leftcolumn_style',$pages_obj[$this->page_id]['leftcolumn_style']);
        $this->_renderer->assign('centercolumn_style',$pages_obj[$this->page_id]['centercolumn_style']);
        $this->_renderer->assign('rightcolumn_style',$pages_obj[$this->page_id]['rightcolumn_style']);

        if($this->session->getParameter("_auth_id") >= _AUTH_CHIEF) {
			$pagestyle_list = $this->session->getParameter("pagestyle_list");
			if(isset($pagestyle_list[$this->page_id]) && $this->pagestyle_x && $this->pagestyle_y) {
				$this->pagestyle_x = intval($this->pagestyle_x);
				$this->pagestyle_y = intval($this->pagestyle_y);
				$template_dir = $template_dir = MODULE_DIR . "/pages/templates/";
				$this->_renderer->setTemplateDir($template_dir);
				//キャッシュ処理をしない
				$caching = $this->_renderer->getCaching();
	    		$this->_renderer->setCaching(0);

				$template_name = "pagestyle_script.html";
				$this->_renderer->assign('pagestyle_x',$this->pagestyle_x);
				$this->_renderer->assign('pagestyle_y',$this->pagestyle_y);
				if($this->active_tab == null) $this->active_tab = 0;
				$this->_renderer->assign('active_tab',$this->active_tab);

				$page_theme_render = $this->_renderer->fetch($template_name);

				//キャッシュ処理を元に戻す
				$this->_renderer->setCaching($caching);
			}
        }

        $layoutmode = $this->session->getParameter("_layoutmode");
        $this->session->setParameter("_layoutmode_centercolumn", $layoutmode);
        $this->_renderer->assign('layoutmode',$layoutmode);

        //ページテーマに対応したブロックテーマ読み込み
		$theme_list = $this->session->getParameter("_theme_list");

		if($this->session->getParameter("_user_id") == 0) {
			//ログイン前
			// 自動登録
			$autoregist_use = $this->configView->getConfigByConfname(_SYS_CONF_MODID, 'autoregist_use');
			$this->_renderer->assign('autoregist_use',$autoregist_use['conf_value']);

			$config =& $this->getData->getParameter("config");
			$this->_renderer->assign('use_ssl',$config[_SYS_CONF_MODID]['use_ssl']['conf_value']);
		}

		// メイン----------------------------------------------------
		$include_dir_name_css_arr = array();
		$include_block_id_css_arr = array();
		if(is_array($blocks)) {
			$lang = $this->_renderer->get_template_vars("lang");
			$conf = $this->_renderer->get_template_vars("conf");
			//module_obj取得
			$moduleidList = array();
			foreach($blocks as $block) {
				$moduleidList[$block['module_id']] = $block['module_id'];
			}

			//$modules_obj = null;
			$func = array($this, 'fetchcallback');
			list($modules_obj, $include_dir_name_arr) = $this->modulesView->getModulesById($moduleidList, $func);

			$this->getData->setParameter("modules",$modules_obj);

			$default_entry_page_id = 0;
			$count = 0;
			$active_action_err_flag = true;
			foreach($blocks as $block) {
				if($default_entry_page_id != $block['page_id']) {
					//カラムがかわったら
					if($this->session->getParameter("_default_entry_auth_group")) {
			    		if(isset($pages_obj[$block['page_id']]['page_id']) && $pages_obj[$block['page_id']]['space_type'] == _SPACE_TYPE_GROUP && $pages_obj[$block['page_id']]['private_flag'] == _OFF) {
							$this->session->setParameter("_default_entry_auth", $this->session->getParameter("_default_entry_auth_group"));
							$this->session->setParameter("_default_entry_hierarchy", $this->session->getParameter("_default_entry_hierarchy_group"));
			    		} else if($pages_obj[$block['page_id']]['private_flag'] == _ON) {
			    			$this->session->setParameter("_default_entry_auth", $this->session->getParameter("_default_entry_auth_private"));
			    			$this->session->setParameter("_default_entry_hierarchy", $this->session->getParameter("_default_entry_hierarchy_private"));
			    		} else {
			    			$this->session->setParameter("_default_entry_auth", $this->session->getParameter("_default_entry_auth_public"));
			    			$this->session->setParameter("_default_entry_hierarchy", $this->session->getParameter("_default_entry_hierarchy_public"));
			    		}
					} else {
						$this->session->setParameter("_default_entry_auth", _AUTH_GUEST);
						$this->session->setParameter("_default_entry_hierarchy", _HIERARCHY_GUEST);
			    	}
			    	$default_entry_page_id = $block['page_id'];
				}
				if(($pages_obj[$block['page_id']]['display_position'] == _DISPLAY_POSITION_HEADER && !$header_flag) ||
				   ($pages_obj[$block['page_id']]['display_position'] == _DISPLAY_POSITION_LEFT && !$leftcolumn_flag) ||
				   ($pages_obj[$block['page_id']]['display_position'] == _DISPLAY_POSITION_RIGHT && !$rightcolumn_flag)) {
				   	$count++;
				   	continue;
				}
				if($this->session->getParameter("_user_id") == 0) {
					//ログイン前
					$pages_obj[$block['page_id']]['authority_id'] == _AUTH_OTHER;
				} else {
					if($pages_obj[$block['page_id']]['authority_id'] == null) {
						$pages_obj[$block['page_id']]['authority_id'] = $this->session->getParameter("_default_entry_auth");
					}
					$this->session->setParameter("_auth_id", intval($pages_obj[$block['page_id']]['authority_id']));
					$this->session->setParameter("_hierarchy", intval($pages_obj[$block['page_id']]['hierarchy']));
				}

				if(!isset($block['theme_name']) || $block['theme_name']=="" || $this->session->getParameter("_change_blocktheme") == _OFF) {
					//block_theme=任意(ページテーマにあわせる)
					//ブロックテーマの変更を許さない場合等
					$block['theme_name'] = $theme_list[$pages_obj[$block['page_id']]['display_position']];
					$blocks[$count]['theme_name'] = $block['theme_name'];
				}
				$include_block_id_css_arr[] = $block['block_id'];
				$include_dir_name_css_arr[$block['theme_name']] = $block['theme_name'];
				//Active Action指定
				//検索結果等のリンク先で使用
				$active_action_flag = false;
				if($block['action_name'] != "pages_view_grouping" && $this->active_action != null && $this->block_id == $block['block_id']) {
					$pathList = explode("_", $block['action_name']);
					$pathActiveList = explode("_", $this->active_action);
					$active_action_err_flag = false;
					if($pathList[0] == $pathActiveList[0]) {
						$block['action_name'] = $this->active_action;
						$active_action_flag = true;
						$action_name = $block['action_name'];
						$pathList = $pathActiveList;
					} else {
						// エラー
						$errorList->add(get_class($this), _INVALID_INPUT."_".$pathList[0]."_".$pathActiveList[0]);
						break;
					}
				} else {
					$action_name = $block['action_name'];
					$pathList = explode("_", $action_name);
				}
				$this->session->setParameter("_shortcut_flag", intval($block['shortcut_flag']));

				//add_blockかどうか
				//$layoutmode == "on"でなくても編集できるブロックがある
				if(($_editing_block_id != null && $block['block_id'] == $_editing_block_id) && isset($modules_obj[$pathList[0]]['edit_action_name']) && $modules_obj[$pathList[0]]['edit_action_name'] != "") {
					$blocks[$count]['action_name'] =  $modules_obj[$pathList[0]]['edit_action_name'];
					$block['action_name'] =  $modules_obj[$pathList[0]]['edit_action_name'];
					$action_name = $block['action_name'];
					$pathList = explode("_", $action_name);
				}

				if($block['url'] =="" || $block['url'] == null || $block['url'] == BASE_URL) {
					$mysite = true;
				} else {
					$mysite = false;
				}

				//既に読み込んでいる場合、スキップ
				if(!isset($headerinc_buf[$block['url'].":".$block['action_name'].":".$block['theme_name'].":".$block['temp_name']])) {
					if($mysite) {
						//自サイト
						//include_header読み込み
						$headerInc->setParams($block['action_name'],$block['block_id'],$block['theme_name'],$block['temp_name']);
						$res = $headerInc->execute();
						if($res) {
							$this->pagesCompmain->setHeaderArray($res,$block,$all_headerinc_arr);
						}
					} else {
						//他サイト
						$header_url = $block['url']."?action=headerinc_view_main&action_name=".$block['action_name'].$block['parameters']."&theme_name=".$block['theme_name']."&temp_name=".$block['temp_name']."&print_flag=1";
						//TODO:他サイトの場合、キャッシュをもたせるようなしくみが必要
						//また無駄なフィルターをチェックしないようにする？
						$res = $this->requestMain->getResponseHtml($header_url);
						$this->pagesCompmain->setHeaderArray($res,$block,$all_headerinc_arr);
					}
					$headerinc_buf[$block['url'].":".$block['action_name'].":".$block['theme_name'].":".$block['temp_name']] = true;
				}
				if($mysite) {

					//
					//自サイト
					//
					//if($block['shortcut_flag']) {
					//	$blocks[$count]['full_path'] = BASE_URL.INDEX_FILE_NAME."?action=".$block['action_name'].$block['parameters']."&ref_block_id=".$block['block_id'];	//絶対座標に変換
					//} else
						$blocks[$count]['full_path'] = BASE_URL.INDEX_FILE_NAME."?action=".$block['action_name']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];	//絶対座標に変換

					//$active_block_objs[] = $block;
					//$this->preexecute->preExecute($action_name, array("action" =>$action_name, "theme_name" =>$block['theme_name'], "temp_name" =>$block['temp_name'], "block_id" =>$block['block_id'],"_header" =>"0","style" =>"display:none;"));

				} else {
					$blocks[$count]['full_path'] = $block['url']."?action=".$block['action_name']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];	//絶対座標に変換
				}
				//if(!isset($pages_obj[$block['page_id']])) {
				//	//ショートカットが貼り付けてある
				//	$buf_pages = $pages->getPageById($page_id_arr);
				//	//データセット
    			//	$this->getData->setParameter("pages",$block['page_id']);
    			//	$pages_obj[$block['page_id']] = $buf_pages;
				//}
				$block_objs[$pages_obj[$block['page_id']]['display_position']][$block['parent_id']][$block['col_num']][$block['row_num']] = $blocks[$count];
				$block_objs_id[$block['block_id']] =& $blocks[$count];
				$this->getData->setParameter("blocks",$block_objs_id);

				//権限設定
				//$this->session->setParameter("_auth_id",$pages_obj[$block['page_id']]['authority_id']);

				if($block['action_name'] != "pages_view_grouping") {
					//ob_start();
					if($mysite) {
						//parametersの配列分解
						$params_sub = array();
						$params = array(
										"action" =>$block['action_name'],
										"theme_name" =>$block['theme_name'],
										"temp_name" =>$block['temp_name'],
										"page_id" =>$block['page_id'],
										"room_id" =>$pages_obj[$block['page_id']]['room_id'],
										"block_id" =>$block['block_id'],
										"module_id" =>$block['module_id'],
										"_restful_permalink" => $this->request->getParameter("_restful_permalink"),
										"_layoutmode" => $layoutmode,
										"_header" =>"0",
										"_output" =>"0"
									);
						if(isset($block['parameters']) && $block['parameters'] != "") {
							$parameters = split("&", $block['parameters']);
							foreach($parameters as $parameter) {
								$paramArray = split('=',$parameter);
								if($paramArray[0] != "")
		      						$params_sub[$paramArray[0]] = $paramArray[1];
							}
							$params = array_merge($params,$params_sub);
						}
						//TODO:現状、使用していないためコメント　_ref_block_idをセット
						//if($block['shortcut_flag']) {
						//	$buf_id = $params['block_id'];
						//	$params['block_id'] = $params['_ref_block_id'];
						//	$params['_ref_block_id'] = $buf_id;
						//}
						$this->commonMain->getTopId($block['block_id'], $block['module_id']);
						if($active_action_flag) {
							$html[$block['block_id']] = $this->preexecute->preExecute($block['action_name'], $params, true);
						} else {
							$html[$block['block_id']] = $this->preexecute->preExecute($block['action_name'], $params);
						}
						//$block_objs[$block['thread_num']][$block['parent_id']][$block['col_num']][$block['row_num']][$block['block_id']] = $this->response->getResult();
						//$html[$block['block_id']] = $this->response->getResult();
					} else {
						//TODO:他サイトの場合、キャッシュをもたせるようなしくみが必要
						//$block_objs[$block['thread_num']][$block['parent_id']][$block['col_num']][$block['row_num']][$block['block_id']] = $this->requestMain->getResponseHtml($block_obj[$count]['full_path']);
						$html[$block['block_id']] = $this->requestMain->getResponseHtml($blocks[$count]['full_path']);
					}
				}
				$count++;

			}
			if($this->active_action != null && $active_action_err_flag == true) {
				// active_actionの指定があるが、それに対応したブロックなし
				$pathActiveList = explode("_", $this->active_action);
				$errorList->add(get_class($this), sprintf(_ACCESS_FAILURE_DELETE_BLOCK, $this->modulesView->loadModuleName($pathActiveList[0]), CURRENT_URL));
			}
			if ($errorList->isExists()) {
				$this->commonMain->redirectHeader();
			}
			$this->_renderer->clearAction();
			$this->_renderer->clearErrorList();
			$this->_renderer->clearToken();
			$this->_renderer->clearScriptName();
			$this->_renderer->clear_assign('lang');
			$this->_renderer->clear_assign('conf');
			$this->_renderer->assign("lang", $lang);
			$this->_renderer->assign("conf", $conf);
		}

		if ($this->pagesCompmain->setLoginHtml()) {
			$include_dir_name_arr[] = 'login';
		}

		if($_editing_block_id != null) {
			$this->session->removeParameter("_editing_block_id");
		}
		//-------------------------------------------------
        // ヘッダー
        //-------------------------------------------------
        //TODO:configにもたすようにする可能性あり
        //$leftcolumn_flag = $pages_obj[$leftcolumn_page_id]['leftcolumn_flag'];
        //$rightcolumn_flag = $pages_obj[$rightcolumn_page_id]['rightcolumn_flag'];


        //pagetheme
        //if(file_exists(MODULE_DIR."/pages/templates/".$pages_obj[$this->page_id]['theme_name']."/header.html")) {
        //	$this->_renderer->assign('pagetheme_name',$pages_obj[$this->page_id]['theme_name']);
        //} else {
		//	$this->_renderer->assign('pagetheme_name',"default");
        //}

        //setting.gif
/*
        $this->_renderer->assign('_theme_name', $pages_obj[$this->page_id]['theme_name']);
        $theme_arr = explode("_", $pages_obj[$this->page_id]['theme_name']);
        if(count($theme_arr) == 1) {
        	$this->_renderer->assign('_theme_first_name', $pages_obj[$this->page_id]['theme_name']);
			$this->_renderer->assign('_theme_second_name', "default");
        } else {
        	$this->_renderer->assign('_theme_first_name', $theme_arr[0]);
			$this->_renderer->assign('_theme_second_name', $theme_arr[1]);
        }
*/
        //if(file_exists(HTDOCS_DIR."/themes/images/".$pages_obj[$this->page_id]['theme_name']."/setting.gif")) {
        //	$this->_renderer->assign('imagetheme_name',$pages_obj[$this->page_id]['theme_name']);
        //} else {
		//	$this->_renderer->assign('imagetheme_name',"default");
        //}

        $this->_renderer->assign('temp_name',$pages_obj[$this->page_id]['temp_name']);

        //$this->pagesCompmain->showHeader($pages_obj[$this->page_id],$all_headerinc_arr, $include_dir_name_css_arr, $include_block_id_css_arr, true);
        //if($ob_buffer) {
		//	print $ob_buffer;
		//	flush();
		//}
		//-------------------------------------------------
        // コンテンツ
        //-------------------------------------------------
		if(is_array($block_objs)) {
			$template_dir = WEBAPP_DIR . "/templates/"."main/";
			//$template_dir = $template_dir = MODULE_DIR . "/pages/templates/".$pages_obj[$this->page_id]['temp_name']."/";
			$this->_renderer->setTemplateDir($template_dir);
			//キャッシュ処理をしない
			$caching = $this->_renderer->getCaching();
    		$this->_renderer->setCaching(0);
    		$this->_renderer->assign('centercolumn_page_id',$this->page_id);
    		if($header_flag) {
    			//header
    			$this->_renderer->assign('content_header_field',$this->pagesCompmain->setPageFetch($block_objs,$pages_obj,$html,$template_dir,_DISPLAY_POSITION_HEADER,0,0));
    		}
    		if($leftcolumn_flag) {
				$this->_renderer->assign('content_left_field',$this->pagesCompmain->setPageFetch($block_objs,$pages_obj,$html,$template_dir,_DISPLAY_POSITION_LEFT,0,0));
    		}
    		if($active_center_action == null) {
				$this->_renderer->assign('content_center_field',$this->pagesCompmain->setPageFetch($block_objs,$pages_obj,$html,$template_dir,_DISPLAY_POSITION_CENTER,0,0));
    		}
			if($rightcolumn_flag) {
				$this->_renderer->assign('content_right_field',$this->pagesCompmain->setPageFetch($block_objs,$pages_obj,$html,$template_dir,_DISPLAY_POSITION_RIGHT,0,0));
			}
			//モジュール追加ボックス
			if($layoutmode == "on") {
				$self_toppublic_field =& $this->modulesView->getModulesByUsed(_SELF_TOPPUBLIC_ID);

				if($header_flag) {
					$this->_renderer->assign('addblock_header_field',$self_toppublic_field);
				}
				if($leftcolumn_flag) {
					$this->_renderer->assign('addblock_left_field',$self_toppublic_field);
				}
				//$this->_renderer->assign('addblock_left_field',$this->modulesView->getModulesByUsed($this->page_id,$this->_space_type));
				if($pages_obj[$this->page_id]['private_flag'] == _ON) {
					//プライベートスペース
					$_role_auth_id = $this->session->getParameter("_role_auth_id");
					$this->_renderer->assign('addblock_center_field',$this->modulesView->getAuthoritiesModulesByUsed($_role_auth_id));
				} else {
					//グループスペース
					$room_id = $pages_obj[$this->page_id]['room_id'];
					$this->_renderer->assign('addblock_center_field',$this->modulesView->getModulesByUsed($room_id));
				}
				if($rightcolumn_flag) {
					$this->_renderer->assign('addblock_right_field',$self_toppublic_field);
				}
				//$this->_renderer->assign('addblock_right_field',$this->modulesView->getModulesByUsed($this->page_id,$this->_space_type));
			}
			//$template_dir = $template_dir = MODULE_DIR . "/pages/templates/".$pages_obj[$this->page_id]['temp_name']."/";
			$template_dir = MODULE_DIR . "/pages/templates/". $temp_name . "/";
			$this->_renderer->setTemplateDir($template_dir);
			$template_name = "page.html";
			//$this->_renderer->assign('current_url',BASE_URL.INDEX_FILE_NAME."?action=pages_view_main&page_id=".$this->page_id);
			$main_result = $this->_renderer->fetch($template_name, null,"/pages/templates/".$pages_obj[$this->page_id]['temp_name']."/");

			//キャッシュ処理を元に戻す
			$this->_renderer->setCaching($caching);

			//print $main_result;

		}
        if($active_center_action != null) {
			$pathList = explode("_", $active_center_action);
			$include_dir_name_arr[] = $pathList[0];
		}

		$this->pagesCompmain->showHeader($pages_obj[$this->page_id],$include_dir_name_arr, $all_headerinc_arr, $include_dir_name_css_arr, $include_block_id_css_arr, true);
        if(isset($main_result)) print $main_result;

		//-------------------------------------------------
        // フッター
        //-------------------------------------------------
        $this->pagesCompmain->showFooter($pages_obj[$this->page_id], $include_dir_name_arr);

        if($buf_layoutmode == "on" || $buf_layoutmode == "off") {
        	$this->session->setParameter("_layoutmode", $buf_layoutmode);
        }

		// footer_終了----------------------------------------------------
		if($page_theme_render != "") {
        	print $page_theme_render;
        }
		return 'success';

	}

	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $menus_obj
	 * @access	private
	 */
	function &fetchcallback($result) {
		$modules = null;
		$dir_name_arr = array();
		while ($module = $result->fetchRow()) {
			$pathList = explode("_", $module['action_name']);
			$module["module_name"] = $this->modulesView->loadModuleName($pathList[0]);
			$modules[$pathList[0]] = $module;
			$dir_name_arr[] = $pathList[0];
		}
		$ret = array($modules, $dir_name_arr);
		return $ret;
	}
}
?>
