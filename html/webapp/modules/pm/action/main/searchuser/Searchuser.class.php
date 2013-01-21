<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Pm_Action_Main_Searchuser extends Action
{
	// リクエストパラメータを受け取るため
	var $handle = null;
	var $now_page = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $usersView = null;
	var $db = null;
	var $pmView = null;
	
	// 値をセットするため
	var $users = null;
	
	//ページ
    var $pager = array();
	
    /**
     * メール宛先検索アクション
     *
     * @access  public
     */
    function execute()
    {
    	$select_str = "SELECT {users}.user_id, {users}.handle AS name, {authorities}.role_authority_name,".
						"{authorities}.user_authority_id,{authorities}.system_flag AS auth_system_flag,".
						"avatar.public_flag, avatar.content AS icon_path,avatar_authorities.under_public_flag,".
						"avatar_authorities.self_public_flag,avatar_authorities.over_public_flag";
		list($from_str,$where_str) = $this->pmView->getAuthoritySQL();

		$from_str .= " INNER JOIN {items} avatar_items ON avatar_items.type='file'".
					" INNER JOIN {items_authorities_link} avatar_authorities".
						" ON avatar_items.item_id = avatar_authorities.item_id".
						" AND avatar_authorities.user_authority_id = ".$this->session->getParameter('_user_auth_id').
					" LEFT JOIN {users_items_link} avatar ON avatar.user_id = {users}.user_id AND avatar.item_id = avatar_items.item_id";

		$items_parameter = array(
			"handle" => $this->handle
		);
		$where_params = array(
			"tag_name" => "handle"
		);
		$item_handle = $this->usersView->getItems($where_params);
		if ($item_handle === false) {
			return 'error';
		}
		$items = array(
			"handle" => $item_handle[0]
		);

		$where_params = array();
		$from_params = array();

		//初期化し設定
		foreach($items_parameter as $item_name => $item_value) {
			if (!isset($items[$item_name])) {
				continue;
			}
			$item = $items[$item_name];
			if ($item['display_flag'] == _OFF) {
				return 'error';
			}

			if(is_array($item_value)) {
				foreach($item_value as $key => $value) {
					$this->usersView->createSearchSqlParameter($from_str, $from_params, $where_str, $where_params, $item, $value, "_".$key);
				}
			} else {
				$this->usersView->createSearchSqlParameter($from_str, $from_params, $where_str, $where_params, $item, $item_value);
			}
		}

		$params = array_merge($from_params, $where_params);
		// レコード数取得
		$count_sql = "SELECT COUNT(*) ";
		$count_sql .= $from_str.$where_str;
		$count_result =& $this->db->execute($count_sql, $params, null, null, false);
		if($count_result === false) {
			$this->db->addError();
			return 'error';
		}
		$count = intval($count_result[0][0]);
		
		$order_params = array(
			"{users}.system_flag" => "DESC",
			"{users}.handle" => "ASC"
		);
		$order_str = " ".$this->db->getOrderSQL($order_params);

		$sql = $select_str.$from_str.$where_str.$order_str;
		$this->pmView->setPageInfo($this->pager, $count, PM_SEARCH_VISIBLE_ITEM_CNT, $this->now_page);
		$this->users =& $this->db->execute($sql, $params, PM_SEARCH_VISIBLE_ITEM_CNT, $this->pager['disp_begin'], true, array($this, "_SearchFetchcallback"));
		if($this->users === false) {
			$this->db->addError();
			return 'error';
		}

		return "success";
    }

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array users
	 * @access	private
	 */
	function &_SearchFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(isset($row['auth_system_flag']) && $row['auth_system_flag'] == _ON &&
				defined($row['role_authority_name'])) {
					$row['role_authority_name'] = constant($row['role_authority_name']);
			}
			$ret[] = $row;
		}
		return $ret;
	}
}
?>
