<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索実行(会員絞込み)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Search extends Action
{
	// リクエストパラメータを受け取るため
	var $items = null;
	var $room_current_id = null;	//ルーム管理-会員絞込み
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $usersView = null;
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$this->room_current_id = ($this->room_current_id == null) ? 0 : intval($this->room_current_id);
		$userAuthorityId = $this->session->getParameter('_user_auth_id');
		$where_params = array(
							"user_authority_id" => $userAuthorityId
						);
		$result =& $this->usersView->getItems($where_params, null, null, null, array($this->usersView, '_fetchSearchResultItem'));
		if($result === false) return 'error';
		list($items, $tags_items) = $result;

		//
		// SQL文作成処理
		//
		//$select_str =& $this->session->getParameter(array("room", $this->room_current_id,"selected_select_str"));
    	//$from_str =& $this->session->getParameter(array("room", $this->room_current_id,"selected_from_str"));
	    //$where_str =& $this->session->getParameter(array("room", $this->room_current_id,"selected_where_str"));
    	//$params =& $this->session->getParameter(array("room", $this->room_current_id,"selected_params"));
    	
    	//初期化し設定
    	$add_from_str = "";
    	$add_where_str = "";
    	$where_params = array();
    	$from_params = array();

		$this->session->removeParameter(array("room", "search", 0));
		foreach($this->items as $item_id => $item_value) {
			if (!isset($items[$item_id])) {
				continue;
			}
			$item = $items[$item_id];
			if ($item['display_flag'] == _OFF) {
				return 'error';
			}

			if(is_array($item_value)) {
				foreach($item_value as $key => $value) {
					$this->usersView->createSearchSqlParameter($add_from_str, $from_params, $add_where_str, $where_params, $item, $value, "_".$key);
				}
			} else {
				$this->usersView->createSearchSqlParameter($add_from_str, $from_params, $add_where_str, $where_params, $item, $item_value);
			}
		}

		$add_where_str .= $this->usersView->createSearchWhereString();

		$this->session->setParameter(array("room", $this->room_current_id,"selected_add_from_str"), $add_from_str);
		$this->session->setParameter(array("room", $this->room_current_id,"selected_add_where_str"), $add_where_str);
		$this->session->setParameter(array("room", $this->room_current_id,"selected_from_params"), $from_params);
		$this->session->setParameter(array("room", $this->room_current_id,"selected_where_params"), $where_params);
		
		return 'success';
	}
}
?>