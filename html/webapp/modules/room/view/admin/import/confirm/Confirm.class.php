<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * アップロードファイル取り込み後の確認画面表示
 *
 * @package	 NetCommons
 * @author	  Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2012 AllCreator Co., Ltd.
 * @project	 NC Support Project, provided by AllCreator Co., Ltd.
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @access	  public
 */
class Room_View_Admin_Import_Confirm extends Action
{
	// リクエストパラメータを受け取るため

	// 使用コンポーネントを受け取るため
    var $session = null;
	var $authoritiesView = null;

	// Filterによりセット

	// validatorから受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
    var $page = null;

	// 値をセットするため
	var $delete_num = null;
	var $added_auth_member_num = null;
	var $total_auth_member_num = null;
	var $warnMsg = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// 記憶されているセッション情報を呼び出して、確認画面の文字列を作成する
		// 対象となる権限達
		$this->added_auth_member_num = array();
		$this->total_auth_member_num = array();
		$authorities = $this->authoritiesView->getAuthorities(array('(system_flag ='. _ON . ' OR user_authority_id = '. _AUTH_MODERATE. ') AND user_authority_id != '. _AUTH_ADMIN => null));
		foreach($authorities as $a) {
			$this->added_auth_member_num[$a['role_authority_name']] = $this->session->getParameter(array('room', 'import', 'count', $a['role_authority_id'], 'added_num'));
			$this->total_auth_member_num[$a['role_authority_name']] = $this->session->getParameter(array('room', 'import', 'count', $a['role_authority_id'], 'new_total'));
		}
		// 不参加への変更数
		$this->delete_num = $this->session->getParameter(array('room', 'import', 'count', 'delete_num'));

		// UPLOAD受け取り処理時に発生した警告文
		$this->warnMsg = $this->session->getParameter(array('room', 'import', 'warn'));
	    return 'succsess';
	}
}
?>
