<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページ追加アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Action_Edit_Addpage extends Action
{
    // リクエストパラメータを受け取るため
    var $main_page_id = null;
    var $node_flag = null;
    var $visibility_flag=null;
    var $block_id = null;

    // 使用コンポーネントを受け取るため
    var $pagesView = null;
    var $pagesAction = null;
    var $session = null;
    var $db = null;
    var $menuView = null;
    var $menuAction = null;
    var $getdata = null;
    var $modulesView = null;

    // 値をセットするため
    var $ins_page = null;

    /**
     * ページ追加アクション
     *
     * @access  public
     */
    function execute()
    {
    	$page_id = intval($this->main_page_id);
		$page =& $this->pagesView->getPageById($page_id);
		if($page === false || !isset($page['page_id'])) {
			return 'error';
		}
		//thread_num==0でグループスペース
		if($page['private_flag']==_OFF && $page['space_type'] == _SPACE_TYPE_GROUP && $page['thread_num']==0) {
			return 	'error';
		}

		if(!$page['node_flag']) {
			//ノードではないのでノードまでさかのぼる
			while(!$page['node_flag']) {
				$page =& $this->pagesView->getPageById($page['parent_id']);
				if($page === false || !isset($page['page_id'])) {
					return 'error';
				}
			}
			$this->main_page_id = $page['page_id'];
			$page_id = intval($this->main_page_id);
		}

		$room_id = intval($page['room_id']);
		$room =& $this->pagesView->getPageById($room_id);
		if($room === false || !isset($room['page_id'])) {
			return 'error';
		}
		//セッションが持っている言語のページを作る
		$lang_dirname = "";
		if($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['display_position'] == _DISPLAY_POSITION_CENTER) {
			//パブリックスペースのセンターカラムのページlang_dirname=japaneseもしくはenglishとする
			$lang_dirname = $this->session->getParameter("_lang");
			if(empty($lang_dirname)) {
				//セッションが言語を持ってない場合、エラーとする
				return 'error';
			}
		}

		$count = $this->pagesView->getMaxChildPage($page_id, $lang_dirname) + 1;
		//$header_flag = _ON;
		//$footer_flag = _ON;
		//$leftcolumn_flag = _ON;
		//$rightcolumn_flag = _OFF;
		if($page['thread_num'] + 1 == 1) {
			$add_name = "";
		} else {
			$add_name = ($page['thread_num'] + 1)."-";
		}
		if($this->node_flag) {
			$node_flag = _ON;
			$page_name = MENU_NEW_NODE_NAME.$add_name.$count;
			$action_name = DEFAULT_ACTION;
			//$action_name = "";	//ノードだけにする場合
		} else {
			$node_flag = _OFF;
			$page_name = MENU_NEW_PAGE_NAME.$add_name.$count;
			$action_name = DEFAULT_ACTION;
		}
		if($page['root_id'] == 0) {
			$root_id = $page['page_id'];
		} else {
			$root_id = $page['root_id'];
		}

		if($page['space_type'] == _SPACE_TYPE_PUBLIC && $page['thread_num'] + 1 == 1 && $count == 1) {
			// トップページ
			$permalink = "";
		} else {
			$replace_page_name = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $page_name);
			if($page['permalink'] != "") {
				$permalink = $page['permalink'].'/'.$replace_page_name;
			} else {
				$permalink = $replace_page_name;
			}
		}
		$permalink_count = 0;
		$old_permalink = $permalink;
	    while(1) {
	    	$where_params = array(
				'permalink' => $permalink,
	    		'lang_dirname' => $lang_dirname
			);
			$result = $this->db->selectExecute("pages", $where_params, null, 1);
			if(isset($result[0])) {
				$permalink_count++;
			} else {
				break;
			}
			$permalink = $old_permalink ."-". $permalink_count;
		}
		$ins_page = array(
			"room_id" => $page['room_id'],
			"site_id" => $this->session->getParameter("_site_id"),
			"root_id" => $root_id,
			"parent_id" => $page_id,
			"thread_num" => $page['thread_num'] + 1,
			"display_sequence" => $count,
			"url" => "",
			"action_name" => $action_name,
			"parameters" => "",
			"lang_dirname" => $lang_dirname,
			"page_name" => $page_name,
			'permalink' => $permalink,
			//"theme_name" => "default",
			//"temp_name" => "default",
			"show_count" => 0,
			"private_flag" => $page['private_flag'],
			"default_entry_flag" => $room['default_entry_flag'],
			"space_type" => $page['space_type'],
			"node_flag" => $node_flag,
			"shortcut_flag" => _OFF,
/*
			"header_flag" => $header_flag,
			"footer_flag" => $footer_flag,
			"leftcolumn_flag" => $leftcolumn_flag,
			"rightcolumn_flag" => $rightcolumn_flag,
			"body_style" => "",
			"header_style" => "",
    		"footer_style" => "",
    		"leftcolumn_style" => "",
    		"centercolumn_style" => "",
    		"rightcolumn_style" => "",
*/
			"display_flag" => $room['display_flag']
		);

		$new_page_id = $this->pagesAction->insPage($ins_page);
		if(!$new_page_id) {
			return 'error';
		}
		$ins_page['page_id'] = $new_page_id;
		$ins_page['edit_mode'] = _ON;
		$ins_page['visibility_flag'] = _ON;
		$ins_page['authority_id'] = _AUTH_CHIEF;

		$ins_page['edit_flag'] = true;
		$ins_page['chgseq_flag'] = true;
		if($this->session->getParameter("_auth_id") >= _AUTH_CHIEF) {
			$ins_page['visible_flag'] = true;
		} else {
			$ins_page['visible_flag'] = false;
		}
		$this->ins_page[0] =& $ins_page;
		// ----------------------------------------------------------------------
		// --- ページスタイルテーブル追加 　　                                ---
		// --- 親ルームにページスタイルテーブルのデータがあれば               ---
		// --- 追加する                                                       ---
		// ----------------------------------------------------------------------
		$pages_style = $this->pagesView->getPagesStyle(array("set_page_id" => intval($page['room_id'])));
		if(isset($pages_style[0])) {
			$pages_style[0]["set_page_id"] = $new_page_id;
			$result = $this->pagesAction->insPageStyle($pages_style[0]);
		}

		//
		// 現ブロック以外に貼ってあるmenuモジュールにおける表示を親のルームをみて判断
		//

		$where_params = array(
			"page_id" => $ins_page['parent_id'],
			"visibility_flag" => _OFF,
			"block_id!" => intval($this->block_id)
		);
		$menus = $this->menuView->getMenuDetail($where_params);
		$insert_blocks_id_arr = array();
		foreach($menus as $menu) {
			$params = array(
				"block_id" => $menu['block_id'],
				"page_id" => $new_page_id,
				"visibility_flag" => _OFF
			);
			$insert_blocks_id_arr[] = $menu['block_id'];
			if(!$this->menuAction->insMenuDetail($params)) {
				return 'error';
			}
		}

    	// ---------------------------------------------------------------------------------------
		// --- パブリックでセンターカラムにメニューがはってある場合、非表示としてルームを登録  ---
		// ---------------------------------------------------------------------------------------
    	$module = $this->modulesView->getModuleByDirname("menu");
    	if($page['space_type'] == _SPACE_TYPE_PUBLIC && isset($module['module_id'])) {
			$config_obj = $this->getdata->getParameter("config");

			// 左右カラムのpage_idをセッションにセット
			// （パブリックスペースpage_id | プライベートスペースpage_id | グループスペースpage_id）
			$headercolumn_page_id_str = $config_obj[_PAGESTYLE_CONF_CATID]['headercolumn_page_id']['conf_value'];
			$leftcolumn_page_id_str   = $config_obj[_PAGESTYLE_CONF_CATID]['leftcolumn_page_id']['conf_value'];
			$rightcolumn_page_id_str  = $config_obj[_PAGESTYLE_CONF_CATID]['rightcolumn_page_id']['conf_value'];

			$page_id_arr = array_merge ( explode("|",$headercolumn_page_id_str), explode("|",$leftcolumn_page_id_str), explode("|",$rightcolumn_page_id_str));

			$where_params = array(
				"page_id NOT IN (" . implode(',', $page_id_arr) . ") " => null,
				"module_id" => $module['module_id'],
				"block_id!" => intval($this->block_id)
			);
			$blocks = $this->db->selectExecute("blocks", $where_params);
			foreach($blocks as $block) {
				$block_page_id = intval($block['page_id']);
		    	$where_params = array(
					"page_id" => $block_page_id
				);
				$current_page =& $this->pagesView->getPages($where_params);

				if(!in_array ($block['block_id'], $insert_blocks_id_arr)) {
					$params = array(
						"block_id" => $block['block_id'],
						"page_id" => $new_page_id,
						"visibility_flag" => _OFF,
						"room_id" => $current_page[0]['room_id']
					);
					if(!$this->menuAction->insMenuDetail($params)) {
						return 'error';
					}
				}
			}
    	}
		return 'success';
    }
}
?>
