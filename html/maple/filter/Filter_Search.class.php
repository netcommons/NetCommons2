<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Search用クラスwhere文作成用Filter
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Filter_Search extends Filter
{
    var $_classname = "Filter_Search";

    var $_container;
    var $_log;
    var $_actionChain;
    var $_filterChain;
    var $_db;
    var $_response;
    var $_modulesView;
    var $_session;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function Filter_Search()
    {
        parent::Filter();
    }

    /**
     * Search用クラスinclude,where文作成処理を実行
     *
     * @access  public
     *
     */
    function execute()
    {
        $this->_container   =& DIContainerFactory::getContainer();
        $this->_log         =& LogFactory::getLog();
        $this->_filterChain =& $this->_container->getComponent("FilterChain");
        $this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_db   =& $this->_container->getComponent("DbObject");
        $this->_request     =& $this->_container->getComponent("Request");
        $this->_response    =& $this->_container->getComponent("Response");
        $this->_commonMain  =& $this->_container->getComponent("commonMain");
        $this->_modulesView  =& $this->_container->getComponent("modulesView");
        $this->_session  =& $this->_container->getComponent("Session");

        $this->_log->trace("{$this->_classname}の前処理が実行されました", "{$this->_classname}#execute");
        $this->_preFilter();

        $this->_filterChain->execute();

        $this->_log->trace("{$this->_classname}の後処理が実行されました", "{$this->_classname}#execute");
        $this->_postFilter();
    }

    /**
     * プリフィルタ
     *
     * @access  private
     */
    function _preFilter()
    {
    	$action =& $this->_actionChain->getCurAction();
    	$attributes = $this->getAttributes();

    	// リクエストを受け取るため
    	$keyword = $this->_request->getParameter("keyword");
     	$select_kind = $this->_request->getParameter("select_kind");
    	$handle = $this->_request->getParameter("handle");
		$display_days = $this->_request->getParameter("display_days");
		if (isset($display_days)) {
    		$today = timezone_date(null, false, "Ymd");
    		$fm_target_date = date("Ymd", mktime(0, 0, 0, intval(substr($today,4,2)), intval(substr($today,6,2))-$display_days, intval(substr($today,0,4))));
    		$to_target_date = $today;
		} else {
			$fm_target_date = str_replace(_DAYSEPARATOR,"",$this->_request->getParameter("fm_target_date"));
			$to_target_date = str_replace(_DAYSEPARATOR,"",$this->_request->getParameter("to_target_date"));
		}

    	//
    	// セットパラメータ名称取得
    	// sqlwhere		:default sqlwhere
    	// params		:default params
    	//
    	$request_sqlwhere = (isset($attributes['sqlwhere']) ? $attributes['sqlwhere'] : "sqlwhere");
    	$request_params = (isset($attributes['params']) ? $attributes['params'] : "params");

    	//
    	// カラム名称取得
    	// keyword			:default 複数カラム検索する場合、「,」区切りにすること
    	// wysiwyg			:default 複数カラム検索する場合、「,」区切りにすること
    	//        keyword or wysiwyg どちらか必須
    	// handle			:default insert_user_name
    	// fm_target_date	:default insert_time
    	// to_target_date	:default insert_time
    	//
        $col_keyword = (isset($attributes['keyword']) ? explode(",", $attributes['keyword']) : null);
        $col_wysiwyg = (isset($attributes['wysiwyg']) ? explode(",", $attributes['wysiwyg']) : null);
        $col_handle = (isset($attributes['handle']) ? $attributes['handle'] : "insert_user_name");
        $col_target_time = (isset($attributes['target_time']) ? explode(",", $attributes['target_time']) : array("insert_time"));

    	$sqlwhere = "";
		$params = array();

    	//// 日付 ////
    	// 対象日(from)
		if ($fm_target_date) {
			if (strlen($fm_target_date) == 8) {
				$fm_target_date = $fm_target_date."000000";
			}
			$fm_target_date = timezone_date($fm_target_date, true, "YmdHis");
		}
		// 対象日(to)
		if ($to_target_date) {
			if (strlen($to_target_date) == 8) {
				$to_target_date = $to_target_date."240000";
			}
			$to_target_date = timezone_date($to_target_date, true, "YmdHis");
		}
    	if ($fm_target_date || $to_target_date) {
			$sqlwhere .= " AND (";
			$col_count = 0;
			foreach ($col_target_time as $col_target_time_name) {
				if ($col_count > 0) {
					$sqlwhere .= " OR ";
				}
				$sqlwhere .= "(";
				if ($fm_target_date) {
					$params['fm_target_date'.$col_count] = $fm_target_date;
					$sqlwhere .= $col_target_time_name." >= ?";
				} else {
					$sqlwhere .= "1=1";
				}
				$sqlwhere .= " AND ";
				if ($to_target_date) {
					$params['to_target_date'.$col_count] = $to_target_date;
					$sqlwhere .= $col_target_time_name." < ?";
				} else {
					$sqlwhere .= "1=1";
				}
				$sqlwhere .= ")";
				$col_count++;
			}
	    	$sqlwhere .= ")";
    	}

		//// ハンドル(検索のみ) ////
		if ($handle != "" && $handle != null) {
			$handle = str_replace(_SEARCH_EM_SIZE_SPACE, " ", $handle);
			$handle_arr = explode(" ", $handle);
			$sqlwhere .= " AND (";
			$count = 0;
			$querysql = array();
			foreach($handle_arr as $handle) {
				if (trim($handle) == "") { continue; }
				$params["handle".$count] = "%".$handle."%";
				$querysql[] = $col_handle . " LIKE ?";
				$count++;
			}
			$sqlwhere .= '('.join(' OR ', $querysql).')';
			$sqlwhere .= ")";
		}
		//// キーワード ////
		if($keyword != "" && $keyword != null) {
			$col_keyword_count =  count($col_keyword);
			$col_wysiwyg_count =  count($col_wysiwyg);
			if ($col_keyword_count > 0 || $col_wysiwyg_count > 0) {
				$sqlwhere .= " AND (";
			}
			if ($select_kind == _SELECT_KIND_PHRASE) {
				//フレーズ
				$col_count = 0;
				if ($col_keyword_count > 0) {
					foreach ($col_keyword as $col_keyword_name) {
						if ($col_count > 0) {
							$sqlwhere .= " OR ";
						}
						if(preg_match('/:text$/', $col_keyword_name)) {
							$col_keyword_name = preg_replace('/:text$/', '', $col_keyword_name);
							$params['content'.$col_count] = '"'.$keyword.'"';
							$sqlwhere .= " MATCH(".$col_keyword_name.") AGAINST (? IN BOOLEAN MODE)";
						} else {
							$params['content'.$col_count] = "%".$keyword."%";
							$sqlwhere .= $col_keyword_name." LIKE ?";
						}

						$col_count++;
					}
				}
				if ($col_wysiwyg_count > 0) {
					foreach ($col_wysiwyg as $col_wysiwyg_name) {
						if ($col_count > 0) {
							$sqlwhere .= " OR ";
						}
						if(preg_match('/:text$/', $col_wysiwyg_name)) {
							$col_wysiwyg_name = preg_replace('/:text$/', '', $col_wysiwyg_name);
							$params['content'.$col_count] = '"'.htmlspecialchars($keyword).'"';
							$sqlwhere .= " MATCH(".$col_wysiwyg_name.") AGAINST (? IN BOOLEAN MODE)";
						} else {
							$params['content'.$col_count] = "%".htmlspecialchars($keyword)."%";
							$sqlwhere .= $col_wysiwyg_name." LIKE ?";
						}

						$col_count++;
					}
				}
			} else {
				if ($select_kind == _SELECT_KIND_AND) {
					//すべて(AND検索)
					$andor = "AND";
				} else {
					//いずれか(OR検索)
					$andor = "OR";
				}
				$col_count = 0;
				if ($col_keyword_count > 0) {
					foreach ($col_keyword as $col_keyword_name) {
						if ($col_count > 0) {
							$sqlwhere .= " OR ";
						}
						$keyword = str_replace(_SEARCH_EM_SIZE_SPACE, " ", $keyword);
						$keyword_arr = explode(" ", $keyword);
						$count = 0;
						$querysql = array();
						$text_flag = false;
						if(preg_match('/:text$/', $col_keyword_name)) {
							$text_flag = true;
							$col_keyword_name = preg_replace('/:text$/', '', $col_keyword_name);
						}

						if($text_flag) {
							$match_against = '';
							foreach($keyword_arr as $word) {
								if (trim($word) == "") { continue; }
								if($match_against != '') {
									$match_against .= ' ';
								}
								$match_against .= '+'.$this->_db->stringMatchAgainst($word);
							}
							if($match_against != '') {
								$params['content'.$col_count] = $match_against;
								$querysql[] = " MATCH(".$col_keyword_name.") AGAINST (? IN BOOLEAN MODE)";
							}
						} else {
							foreach($keyword_arr as $word) {
								if (trim($word) == "") { continue; }
								$params['content'.$col_count.$count] = "%".$word."%";
								$querysql[] = $col_keyword_name." LIKE ?";
								$count++;
							}
						}

						$sqlwhere .= '('.join(' '.$andor.' ', $querysql).')';
						$col_count++;
					}
				}
				if ($col_wysiwyg_count > 0) {
					foreach ($col_wysiwyg as $col_wysiwyg_name) {
						if ($col_count > 0) {
							$sqlwhere .= " OR ";
						}
						$keyword = str_replace(_SEARCH_EM_SIZE_SPACE, " ", $keyword);
						$keyword_arr = explode(" ", $keyword);
						$count = 0;
						$querysql = array();
						$text_flag = false;
						if(preg_match('/:text$/', $col_wysiwyg_name)) {
							$text_flag = true;
							$col_wysiwyg_name = preg_replace('/:text$/', '', $col_wysiwyg_name);
						}

						if($text_flag) {
							$match_against = '';
							foreach($keyword_arr as $word) {
								if (trim($word) == "") { continue; }
								if($match_against != '') {
									$match_against .= ' ';
								}
								$match_against .= '+'.$this->_db->stringMatchAgainst($word);
							}
							if($match_against != '') {
								$params['content'.$col_count] = $match_against;
								$querysql[] = " MATCH(".$col_wysiwyg_name.") AGAINST (? IN BOOLEAN MODE)";
							}
						} else {
							foreach($keyword_arr as $word) {
								if (trim($word) == "") { continue; }
								$params['content'.$col_count.$count] = "%".htmlspecialchars($word)."%";
								$querysql[] = $col_wysiwyg_name." LIKE ?";
								$count++;
							}
						}

						$sqlwhere .= '('.join(' '.$andor.' ', $querysql).')';
						$col_count++;
					}
				}
			}
			$sqlwhere .= ")";
		}

		$set_arr = array();
		$set_arr[$request_sqlwhere] = $sqlwhere;
		$set_arr[$request_params] = $params;
    	BeanUtils::setAttributes($action, $set_arr, true);
    }

    /**
     * ポストフィルタ
     *
     * @access  private
     */
    function _postFilter()
    {
    	$action =& $this->_actionChain->getCurAction();

		$dir_name = $this->_request->getParameter("dir_name");
		$target_module = $this->_request->getParameter("target_module");

		//// channel要素 ////
		$meta = $this->_session->getParameter("_meta");
		$channel = $this->_request->getParameter("channel");
		if (!isset($channel) || !isset($channel["title"])) {
			if (!isset($dir_name)) {
				$module_obj = $this->_modulesView->getModulesById($target_module);
				$module_name = $module_obj["module_name"];
			} else {
				$module_name = $this->_modulesView->loadModuleName($dir_name);
			}
			$channel["title"] = $module_name;
		}
		if (!isset($channel["url"])) {
			$channel["url"] = BASE_URL.INDEX_FILE_NAME;
		}
		if (!isset($channel["description"])) {
			$channel["description"] = (!empty($meta["meta_description"]) ? $meta["meta_description"] : $channel["title"]);
		}
		if (!isset($channel["language"]) && !empty($meta["meta_language"])) {
			$channel["language"] = $meta["meta_language"];
		}
		if (!isset($channel["copyright"]) && !empty($meta["meta_copyright"])) {
			$channel["copyright"] = $meta["meta_copyright"];
		}
		if (!isset($channel["webMaster"]) && !empty($meta["meta_author"])) {
			$channel["webMaster"] = $meta["meta_author"];
		}
		if (!isset($channel["pubDate"])) {
			$channel["pubDate"] = timezone_date(null, false, "r");
		}
		if (!isset($channel["lastBuildDate"])) {
			$channel["lastBuildDate"] = timezone_date(null, false, "r");
		}

		//// item要素 ////
		$convertHtml =& $this->_commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
		for ($i = 0; $i < count($action->results); $i++) {
    		$block_name ="";
    		$page_id = "";
    		$page_name ="";
    		$room_name ="";

    		if(isset($action->results[$i]['block_id'])) {
    			//ルーム名称-ページ名称-ブロック名称
    			$blocks_list = $this->_db->selectExecute("blocks",array("block_id"=>intval($action->results[$i]['block_id'])));
        		$block_name = $blocks_list[0]['block_name'];
        		$page_id = $blocks_list[0]['page_id'];
        		$pages_list = $this->_db->selectExecute("pages",array("page_id"=>intval($page_id)));
        		$page_name = $pages_list[0]['page_name'];
    			if($pages_list[0]['room_id'] == $pages_list[0]['page_id']) {
    				$room_name = $page_name;
    				$page_name = "";
    			} else {
    				$rooms_list = $this->_db->selectExecute("pages",array("page_id"=>intval($pages_list[0]['room_id'])));
        			$room_name = $rooms_list[0]['page_name'];
    			}
    		} else if(isset($action->results[$i]['page_id'])) {
    			//ルーム名称-ページ名称
    			$page_id = $action->results[$i]['page_id'];
    			$pages_list = $this->_db->selectExecute("pages",array("page_id"=>intval($page_id)));
        		$page_name = $pages_list[0]['page_name'];
    			if($pages_list[0]['room_id'] == $pages_list[0]['page_id']) {
    				$room_name = $page_name;
    				$page_name = "";
    			} else {
    				$rooms_list = $this->_db->selectExecute("pages",array("page_id"=>intval($pages_list[0]['room_id'])));
        			$room_name = $rooms_list[0]['page_name'];
    			}
    		} else if(isset($action->results[$i]['room_id'])) {
    			//ルーム名称のみ表示
    			$page_id = $action->results[$i]['room_id'];
    			$rooms_list = $this->_db->selectExecute("pages",array("page_id"=>intval($page_id)));
        		$room_name = $rooms_list[0]['page_name'];
    		} else if(isset($action->results[$i]['room_name'])) {
    			//ルーム名称指定
    			$room_name = $action->results[$i]['room_name'];
    		}

    		//
    		// ヘッダー、レフトカラム、ライトカラムならば定義名称変更
    		//
    		switch($page_id) {
    			case $this->_session->getParameter("_headercolumn_page_id"):
    				$page_name = _HEADER_COLUMN_LANG;
    				break;
    			case $this->_session->getParameter("_leftcolumn_page_id"):
    				$page_name = _LEFT_COLUMN_LANG;
    				break;
    			case $this->_session->getParameter("_rightcolumn_page_id"):
    				$page_name = _RIGHT_COLUMN_LANG;
    				break;
    		}

    		//
    		//件名
    		//
    		if (!isset($action->results[$i]['title'])) {
	    		$title = "";
	    		if($room_name != "") {
	    			$title = $room_name;
	    		}
	    		if($page_name != "") {
	    			if($title != "") $title .= _SEARCH_SUBJECT_SEPARATOR;
	    			$title .= $page_name;
	    		}
	    		if($block_name != "") {
	    			if($title != "") $title .= _SEARCH_SUBJECT_SEPARATOR;
	    			$title .= $block_name;
	    		}
	    		if($title == "") {
	    			//件名指定なし
	    			$action->results[$i]['title'] = _SEARCH_SUBJECT_NONEXISTS;
	    		} else {
	    			$action->results[$i]['title'] = $title;
	    		}
    		}
    		$title = $action->results[$i]['title'];
			$action->results[$i]['title'] = mb_substr($title, 0, _SEARCH_SUBJECT_LEN + 1, INTERNAL_CODE);

    		//
    		//URL
    		//
    		if(!isset($action->results[$i]['url'])) {
	    		if($page_id == "") {
	    			$action->results[$i]['url'] = "";
	    		} else {
		    		if(isset($action->results[$i]['ref_block_id'])) {
		    			$block_id = $action->results[$i]['ref_block_id'];
		    		} else if(isset($action->results[$i]['block_id'])) {
		    			$block_id = $action->results[$i]['block_id'];
		    		}
		    		if(isset($action->results[$i]['action'])) {
		    			$active_action= "&active_action=".$action->results[$i]['action'];
		    		}

		    		$action->results[$i]['url'] = BASE_URL.INDEX_FILE_NAME."?action=".DEFAULT_ACTION."&block_id=".$block_id.
														"&page_id=".$page_id.$active_action."#_".$block_id;
	    		}
    		}
    		//
    		//本文
    		//
    		$action->results[$i]['description'] = $convertHtml->convertHtmlToText($action->results[$i]['description']);
    		$action->results[$i]['description'] = preg_replace("/\\\n/", " ", $action->results[$i]['description']);
			$action->results[$i]['description'] = mb_substr($action->results[$i]['description'], 0, _SEARCH_CONTENTS_LEN + 1, INTERNAL_CODE);
    		//
    		//guidの設定
    		//
    		if (!isset($action->results[$i]['guid'])) {
    			$action->results[$i]['guid'] = "";
    		}
			if (!empty($page_id)) {
				$action->results[$i]['guid'] .= (empty($action->results[$i]['guid']) ? "" : "&"). "page_id=".$page_id;
			}
			if (!empty($action->results[$i]['block_id'])) {
				$action->results[$i]['guid'] .= (empty($action->results[$i]['guid']) ? "" : "&"). "block_id=".$action->results[$i]['block_id'];
			}
			if (!empty($target_module)) {
				$action->results[$i]['guid'] .= (empty($action->results[$i]['guid']) ? "" : "&"). "module_id=".$target_module;
			}
    	}
    }
}
?>