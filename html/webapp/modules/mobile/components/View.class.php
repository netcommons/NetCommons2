<?php
/**
 * メニューテーブル表示用クラス
 *
 * @package     NetCommons.component
 * @author
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Components_View
{
	/**
	 * @var Containerオブジェクトを保持
	 *
	 * @access	private
	 */

	var $_container = null;

	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var ConfigViewオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_configView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Mobile_Components_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_configView =& $this->_container->getComponent("configView");
	}

	/**
	 * 表示形式取得
	 *
	 */
	function getMobileMenuDisplayType( $module_id )
	{
		$conf = $this->_configView->getConfig( $module_id, false );
		if( $conf === false ) {
			return false;
		}
		return( $conf['mobile_menu_type']['conf_value'] );
	}
	/**
	 * ルーム別メニュー設定取得
	 *
	 */
	function getMobileMenuEachRoomMenu( $module_id )
	{
		$conf = $this->_configView->getConfig( $module_id, false );
		if( $conf === false ) {
			return false;
		}
		return( $conf['mobile_menu_each_room']['conf_value'] );
	}

	/**
	 * 表示可能なページデータ取得
	 * とりあえず定義されている全てのページは取り出すが、携帯で表示可能なのかどうかの付加情報を加える
	 * @param int      id(block_id),
	 * @param int      page_id
	 * @param int      parent_id
	 * @param int      room_id
	 * @param int      thread_num
	 * @param function func
	 * @param array    func_param
	 * @return array pages_array
	 * @access	public
	 */
	function &getShowPageById($id=0, $page_id = 0, $root_id = 0, $parent_id = 0, $room_id = 0, $thread_num = null, $func = null, $func_param = null)
	{
		$session =& $this->_container->getComponent("Session");
		$getdata =& $this->_container->getComponent("GetData");
		$_user_id = $session->getParameter("_user_id");
		$_user_auth_id = $session->getParameter("_user_auth_id");
		$blocks =& $getdata->getParameter("blocks");
		$func_param[count($func_param)] = $blocks[$id]['temp_name'];

		$_insert_user_id = $this->_getUserIdByOpenPrivateSpace($_user_id);

		$sql = "SELECT {pages}.* ,{authorities}.user_authority_id AS authority_id,{authorities}.hierarchy, {mobile_menu_detail}.visibility_flag " .
			" FROM {pages} LEFT JOIN {mobile_menu_detail} ON ({pages}.page_id = {mobile_menu_detail}.page_id OR ({pages}.private_flag = "._ON." AND {mobile_menu_detail}.page_id = -1)) AND {mobile_menu_detail}.block_id = ? ";
		$sql .= " LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = ? ".
			" LEFT JOIN {authorities} ON {pages_users_link}.role_authority_id = {authorities}.role_authority_id ".
			" WHERE 1=1 ";
		$pagesView =& $this->_container->getComponent('pagesView');
		$sql .= " AND (({pages}.space_type = "._SPACE_TYPE_GROUP." AND {pages}.private_flag ="._ON." AND {pages}.insert_user_id = ? )".
			" OR ({pages}.space_type ="._SPACE_TYPE_PUBLIC.") ".
			"OR " .
			$pagesView->getGroupWhereStatement() . ')' .
			" AND {pages}.display_flag != ". _PAGES_DISPLAY_FLAG_DISABLED ." ";

		if($thread_num == null) {
			$sql .= " AND ({pages}.thread_num < 2  OR {pages}.parent_id = ". $page_id .  " OR {pages}.parent_id = ". $parent_id . " OR {pages}.room_id = ". $room_id . ") ";
		} else {
			$sql .= " AND ({pages}.thread_num = ".$thread_num." OR {pages}.parent_id = ". $page_id . ") ";
		}

		$sql .= " ORDER BY {pages}.thread_num,{pages}.display_sequence";

		$params = array(
			"block_id" => $id,
			"user_id" => $_user_id,
			"insert_user_id" => $_insert_user_id
		);

		$result = $this->_db->execute($sql,$params,null,null,true,$func,$func_param);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * _open_private_spaceがonならば、他人のプライベートスペースを見ている場合、他人の会員IDを返す（他人のプライベートスペースのリストをメニューに表示するため）
	 * @param  string $current_user_id
	 * @return string $user_id
	 * @access	private
	 */
	function _getUserIdByOpenPrivateSpace($current_user_id) {
		$session =& $this->_container->getComponent("Session");
		$getdata =& $this->_container->getComponent("GetData");
		$pages =& $getdata->getParameter("pages");
		$_open_private_space = $session->getParameter("_open_private_space");

		if($_open_private_space == _OFF ||
			!isset($pages[$session->getParameter("_main_page_id")]) || $pages[$session->getParameter("_main_page_id")]['private_flag'] == _OFF) {
			return $current_user_id;
		}

		if(!isset($pages[$pages[$session->getParameter("_main_page_id")]['room_id']])) {
			$pagesView =& $this->_container->getComponent("pagesView");
			$pages[$pages[$session->getParameter("_main_page_id")]['room_id']] = $pagesView->getPageById($pages[$session->getParameter("_main_page_id")]['room_id']);
		}

		if($pages[$session->getParameter("_main_room_id")]['default_entry_flag'] == _ON) {
			// マイポータル
			return $pages[$pages[$session->getParameter("_main_room_id")]['room_id']]['insert_user_id'];
		} else {
			// マイルーム
			if(($current_user_id == "0" &&
				($_open_private_space == _OPEN_PRIVATE_SPACE_PUBLIC || $_open_private_space == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC)) ||
				($current_user_id != "0" &&
				($_open_private_space == _OPEN_PRIVATE_SPACE_GROUP || $_open_private_space == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP))) {
				return $pages[$pages[$session->getParameter("_main_room_id")]['room_id']]['insert_user_id'];
			}
		}
		return $current_user_id;
	}


	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $menus_obj
	 * @access	private
	 */
	function &fetchcallback($result, $fun_param)
	{
		$main_page_id = $fun_param[0];
		$main_root_id = $fun_param[1];
		$main_parent_id = $fun_param[2];
		$main_room_id = $fun_param[3];
		$main_space_type = $fun_param[4];
		$main_active_node_arr =& $fun_param[5];
		$top_page_arr =& $fun_param[6];
		$edit_flag =& $fun_param[7];
		$mode =& $fun_param[8];
		$display_type =& $fun_param[9];

		if($display_type == MOBILE_MENU_DISPLAY_FLAT ) {
			$flat_flag = true;
		} else {
			$flat_flag = false;
		}
		$container =& DIContainerFactory::getContainer();
		$pagesView =& $container->getComponent("pagesView");
		$session =& $container->getComponent("Session");


		$main_root_id = ($main_root_id == 0) ? $main_page_id : $main_root_id;


		// 携帯用blockを持っているページ配列
		$block_sql = "SELECT {blocks}.page_id, "
							. "COUNT(block_id) as block_ct "
					. "FROM {blocks} "
					. "INNER JOIN {mobile_modules} "
						. "ON {mobile_modules}.module_id = {blocks}.module_id "
					. "INNER JOIN {pages} "
						. "ON {blocks}.page_id = {pages}.page_id "
						. "AND {pages}.space_type IN (?, ?) "
						. "AND {pages}.private_flag = ? "
					. "WHERE {mobile_modules}.display_position = ? "
					. "AND {mobile_modules}.use_flag = ? "
					. "GROUP BY {blocks}.page_id";

		$block_params = array(
			_SPACE_TYPE_PUBLIC,
			_SPACE_TYPE_GROUP,
			_OFF,
			'display_position' => _DISPLAY_POSITION_CENTER,
			'use_flag' => _ON
		);

		$block_result = $this->_db->execute( $block_sql, $block_params, null, null, true, array( $this, '_fetchGetMobileModuleBlockPageList') );


		$ret = array();
		$get_deep_thread_num = 0;

		while ($row = $result->fetchRow()) {
			if (!empty($row['lang_dirname'])
				&& $row['lang_dirname'] != $session->getParameter('_lang')) {
				continue;
			}

			$row['force_off_setting'] = _OFF;
			if( is_null( $row['visibility_flag'] ) ) {
				$row['visibility_flag'] = _ON;
			}
			else {
				if( $row['visibility_flag'] == _OFF ) {
					$row['force_off_setting'] = _ON;
				}
				else {
					$row['force_off_setting'] = _OFF;
				}
				$row['visibility_flag'] = _OFF;
			}
			$row['visibility_auto_off'] = false;


			// このページが携帯表示可能なブロックを持っているか調べる


			// visibility : メニューのアイコン　－かONかの表示にかかわる。
			// disabled : ON/OFFの切り替えが可能かどうかにかかわる。Javascriptですね 自分自身、もしくは配下のページに携帯用ブロックあればenableだがなければdisabledだ
			// hasMobileContent : そのページ自身が携帯用ブロックを持っているかどうかを表す
			// 自らのページ内に携帯用ブロック０で且つ配下のページ群にも携帯用ブロックが０＝visibility=OFF disabled=true hasMobileContent=false
			// 自らのページ内に携帯用ブロック！０で且つ配下のページ群は携帯用ブロックが０＝visibility=-- disabled=false hasMobileContent=true
			// 自らのページ内に携帯用ブロック０で且つ配下のページ群は携帯用ブロックが！０＝visibility=-- disabled=false hasMobileContent=false
			// ただし、かてごり、ルームなどで配下に携帯表示可能ブロックを持つページがいたら、その限りではない
			// かつdisabledに判断結果を

			// ルートのところだけは別扱いにしている
			if( $row['page_id']!=$row['room_id'] || $row['thread_num']!=0  ) {	// 通常ページ
				if( !in_array( $row['page_id'], $block_result ) ) {
					$row['hasMobileContent'] = false;
					if( $row['visibility_flag'] == _ON ) {
						$row['visibility_flag'] = _OFF;
						$row['visibility_auto_off'] = true;
					}
					$row['disabled'] = true;
				}
				else {
					$row['hasMobileContent'] = true;
				}
			}
			else {
				$row['hasMobileContent'] = true;
			}

			// システム管理者、管理者しか触れないので、これ以上の権限チェックは不要
			$row['visible_flag'] = true;
			$row['edit_flag'] = true;

			$ret[$row['thread_num']][$row['parent_id']][$row['display_sequence']] = $row;

			if(($row['private_flag'] == _OFF && ($main_parent_id == $row['page_id'] OR $row['thread_num'] == 0))) {
				$main_active_node_arr[$row['page_id']] = _ON;
			} else if($row['space_type'] == _SPACE_TYPE_PUBLIC && $row['thread_num'] == 0) {
				$main_active_node_arr[$row['page_id']] = _ON;
			} else {
				$main_active_node_arr[$row['page_id']] = _OFF;
			}

			if( $get_deep_thread_num < $row['thread_num'] ) {
				$get_deep_thread_num = $row['thread_num'];
			}

		}

		$sql = "SELECT page_id, "
					. "parent_id, "
					. "thread_num "
				. "FROM {pages} "
				. "WHERE {pages}.space_type IN (?, ?) "
					. "AND {pages}.private_flag = ? "
					. "AND {pages}.thread_num >= ? "
				. "ORDER BY {pages}.thread_num desc, "
							. "{pages}.parent_id desc, "
							. "{pages}.display_sequence DESC";
		$params = array(
			_SPACE_TYPE_PUBLIC,
			_SPACE_TYPE_GROUP,
			_OFF,
			$get_deep_thread_num
		);
		$children = $this->_db->execute( $sql, $params, null, null, true, array( $this, '_fetchCheckMobileBlockPage' ) );
		if( $children === false ) {
			return $children;
		}

		foreach( $children as $page_id=>$child ) {

			// もしもこのページが子供ページ(パブリックスペース、グループスペース、プライベートスペース、その直下より下)で。
			// そのモバイルコンテンツを持っているんだったら、その親のdisabledは解除してやらなくてはなりませんし
			// 強制的なvisibilityOFFは解除してやらねばなりません

			if( $child['thread_num'] > $get_deep_thread_num ) {

				if( in_array( $child['page_id'], $block_result ) || $children[ $child['page_id'] ]['visible_ok'] == true ) {

					$temporaryParentId = $child['parent_id'];
					$child['visible_ok'] = true;

					if( $child['thread_num']-1 > $get_deep_thread_num && isset( $children[ $temporaryParentId ] ) ) {
						$children[ $temporaryParentId ]['visible_ok'] = true;
					}
					else {
						$grand_parent_id = $children[ $child['parent_id'] ]['parent_id'];
						if( isset( $ret[ $child['thread_num'] -1 ][ $grand_parent_id ] ) ) {
							foreach( $ret[ $child['thread_num'] -1 ][ $grand_parent_id ] as $parent ) {
								if( $parent['page_id'] == $child['parent_id'] ) {
									$display_sequence = $parent['display_sequence'];
									if( $ret[ $child['thread_num'] -1 ][ $grand_parent_id ][$display_sequence]['visibility_auto_off'] == true ) {
										$ret[ $child['thread_num'] -1 ][ $grand_parent_id ][$display_sequence]['visibility_flag'] = _ON;
									}
										$ret[ $child['thread_num'] -1 ][ $grand_parent_id ][$display_sequence]['disabled'] = false;
									break;
								}
							}
						}
					}
				}
			}
			else {
				break;
			}
		}
		return $ret;
	}

	//
	// モバイル用隠しメニューのページID配列を得る
	//
	function &getMobileMenuDetailList()
	{
		$sql = "SELECT page_id FROM {mobile_menu_detail}";
		$result = $this->_db->execute( $sql, null, null, null, true, array( $this, '_fetchGetMobileMenuDetailList') );
		return $result;
	}
	function &_fetchGetMobileMenuDetailList( &$recordSet )
	{
		$invisiblePageIds = array();
		while ($page = $recordSet->fetchRow()) {
			$invisiblePageIds[] = $page['page_id'];
		}
		return $invisiblePageIds;
	}

	function &_fetchGetMobileModuleBlockPageList( &$recordSet )
	{
		$hasMobileBlockPageIds[] = array();
		while( $page = $recordSet->fetchRow() ) {
			if( $page['block_ct'] > 0 ) {
				$hasMobileBlockPageIds[] = $page['page_id'];
			}
		}
		return $hasMobileBlockPageIds;
	}
	function &_fetchCheckMobileBlockPage( &$recordSet )
	{
		$hasMobileBlockPages = array();
		while( $page = $recordSet->fetchRow() ) {
			$pageId = $page['page_id'];
			$page['visible_ok'] = _OFF;
			$hasMobileBlockPages[$pageId] = $page;
		}
		return $hasMobileBlockPages;
	}

	/**
	 * mobile_menu_detailリストを取得する
	 *
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @param   array   $func          関数
	 * @param   array   $func_params   Funcパラメータ引数
	 * @return array
	 * @access	public
	 */
	function &getMenuDetail($where_params=null, $order_params=null, $func=null, $func_param=null)
	{
		$db_params = array();
		$sql = $this->_db->getSelectSQL("mobile_menu_detail", $db_params, $where_params, $order_params);
		$result = $this->_db->execute($sql, $db_params, null, null, true, $func, $func_param);
		if ($result === false) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	function hasMobileBlock( $page_id )
	{
		$block_sql =
			"SELECT count( block_id ) as block_ct FROM {blocks} " .
			" INNER JOIN {mobile_modules} ON {mobile_modules}.module_id = {blocks}.module_id " .
			" WHERE {blocks}.page_id = ? ".
			" AND {mobile_modules}.display_position = " . _DISPLAY_POSITION_CENTER;

			$block_params = array( "page_id"=>$page_id );
			$block_result = $this->_db->execute($block_sql,$block_params,null,null,true);
			if( $block_result === false ) {
				return false;
			}
			else {
				return( $block_result[0]['block_ct'] );
			}

	}
}
?>
