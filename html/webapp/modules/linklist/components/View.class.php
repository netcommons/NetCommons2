<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Linklist_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * リンクリストが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock()
	{
		$params = array($this->_request->getParameter("linklist_id"));
		$sql = "SELECT L.room_id, B.block_id ".
				"FROM {linklist} L ".
				"INNER JOIN {linklist_block} B ".
				"ON L.linklist_id = B.linklist_id ".
				"WHERE L.linklist_id = ? ".
				"ORDER BY B.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * リンクリストが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function linklistExists()
	{
		$params = array(
			$this->_request->getParameter("linklist_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT linklist_id ".
				"FROM {linklist} ".
				"WHERE linklist_id = ? ".
				"AND room_id = ?";
		$linklistIDs = $this->_db->execute($sql, $params);
		if ($linklistIDs === false) {
			$this->_db->addError();
			return $linklistIDs;
		}
		if (count($linklistIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDのリンクリスト件数を取得する
	 *
     * @return string	リンクリスト件数
	 * @access	public
	 */
	function getLinklistCount()
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$count = $this->_db->countExecute("linklist", $params);

		return $count;
	}

	/**
	 * 在配置されているリンクリストIDを取得する
	 *
     * @return string	配置されているリンクリストID
	 * @access	public
	 */
	function &getCurrentLinklistID()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT linklist_id ".
				"FROM {linklist_block} ".
				"WHERE block_id = ?";
		$linklistIDs = $this->_db->execute($sql, $params);
		if ($linklistIDs === false) {
			$this->_db->addError();
			return $linklistIDs;
		}

		return $linklistIDs[0]["linklist_id"];
	}

	/**
	 * リンクリスト一覧データを取得する
	 *
     * @return array	リンクリスト一覧データ配列
	 * @access	public
	 */
	function &getLinklists()
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "linklist_id";
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT linklist_id, linklist_name, insert_time, insert_user_id, insert_user_name ".
				"FROM {linklist} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$linklists = $this->_db->execute($sql, $params, $limit, $offset);
		if ($linklists === false) {
			$this->_db->addError();
		}

		return $linklists;
	}

	/**
	 * ULタグのstyle属性を取得する
	 *
     * @param	string	$mark	マーク値
     * @return string	 ULタグのstyle属性
	 * @access	public
	 */
	function &_getMarkStyle($mark)
	{
        if(preg_match("/[.gif|.jpg|.png|bmp]$/i", $mark)) {
        	$style = "list-style-image:url('".get_image_url()."/images/common/mark/". $mark. "');";
        } else {
        	$style = "list-style-type:". $mark. ";";
        }

		return $style;
	}

	/**
	 * リンクリスト用デフォルトデータを取得する
	 *
     * @return array	リンクリスト用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultLinklist()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);
		if ($config === false) {
        	return $config;
        }

		$linklist = array(
			"category_authority" => constant($config["category_authority"]["conf_value"]),
			"link_authority" => constant($config["link_authority"]["conf_value"]),
			"display" => constant($config["display"]["conf_value"]),
			"target_blank_flag" => constant($config["target_blank_flag"]["conf_value"]),
			"view_count_flag" => $config["view_count_flag"]["conf_value"],
			"line" => $config["line"]["conf_value"],
			"mark" => $config["mark"]["conf_value"],
			"mark_style" => $this->_getMarkStyle($config["mark"]["conf_value"])
		);

		return $linklist;
	}

	/**
	 * 配置されているリンクリストデータを取得する
	 *
     * @return string	リンクリストデータ
	 * @access	public
	 */
	function &getLinklist()
	{
		$params = array($this->_request->getParameter("linklist_id"));

		$sql = "SELECT linklist_id, linklist_name, category_authority, link_authority ".
				"FROM {linklist} ".
				"WHERE linklist_id = ?";
		$linklists = $this->_db->execute($sql, $params, 1);
		if ($linklists === false) {
			$this->_db->addError();
			return $linklists;
		}

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName != "linklist_view_edit_entry" &&
				$actionName != "linklist_action_edit_entry") {
			$block = $this->getCurrentLinklist();
			if ($block === false) {
				$this->_db->addError();
				return $block;
			}
			$linklists[0]["category_authority"] = false;
			$linklists[0]["link_authority"] = false;
			$linklists[0]["display"] = $block["display"];
			$linklists[0]["target_blank_flag"] = $block["target_blank_flag"];
			$linklists[0]["view_count_flag"] = $block["view_count_flag"];
			$linklists[0]["line"] = $block["line"];
			$linklists[0]["mark"] = $block["mark"];
			$linklists[0]["mark_style"] = $this->_getMarkStyle($linklists[0]["mark"]);
		}

		return $linklists[0];
	}

	/**
	 * 現在配置されているリンクリストデータを取得する
	 *
     * @return array	配置されているリンクリストデータ配列
	 * @access	public
	 */
	function &getCurrentLinklist()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT B.block_id, B.linklist_id, B.display, B.target_blank_flag, B.view_count_flag, ".
						"B.line, B.mark, ".
						"L.linklist_name, L.category_authority, L.link_authority ".
				"FROM {linklist_block} B ".
				"INNER JOIN {linklist} L ".
				"ON B.linklist_id = L.linklist_id ".
				"WHERE B.block_id = ?";
		$linklists = $this->_db->execute($sql, $params);
		if ($linklists === false) {
			$this->_db->addError();
		}
		if (empty($linklists)) {
			return $linklists;
		}

		$linklists[0]["category_authority"] = $this->_hasCategoryAuthority($linklists[0]);
		$linklists[0]["link_authority"] = $this->_hasLinkAuthority($linklists[0]);
		$linklists[0]["mark_style"] = $this->_getMarkStyle($linklists[0]["mark"]);

		return $linklists[0];
	}

	/**
	 * カテゴリ登録権限を取得する
	 *
	 * @param	array	$linklist	カテゴリ権限の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasCategoryAuthority($linklist)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		if ($authID >= $linklist["category_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * リンク登録権限を取得する
	 *
	 * @param	array	$linklist	リンク権限の配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasLinkAuthority($linklist)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		if ($authID >= $linklist["link_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * カテゴリが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function categoryExists()
	{
		$params = array(
			$this->_request->getParameter("category_id"),
			$this->_request->getParameter("linklist_id")
		);
		$sql = "SELECT category_id ".
				"FROM {linklist_category} ".
				"WHERE category_id = ? ".
				"AND linklist_id = ?";
		$categoryIDs = $this->_db->execute($sql, $params);
		if ($categoryIDs === false) {
			$this->_db->addError();
			return $categoryIDs;
		}

		if (count($categoryIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * リンクが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function linkExists()
	{
		$params = array(
			$this->_request->getParameter("linklist_id"),
			$this->_request->getParameter("link_id")
		);

		$sql = "SELECT link_id ".
				"FROM {linklist_link} ".
				"WHERE linklist_id = ? ".
				"AND link_id = ?";
		$linkIDs = $this->_db->execute($sql, $params, 1);
		if ($linkIDs === false) {
			$this->_db->addError();
			return $linkIDs;
		}

		if (count($linkIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * カテゴリデータを取得する
	 *
     * @return array	カテゴリデータ
	 * @access	public
	 */
	function &getCategory()
	{
		$params = array($this->_request->getParameter("category_id"));
		$sql = "SELECT category_id, linklist_id, default_flag, insert_user_id ".
				"FROM {linklist_category} ".
				"WHERE category_id = ?";
		$categories = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCategoryArray"));
		if ($categories === false) {
			$this->_db->addError();
		}

		return $categories[key($categories)];
	}

	/**
	 * カテゴリ件数を取得する
	 *
     * @return array	カテゴリ件数
	 * @access	public
	 */
	function &getCategoryCount()
	{
    	$params = array(
			"linklist_id" => $this->_request->getParameter("linklist_id")
		);
    	$count = $this->_db->countExecute("linklist_category", $params);

		return $count;
	}

	/**
	 * カテゴリデータ配列を取得する
	 *
     * @return array	カテゴリデータ配列
	 * @access	public
	 */
	function &getCategories()
	{
		$params = array($this->_request->getParameter("linklist_id"));
		$sql = "SELECT category_id, linklist_id, category_name, default_flag, insert_user_id ".
				"FROM {linklist_category} ".
				"WHERE linklist_id = ? ".
				"ORDER BY category_sequence";
		$categories = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCategoryArray"));
		if ($categories === false) {
			$this->_db->addError();
		}

		return $categories;
	}

	/**
	 * カテゴリ番号データを取得する
	 *
     * @return array	カテゴリ番号データ配列
	 * @access	public
	 */
	function &getCategorySequence()
	{
		$params = array(
			$this->_request->getParameter("drag_category_id"),
			$this->_request->getParameter("drop_category_id"),
			$this->_request->getParameter("linklist_id")
		);

		$sql = "SELECT category_id, category_sequence ".
				"FROM {linklist_category} ".
				"WHERE (category_id = ? ".
				"OR category_id = ?) ".
				"AND linklist_id = ?";
		$categories = $this->_db->execute($sql, $params);
		if ($categories === false
				|| count($categories) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$categories[0]["category_id"]] = $categories[0]["category_sequence"];
		$sequences[$categories[1]["category_id"]] = $categories[1]["category_sequence"];

		return $sequences;
	}

	/**
	 * リンク件数を取得する
	 *
     * @return string	リンクリスト件数
	 * @access	public
	 */
	function getLinkCount()
	{
    	$params["category_id"] = $this->_request->getParameter("category_id");
    	$count = $this->_db->countExecute("linklist_link", $params);

		return $count;
	}

	/**
	 * リンクデータを取得する
	 *
     * @return array	リンクデータ
	 * @access	public
	 */
	function &getLink()
	{
		$params = array($this->_request->getParameter("link_id"));
		$sql =  "SELECT linklist_id, insert_user_id ".
				"FROM {linklist_link} ".
				"WHERE link_id = ?";
		$links = $this->_db->execute($sql, $params);
		if ($links === false) {
			$this->_db->addError();
		}
		if (empty($links)) {
			return $links;
		}

		$linklist = $this->_request->getParameter("linklist");
		$links[0]["edit_authority"] = ($linklist["link_authority"] && $this->_hasEditAuthority($links[0]["insert_user_id"]));

		return $links[0];
	}

	/**
	 * 参照数データを取得する
	 *
     * @return array	参照数データ
	 * @access	public
	 */
	function &getLinkViewCount()
	{
		$params = array($this->_request->getParameter("link_id"));
		$sql = "SELECT title, view_count ".
				"FROM {linklist_link} ".
				"WHERE link_id = ?";
		$links = $this->_db->execute($sql, $params);
		if ($links === false) {
			$this->_db->addError();
		}
		if (empty($links)) {
			return $links;
		}

		return $links[0];
	}

	/**
	 * 参照数データを取得する
	 *
     * @return array	参照数データ
	 * @access	public
	 */
	function &getMaxLinkID()
	{
		$params = array($this->_request->getParameter("drop_category_id"));
		$sql = "SELECT link_id ".
				"FROM {linklist_link} ".
				"WHERE category_id = ? ".
				"ORDER BY link_sequence DESC";
		$links = $this->_db->execute($sql, $params, 1, null, false);
		if ($links === false) {
			$this->_db->addError();
		}
		if (empty($links)) {
			return $links;
		}

		return $links[0][0];
	}

	/**
	 * リンク番号データを取得する
	 *
     * @return array	リンク番号データ配列
	 * @access	public
	 */
	function &getLinkSequence()
	{
		$dropLinkID = $this->_request->getParameter("drop_link_id");
		$params = array(
			$this->_request->getParameter("drag_link_id"),
			$dropLinkID,
			$this->_request->getParameter("linklist_id")
		);

		$sql = "SELECT link_id, category_id, link_sequence ".
				"FROM {linklist_link} ".
				"WHERE (link_id = ? ".
				"OR link_id = ?) ".
				"AND linklist_id = ?";
		$links = $this->_db->execute($sql, $params);
		if ($links === false
				|| (empty($dropLinkID) && count($links) != 1)
				|| (!empty($dropLinkID) && count($links) != 2)) {
			$this->_db->addError();
			$sequences = false;
			return $sequences;
		}

		$sequences[$links[0]["link_id"]] = $links[0];
		if (!empty($dropLinkID)) {
			$sequences[$links[1]["link_id"]] = $links[1];
		}

		return $sequences;
	}

	/**
	 * カテゴリリンクデータ配列を取得する
	 *
     * @return array	カテゴリリンクデータ配列
	 * @access	public
	 */
	function &getCategoryLinks()
	{
		$categories = $this->getCategories();
		if (empty($categories)) {
			return $categories;
		}

		$linklist = $this->_request->getParameter("linklist");

		$entry = $this->_request->getParameter("entry");
		$search = $this->_request->getParameter("search");

		$params = array($this->_request->getParameter("linklist_id"));
		$sql = "SELECT L.link_id, L.linklist_id, L.category_id, L.link_sequence, L.title, L.url, L.insert_user_id";
		if ($entry == _ON
				|| $linklist["display"] != LINKLIST_DISPLAY_DROPDOWN) {
			$sql .= ", ".
					"L.description";
		}
		if ($linklist["view_count_flag"] == _ON) {
			$sql .= ", ".
					"L.view_count";
		}
		$sql .= " ".
				"FROM {linklist_link} L ".
				"INNER JOIN {linklist_category} C ".
				"ON L.category_id = C.category_id ".
				"WHERE L.linklist_id = ? ";
		if (!empty($search)) {
			$sql .= "AND (L.title LIKE ? ".
							"OR L.description LIKE ?) ";
			$params[] = "%". $search. "%";
			$params[] = "%". $search. "%";
		}
		$sql .= "ORDER BY C.category_sequence, L.link_sequence";
		$categoryLinks = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCategoryLinkArray"), $categories);
		if ($categoryLinks === false) {
			$this->_db->addError();
		}

		return $categoryLinks;
	}

	/**
	 * カテゴリデータ配列を生成する
	 *
	 * @param	array	$recordSet	カテゴリADORecordSet
	 * @return array	カテゴリデータ配列
	 * @access	private
	 */
	function &_makeCategoryArray(&$recordSet)
	{
		$linklist = $this->_request->getParameter("linklist");

		$categories = array();
		while ($row = $recordSet->fetchRow()) {
			$row["edit_authority"] = false;
			if ($linklist["category_authority"]
					&& $row["default_flag"] != _ON
					&& $this->_hasEditAuthority($row["insert_user_id"])) {
				$row["edit_authority"] = true;
				$row["delete_authority"] = true;
			}

			$categoryID = $row["category_id"];
			$categories[$categoryID] = $row;
		}

		return $categories;
	}

	/**
	 * カテゴリリンクデータ配列を生成する
	 *
	 * @param	array	$recordSet	リンクADORecordSet
	 * @param	array	$categories	カテゴリ配列
	 * @return array	カテゴリリンクデータ配列
	 * @access	private
	 */
	function &_makeCategoryLinkArray(&$recordSet, &$categories)
	{
		$linklist = $this->_request->getParameter("linklist");

		$entry = $this->_request->getParameter("entry");
		if ($entry == _ON) {
			$categoryLinks = $categories;
		} else {
			$categoryLinks = array();
		}

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		while ($row = $recordSet->fetchRow()) {
			$row["edit_authority"] = false;
			if ($linklist["link_authority"]
					&& $this->_hasEditAuthority($row["insert_user_id"])) {
				$row["edit_authority"] = true;
			}

			$categoryID = $row["category_id"];
			if ($actionName != "linklist_view_main_search_result"
					&& empty($categoryLinks[$categoryID])) {
				$categoryLinks[$categoryID] = $categories[$categoryID];	
			}

			if ($authID < _AUTH_CHIEF) {
				$categoryLinks[$categoryID]["delete_authority"] = false;
			}

			$categoryLinks[$categoryID]["links"][] = $row;
		}

		return $categoryLinks;
	}

	/**
	 * 編集権限を取得する
	 *
	 * @param	array	$insertUserID	登録者ID
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasEditAuthority(&$insertUserID)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$authID = $session->getParameter("_auth_id");
		if ($authID >= _AUTH_CHIEF) {
			return true;
		}

		$userID = $session->getParameter("_user_id");
		if ($insertUserID == $userID) {
			return true;
		}

		$hierarchy = $session->getParameter("_hierarchy");
		$authCheck =& $container->getComponent("authCheck");
		$insetUserHierarchy = $authCheck->getPageHierarchy($insertUserID);
		if ($hierarchy > $insetUserHierarchy) {
	        return true;
		}

	    return false;
	}
}
?>
