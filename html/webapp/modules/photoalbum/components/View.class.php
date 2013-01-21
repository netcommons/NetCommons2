<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバムデータ取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Components_View
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
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Photoalbum_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
		$this->_session =& $container->getComponent("Session");
	}

	/**
	 * フォトアルバムが配置されているブロックデータを取得する
	 *
     * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock() {
		$params = array($this->_request->getParameter("photoalbum_id"));
		$sql = "SELECT P.room_id, B.block_id ".
				"FROM {photoalbum} P ".
				"INNER JOIN {photoalbum_block} B ".
				"ON P.photoalbum_id = B.photoalbum_id ".
				"WHERE P.photoalbum_id = ? ".
				"ORDER BY B.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * フォトアルバムが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function photoalbumExists() {
		$params = array(
			$this->_request->getParameter("photoalbum_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT photoalbum_id ".
				"FROM {photoalbum} ".
				"WHERE photoalbum_id = ? ".
				"AND room_id = ?";
		$photoalbumIDs = $this->_db->execute($sql, $params);
		if ($photoalbumIDs === false) {
			$this->_db->addError();
			return $photoalbumIDs;
		}

		if (count($photoalbumIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDのフォトアルバム件数を取得する
	 *
     * @return string	フォトアルバム件数
	 * @access	public
	 */
	function getPhotoalbumCount()
	{
    	$params["room_id"] = $this->_request->getParameter("room_id");
    	$count = $this->_db->countExecute("photoalbum", $params);

		return $count;
	}

	/**
	 * 在配置されているフォトアルバムIDを取得する
	 *
     * @return string	配置されているフォトアルバムID
	 * @access	public
	 */
	function &getCurrentPhotoalbumID()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT photoalbum_id ".
				"FROM {photoalbum_block} ".
				"WHERE block_id = ?";
		$photoalbumIDs = $this->_db->execute($sql, $params);
		if ($photoalbumIDs === false) {
			$this->_db->addError();
			return $photoalbumIDs;
		}

		return $photoalbumIDs[0]["photoalbum_id"];
	}

	/**
	 * フォトアルバムの設定データを取得する
	 *
     * @return string	設定データ配列
	 * @access	public
	 */
	function &getConfig()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);

		return $config;
	}

	/**
	 * フォトアルバム一覧データを取得する
	 *
     * @return array	フォトアルバム一覧データ配列
	 * @access	public
	 */
	function &getPhotoalbums()
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "photoalbum_id";
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT photoalbum_id, photoalbum_name, insert_time, insert_user_id, insert_user_name ".
				"FROM {photoalbum} ".
				"WHERE room_id = ? ".
				$this->_db->getOrderSQL($orderParams);
		$photoalbums = $this->_db->execute($sql, $params, $limit, $offset);
		if ($photoalbums === false) {
			$this->_db->addError();
		}

		return $photoalbums;
	}

	/**
	 * フォトアルバム用デフォルトデータを取得する
	 *
     * @return array	フォトアルバム用デフォルトデータ配列
	 * @access	public
	 */
	 function &getDefaultPhotoalbum() {
		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }

		$photoalbum = array(
			"album_authority" => constant($config["album_authority"]["conf_value"]),
			"album_new_period" => $config["album_new_period"]["conf_value"],
			"display" => constant($config["display"]["conf_value"]),
			"slide_type" => constant($config["slide_type"]["conf_value"]),
			"slide_time" => $config["slide_time"]["conf_value"],
			"size_flag" => constant($config["size_flag"]["conf_value"]),
			"width" => $config["width"]["conf_value"],
			"height" => $config["height"]["conf_value"],
			"album_visible_row" => $config["album_visible_row"]["conf_value"]
		);

		return $photoalbum;
	}

	/**
	 * フォトアルバムデータを取得する
	 *
     * @return array	フォトアルバムデータ
	 * @access	public
	 */
	function &getPhotoalbum() {
		$params = array($this->_request->getParameter("photoalbum_id"));
		$sql = "SELECT photoalbum_id, photoalbum_name, album_authority, album_new_period ".
				"FROM {photoalbum} ".
				"WHERE photoalbum_id = ?";
		$photoalbums = $this->_db->execute($sql, $params);
		if ($photoalbums === false) {
			$this->_db->addError();
			return $photoalbums;
		}
		$photoalbum = $photoalbums[0];

		$prefixID = $this->_request->getParameter("prefix_id_name");
		if (strpos($prefixID, PHOTOALBUM_PREFIX_REFERENCE) === false
				&& strpos($prefixID, PHOTOALBUM_PREFIX_ALBUM_LIST) === false) {
			return $photoalbum;
		}

		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }
		$photoalbum["display"] = PHOTOALBUM_DISPLAY_LIST;
		$photoalbum["slide_type"] = PHOTOALBUM_SLIDE_TYPE_FADE;
		$photoalbum["slide_time"] = $config["slide_time"]["conf_value"];
		$photoalbum["size_flag"] = _ON;
		$photoalbum["width"] = $config["width"]["conf_value"];
		$photoalbum["height"] = $config["height"]["conf_value"];
		$photoalbum["album_visible_row"] = $config["album_visible_row"]["conf_value"];
		$photoalbum["album_authority"] = false;
		$photoalbum["album_new_period_time"] = $this->_getNewPeriodTime($photoalbum["album_new_period"]);

		return $photoalbum;
	}

	/**
	 * 現在配置されているフォトアルバムデータを取得する
	 *
     * @return array	配置されているアルバムデータ配列
	 * @access	public
	 */
	function &getCurrentPhotoalbum() {
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT B.block_id, B.photoalbum_id, B.display, B.display_album_id, ".
						"B.slide_type, B.slide_time, B.size_flag, B.width, B.height, B.album_visible_row, ".
						"P.photoalbum_name, P.album_authority, P.album_new_period ".
				"FROM {photoalbum_block} B ".
				"INNER JOIN {photoalbum} P ".
				"ON B.photoalbum_id = P.photoalbum_id ".
				"WHERE block_id = ?";
		$photoalbums = $this->_db->execute($sql, $params);
		if ($photoalbums === false) {
			$this->_db->addError();
		}
		if (empty($photoalbums)) {
			return $photoalbums;
		}
		$photoalbum = $photoalbums[0];

		$photoalbum["album_authority"] = $this->_hasAlbumAuthority($photoalbum);
		$photoalbum["album_new_period_time"] = $this->_getNewPeriodTime($photoalbum["album_new_period"]);

		return $photoalbum;
	}

	/**
	 * アルバム作成権限を取得する
	 *
	 * @param	array	$photoalbum	フォトアルバムデータ配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasAlbumAuthority($photoalbum) {
		$authID = $this->_session->getParameter("_auth_id");
		if ($authID >= $photoalbum["album_authority"]) {
			return true;
		}

		return false;
	}

	/**
	 * new記号表示期間から対象年月日を取得する
	 *
	 * @param	string	$new_period		new記号表示期間
     * @return string	new記号表示対象年月日(YmdHis)
	 * @access	public
	 */
	function &_getNewPeriodTime($new_period)
	{
		if (empty($new_period)) {
			$new_period = -1;
		}

		$time = timezone_date();
		$time = mktime(0, 0, 0,
						intval(substr($time, 4, 2)),
						intval(substr($time, 6, 2)) - $new_period,
						intval(substr($time, 0, 4))
						);
		$time = date("YmdHis", $time);

		return $time;
	}

	/**
	 * 権限判断用のSQL文FROM句を取得する
	 *
     * @return string	権限判断用のSQL文FROM句
	 * @access	public
	 */
	function &_getAuthorityFromSQL() {
		$authID = $this->_session->getParameter("_auth_id");

		$sql = "";
		if ($authID >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "LEFT JOIN {pages_users_link} PU ".
					"ON A.insert_user_id = PU.user_id ".
					"AND A.room_id = PU.room_id ";
		$sql .= "LEFT JOIN {authorities} AU ".
					"ON PU.role_authority_id = AU.role_authority_id ";

		return $sql;
	}

	/**
	 * 権限判断用のSQL文WHERE句を取得する
	 * パラメータ用配列に必要な値を追加する
	 *
	 * @param	array	$params	パラメータ用配列
     * @return string	権限判断用のSQL文WHERE句
	 * @access	public
	 */
	function &_getAuthorityWhereSQL(&$params) {
		$authID = $this->_session->getParameter("_auth_id");

		$sql = "";
		if ($authID >= _AUTH_CHIEF) {
			return $sql;
		}

		$sql .= "AND (A.public_flag = ? OR AU.hierarchy < ? OR A.insert_user_id = ?";

		$defaultEntry = $this->_session->getParameter("_default_entry_flag");
		$hierarchy = $this->_session->getParameter("_hierarchy");
		if ($defaultEntry == _ON && $hierarchy > $this->_session->getParameter("_default_entry_hierarchy")) {
			$sql .= " OR AU.hierarchy IS NULL) ";
		} else {
			$sql .= ") ";
		}

		$params[] = _ON;
		$params[] = $hierarchy;
		$params[] = $this->_session->getParameter("_user_id");

		return $sql;
	}

	/**
	 * アルバム件数を取得する
	 *
     * @return string	アルバム件数
	 * @access	public
	 */
	function &getAlbumCount() {
		$params = array($this->_request->getParameter("photoalbum_id"));
		$sql = "SELECT count(A.album_id) ".
				"FROM {photoalbum_album} A ".
				$this->_getAuthorityFromSQL().
				"WHERE photoalbum_id = ? ".
				$this->_getAuthorityWhereSQL($params);
		$counts = $this->_db->execute($sql, $params, null, null, false);
		if ($counts === false) {
        	$this->_db->addError();
			return $counts;
		}
		$count = $counts[0][0];

		return $count;
	}

	/**
	 * アルバム一覧データを取得する
	 *
	 * @param	string	$offset	取得開始行
     * @return array	アルバム一覧データ配列
	 * @access	public
	 */
	function &getAlbums($offset = null)
	{
		$params = array($this->_request->getParameter("photoalbum_id"));

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "photoalbum_view_edit_style") {
			$sql = "SELECT album_id, album_name ".
					"FROM {photoalbum_album} ".
					"WHERE photoalbum_id = ? ".
					"ORDER BY photo_upload_time DESC";
			$albums = $this->_db->execute($sql, $params);
		} else {
			$blockID = $this->_request->getParameter("block_id");
			$limit = $this->_session->getParameter("photoalbum_visible_row".  $blockID);

			$sort = $this->_session->getParameter("photoalbum_album_sort".  $blockID);
			if (!isset($sort)
					|| $sort == PHOTOALBUM_ALBUM_SORT_NEW) {
				$orderParams["photo_upload_time"] = "DESC";
			} elseif ($sort == PHOTOALBUM_ALBUM_SORT_NONE) {
				$orderParams["album_sequence"] = "ASC";
			} elseif ($sort == PHOTOALBUM_ALBUM_SORT_VOTE) {
				$orderParams["vote_flag"] = "DESC";
				$orderParams["album_vote_count"] = "DESC";
			}

			$sql = "SELECT A.album_id, A.album_name, A.upload_id, A.album_jacket, A.width, A.height, ".
							"A.album_sequence, A.album_description, ".
							"A.photo_count, A.photo_upload_time, A.photo_new_period, A.album_vote_count, A.vote_flag, A.public_flag, ".
							"A.insert_time, A.insert_user_name, A.insert_user_id ".
					"FROM {photoalbum_album} A ".
					$this->_getAuthorityFromSQL().
					"WHERE photoalbum_id = ? ".
					$this->_getAuthorityWhereSQL($params).
					$this->_db->getOrderSQL($orderParams);
			$albums = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makeAlbumArray"));
			if ($albums === false) {
				$this->_db->addError();
			}
		}

		return $albums;
	}

	/**
	 * アルバムデータ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeAlbumArray(&$recordSet)
	{
		$photoalbum = $this->_request->getParameter("photoalbum");

		$albums = array();
		while ($row = $recordSet->fetchRow()) {
			if (!empty($row["album_jacket"])) {
				$row["jacket_style"] = $this->getImageStyle($row["width"], $row["height"], PHOTOALBUM_JACKET_WIDTH, PHOTOALBUM_JACKET_HEIGHT);
			}

			$row["edit_authority"] = false;
			if ($photoalbum["album_authority"]
					&& $this->_hasEditAuthority($row["insert_user_id"])) {
				$row["edit_authority"] = true;
			}

			$row["photo_new_period_time"] = $this->_getNewPeriodTime($row["photo_new_period"]);

			$albums[] = $row;
		}

		return $albums;
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

		$authID = $this->_session->getParameter("_auth_id");
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

	/**
	 * アルバム用デフォルトデータを取得する
	 *
     * @return array	アルバム用デフォルトデータ配列
	 * @access	public
	 */
	 function &getDefaultAlbum() {
		$config = $this->getConfig();
		if ($config === false) {
        	return $config;
        }

		$photoalbum = array(
			"album_jacket" => $config["album_jacket"]["conf_value"],
			"photo_new_period" => $config["photo_new_period"]["conf_value"],
			"vote_flag" => constant($config["vote_flag"]["conf_value"]),
			"comment_flag" => constant($config["comment_flag"]["conf_value"]),
			"public_flag" => constant($config["public_flag"]["conf_value"])
		);

		return $photoalbum;
	}

	/**
	 * アルバムデータを取得する
	 *
     * @return array	フォトアルバムデータ
	 * @access	public
	 */
	function &getAlbum() {
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "photoalbum_view_main_init") {
			$select = "A.photoalbum_id, A.photo_count, A.photo_new_period, A.public_flag ";
		} elseif ($actionName == "photoalbum_view_main_album_entry") {
			$select = "A.album_id, A.photoalbum_id, A.album_name, A.upload_id, A.album_jacket, A.width, A.height, A.album_description, ".
						"A.photo_new_period, A.vote_flag, A.comment_flag, A.public_flag, A.insert_user_id ";
		} else {
			$select = "A.album_id, A.photoalbum_id, A.album_name, ".
						"A.photo_count, A.photo_upload_time, A.photo_new_period, A.vote_flag, A.comment_flag, A.public_flag, A.insert_user_id ";
		}

		$params = array($this->_request->getParameter("album_id"));
		$sql = "SELECT ". $select.
				"FROM {photoalbum_album} A ".
				$this->_getAuthorityFromSQL().
				"WHERE album_id = ? ".
				$this->_getAuthorityWhereSQL($params);
		$albums = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeAlbumArray"));
		if ($albums === false) {
			$this->_db->addError();
			return $albums;
		}
		$album = isset($albums[0]) ? $albums[0] : null;

		return $album;
	}

	/**
	 * 写真が存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function photoExists() {
		$params = array($this->_request->getParameter("album_id"));
		$sql = "SELECT photo_id ".
				"FROM {photoalbum_photo} ".
				"WHERE album_id = ?";
		$photoIDs = $this->_db->execute($sql, $params, 1);
		if ($photoIDs === false) {
			$this->_db->addError();
			return $photoIDs;
		}

		if (count($photoIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * 写真一覧データを取得する
	 *
	 * @param	string	$offset	取得開始行
     * @return array	アルバム一覧データ配列
	 * @access	public
	 */
	function &getPhotos($limit=null, $offset=null)
	{
		$params = array($this->_request->getParameter("album_id"));

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "photoalbum_action_main_photo_sequence") {
			$sql = "SELECT photo_id ".
					"FROM {photoalbum_photo} ".
					"WHERE album_id = ?";
			$photos = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_checkPhotoSequence"));
		} else {
			$sort = $this->_request->getParameter("sort");
			$album = $this->_request->getParameter("album");
			if ($album["vote_flag"] != _ON
					&& $sort == PHOTOALBUM_ALBUM_SORT_VOTE) {
				unset($sort);
			}
			if (!isset($sort)
					|| $sort == PHOTOALBUM_PHOTO_SORT_NONE) {
				$orderParams["photo_sequence"] = "ASC";
			} elseif ($sort == PHOTOALBUM_PHOTO_SORT_DATE_DESC) {
				$orderParams["insert_time"] = "DESC";
			} elseif ($sort == PHOTOALBUM_PHOTO_SORT_DATE_ASC) {
				$orderParams["insert_time"] = "ASC";
			} elseif ($sort == PHOTOALBUM_PHOTO_SORT_PHOTO_NAME) {
				$orderParams["photo_name"] = "ASC";
			} elseif ($sort == PHOTOALBUM_PHOTO_SORT_VOTE) {
				$orderParams["photo_vote_count"] = "DESC";
			}

			$sql = "SELECT photo_id, photo_name, photo_sequence, photo_path, width, height, ".
							"photo_vote_count, photo_description, insert_user_id ".
					"FROM {photoalbum_photo} ".
					"WHERE album_id = ? ".
					$this->_db->getOrderSQL($orderParams);
			$photos = $this->_db->execute($sql, $params, $limit, $offset, true, array($this, "_makePhotoArray"));
		}
		if ($photos === false) {
			$this->_db->addError();
		}

		return $photos;
	}

	/**
	 * 写真データ配列を生成する
	 *
	 * @param	array	$recordSet	写真ADORecordSet
	 * @return array	写真データ配列
	 * @access	private
	 */
	function &_makePhotoArray(&$recordSet)
	{
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$photos = array();
		while ($row = $recordSet->fetchRow()) {
			$row["thumbnail_style"] = $this->getImageStyle($row["width"], $row["height"], PHOTOALBUM_THUMBNAIL_WIDTH, PHOTOALBUM_THUMBNAIL_HEIGHT);

			if ($actionName == "photoalbum_view_main_photo_list") {
				$row["edit_authority"] = false;
				if ($this->_hasEditAuthority($row["insert_user_id"])) {
					$row["edit_authority"] = true;
				}
			}

			$photos[] = $row;
		}

		return $photos;
	}

	/**
	 * 画像のスタイル属性値を取得する
	 *
	 * @param	string	$width	幅
	 * @param	string	$height	高さ
	 * @param	string	$maxWidth	最大幅
	 * @param	string	$maxHeight	最大高さ
	 * @return array	画像のスタイル属性値
	 * @access	private
	 */
	function &getImageStyle($width, $height, $maxWidth, $maxHeight)
	{
		$ratio = $height / $width;

		$widthRatio = $width / $maxWidth;
		$heightRatio = $height / $maxHeight;

		if ($widthRatio > $heightRatio) {
			$height = $maxHeight;
			$widht = intval($height / $ratio);
			$top = 0;
			$right = intval(($widht + $maxWidth) / 2);
			$bottom = $maxHeight;
			$left = intval(($widht - $maxWidth) / 2);
			$marginLeft = $left * -1;
			$marginTop = $top;
		} else {
			$widht = $maxWidth;
			$height = intval($widht * $ratio);
			$top = intval(($height - $maxHeight) / 2);
			$right = $maxWidth;
			$bottom = intval(($height + $maxHeight) / 2);
			$left = 0;
			$marginLeft = $left;
			$marginTop = $top * -1;
		}

		$style = sprintf(PHOTOALBUM_THUMBNAIL_STYLE, $widht, $height, $top, $right, $bottom, $left, $marginLeft, $marginTop);
		if (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0;')
			|| stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0;')) {
			$style = str_replace(',', ' ', $style);
		}

		return $style;
	}

	/**
	 * 写真データ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_checkPhotoSequence(&$recordSet)
	{
		$photoIDs = array();
		$requestPhotoIDs = $this->_request->getParameter("photo_ids");
		while ($row = $recordSet->fetchRow()) {
			if (!in_array($row["photo_id"], $requestPhotoIDs)) {
				return false;
			}

			$photoIDs[] = $row["photo_id"];
		}

		return $photoIDs;
	}

	/**
	 * 写真データを取得する
	 *
     * @return array	写真データ
	 * @access	public
	 */
	function &getPhoto() {
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "photoalbum_view_main_photo_entry") {
			$select = "photo_id, album_id, photoalbum_id, photo_name, photo_description, insert_user_id ";
		} elseif ($actionName == "photoalbum_action_main_photo_entry"
					|| $actionName == "photoalbum_action_main_photo_delete") {
			$select = "photo_id, album_id, photoalbum_id, insert_user_id ";
		} elseif ($actionName == "photoalbum_view_main_photo_footer") {
			$select = "photo_id, album_id, photoalbum_id, photo_vote_count ";
		} else {
			$select = "photo_id, album_id, photoalbum_id ";
		}

		$params = array($this->_request->getParameter("photo_id"));
		$sql = "SELECT ". $select.
				"FROM {photoalbum_photo} ".
				"WHERE photo_id = ?";
		$photos = $this->_db->execute($sql, $params, 1);
		if ($photos === false) {
			$this->_db->addError();
			return $photos;
		}
		$photo = $photos[0];

		if ($actionName == "photoalbum_view_main_photo_footer"
				|| $actionName == "photoalbum_action_main_photo_vote") {
			$photo["vote_authority"] = $this->_hasVoteAuthority($photo);
		} elseif ($actionName == "photoalbum_view_main_photo_entry"
						|| $actionName == "photoalbum_action_main_photo_entry"
						|| $actionName == "photoalbum_action_main_photo_delete") {
			$photo["edit_authority"] = false;
			if ($this->_hasEditAuthority($photo["insert_user_id"])) {
				$photo["edit_authority"] = true;
			}
		} elseif ($actionName == "photoalbum_view_main_comment") {
			$photo["comment_authority"] = $this->_hasCommentAuthority();
		}

		return $photo;
	}

	/**
	 * 投票権限を取得する
	 *
	 * @param	array	$photo	写真データ配列
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasVoteAuthority($photo)
	{
		$album = $this->_request->getParameter("album");
		if ($album["vote_flag"] != _ON) {
			return false;
		}

		$votes = $this->_session->getParameter("photoalbum_votes");
		if (!empty($votes) && in_array($photo["photo_id"], $votes)) {
			return false;
    	}

		$userID = $this->_session->getParameter("_user_id");
		if (empty($userID)) {
			return true;
		}

		$params = array(
			$userID,
			$photo["photo_id"]
		);
		$sql = "SELECT vote_flag ".
				"FROM {photoalbum_user_photo} ".
				"WHERE user_id = ? ".
				"AND photo_id = ?";
		$voteFlags = $this->_db->execute($sql, $params, null, null, false);
		if ($voteFlags === false) {
        	$this->_db->addError();
			return false;
		}

		if (empty($voteFlags) || $voteFlags[0][0] != _ON) {
			return true;
		}

		return false;
	}

	/**
	 * コメント権限を取得する
	 *
	 * @return boolean	true:権限有り、false:権限無し
	 * @access	public
	 */
	function _hasCommentAuthority()
	{
		$authID = $this->_session->getParameter("_auth_id");
		if ($authID <= _AUTH_GUEST) {
			return false;
		}

		$album = $this->_request->getParameter("album");
		if ($album["comment_flag"] != _ON) {
			return false;
		}

		return true;
	}

	/**
	 * コメント件数を取得する
	 *
     * @return string	フォトアルバム件数
	 * @access	public
	 */
	function getCommentCount()
	{
    	$params["photo_id"] = $this->_request->getParameter("photo_id");
    	$count = $this->_db->countExecute("photoalbum_comment", $params);

		return $count;
	}

	/**
	 * コメント一覧データを取得する
	 *
     * @return array	コメント一覧データ配列
	 * @access	public
	 */
	function &getComments()
	{
		$params = array($this->_request->getParameter("photo_id"));
		$sql = "SELECT comment_id, comment_value, insert_time, insert_user_name, insert_user_id ".
				"FROM {photoalbum_comment} ".
				"WHERE photo_id = ? ".
				"ORDER BY insert_time";
		$comments = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeCommentArray"));
		if ($comments === false) {
			$this->_db->addError();
		}

		return $comments;
	}

	/**
	 * コメントデータ配列を生成する
	 *
	 * @param	array	$recordSet	タスクADORecordSet
	 * @return array	タスクデータ配列
	 * @access	private
	 */
	function &_makeCommentArray(&$recordSet)
	{
		$commentAuthority = $this->_hasCommentAuthority();

		$comments = array();
		while ($row = $recordSet->fetchRow()) {
			$row["edit_authority"] = false;
			if ($commentAuthority
					&& $this->_hasEditAuthority($row["insert_user_id"])) {
				$row["edit_authority"] = true;
			}

			$comments[] = $row;
		}

		return $comments;
	}

	/**
	 * コメントデータを取得する
	 *
     * @return array	コメントデータ配列
	 * @access	public
	 */
	function &getComment()
	{
		$params = array($this->_request->getParameter("comment_id"));
		$sql = "SELECT comment_id, photo_id, album_id, photoalbum_id, comment_value, insert_user_id ".
				"FROM {photoalbum_comment} ".
				"WHERE comment_id = ?";
		$comments = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeCommentArray"));
		if ($comments === false) {
			$this->_db->addError();
		}
		$comment = $comments[0];

		return $comment;
	}

	/**
	 * 画像のサイズを取得する
	 *
	 * @param	string	$image	upload_id or 画像ファイル名
	 * @access	private
	 */
	function &getImageSize($image)
	{
		if (is_numeric($image)) {
			$container =& DIContainerFactory::getContainer();

			$uploads =& $container->getComponent("uploadsView");
			$uploadFiles = $uploads->getUploadById($image);
			if (empty($uploadFiles)) {
				return false;
			}

			$imageSize = getimagesize(FILEUPLOADS_DIR. "photoalbum/". $uploadFiles[0]["physical_file_name"]);
		} else {
			$imageSize = getimagesize(PHOTOALBUM_SAMPLR_JACKET_PATH. $image);
		}

		return $imageSize;
	}

	/**
	 * 写真データを取得する（携帯用）
	 *
	 * @return array	写真データ
	 * @access	public
	 */
	function &getPhotoForMobile($seq, $photo_count)
	{
		$prevFlag = ($seq > 1);
		$nextFlag = ($seq < $photo_count);

		$limit = 1;
		$offset = $seq-1;
		if ($prevFlag) {
			$limit++;
			$offset--;
		}
		if ($nextFlag) {
			$limit++;
		}
		$photos = $this->getPhotos($limit, $offset);
		if (empty($photos)) {
			return false;
		}

		if ($prevFlag) {
			$photo = $photos[1];
			$photo['prev_photo_id'] = $photos[0]['photo_id'];
			$next_photo_index = 2;
		} else {
			$photo = $photos[0];
			$next_photo_index = 1;
		}
		if ($nextFlag) {
			$photo['next_photo_id'] = $photos[$next_photo_index]['photo_id'];
		}
		$photo['vote_authority'] = $this->_hasVoteAuthority($photo);
		$photo['comment_authority'] = $this->_hasCommentAuthority();

		return $photo;
	}
}
?>