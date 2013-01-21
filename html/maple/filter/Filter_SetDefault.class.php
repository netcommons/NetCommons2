<?php

//require_once MAPLE_DIR.'/nccore/GetData.class.php';

 /**
 * 初期処理を行うFilter
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_SetDefault extends Filter {

    var $_container;

    var $_log;

    var $_filterChain;

    var $_actionChain;

    var $_request;

    var $_response;

    var $_session;

    var $_className;

    var $_getdata;

    var $_errorList;

    var $_url;

    var $action_name = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Filter_SetDefault() {
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
        //$this->_response =& $this->_container->getComponent("Response");
        $this->_session =& $this->_container->getComponent("Session");
        $this->_getdata =& $this->_container->getComponent("GetData");

        $this->_className = get_class($this);

        $this->_errorList =& $this->_actionChain->getCurErrorList();

        // ----------------------------------------------
		// ---  CURRENT_URL定義　　  　　            ---
		// ----------------------------------------------
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$url = BASE_URL_HTTPS.INDEX_FILE_NAME.$this->_request->getStrParameters();
		} else {
			$url = BASE_URL.INDEX_FILE_NAME.$this->_request->getStrParameters();
		}
		define("CURRENT_URL",$url);

        // ----------------------------------------------
		// ---  PERMALINK_URL定義　　　　            ---
		// ----------------------------------------------
		if($this->_session->getParameter("_permalink_flag") == _OFF) {
			$url = CURRENT_URL;
		} else {
			$_restful_permalink = $this->_request->getParameter("_restful_permalink");
			$request_str = $this->_request->getStrParameters(false);
			if(isset($_restful_permalink) && $_restful_permalink != '') {
				$url = BASE_URL.'/'.$_restful_permalink.'/'.$request_str;
			} else {
				$url = BASE_URL.'/'.$request_str;
			}
		}
		define("PERMALINK_URL", $url);

		// ----------------------------------------------
		// ---  _JS_VERSION定義    　　　　            ---
		// -----------------------------------------------
		$db =& $this->_container->getComponent("DbObject");
		$js_update_time = $db->maxExecute("javascript_files", "update_time");
		define("_JS_VERSION", $js_update_time);

		$cssUpdateTime = $db->maxExecute('css_files', 'update_time');
		define('_CSS_VERSION', $cssUpdateTime);


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
    	if ($this->_errorList->isExists()) {
    		//既にエラーがあればそのまま返却
    		return;
    	}
    	//block or pageオブジェクト取得
	    $page_id = $this->_request->getParameter("page_id");
    	$block_id = intval($this->_request->getParameter("block_id"));
    	$this->_request->setParameter("block_id", $block_id);

	    $commonMain =& $this->_container->getComponent("commonMain");

    	$attributes = $this->getAttributes();
    	$this->action_name = ($this->_request->getParameter(ACTION_KEY)) ? $this->_request->getParameter(ACTION_KEY) : DEFAULT_ACTION;

    	$mobile_flag = $this->_session->getParameter("_mobile_flag");
		if ($mobile_flag == _ON && $this->action_name == DEFAULT_MOBILE_ACTION) {
			$this->action_name = DEFAULT_ACTION;
		}
		if ($mobile_flag == _ON && $page_id == 0) {
			$page_id = intval($this->_session->getParameter("_mobile_page_id"));
		}

    	//if(!$action_name) {
    	//	$action_name = DEFAULT_ACTION;
    	//	$this->_request->setParameter(ACTION_KEY,DEFAULT_ACTION);
    	//}
    	$this->_url = CURRENT_URL;//BASE_URL.INDEX_FILE_NAME."?".ACTION_KEY."=".$action_name;
   		$pathList = explode("_", $this->action_name);

   		$script_str ="";
   		//$curActionName = $this->_actionChain->getCurActionName();
		if($this->action_name == "pages_view_main" || $this->action_name == "control_view_main" || $this->_request->getParameter("_header")) {
        	$timeout_time = $this->_session->getParameter('_session_gc_maxlifetime')*60;
			$script_str = "commonCls.commonInit('"._SESSION_TIMEOUT_ALERT."',".$timeout_time.");";
			$script_str .= "loginCls['_0'] = new clsLogin(\"_0\");";
		}

		$this->_getdata->setParameter("script_str",$script_str);
		$script_str_all = "";
		$this->_getdata->setParameter("script_str_all",$script_str_all);

    	//ログインしていなければ0をセット
    	$_user_id = $this->_session->getParameter("_user_id");

		$config_obj = $this->_getdata->getParameter("config");

		// 左右カラムのpage_idをセッションにセット
		// （パブリックスペースpage_id | プライベートスペースpage_id | グループスペースpage_id）
		$headercolumn_page_id_str = $config_obj[_PAGESTYLE_CONF_CATID]['headercolumn_page_id']['conf_value'];
		$leftcolumn_page_id_str   = $config_obj[_PAGESTYLE_CONF_CATID]['leftcolumn_page_id']['conf_value'];
		$rightcolumn_page_id_str  = $config_obj[_PAGESTYLE_CONF_CATID]['rightcolumn_page_id']['conf_value'];

		$headercolumn_page_id_arr = explode("|",$headercolumn_page_id_str);
		$leftcolumn_page_id_arr   = explode("|",$leftcolumn_page_id_str);
		$rightcolumn_page_id_arr  = explode("|",$rightcolumn_page_id_str);

		if($config_obj[_PAGESTYLE_CONF_CATID]['column_space_type_use']['conf_value'] == _OFF) {
			// スペースタイプ毎に左右カラム、ヘッダーを分けない
			$headercolumn_page_id_str = $headercolumn_page_id_arr[0];
			$leftcolumn_page_id_str =  $leftcolumn_page_id_arr[0];
			$rightcolumn_page_id_str =  $rightcolumn_page_id_arr[0];

			$headercolumn_page_id_arr = array($headercolumn_page_id_str);
			$leftcolumn_page_id_arr = array($leftcolumn_page_id_str);
			$rightcolumn_page_id_arr = array($rightcolumn_page_id_str);
		}

		//取得ページID配列初期化
		//$modules = $this->_getdata->getParameter("modules");
		$pages = array();
		$default_private_space = 0;
		$page_id_arr = array();
		$pagesView =& $this->_container->getComponent("pagesView");
		//マイページからpage_id取得
		if($page_id == -1) {
			$buf_page_obj_private =& $pagesView->getPrivateSpaceByUserId($_user_id, 1);
			if(isset($buf_page_obj_private[0])) {
				$page_id = $buf_page_obj_private[0]['page_id'];
			} else {
				$this->_errorList->add("Auth_Error", sprintf(_LOGINAGAIN_MES, $this->_url));
				$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
				return;
			}
		}
		if($block_id != 0) {
			$blocksView =& $this->_container->getComponent("blocksView");
			$buf_block = $blocksView->getBlockById($block_id);
			$blocks[$block_id] = $buf_block;
			//データセット
    		$this->_getdata->setParameter("blocks", $blocks);
		}
		if ($this->action_name == DEFAULT_ACTION) {
			// 左右カラム or ヘッダーの権限は、_SELF_TOPPUBLIC_IDの権限
			$set_pages_arr = explode("|", _SELF_TOPPUBLIC_ID."|".$headercolumn_page_id_str."|".$leftcolumn_page_id_str."|".$rightcolumn_page_id_str);
			if($block_id != 0) {
				//
				// page_idの指定はないが、block_idの指定があるならば、blocksテーブルから取得
				// block_idが左右カラム or ヘッダーならば、見れるセンターカラムのpage_idを付与する
				// 見れるpage_idがなければエラーとしリダイレクト
				// page_idの指定があっても、左右カラム or ヘッダーならば変更
				//
				if($buf_block !== false && isset($buf_block['block_id'])) {
					if($page_id == 0) {
						$page_id = $buf_block['page_id'];
					}
					if($page_id == $buf_block['page_id']) {
						$search_key = array_search($buf_block['page_id'], $set_pages_arr);
						if($search_key !== false) {
							// 指定page_idが左右カラム or ヘッダーなのでpage_id付け替え
							// 左カラム、右カラム、ヘッダーのどれにあたるかをみて、レイアウトで表示されているセンターカラムを検索
							if(count($set_pages_arr) == 4) {
								if($search_key == 1) {
									$column_name = "header_flag";
								} else if($search_key == 2) {
									$column_name = "leftcolumn_flag";
								} else {
									$column_name = "rightcolumn_flag";
								}
							} else {
								if($search_key <= 3) {
									$column_name = "header_flag";
								} else if($search_key <= 6) {
									$column_name = "leftcolumn_flag";
								} else {
									$column_name = "rightcolumn_flag";
								}
							}
							if($config_obj[_PAGESTYLE_CONF_CATID][$column_name]['conf_value'] == _ON) {
								$column_name = "("."{pages_style}." . $column_name ."=1 OR ISNULL("."{pages_style}." . $column_name."))";
								$column_value = null;
							} else {
								$column_name = "{pages_style}." . $column_name;
								$column_value = _ON;
							}

							if(count($set_pages_arr) == 4 || ($search_key == 0 || $search_key == 1 || $search_key == 4 || $search_key == 7)) {

								// トップにあるページを表示
								$where_params = array(
									"action_name!=''"=>null,
									"display_sequence!=0"=>null,
									//"space_type"=>_SPACE_TYPE_PUBLIC,
									"display_flag"=>_ON,
									$column_name => $column_value
								);
							} else if($search_key%3 == 0) {
								// グループスペースの左右カラム or ヘッダー
								$where_params = array(
									"action_name!=''"=>null,
									"display_sequence!=0"=>null,
									"space_type"=>_SPACE_TYPE_GROUP,
									"private_flag"=>_OFF,
									"display_flag"=>_ON,
									$column_name => $column_value
								);
							} else {
								// プライベートスペースの左右カラム or ヘッダー
								$where_params = array(
									"action_name!=''"=>null,
									"display_sequence!=0"=>null,
									"space_type"=>_SPACE_TYPE_GROUP,
									"private_flag"=>_ON,
									"display_flag"=>_ON,
									$column_name => $column_value
								);
							}
							$order_params =array(
													"{pages}.space_type" => "ASC",
													"{pages}.thread_num" => "ASC",
													"{pages}.display_sequence" => "ASC",
													"{pages}.default_entry_flag" => "ASC"
													);

							$buf_pages_obj =& $pagesView->getShowPagesList($where_params, $order_params, 1, 0, array($pagesView, 'fetchcallback'));
							if(!isset($buf_pages_obj[0])) {
								// エラー
								$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE, $this->_url));
								$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
							} else {
								//少なくともバブリックページは１ページはあるとして処理
								$page_id = $buf_pages_obj[0]['page_id'];
								$pages[$page_id] = $buf_pages_obj[0];
								unset($set_pages_arr[$page_id]);
							}
						}
					} else {
						// block_idは、	左右カラム or ヘッダーである
						// そうでなければエラー
						if(!in_array($buf_block['page_id'], $set_pages_arr)) {
							// エラー
							$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE, $this->_url));
							$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
						}
						if(in_array($page_id, $set_pages_arr)) {
							// page_idとblock_idの指定がおかしい
							// エラー
							$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE, $this->_url));
							$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
						}
					}
					//$page_id = $buf_block['page_id'];
				} else {
					// エラー表示
					// 指定ブロックIDなし
					//
					$modulesView =& $this->_container->getComponent("modulesView");
					$active_action = $this->_request->getParameter("active_action");
					if( isset($active_action) ) {
						$sub_pathList = explode("_", $active_action);
						$module_name = $modulesView->loadModuleName($sub_pathList[0]);
					} else {
						$module_name = $modulesView->loadModuleName($pathList[0]);
					}
					if($module_name != _NONE_MODULE_NAME) {
						$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE_DELETE_BLOCK,$module_name, $this->_url));
					} else {
						$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE,$this->_url));
					}
					$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
				}
			}
			if($page_id != 0) {
				array_push($page_id_arr, $page_id);
			}
			if ($page_id == 0) {
				//
				// 必ずトップページをデフォルトに変更（permalinkに対応するため）
				//
				$page_id = _SELF_TOPPUBLIC_ID;
				array_push($page_id_arr, $page_id);
			}
			//パブリックスペース、プライベートスペース、グループスペースでヘッダー、レフトカラム、ライトカラムを
			//を分けることに対応するため
			$page_id_arr = array_merge($page_id_arr, $set_pages_arr);
		} else if($page_id != 0) {
			array_push($page_id_arr, $page_id);
		}

		if(isset($page_id_arr[0]) && $this->action_name == DEFAULT_ACTION) {

			$buf_pages_obj =& $pagesView->getPageById($page_id_arr);
			$buf_page_obj = "";
			$show_page_id = 0;
			$set_default_private_space = 0;
			foreach($buf_pages_obj as $page_obj) {
				if($page_id == $page_obj['page_id'] || in_array($page_obj['page_id'], $set_pages_arr)) {
					$pages[$page_obj['page_id']] = $page_obj;
				}
			}

			if($page_id != 0){
				if(isset($pages[$page_id]) && $pages[$page_id]['node_flag'] == _ON && $pages[$page_id]['action_name'] == "") {
					//指定したpage_idがnodeであるならば
					//nodeの子供のうち最も近いページIDを取得
					if($pages[$page_id]['root_id'] == 0) {
						$root_id = $pages[$page_id]['page_id'];
					} else {
						$root_id = $pages[$page_id]['root_id'];
					}
					//言語対応ページ
					$lang_dirname = $this->_session->getParameter("_lang");
					$where_params = array(
						"action_name!=''"=>null,
						"space_type"=>$pages[$page_id]['space_type'],
						"private_flag"=>$pages[$page_id]['private_flag'],
						"display_sequence!=0"=>null,
						"display_flag"=>_ON,
						"root_id"=>$root_id,
						"display_position"=>$pages[$page_id]['display_position'],
						"thread_num>".$pages[$page_id]['thread_num']=>null
					);
					$order_params =array(
											"{pages}.thread_num" => "ASC",
											"{pages}.display_sequence" => "ASC"
										);

					$buf_pages_obj_child =& $pagesView->getShowPagesList($where_params, $order_params, null, null, array($pagesView, 'fetchcallback'));
					if($buf_pages_obj_child) {
						//親ノードの子供
						$has_lang_page = false;
						if(count($buf_pages_obj_child) > 1) {
							foreach($buf_pages_obj_child as $child) {
								if($child['lang_dirname'] == $lang_dirname) {
									$has_lang_page = true;
									$page_id = $child['page_id'];
									$pages[$page_id] = $child;
									break;
								}
							}
						}
						if(!$has_lang_page) {
							$page_id = $buf_pages_obj_child[0]['page_id'];
							$pages[$page_id] = $buf_pages_obj_child[0];
						}
					} else {
						//親ノードに対応する子ノードで閲覧可能なページなし
						return;
					}
				} else if(isset($pages[$page_id]) && $this->_actionChain->getCurActionName() == "pages_view_main" &&
					$pages[$page_id]['display_sequence'] == 0) {
					// ヘッダー、フッター、レフトカラム、ライトカラムだとエラー
					$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE, $this->_url));
					$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
				}
				// else {
				//	$pages[$page_id] = $page_obj;
				//}
			}

			if($page_id == 0) {
				//デフォルトページがみつからない
				//見れるページIDを取得
				$where_params = array(
					"action_name!=''"=>null,
					"display_flag"=>_ON,
				);
				$order_params =array(
										"{pages}.thread_num" => "ASC",
										"{pages}.display_sequence" => "ASC"
										);
				$buf_pages_obj =& $pagesView->getShowPagesList($where_params, $order_params, 1, 0, array($pagesView, 'fetchcallback'));

				//少なくともバブリックページは１ページはあるとして処理
				$page_id = $buf_pages_obj[0]['page_id'];
				$pages[$page_id] = $buf_pages_obj[0];
			}
		}

		$page_id = intval($page_id);

		$headercolumn_page_id = $this->_getColumnPageid($pages[$page_id], $headercolumn_page_id_arr);
		$leftcolumn_page_id = $this->_getColumnPageid($pages[$page_id], $leftcolumn_page_id_arr);
		$rightcolumn_page_id = $this->_getColumnPageid($pages[$page_id], $rightcolumn_page_id_arr);
		$this->_session->setParameter("_headercolumn_page_id",$headercolumn_page_id);
    	$this->_session->setParameter("_leftcolumn_page_id",$leftcolumn_page_id);
    	$this->_session->setParameter("_rightcolumn_page_id",$rightcolumn_page_id);

		if(isset($pages)) {
			//_auth_arrセット
			$auth_arr[intval($pages[$page_id]['display_position'])] = $pages[$page_id]['authority_id'];
	    	if(isset($page_id_arr[0]) && $this->action_name == DEFAULT_ACTION) {
    			$auth_arr[_DISPLAY_POSITION_HEADER] = $pages[_SELF_TOPPUBLIC_ID]['authority_id'];
    			$auth_arr[_DISPLAY_POSITION_LEFT] = $pages[_SELF_TOPPUBLIC_ID]['authority_id'];
    			$auth_arr[_DISPLAY_POSITION_RIGHT] = $pages[_SELF_TOPPUBLIC_ID]['authority_id'];
    		}
    		$this->_session->setParameter("_auth_arr",$auth_arr);
			if ($this->action_name == DEFAULT_ACTION) {
				//ページタイトルをセッションに代入
	    		$this->_session->setParameter("_page_title",$pages[$page_id]['page_name']);
    		} else if ($mobile_flag && count($pages) > 0 && $page_id != 0) {
    			$page_obj =& $pagesView->getPageById($page_id);
    			if ($page_obj) {
	    			$this->_session->setParameter('_page_title', $page_obj['page_name']);
    			}
    		} else {
    			$this->_session->removeParameter("_page_title");
    		}
		}
    	//メインページIDセット
    	$header = $this->_request->getParameter("_header");
    	if($this->action_name == DEFAULT_ACTION || $header == 1) {
			$this->_session->setParameter("_main_page_id", $page_id);
			if(isset($pages[$page_id])) {
				$this->_session->setParameter("_main_room_id", $pages[$page_id]['room_id']);
			}
		}
		//$this->_request->setParameter("_main_page_id",$page_id);

    	$this->_request->setParameter("page_id",$page_id);

    	if($page_id != 0 && !isset($pages[$page_id])) {
    		$page = $pagesView->getPageById($page_id);
   			if($page === false) {
   				return;
   			}
   			if(!isset($page['page_id'])) {
   				$this->_errorList->add("Auth_Error", sprintf(_ACCESS_FAILURE, $this->_url));
				$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
				return;
   			}
   			$pages[$page_id] = $page;
    	}

		// ページスタイル
    	$_pagestyle_flag = $this->_request->getParameter("_pagestyle_flag");
		$pagestyle_list = $this->_session->getParameter("pagestyle_list");
		if($_pagestyle_flag) {
			if(isset($pagestyle_list[$page_id])) {
				//$clear_flag = false;
				if(isset($pagestyle_list[$page_id]['theme_name'])) {
					if($pagestyle_list[$page_id]['theme_name'] != $pages[$page_id]['theme_name']) {
						$clear_flag = true;
					}
					$pages[$page_id]['theme_name'] = $pagestyle_list[$page_id]['theme_name'];
				}
				if(isset($pagestyle_list[$page_id]['header_flag'])) {
					$pages[$page_id]['header_flag'] = intval($pagestyle_list[$page_id]['header_flag']);
				}
				if(isset($pagestyle_list[$page_id]['leftcolumn_flag'])) {
					$pages[$page_id]['leftcolumn_flag'] = intval($pagestyle_list[$page_id]['leftcolumn_flag']);
				}
				if(isset($pagestyle_list[$page_id]['rightcolumn_flag'])) {
					$pages[$page_id]['rightcolumn_flag'] = intval($pagestyle_list[$page_id]['rightcolumn_flag']);
				}
				//if(isset($pagestyle_list[$page_id]['rightcolumn_flag'])) {
				//	$pages[$page_id]['rightcolumn_flag'] = intval($pagestyle_list[$page_id]['rightcolumn_flag']);
				//}
				//if($clear_flag) {
				//	$pages[$page_id]['body_style'] = '';
				//} else {
					if(isset($pagestyle_list[$page_id]['body_background'])) {
						$pages[$page_id]['body_style'] = $pages[$page_id]['body_style']."background:".$pagestyle_list[$page_id]['body_background'].";";
					} else if(isset($pagestyle_list[$page_id]['body_backgroundColor'])) {
						$pages[$page_id]['body_style'] = $pages[$page_id]['body_style']."background-color:".$pagestyle_list[$page_id]['body_backgroundColor'].";";
					}
				//}
				if(isset($pagestyle_list[$page_id]['page_name'])) {
					$pages[$page_id]['page_name'] = $pagestyle_list[$page_id]['page_name'];
				}
				if(isset($pagestyle_list[$page_id]['align'])) {
					$pages[$page_id]['align'] = $pagestyle_list[$page_id]['align'];
				}
				if(isset($pagestyle_list[$page_id]['leftmargin'])) {
					$pages[$page_id]['leftmargin'] = intval($pagestyle_list[$page_id]['leftmargin']);
				}
				if(isset($pagestyle_list[$page_id]['rightmargin'])) {
					$pages[$page_id]['rightmargin'] = intval($pagestyle_list[$page_id]['rightmargin']);
				}
				if(isset($pagestyle_list[$page_id]['topmargin'])) {
					$pages[$page_id]['topmargin'] = intval($pagestyle_list[$page_id]['topmargin']);
				}
				if(isset($pagestyle_list[$page_id]['bottommargin'])) {
					$pages[$page_id]['bottommargin'] = intval($pagestyle_list[$page_id]['bottommargin']);
				}

				$loop_array = array("headercolumn_backgroundColor"=>"background-color","headercolumn_borderTop"=>"border-top","headercolumn_borderRight"=>"border-right",
										"headercolumn_borderBottom"=>"border-bottom","headercolumn_borderLeft"=>"border-left","headercolumn_background"=>"background",
									"centercolumn_backgroundColor"=>"background-color","centercolumn_borderTop"=>"border-top","centercolumn_borderRight"=>"border-right",
										"centercolumn_borderBottom"=>"border-bottom","centercolumn_borderLeft"=>"border-left","centercolumn_background"=>"background",
									"leftcolumn_backgroundColor"=>"background-color","leftcolumn_borderTop"=>"border-top","leftcolumn_borderRight"=>"border-right",
										"leftcolumn_borderBottom"=>"border-bottom","leftcolumn_borderLeft"=>"border-left","leftcolumn_background"=>"background",
									"rightcolumn_backgroundColor"=>"background-color","rightcolumn_borderTop"=>"border-top","rightcolumn_borderRight"=>"border-right",
										"rightcolumn_borderBottom"=>"border-bottom","rightcolumn_borderLeft"=>"border-left","rightcolumn_background"=>"background",
									"footercolumn_backgroundColor"=>"background-color","footercolumn_borderTop"=>"border-top","footercolumn_borderRight"=>"border-right",
										"footercolumn_borderBottom"=>"border-bottom","footercolumn_borderLeft"=>"border-left","footercolumn_background"=>"background");
				foreach($loop_array as $key => $value) {
					if($key == "headercolumn_backgroundColor" || $key == "headercolumn_background") {
						$style_column = "header_style";
					}else if($key == "centercolumn_backgroundColor" || $key == "centercolumn_background") {
						$style_column = "centercolumn_style";
					}else if($key == "leftcolumn_backgroundColor" || $key == "leftcolumn_background") {
						$style_column = "leftcolumn_style";
					}else if($key == "rightcolumn_backgroundColor" || $key == "rightcolumn_background") {
						$style_column = "rightcolumn_style";
					}else if($key == "footercolumn_backgroundColor" || $key == "footercolumn_background") {
						$style_column = "footer_style";
					}
					//if($clear_flag) {
					//	$pages[$page_id][$style_column] = '';
					//} else {
						if(isset($pagestyle_list[$page_id][$key])) {
							if(isset($pages[$page_id][$style_column]) && $pages[$page_id][$style_column] != "") {
								//同じスタイルがすでに適用されていれば削除
								$pages[$page_id][$style_column] = preg_replace("/".preg_quote($value, '/').":.*;/iU", "", $pages[$page_id][$style_column]);
							}
							$pages[$page_id][$style_column] .= $value.":".$pagestyle_list[$page_id][$key].";";
						}
					//}
				}
			}
		} else if($pathList[0] == "pages"){
			$this->_session->setParameter("pagestyle_list", null);
		}
		//ページテーマに対応したブロックテーマ読み込み
		if($this->action_name == DEFAULT_ACTION ||
			($this->_request->getMethod() != "POST" && $this->_request->getParameter("_header") == _ON)) {
			$page_theme_name = $pages[$page_id]['theme_name'];
			$change_blocktheme = _ON;									//ブロックテーマの変更を許す
			$theme_list = null;
			$themeStrList = explode("_", $page_theme_name);
			if(count($themeStrList) == 1) {
				$themeCssPath = "/themes/".$page_theme_name."/config";
				if(file_exists(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE)) {
					$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE,true);
				}
			} else {
				$bufthemeStr = array_shift ( $themeStrList );
				$themeCssPath = "/themes/".$bufthemeStr."/config/";
				if(file_exists(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/"._THEME_INIFILE)) {
					$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath.implode("/", $themeStrList)."/"._THEME_INIFILE,true);
				} else if(file_exists(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE)) {
					$theme_list = parse_ini_file(STYLE_DIR. $themeCssPath."/"._THEME_INIFILE,true);
				}
			}

			$themeList = array();
			$themeList[_DISPLAY_POSITION_CENTER] = $page_theme_name;
			$themeList[_DISPLAY_POSITION_LEFT] = $page_theme_name;
			$themeList[_DISPLAY_POSITION_RIGHT] = $page_theme_name;
			$themeList[_DISPLAY_POSITION_HEADER] = $page_theme_name;
			$themeList[_DISPLAY_POSITION_FOOTER] = $page_theme_name;
			if(isset($theme_list['general'])) {
				if(isset($theme_list['general']['apply_blocktheme']) && $theme_list['general']['apply_blocktheme'] != "auto") {
					$default_theme_name = $theme_list['general']['apply_blocktheme'];
				} else {
					$default_theme_name = $pages[$page_id]['theme_name'];
				}
				if(isset($theme_list['general']['change_blocktheme'])) {
					$change_blocktheme = $theme_list['general']['change_blocktheme'];
				}
				$themeList[_DISPLAY_POSITION_CENTER] = $default_theme_name;
				$themeList[_DISPLAY_POSITION_LEFT] = $default_theme_name;
				$themeList[_DISPLAY_POSITION_RIGHT] = $default_theme_name;
				$themeList[_DISPLAY_POSITION_HEADER] = $default_theme_name;
				$themeList[_DISPLAY_POSITION_FOOTER] = $default_theme_name;
			}
			if(isset($theme_list['headercolumn']['apply_blocktheme']) && $theme_list['headercolumn']['apply_blocktheme'] != "auto") {
				$themeList[_DISPLAY_POSITION_HEADER] = $theme_list['headercolumn']['apply_blocktheme'];
			}
			if(isset($theme_list['leftcolumn']['apply_blocktheme']) && $theme_list['leftcolumn']['apply_blocktheme'] != "auto") {
				$themeList[_DISPLAY_POSITION_LEFT] = $theme_list['leftcolumn']['apply_blocktheme'];
			}
			if(isset($theme_list['centercolumn']['apply_blocktheme']) && $theme_list['centercolumn']['apply_blocktheme'] != "auto") {
				$themeList[_DISPLAY_POSITION_CENTER] = $theme_list['centercolumn']['apply_blocktheme'];
			}
			if(isset($theme_list['rightcolumn']['apply_blocktheme']) && $theme_list['rightcolumn']['apply_blocktheme'] != "auto") {
				$themeList[_DISPLAY_POSITION_RIGHT] = $theme_list['rightcolumn']['apply_blocktheme'];
			}
			if(isset($theme_list['footercolumn']['apply_blocktheme']) && $theme_list['footercolumn']['apply_blocktheme'] != "auto") {
				$themeList[_DISPLAY_POSITION_FOOTER] = $theme_list['footercolumn']['apply_blocktheme'];
			}
			$this->_session->setParameter("_theme_list", $themeList);
			$this->_session->setParameter("_change_blocktheme", $change_blocktheme);
    	}

    	//データセット
    	$this->_getdata->setParameter("pages",$pages);

    	//モジュールオブジェクト取得
    	$module_id = 0;
    	if($pathList[0] == "dialog" || $pathList[0] == "comp" ) {
    		$system_flag = _OFF;
    	} else if($pathList[0] == "pages" || $pathList[0] == "common") {
    		$system_flag = _OFF;
    	} else if($pathList[0] == "control") {
    		$system_flag = _ON;
    	} else {
    		$system_flag = _OFF;
    		$module =& $this->_getModules($this->action_name);
    		if(isset($module['module_id'])) {
    			if($module['system_flag']) {
	    			$module_id = $module['module_id'];
    			}
    			$this->_request->setParameter("module_id",$module['module_id']);
    			$system_flag = $module['system_flag'];
    		}
    	}

    	// システム系の画面かいなか
    	$this->_session->setParameter("_system_flag", $system_flag);

    	// idセット
    	$commonMain->getTopId($block_id, $module_id);

    	// space_typeをセッションに代入
    	if($page_id != 0 && $system_flag == _OFF) {
    		$this->_session->setParameter("_space_type", $pages[$page_id]['space_type']);
    		// デフォルトで参加するルームかどうかをセッションに登録
			$this->_session->setParameter("_default_entry_flag", $pages[$page_id]["default_entry_flag"]);
    	} else {
    		// page_idの指定がないか、管理系モジュールならば未定義
    		$this->_session->setParameter("_space_type", _SPACE_TYPE_UNDEFINED);
    		// デフォルトで参加するルームかどうかをセッションに登録
			$this->_session->setParameter("_default_entry_flag", _OFF);
    	}

	    //
	    //mb_stringの代替を使用するかどうか
	    //

		//mb_stringがロードされているかどうか
    	if (!extension_loaded('mbstring') && !function_exists("mb_convert_encoding")) {
    		if (isset($attributes["mbstring"])) {
    			include_once MAPLE_DIR  . '/includes/mbstring.php';
    		}
    	} else if(function_exists("mb_detect_order")){
    		mb_detect_order(_MB_DETECT_ORDER_VALUE);
    	}
   		if (function_exists("mb_internal_encoding")) {
    		mb_internal_encoding(INTERNAL_CODE);
    	}
    	if (function_exists("mb_language")) {
    		mb_language("Japanese");
    	}

    	//
    	// 「すべての会員をデフォルトで参加」さした場合の権限
    	//
    	if($this->_session->getParameter("_default_entry_auth_group")){
    		if(isset($pages[$page_id]['page_id']) && $pages[$page_id]['space_type'] == _SPACE_TYPE_GROUP && $pages[$page_id]['private_flag'] == _OFF) {
				$this->_session->setParameter("_default_entry_auth", $this->_session->getParameter("_default_entry_auth_group"));
				$this->_session->setParameter("_default_entry_hierarchy", $this->_session->getParameter("_default_entry_hierarchy_group"));
    		} else if($pages[$page_id]['private_flag'] == _ON) {
    			$this->_session->setParameter("_default_entry_auth", $this->_session->getParameter("_default_entry_auth_private"));
    			$this->_session->setParameter("_default_entry_hierarchy", $this->_session->getParameter("_default_entry_hierarchy_private"));
    		} else {
    			$this->_session->setParameter("_default_entry_auth", $this->_session->getParameter("_default_entry_auth_public"));
    			$this->_session->setParameter("_default_entry_hierarchy", $this->_session->getParameter("_default_entry_hierarchy_public"));
    		}
		} else {
			$this->_session->setParameter("_default_entry_auth", _AUTH_GUEST);
			$this->_session->setParameter("_default_entry_hierarchy", _HIERARCHY_GUEST);
    	}
    }

    /**
     * ポストフィルタ
     * @access private
     */
    function _postfilter()
    {
    }
    function &_getModules($action_name)
    {
    	$module = null;
    	$pathList = explode("_", $action_name);
    	$modules = $this->_getdata->getParameter("modules");
		if(!isset($modules[$pathList[0]])) {
			$modulesView =& $this->_container->getComponent("modulesView");
	    	$modules[$pathList[0]] = $modulesView->getModuleByDirname($pathList[0]);
	    	if(isset($modules[$pathList[0]]['module_id'])) {
	    		$this->_getdata->setParameter("modules", $modules);
	    		$block_id = $this->_request->getParameter("block_id");
	    		if($block_id == 0) {
	    			$header = $this->_request->getParameter("_header");
					if(!isset($header) || $header == _ON) {
						$this->_session->setParameter("_page_title", $modules[$pathList[0]]['module_name']);
					}
	    		}
	    		$module =& $modules[$pathList[0]];
	    	}
		} else {
			$module =& $modules[$pathList[0]];
		}
		return $module;
    }

    /**
     * config-XXX_choice_startpageからpage_idを取得
     * @param array page_id_arr(パブリックスペースpage_id|プライベートスペースpage_id|グループスペースpage_id)
     * @return int page_id
     * @access private
     */
    function _getColumnPageid(&$page_obj, $page_id_arr) {
    	if(count($page_id_arr) == 3) {
	    	if($page_obj['space_type'] == _SPACE_TYPE_PUBLIC) {
				//パブリックスペース
				return intval($page_id_arr[0]);
			} else if($page_obj['space_type'] == _SPACE_TYPE_GROUP && $page_obj['private_flag'] == _ON) {
				//プライベートスペース
				return intval($page_id_arr[1]);
			} else {
				//グループスペース
				return intval($page_id_arr[2]);
			}
    	} else {
    		return intval($page_id_arr[0]);
    	}
    }
}
?>
