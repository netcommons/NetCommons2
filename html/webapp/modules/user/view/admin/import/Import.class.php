<?php

/**
 * 会員管理>>インポート
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Import extends Action
{
    // 使用コンポーネントを受け取るため
    var $db = null;
    var $authoritiesView = null;
    var $usersView = null;
    var $pagesView = null;
    
    // 値をセットするため
    var $help = null;	// ヘルプの内容
    
	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
		// ヘルプ内容設定
		$users_admin = $this->usersView->getUsers(array("user_authority_id" => _AUTH_ADMIN));
		$this->showitems = $this->usersView->getShowItems($users_admin[0]['user_id'], _AUTH_ADMIN, null);
    	foreach($this->showitems as $item_list) {
    		foreach($item_list as $item) {
    			if (!isset($item['item_name']) || ($item['item_name'] == "")) continue;
    			
				if (!(!strcmp($item['tag_name'], "insert_time") ||
					  !strcmp($item['tag_name'], "insert_user_name") ||
					  !strcmp($item['tag_name'], "update_time") ||
					  !strcmp($item['tag_name'], "update_user_name") ||
					  !strcmp($item['tag_name'], "password_regist_time") ||
					  !strcmp($item['tag_name'], "last_login_time") ||
					  !strcmp($item['tag_name'], "previous_login_time") ||
					  !strcmp($item['item_name'], USER_ITEM_AVATAR))) {
					  // 項目名
					$this->help[$item['item_name']]['name'] = $item['item_name'];
					//　必須項目
					if (strcmp($item['require_flag'], "1")) {
						$this->help[$item['item_name']]['need'] = _OFF;
					} else {
						$this->help[$item['item_name']]['need'] = _ON;
					}
					// 説明
					//　デフォルト値
					$this->help[$item['item_name']]['desc'] = $item['description'];
					$this->help[$item['item_name']]['defaultset'] = USER_IMPORT_NOSET_DEFAULT;
					if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_LOGIN)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_LOGINID_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_HANDLE)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_HANDLER_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_PASSWORD)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_PASSWORD_EXP;
						$this->help[$item['item_name']]['defaultset'] = USER_IMPORT_PASSWORD_DEFAULT;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_USER_NAME)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_USERNAME_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_EMAIL)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_EMAIL_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_MOBILE_EMAIL)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_MOBILE_EMAIL_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_GENDER)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_GENDER_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_PROFILE)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_PROFILE_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_TIMEZONE_OFFSET)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_TIMEZONE_EXP;
						$this->help[$item['item_name']]['defaultset'] = USER_IMPORT_TIMEZONE_DEFAULT;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_LANG_DIRNAME)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_LANG_EXP;
						$this->help[$item['item_name']]['defaultset'] = USER_IMPORT_LANG_DEFAULT;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_IMPORT_CATEGORY)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_CATEGORY_EXP;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_ROLE_AUTHORITY_ID)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_ROLE_EXP;
						$this->help[$item['item_name']]['defaultset'] = USER_IMPORT_ROLE_DEFAULT;
					} else if (!strcmp($this->help[$item['item_name']]['name'], USER_ITEM_ACTIVE_FLAG)) {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_ACTIVE_EXP;
						$this->help[$item['item_name']]['defaultset'] = USER_IMPORT_ACTIVE_DEFAULT;
					} else  {
						$this->help[$item['item_name']]['exp'] = USER_IMPORT_NO_EXP;
					}	
					// 選択肢
					if (isset($item['set_options']) && is_array($item['set_options'])) {
						foreach($item['set_options'] as $option) {
							if(empty($option['def_options']) && $item['tag_name'] == 'lang_dirname_lang') $option['def_options'] = USER_IMPORT_LANGUAGE_AUTO;
							$this->help[$item['item_name']]['item'][] = array('name' => $option['options'], 'num' => $option['def_options']);
						}
					} else {
						$this->help[$item['item_name']]['item'] = null;
					}
    			}
    		}
    	}

		// 各項目の公開／非公開設定
		$taganme_public = sprintf(USER_IMPORT_PUBLIC_FLAG, USER_IMPORT_ALLITEMS);
		$this->help[$taganme_public]['name'] = $taganme_public;
		$this->help[$taganme_public]['need'] = _OFF;
		$this->help[$taganme_public]['exp'] = USER_IMPORT_ITEM_PUBLIC_EXP;
		$this->help[$taganme_public]['defaultset'] = USER_IMPORT_ITEM_PUBLIC_DEFAULT; 
		$this->help[$taganme_public]['desc'] = "";
		$this->help[$taganme_public]['item'][] = array('name' => USER_IMPORT_NOPUBLIC, 'num' => USER_NO_PUBLIC); 
		$this->help[$taganme_public]['item'][] = array('name' => USER_IMPORT_PUBLIC, 'num' => USER_PUBLIC); 

		// メールを受け取るか否かの設定
		$tagname_emailuse = sprintf(USER_IMPORT_RECEPTION_EMAIL, USER_IMPORT_ALLITEMS);
		$this->help[$tagname_emailuse]['name'] = $tagname_emailuse;
		$this->help[$tagname_emailuse]['need'] = _OFF;
		$this->help[$tagname_emailuse]['exp'] = USER_IMPORT_EMAIL_USE_EXP;
		$this->help[$tagname_emailuse]['defaultset'] = USER_IMPORT_EMAIL_USE_DEFAULT; 
		$this->help[$tagname_emailuse]['desc'] = "";
		$this->help[$tagname_emailuse]['item'][] = array('name' => USER_IMPORT_EMAIL_NOUSE, 'num' => _OFF);
		$this->help[$tagname_emailuse]['item'][] = array('name' => USER_IMPORT_EMAIL_USE, 'num' => _ON);
		
		return 'success';
    }
}
?>
