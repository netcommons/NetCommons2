<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員参加ルームＣＳＶ管理コンポーネント
 *
 * @package  NetCommons
 * @author    Toshihide Hashimoto, Rika Fujiwara
 * @copyright   2012 AllCreator Co., Ltd.
 * @project  NC Support Project, provided by AllCreator Co., Ltd.
 * @license  http://www.netcommons.org/license.txt  NetCommons License
 * @access    public
 */
class Room_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access  private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access  private
	 */
	var $_container = null;

	var $_session = null;
	var $_pagesView = null;

	/**
	 * コンストラクター
	 *
	 * @access  public
	 */
	function Room_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_authoritiesView =& $this->_container->getComponent("authoritiesView");
		$this->_configView =& $this->_container->getComponent("configView");
	}
	/**
	 * ルーム内参加可能者一覧、およびそれらの人物へ与えているロール権限、そして権限設定可能上限
	 *
	 * @access public
	 */
	function &getRoomUsersList($page, $parent_page, $authorities)
	{
		$subroom_flag = false;
		$edit_current_page_id = $page['page_id'];
		$parent_page_id = $parent_page['page_id'];
		if($edit_current_page_id != _SELF_TOPPUBLIC_ID && $parent_page['thread_num']==1) {
			$subroom_flag = true;
		}
		$space_type = $page['space_type'];
		// 親ページがない＝パブリックスペースTOP
		// このときはcreateroom_flag設定による権限以上の権限取得の抜け駆け禁止
		if($space_type == _SPACE_TYPE_PUBLIC && $page['thread_num'] == 0) {
			$public_top_page_flag = _ON;
		}
		else {
			$public_top_page_flag = _OFF;
		}
		
		$default_entry_flag = $page['default_entry_flag'];

		$config = $this->_configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
		if($config == false) {
			return false;
		}
		$default_entry_role_auth_public = $config['default_entry_role_auth_public']['conf_value'];
		$default_entry_role_auth_group = $config['default_entry_role_auth_group']['conf_value'];


		$params = array();
		$select_str = 'SELECT {users}.user_id, {users}.handle, '.
							'{authorities}.user_authority_id,'.
							'{authorities}.role_authority_id,'.
							'{authorities}.public_createroom_flag,'.
							'{authorities}.group_createroom_flag,'.
							'{authorities}.hierarchy,'.
							'{pages_users_link}.role_authority_id AS authority_id,'.
							'{pages_users_link}.createroom_flag';

		$from_str = ' FROM {authorities}, {users}'.
					' LEFT JOIN {pages_users_link} ON {pages_users_link}.room_id='.$edit_current_page_id.
					' AND {users}.user_id={pages_users_link}.user_id ';

		$where_str = ' WHERE {users}.role_authority_id={authorities}.role_authority_id';

		if($parent_page['thread_num'] >= 1) {
			//親ルームに参加している会員すべて（サブグループ作成）
			if($parent_page['default_entry_flag']) {
				$where_str .= ' AND {users}.user_id NOT IN (SELECT user_id FROM {pages_users_link} WHERE room_id =' . $parent_page_id . ' AND role_authority_id = ' . _ROLE_AUTH_OTHER . ')';
			} 
			else {
				$where_str .= ' AND {users}.user_id IN (SELECT user_id FROM {pages_users_link} WHERE room_id =' . $parent_page_id . ')';
			}
		}
		$sql = $select_str . $from_str . $where_str;
		$users =& $this->_db->execute($sql, $params, null, null, true, 
					array($this, '_usersFetchCallback'), 
					array($subroom_flag, $space_type, $default_entry_flag, $default_entry_role_auth_public, $default_entry_role_auth_group, $authorities, $public_top_page_flag));
		return $users;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access  private
	 */
	function &_roomusersFetchcallback($result) 
	{
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['user_id']] = $row;
		}
		return $ret;
	}
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access  private
	 */
	function &_usersFetchcallback($result, $params) 
	{
		$subroom_flag = $params[0];
		$space_type = $params[1];
		$default_entry_flag = $params[2];
		$default_entry_role_auth_public = $params[3];
		$default_entry_role_auth_group = $params[4];
		$authorities = $params[5];
		$public_top_page_flag = $params[6];

		$auth = array();
		foreach($authorities as $a) {
			$auth[$a['role_authority_id']] = $a;
		}

		$ret = array();

		while ($row = $result->fetchRow()) {
			// createroom_flag がONでも、該当のページがパブリックスペースのTOPのときは有効にならない！
			if(($public_top_page_flag==_OFF && $row['createroom_flag']==_ON) || ($public_top_page_flag==_OFF && $space_type==_SPACE_TYPE_PUBLIC && $row['public_createroom_flag']==_ON) || ($space_type==_SPACE_TYPE_GROUP && $row['group_createroom_flag']==_ON)) {
				$createroom_flag =_ON;
			}
			else {
				$createroom_flag =_OFF;
			}
			// authがNULLのとき デフォルト権限を意味する
			if(is_null($row['authority_id'])) {
				if($default_entry_flag==_OFF) {
					$row['authority_id'] = _ROLE_AUTH_OTHER;
				}
				else {
					if($space_type==_SPACE_TYPE_PUBLIC) {
						$row['authority_id'] = $default_entry_role_auth_public;
					}
					else {
						$row['authority_id'] = $default_entry_role_auth_group;
					}
				}
			}

			$row_auth = array();
			foreach($auth as $auth_id=>$a) {

				$row_auth[$auth_id] = false;

				if($subroom_flag == true) {		// サブルームであれば誰でも何にでもなれる
					$row_auth[$auth_id] = true;
				}
				else {
					if($auth_id==_ROLE_AUTH_CHIEF) {
						if($row['hierarchy'] >=_HIERARCHY_CHIEF || ($row['authority_id'] == _ROLE_AUTH_ADMIN || $row['authority_id'] == _ROLE_AUTH_CHIEF) || $createroom_flag==_ON) {
							$row_auth[$auth_id] = true;
						}
					}
					else if($auth_id==_ROLE_AUTH_GENERAL) {
						if($row['hierarchy'] >=_HIERARCHY_GENERAL || ($row['authority_id'] == _ROLE_AUTH_GENERAL) || $createroom_flag==_ON) {
							$row_auth[$auth_id] = true;
						}
					}
					else if($auth_id==_ROLE_AUTH_GUEST) {	// ゲストは誰でもなれる
						$row_auth[$auth_id] = true;
					}
					else {
						if($row['hierarchy']>=$a['hierarchy'] || $createroom_flag == _ON || $row['authority_id'] == $a['role_authority_id']) {
							$row_auth[$auth_id] = true;
						}
					}
				}
			}
			$row['permitted_auth'] = $row_auth;
			$ret[$row['handle']] = $row;
		}
		return $ret;
	}
}
