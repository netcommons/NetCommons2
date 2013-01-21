<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム作成-編集 参加会員編集画面
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Regist_Selectusers extends Action
{
	// リクエストパラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	
	var $room_name = null;
	var $display_flag = null;
	var $space_type_common = null;
	
	var $subgroup_flag = null;			//サブグループ作成用
	var $location = null;				//サブグループ作成用
	
	// コンポーネントを使用するため
	var $pagesView = null;
	var $db = null;
	var $session = null;
	var $request = null;
	var $authoritiesView = null;
	
	// 値をセットするため
	var $page = null;
	var $parent_page = null;
	
	var $users = null;
	var $count = 0;
	var $select_auth_flag = 0;
	
    /**
     * ルーム作成-編集　参加会員編集画面
     *
     * @access  public
     */
    function execute()
    {
    	$session_space_type_common = $this->session->getParameter(array("room", $this->edit_current_page_id,"general","space_type_common"));
    	if(!isset($session_space_type_common) ||
    		intval($this->space_type_common) != intval($session_space_type_common)) {
    		// 初期化
    		$this->select_auth_flag = _ON;
    		$this->session->removeParameter(array("room", $this->edit_current_page_id));
    	}
    	if($this->subgroup_flag) {
    		// サブグループ
    		if($this->parent_page_id != $this->location) {
    			// 作成場所が変更されたため、セッションクリア
    			$this->session->removeParameter(array("room", $this->edit_current_page_id));
    		}
    		$this->parent_page_id = $this->location;
    		$this->request->setParameter("parent_page_id", $this->parent_page_id);
    		
    	}
    	
    	//
    	// 参加者取得
    	//
    	$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	
    	if($this->edit_current_page_id != _SELF_TOPPUBLIC_ID) {
    		$this->parent_page =& $this->pagesView->getPageById(intval($this->parent_page_id));
    		if($this->parent_page === false) return 'error';
    	}
    	if($this->edit_current_page_id != 0) {
    		$this->page =& $this->pagesView->getPageById(intval($this->edit_current_page_id));
    		if($this->page === false) return 'error';
    	}
    	
    	$select_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_select_str"));
    	if(!$select_str) {
    		$params = array();
	    	$select_str = "SELECT {users}.*, ".
								"{authorities}.user_authority_id,".
								"{authorities}.role_authority_id,".
								"{authorities}.role_authority_name,".
								"{authorities}.public_createroom_flag,".
								"{authorities}.group_createroom_flag,".
								"{authorities}.private_createroom_flag,".
								"{authorities}.hierarchy,".
								"{pages_users_link}.role_authority_id AS authority_id,".
								"{pages_users_link}.createroom_flag";
			if($this->edit_current_page_id != 0) {
				$select_page_id = $this->edit_current_page_id;
			} else {
				$select_page_id = $this->parent_page_id;	
			}
			$from_str = " FROM ({authorities}, {users})".
					" LEFT JOIN {pages_users_link} ON {pages_users_link}.room_id=".$select_page_id." ".
					" AND {users}.user_id={pages_users_link}.user_id ";

			$where_str = " WHERE {users}.role_authority_id={authorities}.role_authority_id";
			if($this->parent_page['thread_num'] >= 1) {
	    		//親ルームに参加している会員すべて（サブグループ作成）
	    		$pages_users_links =& $this->pagesView->getPagesUsers(array("{pages}.page_id" => $this->parent_page['page_id']), null, null, null, array($this,"_usersFetchcallback"));
				if($pages_users_links === false) return 'error';
				$entry_users = array();
				$not_entry_users = array();
				if(count($pages_users_links) > 0) {
					foreach($pages_users_links as $user_id => $page_user) {
						if($page_user['role_authority_id'] == _ROLE_AUTH_OTHER) {
							$not_entry_users[] = $user_id;
						} else {
							$entry_users[] = $user_id;
						}
					}			
				}
				if($this->parent_page['default_entry_flag']) {
					// 禁止者以外（NOT IN）	
					if(count($not_entry_users) > 0) $where_str .= " AND {users}.user_id NOT IN ('". implode("','", $not_entry_users). "') ";
				} else {
					// 参加者(IN)
					if(count($entry_users) > 0) $where_str .= " AND {users}.user_id IN ('". implode("','", $entry_users). "') ";
				}
				
	    		//$where_params = array(
				//						"{pages_users_link}.room_id"=>intval($select_page_id),
				//						"{pages_users_link}.role_authority_id != "._ROLE_AUTH_OTHER=>null
				//						);
	    		//$where_str .= $this->db->getWhereSQL($params, $where_params, false);
			}
			$add_from_str = "";
			$add_where_str = "";
			$from_params = array();
			$where_params = array();
			
			// 検索条件をセッションに保存
			$this->session->setParameter(array("room", $this->edit_current_page_id,"selected_select_str"), $select_str);
			$this->session->setParameter(array("room", $this->edit_current_page_id,"selected_from_str"), $from_str);
			$this->session->setParameter(array("room", $this->edit_current_page_id,"selected_where_str"), $where_str);
			$this->session->setParameter(array("room", $this->edit_current_page_id,"selected_params"), $params);
    	} else {
	    	// セッションデータから取得
	    	// 検索絞込み時
	    	$from_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_from_str"));
	    	$add_from_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_add_from_str"));
  			$where_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_where_str"));
    		$add_where_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_add_where_str"));
    		$params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_params"));
    		$from_params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_from_params"));
    		$where_params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_where_params"));
    	}
    	if(!is_array($from_params)) $from_params = array();
    	if(!is_array($where_params)) $where_params = array();
    	$sql_params = array_merge((array)$params, (array)$from_params, (array)$where_params);
    	
    	$sql = "SELECT COUNT(*) AS count ".$from_str.$add_from_str.$where_str.$add_where_str;
		$result = $this->db->execute($sql, $sql_params, null, null, false);
		if ($result === false) {
	       	$this->db->addError();
	       	return 'error';
		}
		$this->count = $result[0][0];
		
		if($this->room_name != null) {
			// 基本項目セット
			$this->session->removeParameter(array("room", $this->edit_current_page_id,"general"));
			$this->session->setParameter(array("room", $this->edit_current_page_id,"general","room_name"), $this->room_name);
			$this->session->setParameter(array("room", $this->edit_current_page_id,"general","display_flag"), $this->display_flag);
			$this->space_type_common = ($this->space_type_common == null) ? 0 : intval($this->space_type_common);
			$this->session->setParameter(array("room", $this->edit_current_page_id,"general","space_type_common"), $this->space_type_common);
		} else {
			$this->room_name =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","room_name"));
		}
		
		//
		// モデレータの細分化された一覧を取得
		//
		$mod_where_params = array("user_authority_id" => _AUTH_MODERATE);
		$mod_order_params = array("hierarchy" => "DESC");
		$authorities = $this->authoritiesView->getAuthorities($mod_where_params, $mod_order_params);
		if($authorities === false) {
			return 'error';
		}
		$authorities_count = count($authorities);
		
		$this->session->setParameter(array("room", $this->edit_current_page_id,"authorities"), $authorities);
		$this->session->setParameter(array("room", $this->edit_current_page_id,"authorities_count"), $authorities_count);
		
    	return 'success';
    }
    
    /**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_usersFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['user_id']] = $row;
		}
		return $ret;
	}
}
?>