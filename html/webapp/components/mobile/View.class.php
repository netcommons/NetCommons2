<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯取得コンポーネント
 *
 * @package	 NetCommons Components
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Mobile_View
{
	/**
	 * @var DIコンテナを保持
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
	 * @var Sessionを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var モジュール管理を保持
	 *
	 * @access	private
	 */
	var $_modulesView = null;

	/**
	 * @var モジュールを保持
	 *
	 * @access	private
	 */
	var $_modules = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Mobile_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_modulesView =& $this->_container->getComponent("modulesView");
		$this->_session =& $this->_container->getComponent("Session");
	}

	/**
	 * モジュールの取得
	 *
	 * @access	private
	 */
	function _callbackModules(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$pathList = explode("_", $row["mobile_action_name"]);
			$row["dir_name"] = $pathList[0];
			$row["module_name"] = $this->_modulesView->loadModuleName($row["dir_name"]);
			$result[$row["display_position"]][$row["module_id"]] = $row;
		}
		return $result;
	}

	/**
	 * ブロックタイトルの取得 add by AllCreator 2009.02.07
	 * 携帯の第２メニュー部分、ブロック単位にする
	 *
	 * @access  private
	 */
	function _callbackBlockByPage(&$recordSet)
	{
		$result = array();
		$i = 0;
		$this->_session->setParameter('mobileDisplayTextHtml', _OFF);
		$isSmartphone = $this->_session->getParameter('_smartphone_flag');
		while ($row = $recordSet->fetchRow()) {
			if( !is_null( $row['display_position'] ) && $row['display_position'] != _DISPLAY_POSITION_CENTER ) {
				continue;
			}

			if( !is_null( $row['display_position'] ) ) {
				$pathList = explode("_", $row["mobile_action_name"]);
				$row["dir_name"] = $pathList[0];
				$row["module_name"] = $this->_modulesView->loadModuleName($row["dir_name"]);
			}

			if (!$isSmartphone
					&& !empty($row['module_id'])
					&& empty($row['block_name'])
					&& !empty($row['content'])) {
				$this->_session->setParameter('mobileDisplayTextHtml', _ON);
			}

			$result[ $row['parent_id'] ][ $row['block_id'] ] = $row;
		}
		return $result;
	}
	/**
	 * モジュールの取得
	 *
	 * @access	public
	 */
	function getModules($module_id=null, $func=null)
	{
		$actionChain =& $this->_container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		if ($this->_modules) {
			if (isset($module_id)) {
				if (isset($this->_modules[_DISPLAY_POSITION_CENTER][$module_id])) return $this->_modules[_DISPLAY_POSITION_CENTER][$module_id];
				if (isset($this->_modules[_DISPLAY_POSITION_HEADER][$module_id])) return $this->_modules[_DISPLAY_POSITION_HEADER][$module_id];
			} else {
				return $this->_modules;
			}
		}

		$sql = "SELECT {mobile_modules}.*" .
				" FROM {modules}" .
				" INNER JOIN {mobile_modules} ON ({modules}.module_id = {mobile_modules}.module_id)" .
				(preg_match("/^mobile_/", $actionName) ? "" : " WHERE {mobile_modules}.use_flag=". _ON) .
				" ORDER BY {mobile_modules}.display_position DESC, {mobile_modules}.display_sequence, {modules}.display_sequence";

		if (!isset($func)) {
			$func = array($this,"_callbackModules");
		}
		$this->_modules = $this->_db->execute($sql, null, null, null, true, $func);
		if ($this->_modules === false) {
			$this->_db->addError();
			return $this->_modules;
		}

		if (isset($module_id)) {
			if (isset($this->_modules[_DISPLAY_POSITION_CENTER][$module_id])) return $this->_modules[_DISPLAY_POSITION_CENTER][$module_id];
			if (isset($this->_modules[_DISPLAY_POSITION_HEADER][$module_id])) return $this->_modules[_DISPLAY_POSITION_HEADER][$module_id];
		} else {
			return $this->_modules;
		}
	}

	/**
	 * ブロック項目の取得 add by AllCreator 2009.02.07
	 * mod by AllCreator 2009.05.06 cut [WHERE 1=1]
	 * mod by AllCreator 2009.05.06 cut (preg_match("/^mobile_/", $actionName)
	 * mod by AllCreator 2010.01.30 add more_title お知らせに「もっと読む」があったら
	 * 携帯のメニュー画面用
	 * 指定されたページの中に存在するブロック一覧を取得する。
	 * タイトルのないブロックでかつ「おしらせ」モジュールの場合はそのまま中身を表示
	 * また、ここでブロックが１つしかないときは、すぐにそのモジュールのdetail
	 * 表示処理ページに行くのでこの関数は呼ばれない
	 *
	 * @access  public
	 */
	function getBlocksByPage($page_id=null, $func=null)
	{
		$sql = "SELECT {mobile_modules}.*, {blocks}.*, {announcement}.content, {announcement}.more_title " .
				" FROM {blocks}" .
				" LEFT JOIN {modules} ON ({blocks}.module_id = {modules}.module_id)" .
				" LEFT JOIN {mobile_modules} ON ({modules}.module_id = {mobile_modules}.module_id)" .
				" LEFT JOIN {announcement} ON {blocks}.block_id = {announcement}.block_id" .
				" WHERE {blocks}.page_id = " . intval($page_id) .
				" AND ( {mobile_modules}.use_flag=". _ON .
				" OR {blocks}.action_name = 'pages_view_grouping' ) " .
				" ORDER BY {blocks}.thread_num, {blocks}.row_num, {blocks}.col_num";
		if (!isset($func)) {
			$func = array($this,"_callbackBlockByPage");
		}
		$modules = $this->_db->execute($sql, null, null, null, true, $func);
		if ($modules === false) {
			$this->_db->addError();
			return $modules;
		}
		return $modules;
	}

	/**
	 * モジュールの取得
	 *
	 * @access	public
	 */
	function getCount($module_id)
	{
		$count = $this->_db->countExecute("mobile_modules", array("module_id"=>$module_id));
		if ($count === false) {
			return false;
		}
		return $count;
	}
	/**
	 * 指定ブロックに該当するページの中にあるモバイルで表示できるブロック数の取得
	 * 2009.02.25 add by AllCreator
	 *
	 * default return : 1;
	 *
	 * @access	public
	 */
	function getCountForBlockInPageWithBlock($block_id)
	{
		$result = $this->_db->selectExecute( "blocks", array( "block_id"=>$block_id) );
		if ($result == false ) {
			return 1;
		}
		$page_id = $result[0]['page_id'];
		$ret = $this->getCountForBlockInPage($page_id);

		return $ret;
	}
	/**
	 * 指定ページの中にあるモバイルで表示できるブロック数の取得
	 * 2009.02.13 add by AllCreator
	 * 2009.05.05 mod by AllCreator
	 * 2009.05.06 mod by AllCreator cut [WHERE 1=1]
	 * 2009.05.06 mod by AllCreator cut (preg_match("/^mobile_/", $actionName)
	 *
	 * @access	public
	 */
	function getCountForBlockInPage($page_id)
	{
//		$actionChain =& $this->_container->getComponent("ActionChain");
//		$actionName = $actionChain->getCurActionName();
		$sql = " SELECT module_id from {mobile_modules} where use_flag = " . _ON .
				" AND display_position = " . _DISPLAY_POSITION_CENTER;
		$result = $this->_db->execute( $sql, null, null, null, true );
		if ($result === false) {
			$this->addError();
			return false;
		}
		$where_in_words = "";
		foreach( $result as $r ) {
			$where_in_words .= $r['module_id'] . ",";
		}
		$where_in_words = trim( $where_in_words, "," );

		$sql = " SELECT count(*) " .
				" FROM {blocks}" .
				" WHERE {blocks}.page_id = " . intval($page_id) .
				" AND {blocks}.module_id in ( $where_in_words )"  ;
		$result = $this->_db->execute($sql, null, null,null,false);
		if ($result === false) {
			$this->addError();
			return false;
		}
		return $result[0][0];
	}

	/**
	 * 端末IDの取得
	 *
	 * @access	public
	 */
	function getAutoLogin()
	{
		$mobile_info = $this->_session->getParameter("_mobile_info");
		if ($mobile_info["autologin"] != _AUTOLOGIN_OK) { return false; }

		$result = $this->_db->selectExecute("mobile_users", array("tel_id"=>$mobile_info["tel_id"]));
		if (empty($result)) { return false; }

		return $result[0];
	}
	/**
	 * カレントページデータの取得
	 *
	 * @param string $pageId カレントのページID
	 * @return array 親カレントページデータ配列
	 */
	function getCurrentPage($pageId)
	{
		$currentPage = array();

		$params = array(
			$pageId
		);
		$sql = "SELECT room_id, "
					 . "page_name, "
					 . "space_type "
				. "FROM {pages} "
				. "WHERE page_id = ?";
		$pages = $this->_db->execute($sql, $params);
		if ($pages === false) {
			$this->_db->addError();
			return $pages;
		}

		if (count($pages) > 0) {
			$currentPage = $pages[0];
		}

		return $currentPage;
	}

	/**
	 * 指定ルームのページデータを親ページ毎に取得する
	 * ルームフラット表示が選ばれている場合はルームを限定しない
	 *
	 * @return array 親ページ毎のページデータ配列
	 * @access public
	 */
	function getPageTree($display_type, $each_room, $roomIds)
	{
		$request =& $this->_container->getComponent('Request');

		$invisiblePageIds = $this->getInvisiblePageIds($display_type, $each_room, $roomIds);
		if ($invisiblePageIds === false) {
			return false;
		}

		// 携帯対応モジュールID配列を取得
		$params = array(
			_DISPLAY_POSITION_HEADER,
			_ON
		);
		$sql = "SELECT module_id "
				. "FROM {mobile_modules} "
				. "WHERE display_position != ? "
					. "AND use_flag = ?";
		$mobileModuleIds = $this->_db->execute($sql,
												$params,
												null,
												null,
												true,
												array($this,
														'_fetchMobileModuleId'));
		if ($mobileModuleIds === false) {
			$this->_db->addError();
			return $mobileModuleIds;
		}

		// 親ページ毎のページデータ配列を取得
		$pageTree = array();
		if (empty($mobileModuleIds)) {
			return $pageTree;
		}

		$params = array(
			_DISPLAY_POSITION_CENTER
		);
		if( $each_room == _ON ) {
			$roomIds = array( $request->getParameter('room_id') );
		}

		$sql = "SELECT P.page_id, "
					. "P.root_id, "
					. "P.parent_id, "
					. "P.thread_num, "
					. "P.display_sequence, "
					. "P.lang_dirname, "
					. "P.page_name, "
					. "P.private_flag, "
					. "P.space_type, "
					. "MIN(B.block_id) AS block_id, "
					. "COUNT(B.block_id) AS blockCount "
				. "FROM {pages} P "
					. "LEFT JOIN {blocks} B "
					. "ON P.page_id = B.page_id "
					. "AND B.module_id IN (" . implode(',', $mobileModuleIds) . ") "
				. "WHERE P.room_id IN ( " . implode(',', $roomIds ) . ") "
					. "AND P.space_type IN ("._SPACE_TYPE_PUBLIC.","._SPACE_TYPE_GROUP.") "
        			. "AND P.private_flag IN ("._ON.","._OFF.") "
					. "AND P.display_position = ? "
					. $this->_getPageWhereSQL($invisiblePageIds)
				. "GROUP BY P.page_id, "
						. "P.root_id, "
						. "P.parent_id, "
						. "P.thread_num, "
						. "P.display_sequence, "
						. "P.page_name, "
						. "P.private_flag, "
						. "P.space_type "
				. "ORDER BY P.thread_num, P.parent_id, "
						. "P.display_sequence";
		$parentID = null;
		$branches = array();
		$pageTree = $this->_db->execute($sql,
										$params,
										null,
										null,
										true,
										array($this,
											'_fetchPageTree'),
										array($parentID,
											$branches,
											$invisiblePageIds)
										);
		if ($pageTree === false) {
			$this->_db->addError();
		}

		return $pageTree;
	}

	/**
	 * 親ページ毎のページデータ配列を生成する
	 *
	 * @param object $recordSet ページデータADORecordSet
	 * @param array $invisiblePageIds 非表示ページID配列
	 * @return array 親ページ毎のページデータ配列
	 * @access private
	 */
	function &_fetchPageTree(&$recordSet, &$params)
	{
		// 初期処理
		$parentID = $params[0];
		$branches = $params[1];
		$invisiblePageIds = $params[2];
		$pattern = array('/Y/', '/L/');
		$replacement = array('I', 'B');

		$pageTree = array();
		$pageID = '';

		$_lang = $this->_session->getParameter('_lang');
		while ($page = $recordSet->fields) {
			if ($page['parent_id'] == '0' 
				||(!empty($page['lang_dirname'])
					&& $page['lang_dirname'] != $_lang)) {
				$recordSet->MoveNext();
				continue;
			}

			if (!isset($parentID)) {
				$parentID = $page['parent_id'];
			}

			if ($page['parent_id'] == $parentID) {
				$pageID = $page['page_id'];

				$page['disabled'] = true;		// disabled:ページへのリンクの有無
				$page['mobileModule'] = false;	// mobileModule:携帯対応モジュールの有無
				$page['visible'] = true;		// visible:モバイル管理での表示/非表示設定

				if (in_array($pageID, $invisiblePageIds)
						|| $page['private_flag'] == _ON
							&& $page['root_id'] == 0
							&& in_array(-1, $invisiblePageIds)) {
					$page['visible'] = false;
				}

				if ($page['blockCount'] > 0) {
					$page['disabled'] = false;
					$page['mobileModule'] = true;
				}

				// ページツリー配列を生成
				$pageTree[$parentID][$pageID] = $page;

				// 枝配列を生成
				// 次ページを取得
				$recordSet->MoveNext();
				if ($page['thread_num'] == 1) {
					// 枝配列無し
					$branches[$pageID] = array();
					$pageTree[$parentID][$pageID]['branches'] = array();
					continue;
				} else {
					// 根記事までの枝配列（先祖）に対してY字型をI字型、L字型をB字型(Blank)に変換
					$tempBranches = preg_replace($pattern, $replacement, $branches[$parentID]);
				}

				$nextPost = $recordSet->fields;
				if ($nextPost && $nextPost['parent_id'] == $parentID) {
					// 弟ページがある場合Y字型を付加
					$branches[$pageID] = array_merge($tempBranches, array('Y'));
				} else {
					// 弟ページがない場合L字型を付加
					$branches[$pageID] = array_merge($tempBranches, array('L'));
				}
				$pageTree[$parentID][$pageID]['branches'] = $branches[$pageID];

			} else {
				// 親記事IDが変わった場合は、変わった親記事IDを元に再帰処理
				$params = array(
					$page['parent_id'],
					$branches,
					$invisiblePageIds
				);
				$tempPageTree =& call_user_func(array($this, '_fetchPageTree'), $recordSet, $params);
				if (!empty($tempPageTree)) {
					$childPages = reset($pageTree);
					$childPages = array_reverse($childPages, true);
					$lastBranchChange = false;
					foreach (array_keys($childPages) as $mobileModuleCheckParentId) {
						$mobileModuleChecks = array();
						if (isset($tempPageTree[$mobileModuleCheckParentId])) {
							$mobileModuleChecks = $tempPageTree[$mobileModuleCheckParentId];
						}
						foreach ($mobileModuleChecks as $childPage) {
							if ($childPage['mobileModule']
									&& $childPage['visible']) {
								$pageTree[$parentID][$mobileModuleCheckParentId]['mobileModule'] = true;
								break;
							}
						}

						if (!($pageTree[$parentID][$mobileModuleCheckParentId]['mobileModule'] == true
								&& $pageTree[$parentID][$mobileModuleCheckParentId]['visible'] == true)
							&& $mobileModuleCheckParentId == $pageID) {
							$lastBranchChange = true;
						}

						if ($lastBranchChange
								&& $pageTree[$parentID][$mobileModuleCheckParentId]['mobileModule']) {
							$pageTree[$parentID][$mobileModuleCheckParentId]['branches'] = $branches[$pageID];
							$lastBranchChange = false;
						}
					}
					$pageTree += $tempPageTree;
				}
				return $pageTree;
			}
		}
		return $pageTree;
	}

	/**
	 * 非表示ページID配列を生成する
	 *
	 * @param object $recordSet 非表示ページページADORecordSet
	 * @param string $menuCount 配置されているメニューモジュール数
	 * @return array 非表示ページID配列
	 * @access private
	 */
	function &_fetchInvisiblePage(&$recordSet, $menuCount = NULL)
	{
		$invisiblePageIds = array();
		while ($page = $recordSet->fetchRow()) {
			if( is_null( $menuCount ) ) {
				$invisiblePageIds[] = $page['page_id'];
			}
			else {
				if ($page['pageCount'] == $menuCount) {
					$invisiblePageIds[] = $page['page_id'];
				}
			}
		}

		return $invisiblePageIds;
	}

	/**
	 * 携帯対応モジュールID配列を生成する
	 *
	 * @param object $recordSet ページIDADORecordSet
	 * @return array 携帯対応モジュールID配列
	 * @access private
	 */
	function &_fetchMobileModuleId(&$recordSet)
	{
		$mobileModuleIds = array();
		while ($mobileModule = $recordSet->fetchRow()) {
			$mobileModuleIds[] = $mobileModule['module_id'];
		}

		return $mobileModuleIds;
	}

	/**
	 * 表示できないページID配列を取得する
	 *
	 * @return array 表示されないページのIDの配列
	 * @access private
	 */
	function getInvisiblePageIds( $display_type, $each_room, $roomIds )
	{
		$request =& $this->_container->getComponent('Request');

		// もしもmobile_menu_detailにて非表示メニューが選ばれているなら、そちらを優先
		if( $this->_db->countExecute("mobile_menu_detail") > 0 ) {
			$sql = "SELECT page_id FROM {mobile_menu_detail} ";
			$invisiblePageIds = $this->_db->execute($sql,
												null,
												null,
												null,
												true,
												array($this, '_fetchInvisiblePage') );
		}
		// そうでない場合は
		// 全てのメニューで非表示のページを取得
		else {
			$params = array(
				'module_id' => $request->getParameter('module_id')
			);
			$menuCount = $this->_db->countExecute('blocks', $params);

			if( $each_room == _ON ) {
				$roomIds = array( $request->getParameter('room_id') );
			}
			$params = array(
				_OFF
			);
			$sql = "SELECT page_id, COUNT(block_id) AS pageCount "
						. "FROM {menu_detail} "
						. "WHERE room_id in (" . implode(",",$roomIds) . " )"
						. "AND visibility_flag = ? "
						. "GROUP BY page_id";
			$invisiblePageIds = $this->_db->execute($sql,
												$params,
												null,
												null,
												true,
												array($this,
												'_fetchInvisiblePage'),
												$menuCount);
		}
		if ($invisiblePageIds === false) {
			$this->_db->addError();
		}
		return $invisiblePageIds;
	}

	/**
	 * 携帯対応モジュールID配列を取得する
	 *
	 * @return array 携帯対応モジュールIDの配列
	 * @access private
	 */
	function getMobileModuleIds( $display_type, $each_room, $roomIds )
	{
		// 携帯対応モジュールID配列を取得
		$params = array(
			_DISPLAY_POSITION_HEADER,
			_ON
		);
		$sql = "SELECT module_id "
				. "FROM {mobile_modules} "
				. "WHERE display_position != ? "
					. "AND use_flag = ?";
		$mobileModuleIds = $this->_db->execute($sql,
												$params,
												null,
												null,
												true,
												array($this,
														'_fetchMobileModuleId'));
		if ($mobileModuleIds === false) {
			$this->_db->addError();
		}
		return $mobileModuleIds;
	}

	/**
	 * ページデータ取得用SQLのWHERE句文字列を取得
	 *
	 * @param array $invisiblePageIds 非表示用ページID配列
	 * @return ページデータ取得用SQLのWHERE句文字列
	 * @access public
	 */
	function _getPageWhereSQL($invisiblePageIds)
	{
		$sql = "SELECT page_id, space_type "
				. "FROM {pages} "
				. "WHERE (thread_num = ? "
						. "AND private_flag IN ("._ON.","._OFF.") "
						. "AND space_type = ?) "
					. "OR (thread_num = ? "
						. "AND private_flag = ? "
						. "AND space_type = ?)";
		$params = array(0,
						_SPACE_TYPE_PUBLIC,
						0,
						_OFF,
						_SPACE_TYPE_GROUP);
		$topSpacePageIds = $this->_db->execute($sql,
												$params,
												2,
												null,
												true,
												array($this,
												'_fetchTopSpacePageId'));

		$whereSQL = "";
		if (in_array(-1, $invisiblePageIds )) {
			$whereSQL .= "AND P.private_flag = " . _OFF . " ";
		}
		if (in_array($topSpacePageIds['publicSpaceTopId'], $invisiblePageIds)) {
			$whereSQL .= "AND P.root_id != " . $topSpacePageIds['publicSpaceTopId'] . " ";
		}
		if (in_array($topSpacePageIds['groupSpaceTopId'], $invisiblePageIds)) {
			$whereSQL .= "AND P.root_id != " . $topSpacePageIds['groupSpaceTopId'] . " ";
		}

		return $whereSQL;
	}

	/**
	 * パブリックスペース、グループスペースの最上位にあるページID配列を生成する
	 *
	 * @param object $recordSet パブリックスペース、グループスペースの最上位にあるページデータADORecordSet
	 * @return array パブリックスペース、グループスペースの最上位にあるページID配列
	 * @access private
	 */
	function &_fetchTopSpacePageId(&$recordSet)
	{
		$topSpacePageIds = array();
		while ($page = $recordSet->fetchRow()) {
			if ($page['space_type'] == _SPACE_TYPE_PUBLIC) {
				$topSpacePageIds['publicSpaceTopId'] = $page['page_id'];
			}
			if ($page['space_type'] == _SPACE_TYPE_GROUP) {
				$topSpacePageIds['groupSpaceTopId'] = $page['page_id'];
			}
		}
		return $topSpacePageIds;
	}

	/**
	 * 携帯表示に対応できるルームのツリーを作成する
	 * ルームツリー自体はFilter_AllowRoom...にて作成されている
	 * 作成済みルームツリーに対して、携帯メニューに表示可能なルームを判定する
	 * 2010.02.04 add by AllCreator
	 *
	 * @return true false
	 * @access public
	 */
	function getRoomTree( &$roomArr, $display_type, $each_room, $roomIds )
	{
		$roomTree = array();

		$request =& $this->_container->getComponent('Request');

		$invisiblePageIds = $this->getInvisiblePageIds( $display_type, $each_room, $roomIds );
		// エラー
		if ($invisiblePageIds === false) {
			return false;
		}

		$mobileModuleIds = $this->getMobileModuleIds( $display_type, $each_room, $roomIds );
		if ($mobileModuleIds === false) {
			return false;
		}

		// 表示可能なモジュールが何もない
		if (empty($mobileModuleIds)) {
			// Roomデータ全てにvisible＝falseを設定して戻る
			$temporaryRoomArr = null;
			$this->setVisibleFlagToRoomRecursive( $roomArr, $roomArr[0][0], array(), $temporaryRoomArr);
			return true;
		}

		$params = array(
				_DISPLAY_POSITION_CENTER
		);

		if( empty( $invisiblePageIds ) ) {
			$invisiblePageWhere = "";
		}
		else {
			$invisiblePageWhere = "AND P.page_id NOT IN (" . implode(',', $invisiblePageIds) . ") ";
		}
		$sql = "SELECT P.room_id, count(B.block_id) as block_ct "
				. " FROM {pages} P INNER JOIN {blocks} B ON P.page_id = B.page_id AND B.module_id IN (" . implode(',', $mobileModuleIds) . ") "
				. " WHERE P.room_id IN ( " . implode(',', $roomIds ) . ")"
				. " AND P.space_type IN ("._SPACE_TYPE_PUBLIC.","._SPACE_TYPE_GROUP.") "
				. " AND P.private_flag IN ("._ON.","._OFF.") "
				. " AND P.display_position = ? "
				. $invisiblePageWhere
				. $this->_getPageWhereSQL($invisiblePageIds)
				. " GROUP BY P.room_id ";

		$visibleRoomIds = $this->_db->execute( $sql,
										$params,
										null,
										null,
										true,
										array($this, '_fetchVisibleRoomIds') );
		$temporaryRoomArr = null;
		$this->setVisibleFlagToRoomRecursive( $roomArr, $roomArr[0][0], $visibleRoomIds, $temporaryRoomArr);

		return true;
	}
	/**
	 * 表示可能ルームページID配列を生成する
	 * 2010.02.04 add by AllCreator
	 *
	 * @param object $recordSet 非表示ページページADORecordSet
	 * @return array 表示可能ルームのページID配列
	 * @access private
	 */
	function &_fetchVisibleRoomIds( &$recordSet )
	{
		$visiblePageIds = array();
		while ($room = $recordSet->fetchRow()) {
		if( $room['block_ct'] > 0 ) {
			$visiblePageIds[] = $room['room_id'];
		}
		}
		return $visiblePageIds;
	}

	/**
	 * ルームのツリー配列に対して一律の処理を行う
	 * 2010.02.04 add by AllCreator
	 *
	 * @param $roomArr ルームツリーの全配列 $room_list 現在処理対象の一部のルーム配列 $visibleRoom visible=trueを設定するルームIDの配列
	 * @access private
	 */
	function setVisibleFlagToRoomRecursive( &$roomArr, &$room_list, $visibleRoom, &$temporaryRoomArr)
	{
		if( is_null( $temporaryRoomArr ) ) {
			$temporaryRoomArr = array();
		}

		foreach( $room_list as $key=>$room ) {
			if( in_array( $room['page_id'], $visibleRoom ) ) {
				$room_list[$key]['visible_flag'] = true;
				$room_list[$key]['disable_flag'] = false;

				if( isset( $temporaryRoomArr[ $room['parent_id'] ] ) ) {
					$parent_room_check = $temporaryRoomArr[ $room['parent_id'] ];
					while( $parent_room_check['thread_num'] >= 0 ) {
						$roomArr[ $parent_room_check['thread_num'] ][ $parent_room_check['parent_id'] ][ $parent_room_check['display_sequence'] ]['visible_flag'] = true;
						if( isset(  $temporaryRoomArr[ $parent_room_check['parent_id'] ] ) ) {
							$parent_room_check = $temporaryRoomArr[ $parent_room_check['parent_id'] ];
						}
						else {
							break;
						}
					}
				}
			}
			else {
				$room_list[$key]['visible_flag'] = false;
				$room_list[$key]['disable_flag'] = true;
			}
			$temporaryRoomArr[ $room['page_id'] ] = $room;
			$threadNum = $room['thread_num'] + 1;
			$parentId = $room['page_id'];
			if( isset( $roomArr[$threadNum][$parentId] ) ) {
				$this->setVisibleFlagToRoomRecursive( $roomArr,  $roomArr[$threadNum][$parentId], $visibleRoom, $temporaryRoomArr );
			}
		}
	}
	/**
	 * ルームのツリー配列からIDのみを取り出す
	 * 2010.02.04 add by AllCreator
	 *
	 * @param $roomArr ルームツリーの全配列
	 * @access public
	 */
	function getAllowRoomIdArr( $room_arr )
	{
		$roomIds = array();
		foreach( $room_arr as $thread ) {
			foreach( $thread as $parent ) {
				foreach( $parent as $room ) {
					$roomIds[] = $room['room_id'];
				}
			}
		}
		return $roomIds;
	}
	/**
	 * モバイルのTEXT/HTML表示設定を返す
	 * 2010.10.13 add by AllCreator
	 *
	 * @param $html_flag 画面で直接指示されたTEXT/HTML切り替え設定
	 * @access public
	 * @return _ON:HTML _OFF:TEXT
	 */
	function getTextHtmlMode( $html_flag = null )
	{
		if( $html_flag !== null ) {
			return $html_flag;
		}
		else {
			$texthtml_mode = $this->_session->getParameter( "_mobile_text_html_mode" );
			if( $texthtml_mode == MOBILE_TEXTHTML_MODE_HTML ) {
				return _ON;
			}
			else {
				return _OFF;
			}
		}
	}

}
?>