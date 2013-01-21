<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索実行
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Main_Search extends Action
{
	// リクエストパラメータを受け取るため
	var $items = null;
	var $refresh_flag = null;	//再表示時、使用(会員の削除)
	
	// 使用コンポーネントを受け取るため
	var $db = null;
	var $session = null;
	var $usersView = null;
	var $authoritiesView = null;
	
	// 値をセットするため
	var $count = null;
	var $authorities_str = "";
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if($this->refresh_flag != _ON) {
			$auth_id = $this->session->getParameter("_auth_id");
			//if($auth_id >= _AUTH_CHIEF) $chief_flag = _ON;
			if($auth_id == _AUTH_ADMIN) $chief_flag = _ON;
			else $chief_flag = _OFF;

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
			$select_str = "SELECT {users}.*, {authorities}.role_authority_name, {authorities}.user_authority_id, ".
							"{authorities}.system_flag AS  auth_system_flag,".
							"email_users_items_link.content AS email, ".	//email_users_items_link.public_flag AS email_public_flag, ".
							"user_name_users_items_link.content AS user_name, user_name_users_items_link.public_flag AS user_name_public_flag ";
			$from_str = " FROM ({authorities},{users}) ".
						" LEFT JOIN {users_items_link} user_name_users_items_link ON {users}.user_id = user_name_users_items_link.user_id".
						" AND user_name_users_items_link.item_id=".$tags_items['user_name']['item_id'].
						" LEFT JOIN {users_items_link} email_users_items_link ON {users}.user_id = email_users_items_link.user_id ".
						" AND email_users_items_link.item_id=".$tags_items['email']['item_id'];
			$where_str = " WHERE {users}.role_authority_id = {authorities}.role_authority_id ";

			$where_params = array();
			$from_params = array();

			//初期化し設定
			$this->session->removeParameter(array("user", "search"));
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
						$this->usersView->createSearchSqlParameter($from_str, $from_params, $where_str, $where_params, $item, $value, "_".$key);
					}
				} else {
					$this->usersView->createSearchSqlParameter($from_str, $from_params, $where_str, $where_params, $item, $item_value);
				}
			}

			$where_str .= $this->usersView->createSearchWhereString();
			$params = array_merge($from_params, $where_params);

			// レコード数取得
			$count_sql = "SELECT COUNT(*) ";
			$count_sql .= $from_str.$where_str;
			$count_result =& $this->db->execute($count_sql, $params, null, null, false);
			if($count_result === false) {
				$this->db->addError();
				return 'error';
			}
			$this->count = intval($count_result[0][0]);
			////$sql = $select_str.$from_str.$where_str;
			
			//$sql = $select_str.$from_str.$where_str.$order_str;
			//$this->users =& $this->db->execute($sql, $params, intval($this->limit), intval($this->offset));
			//if($this->users === false) {
			//	$this->db->addError();
			//	return 'error';
			//}
			// 検索条件をセッションに保存
			$this->session->setParameter(array("user", "selected_select_str"), $select_str);
			$this->session->setParameter(array("user", "selected_where_str"), $from_str.$where_str);
			$this->session->setParameter(array("user", "selected_params"), $params);
			
			//
			// 表示項目-非表示項目
			//
			$loop_array = array(
				"handle",
				"login_id",
				"user_name",
				"role_authority_name",
				"active_flag",
				"insert_time",
				"last_login_time",
				"manage"
			);
			foreach($loop_array as $value) {
				if($value == "manage") {
					$display_flag = $chief_flag;
				} else if($tags_items[$value]['display_flag'] == _OFF) {
					$display_flag = _OFF;
				} else if($tags_items[$value]['under_public_flag'] >= USER_PUBLIC || $tags_items[$value]['over_public_flag'] >= USER_PUBLIC) {
					$display_flag = _ON;
				} else {
					$display_flag = _OFF;
				}
				// public_flagをセッションに保存
				$this->session->setParameter(array("user", "display_flag", $value), $display_flag);
			}
		} else {
			$params =& $this->session->getParameter(array("user", "selected_params"));
			$count_sql = "SELECT COUNT(*) ";
			$count_sql .= $this->session->getParameter(array("user", "selected_where_str"));
			$count_result =& $this->db->execute($count_sql, $params, null, null, false);
			if($count_result === false) {
				$this->db->addError();
				return 'error';
			}
			$this->count = intval($count_result[0][0]);
		}
		if($this->session->getParameter(array("user", "display_flag", "manage"))) {
			// エクスポートできない権限の一覧を文字列として取得
			$where_params = array("user_authority_id>=".$this->session->getParameter("_user_auth_id") => null);
			$order_params = array("hierarchy" => "DESC");
			$authorities = $this->authoritiesView->getAuthorities($where_params, $order_params);
			if($authorities === false) {
				return 'error';
			}
			foreach($authorities as $authority) {
				if($this->authorities_str != "") $this->authorities_str .= ",";
				$this->authorities_str .= $authority['role_authority_name'];
			}
		}
		return 'success';
	}

	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function _getItemsFetchcallback($result) {
		$ret = array();
		$ret_tags = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
			if(isset($row['tag_name']) && $row['tag_name'] !="") {
				switch ($row['tag_name']) {
					case "active_flag_lang":
						$tag_name = "active_flag";
						break;
					case "timezone_offset_lang":
						$tag_name = "timezone_offset";
						break;
					default :
						$tag_name = $row['tag_name'];
				}
				$ret_tags[$tag_name] = $row;
			}
		}
		return array($ret, $ret_tags);
	}
}
?>