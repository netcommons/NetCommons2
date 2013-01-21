<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム作成-編集 参加会員編集XML取得 or 登録内容確認XML取得（confirm_flag==_ON）
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_View_Admin_Regist_Userslist extends Action
{
	// リクエストパラメータを受け取るため
	var $parent_page_id = null;
	var $edit_current_page_id = null;
	
	var $selected_auth_id = null;
	var $limit = null;
    var $offset = null;
	
	// コンポーネントを使用するため
	var $pagesView = null;
	var $usersView = null;
	var $db = null;
	var $session = null;
	
	// 値をセットするため
	var $page = null;
	var $parent_page = null;
	
	var $users = null;
	
    /**
     * ルーム作成-編集　参加会員編集XML取得
     *
     * @access  public
     */
    function execute()
    {
    	//if($this->edit_current_page_id != null) {
    	//	$this->page =& $this->pagesView->getPageById(intval($this->edit_current_page_id));
    	//	if($this->page === false) return 'error';
    	//}
    	$this->parent_page =& $this->pagesView->getPageById(intval($this->parent_page_id));
    	if($this->parent_page === false) return 'error';
    	
    	//
    	// 参加者取得
    	//
    	$this->edit_current_page_id = ($this->edit_current_page_id == null) ? 0 : intval($this->edit_current_page_id);
    	$select_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_select_str"));
    	$from_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_from_str"));
	    $add_from_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_add_from_str"));
  		$where_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_where_str"));
    	$add_where_str =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_add_where_str"));
    	$params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_params"));
    	$from_params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_from_params"));
    	$where_params =& $this->session->getParameter(array("room", $this->edit_current_page_id,"selected_where_params"));
    	if(!is_array($from_params)) $from_params = array();
    	if(!is_array($where_params)) $where_params = array();
    	
    	$sql_params = array_merge((array)$params, (array)$from_params, (array)$where_params);
  		//if(!isset($this->page) || $this->page['private_flag'] == _OFF) {
    		$order_params = array("system_flag"=>"DESC", "hierarchy"=>"DESC" , "user_authority_id"=>"DESC" , "handle"=>"ASC");
  		//}
		$order_str = $this->db->getOrderSQL($order_params);
		$sql = $select_str.$from_str.$add_from_str.$where_str.$add_where_str.$order_str;
		if($this->edit_current_page_id != 0) {
			$edit_flag = _ON;
		} else {
			$edit_flag = _OFF;
		}
		$room_authority_arr =& $this->session->getParameter(array("room", $this->edit_current_page_id, "room_authority"));
		$room_createroom_flag_arr =& $this->session->getParameter(array("room", $this->edit_current_page_id, "room_createroom_flag"));
			
		
		//
		// 会員選択画面
		//
		$admin_users = null;	
		$default_entry_flag =& $this->session->getParameter(array("room", $this->edit_current_page_id,"general","space_type_common"));
		$this->users = $this->db->execute($sql, $sql_params, $this->limit, $this->offset, true, array($this, "_fetchcallback"), array($edit_flag, $this->selected_auth_id, $admin_users, $room_authority_arr, $room_createroom_flag_arr, $this->parent_page, $this->page, $default_entry_flag));
		if ($this->users === false) {
		    $this->db->addError();
			return 'error';
		}
		return 'success';
    }
    
	/**
	 * fetch時コールバックメソッド(確認画面)
	 * @param result adodb object
	 * @param array  func_params
	 * 						編集中かどうか
	 * 						全選択を押下しているかどうか（している場合、押下している権限）
	 * 						追加する管理者権限の会員
	 * 						表示された権限リスト					$room_authority_arr
	 * 						表示されたサブグループ作成許可リスト	$room_create_flag_arr
	 * 						親ページ配列							$parent_page
	 * 						ページ配列								$page
	 * 						デフォルトで参加させるフラグ			$default_entry_flag
	 * @return array
	 * @access	private
	 */
	function &_fetchcallback($result, $func_params) {
		$container =& DIContainerFactory::getContainer();
		$authoritiesView =& $container->getComponent("authoritiesView");
		$configView =& $container->getComponent("configView");
		$session =& $container->getComponent("Session");
		
		$config = $configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config === false) return false;
		$default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
    	$default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];
    	$default_entry_role_auth_private = $config['default_entry_role_auth_private']['conf_value'];
    	
		$ret = array();
		$edit_flag = $func_params[0];
		$selected_auth_id = $func_params[1];
		$admin_users = $func_params[2];
		$room_authority_arr =& $func_params[3];
		$room_createroom_flag_arr =& $func_params[4];
		$parent_page =& $func_params[5];
		$current_page =& $func_params[6];
		$default_entry_flag =& $func_params[7];
		$space_type = $parent_page['space_type'];
		$private_flag = $parent_page['private_flag'];
		
		if(isset($current_page) && $current_page['private_flag'] == _ON) {
			$default_entry_auth = $default_entry_role_auth_private;
		}else if($space_type == _SPACE_TYPE_GROUP) {
			$default_entry_auth = $default_entry_role_auth_group;
		} else {
			$default_entry_auth = $default_entry_role_auth_public;
		}
		$authoritiy =& $authoritiesView->getAuthorityById(intval($selected_auth_id));
		if($authoritiy === false) {
			return false;
		}
		
		// 管理者を足す
		if($admin_users != null && (!isset($current_page) || $current_page['private_flag'] == _OFF)) {
		//if($admin_users != null) {
			foreach($admin_users as $admin_user) {
				$admin_user['authority_id'] = _ROLE_AUTH_CHIEF;
				if($parent_page['thread_num'] == 0 && $parent_page['private_flag'] == _OFF) {
					$admin_user['createroom_flag'] = _ON;
				} else {
					$admin_user['createroom_flag'] = _OFF;
				}
				$ret[$admin_user['user_id']] = $admin_user;
			}
		}
		while ($row = $result->fetchRow()) {
			if($row['user_authority_id'] != _AUTH_ADMIN || (isset($current_page) && $current_page['private_flag'] == _ON)) {
				if(isset($room_authority_arr[$row['user_id']])) {
					$row['authority_id'] = $room_authority_arr[$row['user_id']];
					$row['createroom_flag'] = _OFF;//$room_createroom_flag_arr[$row['user_id']];
				} else if($selected_auth_id !== null) {
					if((isset($current_page) && $current_page['private_flag'] == _ON && $row['private_createroom_flag'] == _ON) ||
						($space_type == _SPACE_TYPE_PUBLIC && $row['public_createroom_flag'] == _ON) ||
						($space_type == _SPACE_TYPE_GROUP && $row['group_createroom_flag'] == _ON)
					) {
						$row['authority_id'] = $selected_auth_id;
						//$row['createroom_flag'] = _OFF;
					} else {
						// ルーム作成権限がないならば、自分の権限以上にはなれない
						if($authoritiy['hierarchy'] <= $row['hierarchy']) {
							$row['authority_id'] = $selected_auth_id;
						} else {
							if($default_entry_flag == _ON) {
								if($row['authority_id'] === null) {
									if($row['user_authority_id'] == _AUTH_ADMIN) {
										$regist_role_auth = _ROLE_AUTH_ADMIN;
									} else if($row['user_authority_id'] == _AUTH_CHIEF) {
										$regist_role_auth = _ROLE_AUTH_CHIEF;
									} else if($row['user_authority_id'] == _AUTH_MODERATE) {
										$regist_role_auth = intval($row['role_authority_id']);
									} else if($row['user_authority_id'] == _AUTH_GENERAL) {
										$regist_role_auth = _ROLE_AUTH_GENERAL;
									} else if($selected_auth_id == _AUTH_GUEST || $row['user_authority_id'] == _AUTH_GUEST) {
										$regist_role_auth = _ROLE_AUTH_GUEST;
									} else {
										$regist_role_auth = _ROLE_AUTH_OTHER;
									}
									$row['authority_id'] = $regist_role_auth;	
								}
							} else {
								$row['authority_id'] = _ROLE_AUTH_OTHER;
							}
						}
						$row['createroom_flag'] = _OFF;
					}
				} else if($edit_flag == _OFF && $row['authority_id'] !== null) {
					// 新規作成
					// サブグループ
					if($row['role_authority_id'] != _ROLE_AUTH_ADMIN && 
								$session->getParameter("_user_id") == $row['user_id']) {
						$row['authority_id'] = _ROLE_AUTH_CHIEF;
					} else if($default_entry_flag == _ON && $row['user_authority_id'] == _AUTH_GUEST) {
						$row['authority_id'] = _ROLE_AUTH_GUEST;
					} else if($default_entry_flag == _ON) {
						$row['authority_id'] = $default_entry_auth;
				    //} else if($row['authority_id'] == _AUTH_GENERAL && $default_entry_flag == _ON) {
					//	$row['authority_id'] = $default_entry_auth;
					//} else if($default_entry_flag == _ON) {
					//	$row['authority_id'] = $row['role_authority_id'];
					} else {
						$row['authority_id'] = _ROLE_AUTH_OTHER;
					}
				} else if ($row['authority_id'] === null) {
					//if(($private_flag == _ON && $row['private_createroom_flag'] == _ON) ||
					//	($space_type == _SPACE_TYPE_PUBLIC && $row['public_createroom_flag'] == _ON) ||
					//	($space_type == _SPACE_TYPE_GROUP && $row['group_createroom_flag'] == _ON)
					//) {
					//	$row['createroom_flag'] = _ON;
					//} else {
						$row['createroom_flag'] = _OFF;
					//}
					if($edit_flag == _OFF && $row['role_authority_id'] != _ROLE_AUTH_ADMIN && 
								$session->getParameter("_user_id") == $row['user_id']) {
						// 管理者ではなく、ログインしている会員がルームを作成している場合、主担にチェックをつける
						$row['authority_id'] = _ROLE_AUTH_CHIEF;
					} else if($default_entry_flag == _ON && $row['user_authority_id'] == _AUTH_GUEST) {
						$row['authority_id'] = _ROLE_AUTH_GUEST;
					} else if($default_entry_flag == _ON) {
						$row['authority_id'] = $default_entry_auth; //$regist_role_auth;
					} else {
						$row['authority_id'] = _ROLE_AUTH_OTHER;
					}
				}
				
			} else if(!isset($ret[$row['user_id']])){
				$row['authority_id'] = _ROLE_AUTH_CHIEF;
				if($parent_page['thread_num'] == 0 && $parent_page['private_flag'] == _OFF) {
					$row['createroom_flag'] = _ON;
				} else {
					$row['createroom_flag'] = _OFF;
				}
			}
			$ret[$row['user_id']] = $row;
		}
		return $ret;
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
	
	function &_roleFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['role_authority_id']] = $row;
		}
		return $ret;
	}
}
?>