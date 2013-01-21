<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 項目設定-項目追加
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Additem extends Action
{
	// リクエストパラメータを受け取るため
	var $item_id = null;
	var $item_name = null;
	var $type = null;

	var $require_flag = null;
	var $allow_public_flag = null;
	var $define_flag = null;
	var $allow_email_reception_flag = null;

	var $description = null;
	var $attribute = null;

	var $options = null;
	var $default_selected = null;
	
	// 使用コンポーネントを受け取るため
	var $usersAction = null;
	var $db = null;
	
	// バリデートによりセットするため
	var $items = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$item_id = intval($this->item_id);
		$set_options = "";
		$set_default_selected = "";
		if(isset($this->options)) {
			foreach($this->options as $key => $options) {
    			$default_selected = isset($this->default_selected[$key]) ? _ON : _OFF;
    			$set_options .= $options."|";
    			$set_default_selected .= $default_selected."|";
	    	}
		}	
		if($item_id > 0) {
			// 編集
			$param = array(
				"item_name" => $this->item_name,
				"type" => $this->type,
				"require_flag" => intval($this->require_flag),
				"define_flag" => intval($this->define_flag),
				"allow_public_flag" => intval($this->allow_public_flag),
				"allow_email_reception_flag" => intval($this->allow_email_reception_flag)
			);
			$where_params = array("item_id" => $item_id);
			$result = $this->usersAction->updItem($param, $where_params);
			if ($result === false) {
				return 'error';
			}
			if($this->items['type'] != $this->type) {
				// Typeが変更されたら、今まで入力されたデータ(users_items_link)の削除処理（初期化）
				// Optionsの値がまったくちがうものに変更された場合も初期化するほうが安全だが、現状、行わない
				$result = $this->usersAction->delUsersItemsLinkById($item_id);
				if ($result === false) {
					return 'error';
				}
			}
		} else {
			$max_row = $this->db->maxExecute("items", "row_num") + 1;
			$allow_email_reception_flag = ($this->allow_email_reception_flag == null) ? _OFF : _ON;
			$param = array(
				"item_name" => $this->item_name,
				"type" => $this->type,
				"tag_name" => "",			//固定
				"system_flag" => _OFF,		//固定
				"require_flag" => intval($this->require_flag),
				"define_flag" => intval($this->define_flag),
				"display_flag" => _ON,		//固定
				"allow_public_flag" => intval($this->allow_public_flag),
				"allow_email_reception_flag" => $allow_email_reception_flag,
				"col_num" => 1,				//固定
				"row_num" => $max_row
			);
			// 追加
			$item_id = $this->usersAction->insItem($param);
			if ($item_id === false) {
				return 'error';
			}
			
			//
			// items_authorities_link デフォルト値登録
			//
			$param = array(
				"item_id" => $item_id
			);
			// 管理者
			$param['user_authority_id'] = _AUTH_ADMIN;
			$param['under_public_flag'] = USER_EDIT;
			$param['self_public_flag'] = USER_EDIT;
			$param['over_public_flag'] = USER_EDIT;
			
			$result = $this->usersAction->insItemsAuthLink($param);
			if ($result === false) {
				return 'error';
			}
			// 主担 
			$param['user_authority_id'] = _AUTH_CHIEF;
			$param['under_public_flag'] = USER_NO_PUBLIC;
			$param['self_public_flag'] = USER_EDIT;
			$param['over_public_flag'] = USER_NO_PUBLIC;
			
			$result = $this->usersAction->insItemsAuthLink($param);
			if ($result === false) {
				return 'error';
			}
			// モデレータ
			$param['user_authority_id'] = _AUTH_MODERATE;
			$param['under_public_flag'] = USER_NO_PUBLIC;
			$param['self_public_flag'] = USER_EDIT;
			$param['over_public_flag'] = USER_NO_PUBLIC;
			
			$result = $this->usersAction->insItemsAuthLink($param);
			if ($result === false) {
				return 'error';
			}
			// 一般
			$param['user_authority_id'] = _AUTH_GENERAL;
			$param['under_public_flag'] = USER_NO_PUBLIC;
			$param['self_public_flag'] = USER_EDIT;
			$param['over_public_flag'] = USER_NO_PUBLIC;
			
			$result = $this->usersAction->insItemsAuthLink($param);
			if ($result === false) {
				return 'error';
			}
			// ゲスト
			$param['user_authority_id'] = _AUTH_GUEST;
			$param['under_public_flag'] = USER_NO_PUBLIC;
			$param['self_public_flag'] = USER_EDIT;
			$param['over_public_flag'] = USER_NO_PUBLIC;

			$result = $this->usersAction->insItemsAuthLink($param);
			if ($result === false) {
				return 'error';
			}
			
		}
		
		if($this->description != "" || $this->attribute != "") {
			$param = array(
				"description" => $this->description,
				"attribute" => $this->attribute
			);
			if($this->items['description'] == null && $this->items['attribute'] == null) {
				// 新規登録
				$result = $this->usersAction->insItemDesc($item_id, $param);
			} else {
				$where_params = array("item_id" => $item_id);
				$result = $this->usersAction->updItemDesc($param, $where_params);
			}
			
			if ($result === false) {
				return 'error';
			}
		} else {
			// 削除
			$result = $this->usersAction->delItemDescById($item_id);
			if ($result === false) {
				return 'error';
			}
		}
		
		if($item_id > 0 && $this->items['tag_name'] != "") {
			// tag_nameがあるものは、option値は変更不可能
			return 'success';
		}
			
		if($set_options != "" ) {
			$param = array(
				"options" => $set_options,
				"default_selected" => $set_default_selected
			);
			if($this->items['options'] == null && $this->items['default_selected'] == null) {
				// 新規登録
				$result = $this->usersAction->insItemOptions($item_id, $param);
			} else {
				// 更新
				$where_params = array("item_id" => $item_id);
				$result = $this->usersAction->updItemOptions($param, $where_params);
			}
			if ($result === false) {
				return 'error';
			}
		} else {
			// 削除
			$result = $this->usersAction->delItemOptionsById($item_id);
			if ($result === false) {
				return 'error';
			}
		}
		return 'success';
	}
}
?>
