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
class User_View_Admin_Regist_Confirm extends Action
{
	// リクエストパラメータを受け取るため
	var $user_id = null;
	var $room_authority = null;
	var $createroom_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $pagesView = null;
	var $usersView = null;
	var $authoritiesView = null;
	
	// 値をセットするため
	var $edit_flag = _ON;
    var $enroll_room_list = null;
    var $count = 0;
    var $items = null;
    var $user_auth_id = null;
	var $authorities_count = 0;
	
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
			$user_id = $this->session->getParameter("_system_user_id");;
		} else {
			$user_id = $this->user_id;
    	}
    	$this->user_auth_id = $this->session->getParameter(array("user", "regist_auth", $this->user_id));
    	
    	$this->items =& $this->usersView->getShowItems($this->session->getParameter("_system_user_id"), _AUTH_ADMIN, null);
    	if($this->items === false) return 'error';
    	
    	
		// 権限設定をセッション保存
    	if($this->room_authority != null) {
    		$this->session->removeParameter(array("user", "selauth", $this->user_id));
    		$this->session->setParameter(array("user", "selauth", $this->user_id, "room_authority"), $this->room_authority);
    		$this->session->setParameter(array("user", "selauth", $this->user_id, "createroom_flag"), $this->createroom_flag);
    	}
		
		$where_params = array(
							"user_id" => $user_id,
							"user_authority_id" => _AUTH_ADMIN,
							"role_authority_id" => 1,	//1固定
							"{pages}.page_id={pages}.room_id" => null
						);
		$order_params = array("thread_num" => "ASC");
		$result =& $this->pagesView->getShowPagesList($where_params, $order_params, 0, 0, array($this, '_showpages_fetchcallback'), array($this->user_id));
		if($result === false) {
			return 'error';
		}
		list($this->enroll_room_list, $this->count) = $result;
    	
    	//
		// モデレータの細分化された一覧を取得
		//
		$where_params = array("user_authority_id" => _AUTH_MODERATE);
		$order_params = array("hierarchy" => "DESC");
		$authorities = $this->authoritiesView->getAuthorities($where_params, $order_params);
		if($authorities === false) {
			return 'error';
		}
		$this->authorities_count = count($authorities);
    	
    	
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * @access	private
	 */
	function _showpages_fetchcallback($result, $func_params) {
		$user_id = $func_params[0];
		$count = 0;
		$enroll_ret = array();
		$selroom =& $this->session->getParameter(array("user", "selroom", $user_id));
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
			$selauth =& $this->session->getParameter(array("user", "selauth", $user_id, "room_authority"));
			$selauth_create_flag =& $this->session->getParameter(array("user", "selauth", $user_id, "createroom_flag"));
			while ($row = $result->fetchRow()) {
				if($row['thread_num'] == 0 && $row['space_type'] == _SPACE_TYPE_GROUP && $row['private_flag'] == _OFF) {
					// グループスペース直下　参加
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					
					$count++;
				} else if(isset($enroll_sess_room_list[$row['page_id']])) {
						
					// デフォルトで参加するグループスペースのルームではないが、権限が不参加になっている
					//if(($row['thread_num'] != _SPACE_TYPE_GROUP || $row['default_entry_flag'] != _ON) && 
					//	(!isset($selauth[$row['page_id']]) || $selauth[$row['page_id']] == _AUTH_OTHER)) {
					//	continue;
					//}
					// サブグループ作成権限がONなのにグループスペース(パブリックスペース)のルームではない
					if(($row['thread_num'] != 1) && 
						isset($selauth_create_flag[$row['page_id']]) && $selauth_create_flag[$row['page_id']] == _ON) {
						continue;
					}
					// プライベートスペース＋新規ならば追加しない
					if($row['private_flag'] == _ON && $user_id == "0") {
						continue;
					}
					
					// 親が参加していないのに子グループが参加している
					if($row['thread_num'] >= 2 && !isset($selauth[$row['parent_id']])) {
						continue;	
					}
					
					// 参加
					$enroll_ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
					$ret_selroom[$row['page_id']][0] = $selauth[$row['page_id']];
					if(!isset($selauth_create_flag[$row['page_id']])) {
						$ret_selroom[$row['page_id']][1] = _OFF;
					} else {
						$ret_selroom[$row['page_id']][1] = $selauth_create_flag[$row['page_id']];
					}
					$count++;
				} 
			}
			// 登録時使用セッション
			$this->session->setParameter(array("user", "regist_confirm", $user_id), $ret_selroom);
		} else {
			// 登録時使用セッション
			$this->session->setParameter(array("user", "regist_confirm", $user_id), "");	
		}
		$count--;	// グループスペース分引く
		return array($enroll_ret, $count);
	}
}
?>