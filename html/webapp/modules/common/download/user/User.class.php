<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員管理アバター等、会員毎で表示される画像表示クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Common_Download_User extends Action
{
	// リクエストパラメータを受け取るため
	var $upload_id = null;
	var $thumbnail_flag = null;
	
	// 使用コンポーネントを受け取るため
	var $uploadsView = null;
	var $actionChain = null;
	var $db = null;
	var $session = null;
	var $usersView = null;
	
    /**
     * 会員管理アバター等、会員毎で表示される画像表示クラス
     *
     * @access  public
     */
    function execute()
    {
    	$result = $this->_downloadCheck($this->upload_id, $this->thumbnail_flag);
    	if($result === false) return;
    	list($pathname,$filename,$physical_file_name) = $result;
    	clearstatcache();
    	if($pathname != null) {
    		$this->uploadsView->headerOutput($pathname, $filename, $physical_file_name, _OFF);
    	}
    }
    
    /**
	 * 画像表示できるかどうかのチェック
	 * @param int upload_id
	 * @param int $thumbnail_flag  1 or 0 サムネイル表示するかどうか
	 * @return array(string pathname, string file_name)
	 * @access	public
	 */
    function _downloadCheck($upload_id, $thumbnail_flag = 0) {
    	$action_name = $this->actionChain->getCurActionName();
    	
    	if($upload_id == null || intval($upload_id) == 0) {
    		return false;
    	}
    	
		//権限チェック
		$uploads = $this->uploadsView->getUploadById($upload_id);
		if($uploads === false || !isset($uploads[0])) {
			return false;
		}
		//
		// ActionNameチェック
		//
		if($action_name != $uploads[0]['action_name']) {
			return false;
		}
		
		$user_id = $this->session->getParameter("_user_id");
		$user_auth_id = $this->session->getParameter("_user_auth_id");
		
		$file_name = $uploads[0]['file_name'];
		$pathname = FILEUPLOADS_DIR.$uploads[0]['file_path'];
		if($thumbnail_flag) {
			$physical_file_name = $uploads[0]['upload_id']."_thumbnail.".$uploads[0]['extension'];
			if(!file_exists($pathname.$physical_file_name)) {
				$pathname = MODULE_DIR."/common/files/images/";
	    		$physical_file_name = "thumbnail.gif";
	    	}
		} else {
			$physical_file_name = $uploads[0]['physical_file_name'];
		}
		
		if($user_auth_id == _AUTH_ADMIN) {
			// 新規登録、プレビュー直後：管理者ならばOK
			return array($pathname, $file_name, $physical_file_name);	
		} else if($user_auth_id == _AUTH_OTHER) {
			$user_auth_id = _AUTH_GUEST;
		}
		
		//userテーブル検索
		$user =& $this->usersView->getUserById($uploads[0]['unique_id']);
		if($user === false) {
			return $user;
		}
		if($user['active_flag'] != _USER_ACTIVE_FLAG_ON) {
			// Activeではない
			return false;	
		}
		// users_items_linkテーブル検索
		$params = array(
					"user_id" => $uploads[0]['unique_id'],
					"item_id" => 13,
					// "content" => "?action=".$action_name."&upload_id=".$upload_id,
					"user_authority_id" => $user_auth_id
				);
		$sql = "SELECT {items}.*, {items_authorities_link}.under_public_flag,{items_authorities_link}.self_public_flag,{items_authorities_link}.over_public_flag," .
				"{users_items_link}.public_flag ".
				"FROM {items} ".
				" INNER JOIN {users_items_link} ON ({items}.item_id={users_items_link}.item_id".
				" AND {users_items_link}.user_id=? AND {users_items_link}.item_id=?)".
				" INNER JOIN {items_authorities_link} ON ({items}.item_id={items_authorities_link}.item_id".
				" AND {items_authorities_link}.user_authority_id=?)";
		$users_items_link =& $this->db->execute($sql, $params);
		if($users_items_link === false) {
			// エラーが発生した場合、エラーリストに追加
			//$this->db->addError();
			return $users_items_link;
		}
		if(count($users_items_link) != 1) {
			// 複数レコード存在する
			return false;
		}
		
		//権限チェック
		
		if($user_id == $user['user_id']) {
			// 自分自身	self_public_flag
			$public_flag = $users_items_link[0]['self_public_flag'];
		} else if($user['user_authority_id'] <= $user_auth_id) {
			// 	under_public_flag
			$public_flag = $users_items_link[0]['under_public_flag'];
		} else {
			//  over_public_flag
			$public_flag = $users_items_link[0]['over_public_flag'];
		}

		if ($public_flag < USER_PUBLIC) {
			return false;
		}

		if ($user_id != $user['user_id']
			&& $users_items_link[0]['allow_public_flag'] == _ON
			&& $users_items_link[0]['public_flag'] == _OFF) {
			return false;
		}

		return array($pathname, $file_name, $physical_file_name);
	}
}
?>
