<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム管理 ルーム一覧表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_List extends Action
{
	// リクエストパラメータを受け取るため
	var $show_space_type = null;
	var $show_private_flag = null;
	var $show_default_entry_flag = null;
	var $languages = null;

	// コンポーネントを使用するため
	var $pagesView = null;
	var $session = null;
	var $authoritiesView = null;

	// 値をセットするため
	var $pages = null;
	var $count = null;
	var $show_page_id = null;

	var $subgroup_count = null;

	var $authority = null;

    /**
     * ルーム管理 ルーム一覧表示
     *
     * @access  public
     */
    function execute()
    {
    	// Sessionクリア
		$this->session->removeParameter(array("room"));

		//
		// ルーム作成権限取得
		//
		$this->authority =& $this->authoritiesView->getAuthorityById($this->session->getParameter("_role_auth_id"));
		if($this->authority === false) {
			return 'error';
		}


		//
		// 表示可能ルーム取得
		//

    	$user_id = $this->session->getParameter("_user_id");
    	if($this->show_space_type !== null && $this->show_private_flag !== null) {
	    	$where_params = array(
	    						"user_id" => $user_id,
	    						"space_type" => $this->show_space_type,
	    						"private_flag" => $this->show_private_flag,
	    						"{pages}.room_id={pages}.page_id" => null,
	    						"(private_flag="._OFF." OR ({pages_users_link}.user_id = '".$user_id."' AND {pages}.insert_user_id = '".$user_id."' AND  private_flag="._ON." AND default_entry_flag=".intval($this->show_default_entry_flag)."))" => null
	    					);
    	} else {
    		$where_params = array(
	    						"user_id" => $user_id,
	    						"{pages}.room_id={pages}.page_id" => null
	    					);
    	}
    	$order_params = array(
			"thread_num" => "ASC",
			"display_sequence" => "ASC"
		);
    	$result =& $this->pagesView->getShowPagesList($where_params, $order_params, 0, 0, array($this, '_showpages_fetchcallback'), array($this->authority));
		if($result === false) {
			return 'error';
		}
		list($this->pages, $this->count, $this->show_page_id) =  $result;

		//
		// サブグループ作成リンクを表示するかどうか
		//
		$where_params = array(
							'user_id' => $this->session->getParameter("_user_id"),
							'thread_num' => 1,
							'space_type' => $this->show_space_type,
							'{pages}.page_id={pages}.room_id' => null,
							'createroom_flag' => _ON
						);
		$subgroup_pages =& $this->pagesView->getPagesUsers($where_params);
		if($subgroup_pages === false) return 'error';
		$this->subgroup_count = count($subgroup_pages);

		return 'success';
    }


	/**
	 * fetch時コールバックメソッド(pages)
	 * @param result adodb object
	 * 			情報みれる人（名称変更）　				   ＞　 ルームの主担
	 * 										　					グループスペース直下ならば、管理者ONLY
	 * 			公開中-準備中							 　＞　 ルームの主担
	 * 										　					深さ0ならば変更不可
	 * 			参加会員修正　＞　サブグループならば、その親のルーム＝主担　カレントルーム＝主担
	 * 							　ルームならば、ルームの主担
	 * 							  グループスペース直下ならば、不可
	 * 			配置可能モジュール選択　＞　サブグループならば、その親のルーム＝主担　カレントルーム＝主担
	 * 										ルームならば、ルームの主担
	 * 										グループスペース直下ならば、不可
	 * 			削除					＞　サブグループならば、その親のルーム＝主担　カレントルーム＝主担
	 * 										ルームの主担
	 * 										(深さ０ならば削除不可)
	 * @access	private
	 */
	function _showpages_fetchcallback($result, $params) {
		$ret = array();

		$authority = $params[0];

		$buf_authority_id_arr = array();

		$page_id_ret = array();
		$count = 0;
		$show_page_id = 0;

		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$authCheck =& $container->getComponent("authCheck");

		while ($row = $result->fetchRow()) {
			if($row['thread_num'] == 0) $show_page_id = $row['page_id'];

			if($row['thread_num'] == 0) {
				// グループスペース or プライベートスペース or パブリックスペース
				$row['show_chgdisplay'] = _OFF;
				$row['show_delete'] = _OFF;
				if($row['space_type'] == _SPACE_TYPE_GROUP && $row['private_flag'] == _OFF) {
					// グループスペース
					if($row['thread_num'] == 0 && $session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
						$row['show_inf'] = _ON;
						$row['show_edit'] = _OFF;
					} else if($row['thread_num'] == 0) {
						$row['show_inf'] = _OFF;
						$row['show_edit'] = _OFF;
					} else if($session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
						$row['show_inf'] = _ON;
						$row['show_edit'] = _ON;
					} else {
						$row['show_inf'] = _OFF;
						$row['show_edit'] = _OFF;
					}
					/*
					if($authority['group_createroom_flag'] == _ON) {
						$row['show_inf'] = _ON;
						$row['show_edit'] = _ON;
					} else {
						$row['show_inf'] = _OFF;
						$row['show_edit'] = _OFF;
					}
					*/
				} else if($row['private_flag'] == _ON) {
					// プライベートスペース
					if($row['authority_id'] >= _AUTH_CHIEF) {
						$row['show_inf'] = _ON;
						$row['show_edit'] = _ON;
					} else {
						$row['show_inf'] = _OFF;
						$row['show_edit'] = _OFF;
					}
					//if($authority['private_createroom_flag'] == _ON) {
					//	$row['show_inf'] = _ON;
					//	$row['show_edit'] = _ON;
					//} else {
					//	$row['show_inf'] = _OFF;
					//	$row['show_edit'] = _OFF;
					//}
				} else {
					/*
					if($authority['public_createroom_flag'] == _ON) {
						$row['show_inf'] = _ON;
						$row['show_edit'] = _ON;
					} else {
						$row['show_inf'] = _OFF;
						$row['show_edit'] = _OFF;
					}
					*/
					if($session->getParameter("_user_auth_id") == _AUTH_ADMIN) {
						$row['show_inf'] = _ON;
						$row['show_edit'] = _ON;
					} else {
						$row['show_inf'] = _OFF;
						$row['show_edit'] = _OFF;
					}
				}
			} else if($row['thread_num'] == 1) {
				// ルーム
				//言語切替のため追加、パブリックスペースの場合はセンターカラムだけ言語が付いている
				if($row['space_type'] == _SPACE_TYPE_PUBLIC && $row['private_flag'] == _OFF && $row['display_position'] == _DISPLAY_POSITION_CENTER && $row['lang_dirname'] != $session->getParameter('_lang')) {
					continue;
				}
				if($row['authority_id'] >= _AUTH_CHIEF) {
					$row['show_chgdisplay'] = _ON;
					$row['show_inf'] = _ON;
					$row['show_edit'] = _ON;
					$row['show_delete'] = _ON;
				} else {
					$row['show_chgdisplay'] = _OFF;
					$row['show_inf'] = _OFF;
					$row['show_edit'] = _OFF;
					$row['show_delete'] = _OFF;
				}
			} else {
				// サブグループ
				//if($row['authority_id'] >= _AUTH_CHIEF) {
				//	$row['show_chgdisplay'] = _ON;
				//	$row['show_inf'] = _ON;
				//} else {
				//	$row['show_chgdisplay'] = _OFF;
				//	$row['show_inf'] = _OFF;
				//}
				if($row['authority_id'] >= _AUTH_CHIEF &&
					((isset($buf_authority_id_arr[intval($row['parent_id'])]) && $buf_authority_id_arr[intval($row['parent_id'])] >= _AUTH_CHIEF)
					|| $row['insert_user_id'] == $session->getParameter("_user_id"))) {
					$row['show_edit'] = _ON;
					$row['show_delete'] = _ON;

					$row['show_chgdisplay'] = _ON;
					$row['show_inf'] = _ON;
				} else {
					$row['show_edit'] = _OFF;
					$row['show_delete'] = _OFF;

					$row['show_chgdisplay'] = _OFF;
					$row['show_inf'] = _OFF;
				}
			}
			// 削除する場合は、常に親のルーム作成権限＋そのルームで主担である必要あり
			$createroom_flag = $authCheck->getPageCreateroomFlag($session->getParameter("_user_id"), intval($row['parent_id']));
        	if($createroom_flag == _OFF) {
	        	//ルーム作成権限なし
	        	$row['show_delete'] = _OFF;
	        }
			$buf_authority_id_arr[intval($row['page_id'])] = $row['authority_id'];
			$ret[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
			$count++;
		}
		return array($ret, $count, $show_page_id);
	}
}
?>
