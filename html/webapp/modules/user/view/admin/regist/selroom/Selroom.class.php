<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加ルーム選択画面表示
 * 新規登録-編集　次へボタン押下時
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Regist_Selroom extends Action
{
	// リクエストパラメータを受け取るため
	var $items = null;
	var $items_public = null;
	var $items_reception = null;
	var $user_id = null;
	
	var $room_authority = null;
	var $createroom_flag = null;
	
	// バリデートによりセット
	var $user = null;
	var $show_items = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $pagesView = null;
	
	// 値をセットするため
	var $not_enroll_room_arr = null;
	var $enroll_room_arr = null;
	var $edit_flag = _ON;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->user_id == null || $this->user_id == "0") {
			$this->user_id = "0";
			$this->edit_flag = _OFF;
			$user_id = $this->session->getParameter("_system_user_id");
		} else {
			$user_id = $this->user_id;
    	}
    	
    	// 初期化
    	$this->session->removeParameter(array("user", "regist_confirm", $this->user_id));
    	
    	$pre_user_auth_id = $this->session->getParameter(array("user", "regist_auth", $this->user_id));
    	
    	// 会員登録基本情報をセッション保存
		if($this->items != null) {
			//初期化し設定
			$this->session->removeParameter(array("user", "regist", $this->user_id));
			foreach($this->items as $item_id => $item_value) {
				if(isset($this->show_items[$item_id])) {
					if(is_array($item_value)) {
						foreach($item_value as $key => $value) {
							$this->session->setParameter(array("user", "regist", $this->user_id, $this->show_items[$item_id]['item_id'], $key), $value);
						}
					} else {
						switch ($this->show_items[$item_id]['tag_name']) {
							case "role_authority_name":
								$value_arr = explode("|", $item_value);
								$item_value = intval($value_arr[0]);
								// デフォルト値を設定するため
								$this->session->setParameter(array("user", "regist_auth", $this->user_id), intval($value_arr[1]));
								//if($value_arr[1] == _AUTH_ADMIN) {
								//	$regist_role_auth = _ROLE_AUTH_ADMIN;
								//} else if($value_arr[1] == _AUTH_CHIEF) {
								//	$regist_role_auth = _ROLE_AUTH_CHIEF;
								//} else if($value_arr[1] == _AUTH_MODERATE) {
									$regist_role_auth = intval($item_value);
								//} else if($value_arr[1] == _AUTH_GENERAL) {
								//	$regist_role_auth = _ROLE_AUTH_GENERAL;
								//} else {
								//	$regist_role_auth = _ROLE_AUTH_GUEST;
								//}
								$this->session->setParameter(array("user", "regist_role_auth", $this->user_id), $regist_role_auth);
								break;
							case "active_flag_lang":
								$value_arr = explode("|", $item_value);
								$item_value = intval($value_arr[0]);
								break;
						}
						$this->session->setParameter(array("user", "regist", $this->user_id, $this->show_items[$item_id]['item_id']), $item_value);
					}
				}
			}
			// 公開する
			$this->session->removeParameter(array("user", "regist_public", $this->user_id));
			if($this->items_public != null) {
				foreach($this->items_public as $item_id => $item_value) {
					if(isset($this->show_items[$item_id]['item_id'])) {
						$this->session->setParameter(array("user", "regist_public", $this->user_id, $this->show_items[$item_id]['item_id']), $item_value);
					}
				}
			}
			// 受け取る
			$this->session->removeParameter(array("user", "regist_reception", $this->user_id));
			if($this->items_reception != null) {
				foreach($this->items_reception as $item_id => $item_value) {
					$this->session->setParameter(array("user", "regist_reception", $this->user_id, $this->show_items[$item_id]['item_id']), $item_value);
				}
			}
		}
		
    	$user_auth_id = $this->session->getParameter(array("user", "regist_auth", $this->user_id));
    	//
    	// 管理者が選択されていれば、確認画面へ
    	//
    	// 異なる権限に落とした場合、セッション情報をクリア
    	if($pre_user_auth_id != null && $pre_user_auth_id != $user_auth_id) {
    		//$this->session->removeParameter(array("user", "selroom", $this->user_id));
    		$this->session->removeParameter(array("user", "selauth", $this->user_id));
    	}
    	if($user_auth_id == _AUTH_ADMIN) {
    		// 全ルームを主担、サブグループ作成権限がONにできるルームはすべてONにしてセッションに登録
    		// システム管理者と同等の権限で登録
    		$user_id = $this->session->getParameter("_system_user_id");;
    		$callback_func = array($this, '_sessregist_fetchcallback');
    		$callback_params = array($this->user_id);
    	} else {
    		// 管理者から異なる権限に落とした場合、セッション情報をクリア
    		if($pre_user_auth_id == _AUTH_ADMIN) {
    			$this->session->removeParameter(array("user", "selroom", $this->user_id));
    			$this->session->removeParameter(array("user", "selauth", $this->user_id));
    		}
    		$callback_func = array($this, '_showpages_fetchcallback');
    		$callback_params = array($this->user_id, $this->edit_flag);
    	}
		
		$where_params = array(
							"user_id" => $user_id,
							"user_authority_id" => _AUTH_ADMIN,
							"role_authority_id" => _ROLE_AUTH_ADMIN,	//1固定
							"{pages}.page_id={pages}.room_id" => null
						);
						
		$order_params = array(
							"{pages}.thread_num" => "ASC"
						);
		//$order_params = null;
		$result =& $this->pagesView->getShowPagesList($where_params, $order_params, 0, 0, $callback_func, $callback_params);
		if($result === false) {
			return 'error';
		}
		
		if($user_auth_id == _AUTH_ADMIN) {
			return 'confirm';
		} else {
			list($this->not_enroll_room_arr, $this->enroll_room_arr) = $result;
		}
		
		// 権限設定により設定した値をセッションに保存
    	if($this->room_authority != null) {
    		$this->session->removeParameter(array("user", "selauth", $this->user_id));
    		$this->session->setParameter(array("user", "selauth", $this->user_id, "room_authority"), $this->room_authority);
    		$this->session->setParameter(array("user", "selauth", $this->user_id, "createroom_flag"), $this->createroom_flag);
    	}
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _showpages_fetchcallback($result, $func_params) {
		$user_id = $func_params[0];
		$edit_flag = $func_params[1];
		
		$not_enroll_ret = array();	
		$enroll_ret = array();	
		$selroom =& $this->session->getParameter(array("user", "selroom", $user_id));
		$parent_page_name = array();
		if(isset($selroom)) {
			//
			// sessionデータから振り分ける
			//
			$enroll_sess_room_list = array();
			if(isset($selroom['enroll_room'])) {
				foreach($selroom['enroll_room'] as $enroll_room) {
					$enroll_room_list = explode("_", $enroll_room);
					$enroll_sess_room_list[$enroll_room_list[0]] = $enroll_room_list[0];
				}
			}
			while ($row = $result->fetchRow()) {
				$parent_page_name[intval($row['page_id'])] = $row['page_name'];
				if($row['thread_num'] == 2 && isset($parent_page_name[intval($row['parent_id'])])) {
					$row['parent_page_name'] = $parent_page_name[intval($row['parent_id'])];
				}
				if(!isset($enroll_sess_room_list[$row['page_id']])) {
					// 不参加
					$not_enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					
					$row['page_name'] = '';
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
				} else {
					// 参加
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					
					$row['page_name'] = '';
					$not_enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
				}
			}
		} else {
			while ($row = $result->fetchRow()) {
				$parent_page_name[intval($row['page_id'])] = $row['page_name'];
				if($row['thread_num'] == 2 && isset($parent_page_name[intval($row['parent_id'])])) {
					$row['parent_page_name'] = $parent_page_name[intval($row['parent_id'])];
				}
				// 
				// 親のルームが参加しないと、子グループは参加できないため
				// いっしょに動かす(Javascript)
				// 
				if(($edit_flag == _OFF && $row['default_entry_flag'] == _OFF) || 
					($edit_flag == _ON && (($row['default_entry_flag'] == _OFF && $row['authority_id'] === null) || ($row['role_authority_id'] == _ROLE_AUTH_OTHER && !is_null($row['role_authority_id']))))) {
					// 不参加
					$not_enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					
					$row['page_name'] = '';
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
				} else {
					// 参加
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					
					$row['page_name'] = '';
					$not_enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
				}
			}
		}
		return array($not_enroll_ret, $enroll_ret);
	}
	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _sessregist_fetchcallback($result, $func_params) {
		$user_id = $func_params[0];
		$ret_enroll_room = array();
		$ret_room_authority = array();
		$ret_createroom_flag = array();
		while ($row = $result->fetchRow()) {
			$ret_enroll_room[$row['page_id']] = $row['page_id'];
			$ret_room_authority[$row['page_id']] = $row['role_authority_id'];	//_AUTH_CHIEF;
			$ret_createroom_flag[$row['page_id']] = $row['createroom_flag'];
		}
		// 初期化
		//$this->session->removeParameter(array("user", "selroom", $user_id));
    	//$this->session->removeParameter(array("user", "selauth", $user_id));
    	
    	$this->session->setParameter(array("user", "selroom", $user_id, "enroll_room"), $ret_enroll_room);
		//$this->session->setParameter(array("user", "selroom", $user_id, "not_enroll_room"), $ret_not_enroll_room);
		
		$this->session->setParameter(array("user", "selauth", $user_id, "room_authority"), $ret_room_authority);
		$this->session->setParameter(array("user", "selauth", $user_id, "createroom_flag"), $ret_createroom_flag);
    		
		return true;
	}
	
}
?>