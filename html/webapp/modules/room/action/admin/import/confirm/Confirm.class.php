<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アップロードデータDBへの書き込み処理
 *
 * @package	 NetCommons
 * @author	  Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2012 AllCreator Co., Ltd.
 * @project	 NC Support Project, provided by AllCreator Co., Ltd.
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @access	  public
 */
class Room_Action_Admin_Import_Confirm extends Action
{
	// リクエストパラメータを受け取るため

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $configView = null;
	var $pagesAction = null;
	var $roomView = null;
	var $actionChain = null;
	var $authoritiesView = null;
	var $db = null;

	// Filterによりセット

	// validatorから受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	var $parent_page = null;
	var $page = null;

	// 値をセットするため
    var $config = null;
	var $authorityies = null;
	var $added_auth_member_num = null;
	var $deleted_auth_member_num = null;
	var $total_auth_member_num = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$tmp_authorities = $this->authoritiesView->getAuthorities(array('(system_flag ='. _ON . ' OR user_authority_id = '. _AUTH_MODERATE. ') AND user_authority_id != '. _AUTH_ADMIN => null));
		$this->authorities = array();
		foreach($tmp_authorities as $a) {
			$a['new_total'] = 0;
			$a['added_num'] = 0;
			$this->authorities[$a['role_authority_id']] = $a;
		}

		$errorList =& $this->actionChain->getCurErrorList();
		$warn_cnt = 0;

		// 不要セッション情報初期化
		$this->session->removeParameter(array('room', 'import', 'count'));
		$this->session->removeParameter(array('room', 'import', 'warn'));

		// メンバ変数初期化
		$this->added_auth_member_num = array();
		$this->total_auth_member_num = array();
		$this->deleted_auth_member_num = 0;

		// デフォルト参加権限定義情報を取得しておきます
		$config = $this->configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($this->page['default_entry_flag'] == _OFF) {
			$default_role_auth = _ROLE_AUTH_OTHER;
		}
		else {
			if($this->page['space_type'] == _SPACE_TYPE_PUBLIC) {
				$default_role_auth = $config['default_entry_role_auth_public']['conf_value'];
			}
			else {
				$default_role_auth = $config['default_entry_role_auth_group']['conf_value'];
			}
		}

		// 変更ユーザ情報、対象ルームユーザ情報取得
		$change_users = $this->session->getParameter(array('room', 'import', 'data'));
		$users = $this->roomView->getRoomUsersList($this->page, $this->parent_page, $this->authorities);

		$my_handle = $this->session->getParameter('_handle');

		foreach($change_users as $handle=>$new_auth) {
			// 対象者が存在しない会員だった
			// 管理者権限だった
			// 自分だった
			// 警告追加＆スキップ
			if($handle==$my_handle
				|| !isset($users[$handle]) 
				|| $users[$handle]['user_authority_id'] == _AUTH_ADMIN ) {
				$this->session->setParameter(array('room', 'import', 'warn', $warn_cnt++), $handle);// 警告追加しスキップ
				continue;
			}
			$where_params = array(
				'room_id' => $this->edit_current_page_id,
				'user_id' => $users[$handle]['user_id']
			);
			$ins_params = array(
				'room_id' => $this->edit_current_page_id,
				'user_id' => $users[$handle]['user_id'],
				'role_authority_id' => $new_auth,
				'createroom_flag' => _OFF
			);
			$upd_params = array(
				'role_authority_id' => $new_auth
			);
			$del_upd_params = array(
				'role_authority_id' => $new_auth,
				'createroom_flag' => _OFF
			);

			// 不参加に
			if($new_auth == _ROLE_AUTH_OTHER) {
				if($default_role_auth==$new_auth) {
					$resultPageUserLink = $this->pagesAction->delPageUsersLink($where_params);
				}
				else {
					if($users[$handle]['createroom_flag']==_OFF && $users[$handle]['authority_id']==$default_role_auth) {
						$resultPageUserLink = $this->pagesAction->insPageUsersLink($ins_params);
					}
					else {
						$resultPageUserLink = $this->pagesAction->updPageUsersLink($del_upd_params, $where_params);
					}
				}

				// 対象が親ルームの場合は、全てのサブルームからも削除
				if($this->page['space_type']!=_SPACE_TYPE_PUBLIC && $this->page['thread_num']==1) {
					$subroom_where_params = array($this->edit_current_page_id, $users[$handle]['user_id']);
					$sql = ' DELETE FROM {pages_users_link} WHERE room_id IN (SELECT page_id FROM {pages} WHERE parent_id=? AND page_id=room_id) AND user_id = ?';
					$this->db->execute($sql, $subroom_where_params);
				}
				if($resultPageUserLink) {
					$this->deleted_auth_member_num++;
				}
			}
			// 権限変更
			else {
				if(!isset($users[$handle]['permitted_auth'][$new_auth]) 
					|| $users[$handle]['permitted_auth'][$new_auth]==_OFF) {
					$this->session->setParameter(array('room', 'import', 'warn', $warn_cnt++), $handle);// 警告追加しスキップ
					continue;
				}
				// デフォルト権限での参加に変更
				if($new_auth==$default_role_auth) {
					// しかし現状ルーム作成権限がON
					if($users[$handle]['createroom_flag']==_ON) {
						// ならばUPDATEでレコード温存
						$resultPageUserLink = $this->pagesAction->updPageUsersLink($upd_params, $where_params);
					}
					// ルーム作成権限OFF
					else {
						// ならばレコード削除
						$resultPageUserLink = $this->pagesAction->delPageUsersLink($where_params);
					}
				}
				else {
					if($users[$handle]['authority_id']==$default_role_auth) {
						// しかし現状ルーム作成権限がON
						if($users[$handle]['createroom_flag']==_ON) {
							// ならば既にレコードあり。UPDATE
							$resultPageUserLink = $this->pagesAction->updPageUsersLink($upd_params, $where_params);
						}
						else {
							$resultPageUserLink = $this->pagesAction->insPageUsersLink($ins_params);
						}
					}
					else {
						$resultPageUserLink = $this->pagesAction->updPageUsersLink($upd_params, $where_params);
					}
				}
				if($resultPageUserLink) {
					$this->authorities[$new_auth]['added_num']++;
				}
			}
			if($resultPageUserLink) {
				$users[$handle]['authority_id'] = $new_auth;
			}
			else {
				$this->session->setParameter(array('room', 'import', 'warn', $warn_cnt++), $handle);// 警告追加しスキップ
			}
		}

		foreach($users as $handle=>$user) {
			if(!isset($this->authorities[$user['authority_id']])) {
				continue;
			}
			$this->authorities[$user['authority_id']]['new_total']++;
		}

		foreach($this->authorities as $a) {
			$this->added_auth_member_num[$a['role_authority_name']] = $a['added_num'];
			$this->total_auth_member_num[$a['role_authority_name']] = $a['new_total'];
		}
		return 'success';
	}
}