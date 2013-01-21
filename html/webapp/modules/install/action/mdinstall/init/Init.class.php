<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install モジュールインストール
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Action_Mdinstall_Init extends Action
{
    // リクエストパラメータを受け取るため
    var $dir_name = null;

    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    var $modulesView = null;
    var $usersView = null;
    var $db = null;
    var $databaseSqlutility = null;
    var $modulesAction = null;
    var $pagesAction = null;
    var $actionChain = null;
    var $blocksAction = null;
    var $configView = null;
    var $monthlynumberAction = null;

    // 値をセットするため

    /**
     * Install モジュールインストール
     *
     * @access  public
     */
    function execute()
    {
    	ini_set("memory_limit", INSTALL_MEMORY_LIMIT);

    	if($this->dir_name == "all") {
    		return "module_action_admin_allupdate";
    	} else if($this->dir_name == "end") {
    		// 終了処理
    		if(!$this->_finalProcess()) {
    			return 'error';
    		}
    		return 'success';
    	} else {
    		return "module_action_admin_install";
    	}
    }

    /**
     * 終了処理実行
     *
     * @access  public
     */
    function _finalProcess()
    {
    	$errorList =& $this->actionChain->getCurErrorList();
    	//
    	// デストリビューションの概念を導入した場合にtype値をリクエストパラメータに含めて、
    	// 様々な デストリビューションを実装できるしくみ
    	// 現在、デストリビューションの機能はなしで、「default」固定とする
    	//
		$type = "default";

    	//
		// 共通系のSQLを実行（insert.data.php）
		//
		// $modules[$dir_name], $self_site_id, $private_room_name, $admin_user_id
		$modules = $this->modulesView->getModules(null, null, null, null, array($this->installCompmain, "_fetchcallbackModules"), $this->modulesView);
    	if($modules == false) {
    		return false;
    	}
    	$users = $this->usersView->getUsers(array("active_flag"=> _USER_ACTIVE_FLAG_ON, "{users}.system_flag"=>_ON));
    	if($users == false) {
    		return false;
    	}
    	if(!isset($users[0])) {
    		return false;
    	}

    	$admin_user_id = $users[0]['user_id'];
    	$admin_handle = addslashes($users[0]['handle']);

    	$time = timezone_date();
		$self_site_id = $this->session->getParameter("_site_id");

		$file_name = INSTALL_INSERT_DATA_FILENAME;
		$_lang = $this->session->getParameter("_lang");

		$lang_file_path = BASE_DIR.'/webapp/modules/install/language/'.$_lang.'/'.$file_name;
		if(!@file_exists($lang_file_path)) {
			$_lang = "english";
			$lang_file_path = BASE_DIR.'/webapp/modules/install/language/'.$_lang.'/'.$file_name;
		}

		include_once $lang_file_path;
		$pattern = "/^(.+?):\/\/(.+?):(.*?)@(.+?)\/(.+)$/";
		$database = "mysql";
		if(preg_match($pattern, DATABASE_DSN, $matches)) {
			$database = $matches[1];
    		$dbusername = $matches[2];
    		$dbpass = $matches[3];
    		$dbhost = $matches[4];
    		$dbname = $matches[5];
		}

		$file_path = BASE_DIR.'/webapp/modules/install/sql/'.$database.'/'.$type."/".$file_name;
		if(!@file_exists($file_path) && $database == "mysqli") {
			$database = "mysql";
			$file_path = BASE_DIR.'/webapp/modules/install/sql/'.$database.'/'.$type."/".$file_name;
		}

		$data = "";
		include_once $file_path;

		$this->databaseSqlutility->splitMySqlFile($pieces, $data);
		$adodb =& $this->db->getAdoDbObject();

		foreach ($pieces as $piece) {
			// SQLユーティリティクラスにてテーブル名にプレフィックスをつける
			// 配列としてリターンされ、
            // 	[0] プレフィックスをつけたクエリ
            // 	[4] プレフィックスをつけないテーブル名
			// が格納されている
			$prefixed_query = $this->databaseSqlutility->prefixQuery($piece, $this->db->getPrefix());
			if ( !$prefixed_query ) {
				$errorList->add(get_class($this), $piece);
				return false;
			}

			// 実行
			//$this->db->executeはprefixの変換処理があるため使用しない
			if ( !$adodb->Execute($prefixed_query[0]) ) {
				return false;
				//continue;
			}
		}
		// ルーム一覧取得
		//$pages = $this->db->selectExecute("pages", array("page_id = room_id" => null));
    	//if($pages == false) {
    	//	return false;
    	//}

    	//
    	// pages_modules_linkへの登録処理
    	// モジュールインストールで行っているので必要なし
    	//

    	//
    	// authorities_modules_linkへの登録
    	// モジュールインストールで行っているので必要なし
    	//

    	//
    	// modules display_sequence振替処理
    	//
    	$upd_module_arr = array();
    	$sys_display_sequence = 1;
    	$sys_display_seq = explode(",", INSTALL_MODULES_SYS_DISPLAY_SEQ);
    	foreach($sys_display_seq as $dir_name) {
    		if(isset($modules[$dir_name])) {
    			// update
    			if(!$this->modulesAction->updModuleDisplayseq($modules[$dir_name]['module_id'],$sys_display_sequence)) {
    				return false;
    			}
    			$upd_module_arr[$dir_name] = true;
    			$sys_display_sequence++;
    		}
    	}
    	$display_seq = explode(",", INSTALL_MODULES_GENERAL_DISPLAY_SEQ);
    	$display_sequence = 1;
    	foreach($display_seq as $dir_name) {
    		if(isset($modules[$dir_name])) {
    			// update
    			if(!$this->modulesAction->updModuleDisplayseq($modules[$dir_name]['module_id'],$display_sequence)) {
    				return false;
    			}
    			$upd_module_arr[$dir_name] = true;
    			$display_sequence++;
    		}
    	}
    	foreach($modules as $dir_name => $module) {
    		if(!isset($upd_module_arr[$dir_name])) {
    			if($module['system_flag'] == _ON) {
    				// 管理系
    				// update
	    			if(!$this->modulesAction->updModuleDisplayseq($module['module_id'],$sys_display_sequence)) {
	    				return false;
	    			}
	    			$sys_display_sequence++;
    			} else {
    				// 一般系
    				if(!$this->modulesAction->updModuleDisplayseq($module['module_id'],$display_sequence)) {
	    				return false;
	    			}
	    			$display_sequence++;
    			}
    		}
    	}

    	//
    	// .htaccess作成処理(絶対パスに修正したため、削除)
    	//
    	/*
    	if(HTDOCS_DIR != START_INDEX_DIR && defined("CORE_BASE_URL") &&
    		 CORE_BASE_URL != BASE_URL && CORE_BASE_URL != "http://") {
    		// 画像ファイル、CSSファイルの格納場所がindex.phpの場所とは異なる
    		$writing_flag = true;
    		$htaccess_path = START_INDEX_DIR . "/" . INSTALL_HTACCESS_FILENAME;
    		if(@file_exists($htaccess_path)) {
    			@chmod($htaccess_path, 0777);
    			if (!is_writeable($htaccess_path)) {
    				// 書き込み不可
    				//return true;
    				$writing_flag = false;
    			}
    		}
    		if($writing_flag == true) {
	    		if (! $file = fopen($htaccess_path,"w") ) {
			        $writing_flag = false;
			    }
    		}
    		if($writing_flag == true) {
    			// 書き込み
    			$writing_data = "";
    			$htaccess_data_file_path = BASE_DIR.'/webapp/modules/install/config/'.INSTALL_HTACCESS_DATA_FILENAME;

    			include_once $htaccess_data_file_path;

    			if(STYLE_DIR == BASE_DIR . '/webapp/style') {
    				// スタイル関連もマスタのNetCommonsと同じものを使う
    				// htaccessの先頭の「#」を削除
    				$writing_data = str_replace("#", "", $writing_data);
    			}
    			fwrite($file, $writing_data);
    			fclose($file);
    			@chmod($htaccess_path, 0444);	//0644
    		}
    	}
    	*/

    	// ----------------------------------------------------------------------
		// --- 初期ページ追加　　　　　　		                              ---
		// ----------------------------------------------------------------------
		$result = $this->blocksAction->defaultPrivateRoomInsert(14, $admin_user_id, $users[0]['handle']);		// TODO:14固定
		if($result === false)  {
    		return 'error';
    	}
    	$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return false;
    	if($config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP ||
			$config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
			//
			// ページテーブル追加
			//
			$pages_params = array(
	    		"site_id" => $self_site_id,
	    		"root_id" => 0,
	    		"parent_id" => 0,
	    		"thread_num" => 0,
	    		"display_sequence" => 2,
	    		"action_name" => "pages_view_main",
	    		"parameters" => "",
	    		"page_name" => $admin_handle,
	    		"permalink" => "",
	    		"show_count" => 0,
	    		"private_flag" => _ON,
	    		"default_entry_flag" => _ON,
	    		"space_type" => _SPACE_TYPE_GROUP,
	    		"node_flag" => _ON,
	    		"shortcut_flag" => _OFF,
	    		"copyprotect_flag" => _OFF,
	    		"display_scope" => _DISPLAY_SCOPE_NONE,
	    		"display_position" => _DISPLAY_POSITION_CENTER,
	    		"display_flag" => _PAGES_DISPLAY_FLAG_DISABLED,
	    		"insert_time" =>$time,
				"insert_site_id" => $self_site_id,
				"insert_user_id" => $admin_user_id,
				"insert_user_name" => $admin_handle,
				"update_time" =>$time,
				"update_site_id" => $self_site_id,
				"update_user_id" => $admin_user_id,
				"update_user_name" => $admin_handle
	    	);


			$private_page_id = $this->pagesAction->insPage($pages_params, true, false);
	    	if ($private_page_id === false) return false;

	    	$replace_permalink = preg_replace(_PERMALINK_PROHIBITION, _PERMALINK_PROHIBITION_REPLACE, $admin_handle);
	    	if(_PERMALINK_MYPORTAL_PREFIX_NAME != '') {
	    		$permalink = _PERMALINK_MYPORTAL_PREFIX_NAME."/".$replace_permalink;
	    	} else {
	    		$permalink = $replace_permalink;
	    	}

	    	$result = $this->pagesAction->updPage(array("permalink" => $permalink, "display_sequence" => 3), array("page_id" => $private_page_id));
	    	if ($result === false) return false;

	    	$result = $this->pagesAction->updPage(array("display_sequence" => 4), array("page_id" => _SELF_TOPGROUP_ID));
	    	if ($result === false) return false;

	    	//
			// ページユーザリンクテーブル追加
			//
			$pages_users_link_params = array(
    			"room_id" => $private_page_id,
    			"user_id" => $admin_user_id,
    			"role_authority_id" => _ROLE_AUTH_CHIEF,
    			"createroom_flag" => _OFF
    		);
			$result = $this->pagesAction->insPageUsersLink($pages_users_link_params);
	    	if ($result === false) return false;

			//
			// 月別アクセス回数初期値登録
			//
			$name = "_hit_number";
			$year = intval(substr($time, 0, 4));
    		$month = intval(substr($time, 4, 2));
    		$monthlynumber_params = array(
				"user_id" =>$admin_user_id,
				"room_id" => $private_page_id,
				"module_id" => 0,
				"name" => $name,
				"year" => $year,
				"month" => $month,
				"number" => 0
			);
			$result = $this->monthlynumberAction->insMonthlynumber($monthlynumber_params);
    		if($result === false)  {
	    		return false;
	    	}
		}
    	return true;
    }
}
?>
