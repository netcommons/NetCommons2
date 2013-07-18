<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 *  ルーム管理>>ルーム一覧>>参加者インポート（ファイル選択）
 *
 * @package	 NetCommons
 * @author	  Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2012 AllCreator Co., Ltd.
 * @project	 NC Support Project, provided by AllCreator Co., Ltd.
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @access	  public
 */
class Room_View_Admin_Import extends Action
{
	// リクエストパラメータを受け取るため
	var $room_name = null;
	var $parent_page_id = null;
	var $edit_current_page_id = null;

	// 使用コンポーネントを受け取るため
    var $session = null;
	var $usersView = null;
	var $authoritiesView = null;


	// Filterによりセット

	// validatorから受け取るため

	// 値をセットするため
    var $help = null;	// ヘルプの内容


	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$handle_item = $this->usersView->getItemById($this->usersView->getItemIdByTagName('handle'));

		$this->help[0]['name'] = defined($handle_item['item_name']) ? constant($handle_item['item_name']) : $handle_item['item_name'];
		$this->help[0]['need'] = _ON;
		$this->help[0]['desc'] = $handle_item['description'];
		$this->help[0]['exp'] = ROOM_IMPORT_ITEM_HANDLE;
		$this->help[0]['item'] = null;


		//
		// モデレータの細分化された一覧を取得
		//
		$mod_where_params = array("user_authority_id" => _AUTH_MODERATE);
		$mod_order_params = array("hierarchy" => "DESC");
		$authorities = $this->authoritiesView->getAuthorities($mod_where_params, $mod_order_params);
		if($authorities === false) {
			return 'error';
		}

		$this->help[1]['name'] = ROOM_IMPORT_HELP_AUTH_LABEL;
		$this->help[1]['need'] = _ON;
		$this->help[1]['desc'] = "";// ない
		$this->help[1]['exp'] = ROOM_IMPORT_HELP_AUTH_EXP;
		// 現在使用できる権限を取得して設定する
		$this->help[1]['item'][] = array('name' => _AUTH_CHIEF_NAME, 'num' => _ROLE_AUTH_CHIEF);
		foreach($authorities as $auth) {
			$this->help[1]['item'][] = array('name'=>$auth['role_authority_name'], 'num'=>$auth['role_authority_id']);
		}
		$this->help[1]['item'][] = array('name' => _AUTH_GENERAL_NAME, 'num' => _ROLE_AUTH_GENERAL);
		$this->help[1]['item'][] = array('name' => _AUTH_GUEST_NAME, 'num' => _ROLE_AUTH_GUEST);
		$this->help[1]['item'][] = array('name' => _AUTH_OTHER_NAME, 'num' => _ROLE_AUTH_OTHER);


		// 
		// 編集中のページIDをセッションに記録
		//
		$this->session->setParameter(array('room', 'import', 'parent_page_id'), $this->parent_page_id);
		$this->session->setParameter(array('room', 'import', 'edit_current_page_id'), $this->edit_current_page_id);

		return 'success';
	}
}
?>
