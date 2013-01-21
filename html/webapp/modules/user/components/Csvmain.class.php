<?php
/**
 * 会員情報>>インポート>>CSV出力用コンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
require_once WEBAPP_DIR.'/components/csv/Main.class.php';

class User_Components_Csvmain extends Csv_Main
{
	var $_db = null;
	var $_session = null;
	var $_container = null;
	var $_usersView = null;
	var $_pagesView = null;

	/**
	 * コンストラクター
	 * @access	public
	 */
	function User_Components_Csvmain() {
		$this->_LE = "\n";
		$this->charSet = "SJIS";
		$this->mimeType = "document/unknown";
		$this->division = ",";
		$this->extension = ".csv";
		$this->_csv = "";

		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_usersView =& $this->_container->getComponent("usersView");
		$this->_pagesView =& $this->_container->getComponent("pagesView");
	}

	/**
	 * CSVデータを追加する
	 * @param	headers data
	 * @access	public
	 */
	function add($headers, $data)
	{
		foreach($headers as $index) {
			if (!isset($data[$index]) || ($data[$index] == "")) {
				$data1[$index] = "";
				continue;
			}

			// "\"" を追加
//			$data1[$index] = preg_replace("/[\r\n]/s", " ", $data[$index]);
			$data_tmp = "";
			$double_qoute_flag = true;
			if (strpos($data[$index], "\"") === FALSE) {
				if ($data[$index] != USER_IMPORT_SPACE) {
					//Excelの場合、IDという文字列はエラーとなってしまうため、小文字にする
					if ($data[$index] == "ID") {
						$data[$index] = "id";
					}
				} else {
					$double_qoute_flag = false;
				}
				$data_tmp = $data[$index];
			} else {
				for ($data_idx=0; $data_idx<strlen($data[$index]); $data_idx++) {
					if ($data[$index][$data_idx] == '"') {
						$data_tmp = $data_tmp.'"';
					}
					$data_tmp = $data_tmp.$data[$index][$data_idx];
				}
			}
			if($double_qoute_flag)
				$data_tmp = '"' .$data_tmp. '"';
			$data1[$index] = $data_tmp;
		}
		$string = implode($this->division, $data1);
		$string .= "\n";

		$this->_csv .= mb_convert_encoding($string, $this->charSet, "auto");
	}

	/**
	 * 会員情報のヘッダ（設定値名称）取得
	 * @param
	 * @return header_name
	 * @access	public
	 */
	function make_header()
	{
		$header_name = null;

		/* DBからヘッダを取得 */
		$users_admin = $this->_usersView->getUsers(array("user_authority_id" => _AUTH_ADMIN));
		if (isset($users_admin) && is_array($users_admin)) {
    		$items = $this->_usersView->getShowItems($users_admin[0]['user_id'], _AUTH_ADMIN, null);

    		if (isset($items) && is_array($items)) {
		    	foreach($items as $item_list) {
		    		foreach($item_list as $item) {
						if (!(!strcmp($item['tag_name'], "insert_time") ||
							  !strcmp($item['tag_name'], "insert_user_name") ||
							  !strcmp($item['tag_name'], "update_time") ||
							  !strcmp($item['tag_name'], "update_user_name") ||
							  !strcmp($item['tag_name'], "password_regist_time") ||
							  !strcmp($item['tag_name'], "last_login_time") ||
							  !strcmp($item['tag_name'], "previous_login_time") ||
							  !strcmp($item['item_name'], USER_ITEM_AVATAR))) {
		    				$tagname = $item['item_name'];
			    			$header_name[$tagname] = $tagname;
				    		if (!strcmp($item['allow_public_flag'], "1")) {
				    			$tagname_public = sprintf(USER_IMPORT_PUBLIC_FLAG, $tagname);
		    					$header_name[$tagname_public] = $tagname_public;
				    		}
				    		if (!strcmp($item['type'], USER_TYPE_EMAIL) || !strcmp($item['type'], USER_TYPE_MOBILE_EMAIL)) {
				    			if (!strcmp($item['allow_email_reception_flag'], "1")) {
				    				$tagname_receptemail = sprintf(USER_IMPORT_RECEPTION_EMAIL, $tagname);
				   		 			$header_name[$tagname_receptemail] = $tagname_receptemail;
				    			}
				    		}
		    			}
		    		}
		    	}
    		}
		}

		return $header_name;
	}

	/**
	 * 全会員情報（設定値）取得
	 * @param users
	 * @return datas
	 * @access	public
	 */
	function make_data($users)
	{
		$datas = null;

		$_system_user_id = $this->_session->getParameter("_system_user_id");
		$_user_auth_id = $this->_session->getParameter("_user_auth_id");

		if (isset($users) && is_array($users)) {
			foreach($users as $user) {
				/* 管理者は除く */
//				if ($user['user_id'] != $_system_user_id) {
				if ($user['user_authority_id'] < $_user_auth_id) {
					$items = $this->_usersView->getShowItems($user['user_id'], $user['user_authority_id'], null);
					if (isset($items) && is_array($items)) {
				    	$useritem = $this->_usersView->getUserById($user['user_id'], array($this, "_getUsersFetchcallback"), null);
				    	if (isset($useritem) && is_array($useritem)) {
					    	$data = null;
					    	/* ユーザデータ */
					    	foreach($items as $item_list) {
						    	foreach($item_list as $item) {
							    	if (!(!strcmp($item['tag_name'], "insert_time") ||
					    				  !strcmp($item['tag_name'], "insert_user_name") ||
					    				  !strcmp($item['tag_name'], "update_time") ||
					    				  !strcmp($item['tag_name'], "update_user_name") ||
					    				  !strcmp($item['tag_name'], "password_regist_time") ||
					    				  !strcmp($item['tag_name'], "last_login_time") ||
					    				  !strcmp($item['tag_name'], "previous_login_time") ||
					    				  !strcmp($item['item_name'], USER_ITEM_AVATAR))) {
										//mod AllCreator
										if ((isset($item['tag_name']) && ($item['tag_name'] != "")) && $item['is_users_tbl_fld']!=false) {
											if( isset($useritem[$item['tag_name']]) ) {
												$item_content = $useritem[$item['tag_name']];
											}
											else {
												$item_content = null;
											}
										} else {
											$item_content = $item['content'];
								    	}
										$tagname = $item['item_name'];
								    	$data[$tagname] = $this->getChangeVal($item, $item_content);
								    	if (!strcmp($item['allow_public_flag'], "1")) {
								    		$tagname_public = sprintf(USER_IMPORT_PUBLIC_FLAG, $tagname);
		    								$data[$tagname_public] = $item['public_flag'];
	    									if (!isset($data[$tagname_public]) || ($data[$tagname_public] == ""))
	    										$data[$tagname_public] = sprintf("%s", USER_PUBLIC);
				    					}
								    	if (!strcmp($item['type'], USER_TYPE_EMAIL) || !strcmp($item['type'], USER_TYPE_MOBILE_EMAIL)) {
				    						if (!strcmp($item['allow_email_reception_flag'], "1")) {
				    							$tagname_receptemail = sprintf(USER_IMPORT_RECEPTION_EMAIL, $tagname);
							    				$data[$tagname_receptemail] = $item['email_reception_flag'];
							    				if (!isset($data[$tagname_receptemail]) || ($data[$tagname_receptemail] == ""))
							    					$data[$tagname_receptemail] = sprintf("%s", _OFF);
				    						}
								    	}

				    					if (!strcmp($item['tag_name'], "password")) {
					    					$data[$tagname] = USER_IMPORT_SPACE;
//					    					$data[$tagname] = $useritem['login_id'];
				    					}
					    			}
					    		}
					    	}
		    				$datas[] = $data;
				    	}
	    			}
				}
			}
		}

		return $datas;
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_getUsersFetchcallback($result) {
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");
		$languagesView =& $commonMain->registerClass(WEBAPP_DIR.'/components/languages/View.class.php', "Languages_View", "languagesView");
        $languages =& $languagesView->getLanguagesList();

		$ret = array();
		while ($row = $result->fetchRow()) {
			if(defined("_TZ_GMT0")) {
				//timezone.iniがincludeされているならば
				$row['timezone_offset_lang'] = $timezoneMain->getLangTimeZone($row['timezone_offset'], false);
//				$row['timezone_offset_lang'] = $row['timezone_offset'];
			}
			if($row['authority_system_flag'] == _ON && defined($row['role_authority_name'])) {
				$row['role_authority_name'] = constant($row['role_authority_name']);
			}

			if($row['active_flag'] == _USER_ACTIVE_FLAG_PENDING) {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_PENDING;
			} else if($row['active_flag'] == _USER_ACTIVE_FLAG_MAILED) {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_MAILED;
			} else if($row['active_flag'] == _USER_ACTIVE_FLAG_ON) {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_ON;
			} else {
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_OFF;
				$row['active_flag_lang'] = USER_ITEM_ACTIVE_FLAG_OFF;
			}
//			$row['active_flag_lang'] = $row['active_flag'];

			// 言語
			if(isset($languages[$row['lang_dirname']])) {
				$row['lang_dirname_lang'] = $languages[$row['lang_dirname']];
//				$row['lang_dirname_lang'] = $row['lang_dirname'];
			}
			$ret[] = $row;
		}
		return $ret;
	}

    /**
	 * 設定値を名称から数値に変更
	 * @param item　name
	 * @return res
	 * @access private
	 */
	function getChangeVal($item, $name)
	{
		if (!isset($name)) {
			$name = "";
		}
		$res = $name;

		if ($res != "") {
			if (isset($item['set_options']) && is_array($item['set_options'])) {
				$options = $item['set_options'];
				if (!strcmp($item["type"], USER_TYPE_CHECKBOX)) {
					$in_option_items = explode(",", $name);
					foreach($in_option_items as $in_option_item) {
						foreach ($options as $option) {
						if (!strcmp($option['options'], $in_option_item)) {
								$out_option_items[] = $option['def_options'];
								break;
							}
						}
					}
					$res = implode("|", $out_option_items);
				} else {
					foreach ($options as $option) {
						if (!strcmp($option['options'], $name)) {
							$res = $option['def_options'];
							break;
						}
					}
				}
			}
		}

		if ($res == "") $res = USER_IMPORT_SPACE;	// 無指定
		return $res;
	}
}
?>