<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 個人情報が見れるかどうかのチェック
 * 同じグループルームに参加している人のみ見せる
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_PolicyView extends Validator
{
	var $_session = null;
	
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値		user_id
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
    	$this->_session =& $container->getComponent("Session");
    	$pagesView =& $container->getComponent("pagesView");
    	
    	$view_user_id = $attributes;
    	if($view_user_id == "0") {
        	$view_user_id = $this->_session->getParameter("_user_id");
        }
    	$login_user_id = $this->_session->getParameter("_user_id");
    	$user_auth_id = $this->_session->getParameter("_user_auth_id");
    	if($user_auth_id == _AUTH_ADMIN) {
    		// 管理者ならば、チェックしない
    		return;	
    	}
    	
    	// 自分自身ならば、共通ルームのチェックはしない
    	if($view_user_id == $this->_session->getParameter("_user_id")) {
    		// 正常終了
    		return;
    	}
    	/*
    	$order_params = array();
		$where_params = array("user_id" => $login_user_id, "{pages}.space_type" => _SPACE_TYPE_GROUP ,"{pages}.page_id={pages}.room_id" => null);
		
		$ret_login_room_arr = $pagesView->getShowPagesList($where_params, $order_params, 0, 0, array($this, '_showpages_fetchcallback'));
		if($ret_login_room_arr === false) {
			return $errStr;
		}
		
		$where_params['user_id'] = $view_user_id;
		$ret_view_room_arr = $pagesView->getShowPagesList($where_params, $order_params, 0, 0, array($this, '_showpages_fetchcallback'));
		if($ret_view_room_arr === false) {
			return $errStr;
		}
		$count_arr = count($ret_view_room_arr);
    	$diff_arr = array_diff($ret_view_room_arr, $ret_login_room_arr);
    	if(count($diff_arr) == $count_arr) {
    		// 共通ルームなし
    		return $errStr;
    	}
    	*/
    	return;
    }
    
    
	/**
	 * fetch時コールバックメソッド(blocks)
	 * @param result adodb object
	 * @access	private
	 */
	function _showpages_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if($row['default_entry_flag'] == _ON && $row['authority_id'] == null) {
				if($row['private_flag'] == _ON) {
					$_default_entry_auth_private = $this->_session->getParameter("_default_entry_auth_private");
					if(isset($_default_entry_auth_private)) {
						$row['authority_id'] = $_default_entry_auth_private;
						$row['hierarchy'] = $this->_session->getParameter("_default_entry_hierarchy_private");
					}
				} elseif($row['space_type'] == _SPACE_TYPE_PUBLIC) {
					$_default_entry_auth_public = $this->_session->getParameter("_default_entry_auth_public");
					if(isset($_default_entry_auth_public)) {
						$row['authority_id'] = $_default_entry_auth_public;
						$row['hierarchy'] = $this->_session->getParameter("_default_entry_hierarchy_public");
					}
				} else {
					$_default_entry_auth_group = $this->_session->getParameter("_default_entry_auth_group");
					if(isset($_default_entry_auth_group)) {
						$row['authority_id'] = $_default_entry_auth_group;
						$row['hierarchy'] = $this->_session->getParameter("_default_entry_hierarchy_group");
					}
				}
			}
			if($row['authority_id'] != null) {
				$ret[] = $row['page_id'];
			}
		}
		return $ret;
	}
}
?>
