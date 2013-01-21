<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォーム取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Components_View
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
	function Registration_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * 登録フォームが配置されているブロックデータを取得する
	 *
	 * @return string	ブロックデータ
	 * @access	public
	 */
	function &getBlock()
	{
		$params = array($this->_request->getParameter("registration_id"));
		$sql = "SELECT R.room_id, B.block_id ".
				"FROM {registration} R ".
				"INNER JOIN {registration_block} B ".
				"ON R.registration_id = B.registration_id ".
				"WHERE R.registration_id = ? ".
				"ORDER BY B.block_id";
		$blocks = $this->_db->execute($sql, $params, 1);
		if ($blocks === false) {
			$this->_db->addError();
			return $blocks;
		}

		return $blocks[0];
	}

	/**
	 * 登録フォームが存在するか判断する
	 *
	 * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function registrationExists()
	{
		$params = array(
			$this->_request->getParameter("registration_id"),
			$this->_request->getParameter("room_id")
		);
		$sql = "SELECT registration_id ".
				"FROM {registration} ".
				"WHERE registration_id = ? ".
				"AND room_id = ?";
		$registrationIDs = $this->_db->execute($sql, $params);
		if ($registrationIDs === false) {
			$this->_db->addError();
			return $registrationIDs;
		}

		if (count($registrationIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ルームIDの登録フォーム件数を取得する
	 *
	 * @return string	登録フォーム件数
	 * @access	public
	 */
	function getRegistrationCount()
	{
		$params["room_id"] = $this->_request->getParameter("room_id");
		$count = $this->_db->countExecute("registration", $params);

		return $count;
	}

	/**
	 * 在配置されている登録フォームIDを取得する
	 *
	 * @return string	配置されている登録フォームID
	 * @access	public
	 */
	function &getCurrentRegistrationID()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT registration_id ".
				"FROM {registration_block} ".
				"WHERE block_id = ?";
		$registrationIDs = $this->_db->execute($sql, $params);
		if ($registrationIDs === false) {
			$this->_db->addError();
			return $registrationIDs;
		}

		return $registrationIDs[0]["registration_id"];
	}

	/**
	 * 登録フォーム一覧データを取得する
	 *
	 * @return array	登録フォーム一覧データ配列
	 * @access	public
	 */
	function &getRegistrations()
	{
		$limit = $this->_request->getParameter("limit");
		$offset = $this->_request->getParameter("offset");

		$sortColumn = $this->_request->getParameter("sort_col");
		if (empty($sortColumn)) {
			$sortColumn = "registration_id";
		}
		if ($sortColumn != 'data_count') {
			$sortColumn = "R.". $sortColumn;
		}
		$sortDirection = $this->_request->getParameter("sort_dir");
		if (empty($sortDirection)) {
			$sortDirection = "DESC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("room_id"));
		$sql = "SELECT R.registration_id, R.registration_name, R.active_flag, R.insert_time, R.insert_user_id, R.insert_user_name, ".
						"COUNT(D.data_id) AS data_count ".
				"FROM {registration} R ".
				"LEFT JOIN {registration_data} D ".
				"ON R.registration_id = D.registration_id ".
				"WHERE R.room_id = ? ".
				"GROUP BY R.registration_id, R.registration_name, R.active_flag, R.insert_time, R.insert_user_id, R.insert_user_name ".
				$this->_db->getOrderSQL($orderParams);
		$registrations = $this->_db->execute($sql, $params, $limit, $offset);
		if ($registrations === false) {
			$this->_db->addError();
		}

		return $registrations;
	}

	/**
	 * 登録フォーム用デフォルトデータを取得する
	 *
	 * @return array	用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultRegistration()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);
		if ($config === false) {
			return $config;
		}

		$registration = array(
			'mail_send' => constant($config['mail_send']['conf_value']),
			'regist_user_send' => constant($config['regist_user_send']['conf_value']),
			'chief_send' => constant($config['chief_send']['conf_value']),
			'image_authentication' => constant($config['image_authentication']['conf_value']),
			'limit_number' => $config['limit_number']['conf_value']
		);

		return $registration;
	}

	/**
	 * 登録フォームデータを取得する
	 *
	 * @return array	データ配列
	 * @access	public
	 */
	function &getRegistration()
	{
		$params = array($this->_request->getParameter("registration_id"));
		$sql = "SELECT registration_id, "
					. "registration_name, "
					. "image_authentication, "
					. "limit_number, "
					. "period, "
					. "accept_message, "
					. "mail_send, "
					. "regist_user_send, "
					. "chief_send, "
					. "rcpt_to, "
					. "mail_subject, "
					. "mail_body, "
					. "active_flag "
				. "FROM {registration} "
				. "WHERE registration_id = ?";
		$registrations = $this->_db->execute($sql, $params, 1, null, true, array($this, '_fetchRegistration'));
		if ($registrations === false) {
			$this->_db->addError();
			return $registrations;
		}

		return $registrations[0];
	}

	/**
	 * 現在配置されているデータを取得する
	 *
	 * @return array	配置されているデータ配列
	 * @access	public
	 */
	function &getCurrentRegistration()
	{
		$params = array($this->_request->getParameter("block_id"));
		$sql = "SELECT R.registration_name, "
					. "R.image_authentication, "
					. "R.limit_number, "
					. "R.period, "
					. "R.accept_message, "
					. "R.mail_send, "
					. "R.active_flag, "
					. "COUNT(D.data_id) AS data_count "
				. "FROM {registration_block} B "
				. "INNER JOIN {registration} R "
				. "ON B.registration_id = R.registration_id "
				. "LEFT JOIN {registration_data} D "
				. "ON R.registration_id = D.registration_id "
				. "WHERE B.block_id = ? "
				. "GROUP BY R.registration_name, "
					. "R.image_authentication, "
					. "R.limit_number, "
					. "R.period, "
					. "R.accept_message, "
					. "R.mail_send, "
					. "R.active_flag";
		$registrations = $this->_db->execute($sql, $params, 1, null, true, array($this, '_fetchRegistration'));
		if ($registrations === false) {
			$this->_db->addError();
		}
		if (empty($registrations)) {
			return $registrations;
		}

		return $registrations[0];
	}

	/**
	 * 登録フォーム配列を作成する
	 * 期限の日時をフォーマットする
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @param	string	$format		日付フォーマット文字列
	 * @access	private
	 */
	function &_fetchRegistration(&$recordSet, $format = _DATE_FORMAT)
	{
		$registrations = array();
		while ($row = $recordSet->fetchRow()) {
			if (empty($row['period'])) {
				$registrations[] = $row;
				continue;
			}

			$period = timezone_date_format($row['period'], null);
			if (substr($period, 8) == '000000') {
				$previousDay = -1;
				$format = str_replace('H', '24', $format);
				$timeFormat = str_replace('H', '24', _SHORT_TIME_FORMAT);
			} else {
				$previousDay = 0;
				$timeFormat = _SHORT_TIME_FORMAT;
			}

			$date = mktime(intval(substr($period, 8, 2)),
							intval(substr($period, 10, 2)),
							intval(substr($period, 12, 2)),
							intval(substr($period, 4, 2)),
							intval(substr($period, 6, 2)) + $previousDay,
							intval(substr($period, 0, 4)));
			$row['displayPeriodDate'] = date($format, $date);
			$row['displayPeriodTime'] = date($timeFormat, $date);

			$registrations[] = $row;
		}

		return $registrations;
	}

	/**
	 * 項目件数を取得する
	 *
	 * @return string	項目件数
	 * @access	public
	 */
	function getItemCount()
	{
		$params["registration_id"] = $this->_request->getParameter("registration_id");
		$count = $this->_db->countExecute("registration_item", $params);

		return $count;
	}

	/**
	 * 項目一覧データを取得する
	 *
	 * @return array	項目一覧データ配列
	 * @access	public
	 */
	function &getItems() {
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$sql = "SELECT item_id, item_name, item_sequence, item_type, option_value, require_flag ";
		$where = "";
		if ($actionName == "registration_view_edit_item_list"
				|| $actionName == "registration_view_main_init") {
			$sql .= ", ".
					"require_flag ";
		}
		if ($actionName == "registration_view_edit_data_list") {
			$sql .= ", ".
					"sort_flag ";

			$where = "AND list_flag = ". _ON. " ";
		}
		if ($actionName == "registration_view_main_init") {
			$sql .= ", ".
					"description ";
		}

		$params = array($this->_request->getParameter("registration_id"));
		$sql .= "FROM {registration_item} ".
				"WHERE registration_id = ? ".
				$where.
				"ORDER BY item_sequence";
		$items = $this->_db->execute($sql, $params, null, null, true, array($this, "_makeItems"));
		if ($items === false) {
			$this->_db->addError();
			return $items;
		}

		return $items;
	}

	/**
	 * 項目配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	項目配列
	 * @access	private
	 */
	function &_makeItems(&$recordSet)
	{
		$items = array();
		while ($row = $recordSet->fetchRow()) {
			if ($row["item_type"] == REGISTRATION_TYPE_CHECKBOX
					|| $row["item_type"] == REGISTRATION_TYPE_RADIO
					|| $row["item_type"] == REGISTRATION_TYPE_SELECT) {
				$row["option_values"] = explode(REGISTRATION_OPTION_SEPARATOR, $row["option_value"]);
			}

			$itemID = $row["item_id"];
			$items[$itemID] = $row;
		}

		return $items;
	}

	/**
	 * 項目用デフォルトデータを取得する
	 *
	 * @return array	項目用デフォルトデータ配列
	 * @access	public
	 */
	function &getDefaultItem()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfig($moduleID, false);
		if ($config === false) {
			return $config;
		}

		$item = array(
			"registration_id" => $this->_request->getParameter("registration_id"),
			"item_type" => constant($config["item_type"]["conf_value"]),
			"require_flag" => constant($config["require_flag"]["conf_value"]),
			"list_flag" => constant($config["list_flag"]["conf_value"]),
			"sort_flag" => constant($config["sort_flag"]["conf_value"])
		);

		$item["option_values"] = $this->_getDefaultOption();
		if ($item["option_values"] === false) {
			return $item["option_values"];
		}

		$params = array(
			"registration_id" => $this->_request->getParameter("registration_id")
		);
		$item["item_sequence"] = $this->_db->countExecute("registration_item", $params);
		if ($item["item_sequence"] === false) {
			return $item["item_sequence"];
		}
		$item["item_sequence"]++;

		return $item;
	}

	/**
	 * 項目データを取得する
	 *
	 * @return array	項目データ配列
	 * @access	public
	 */
	function &getItem()
	{
		$params = array($this->_request->getParameter("item_id"));
		$sql = "SELECT item_id, registration_id, item_name, item_type, option_value, ".
						"require_flag, list_flag, sort_flag, description ".
				"FROM {registration_item} ".
				"WHERE item_id = ?";
		$items = $this->_db->execute($sql, $params, 1, null, true, array($this, "_makeItems"));
		if ($items === false) {
			$this->_db->addError();
			return $items;
		}
		$item = $items[key($items)];

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if ($actionName == "registration_view_edit_item_entry"
				&& $item["item_type"] != REGISTRATION_TYPE_CHECKBOX
				&& $item["item_type"] != REGISTRATION_TYPE_RADIO
				&& $item["item_type"] != REGISTRATION_TYPE_SELECT) {

			$item["option_values"] = $this->_getDefaultOption();
			if ($item["option_values"] === false) {
				return $item["option_values"];
			}
		}

		return $item;
	}

	/**
	 * 選択肢用デフォルトデータを取得する
	 *
	 * @return array	選択肢用デフォルトデータ配列
	 * @access	public
	 */
	function &_getDefaultOption()
	{
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$moduleID = $this->_request->getParameter("module_id");
		$config = $configView->getConfigByConfname($moduleID, "option_value_count");
		if ($config === false) {
			return $config;
		}

		$optionValues = array_pad(array(), $config["conf_value"], null);

		return $optionValues;
	}

	/**
	 * 項目番号データを取得する
	 *
	 * @return array	項目番号データ配列
	 * @access	public
	 */
	function &getItemSequence()
	{
		$params = array(
			$this->_request->getParameter("drag_item_id"),
			$this->_request->getParameter("drop_item_id"),
			$this->_request->getParameter("registration_id")
		);

		$sql = "SELECT item_id, item_sequence ".
				"FROM {registration_item} ".
				"WHERE (item_id = ? ".
				"OR item_id = ?) ".
				"AND registration_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false ||
			count($result) != 2) {
			$this->_db->addError();
			return false;
		}

		$sequences[$result[0]["item_id"]] = $result[0]["item_sequence"];
		$sequences[$result[1]["item_id"]] = $result[1]["item_sequence"];

		return $sequences;
	}

	/**
	 * 入力データ件数を取得する
	 *
	 * @return string	項目件数
	 * @access	public
	 */
	function getDataCount()
	{
		$params["registration_id"] = $this->_request->getParameter("registration_id");
		$count = $this->_db->countExecute("registration_data", $params);

		return $count;
	}

	/**
	 * 入力データを取得する
	 *
	 * @return string	入力データ
	 * @access	public
	 */
	function getData()
	{
		$params = array($this->_request->getParameter("data_id"));

		$sql = "SELECT data_id, registration_id ".
				"FROM {registration_data} ".
				"WHERE data_id = ?";
		$datas = $this->_db->execute($sql, $params);
		if (empty($datas)) {
			$this->_db->addError();
			return $datas;
		}

		return $datas[0];
	}

	/**
	 * 入力データ用SQLを取得する
	 *
	 * @return array	入力データ用SQL
	 * @access	public
	 */
	function &_getDataSQL()
	{
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$items = $this->_request->getParameter("items");
		$select = "";
		$join = "";
		foreach ($items as $key=>$item) {
			$alias = "ID". $item["item_id"];
			$select .= ", ". $alias. ".item_data_value as item_data_value". $item["item_id"];
			$join .= " LEFT JOIN {registration_item_data} ". $alias. " ".
						" ON D.data_id = ". $alias. ".data_id ".
						" AND ". $alias. ".item_id = ". $item["item_id"]. " ";

			if ($item["item_type"] == REGISTRATION_TYPE_FILE) {
				$fileAlias = "F". $item["item_id"];
				$select .= ", ". $fileAlias. ".file_name as file_name". $item["item_id"]. " ";
				$join .= " LEFT JOIN {registration_file} ". $fileAlias. " ".
							"ON ". $alias. ".item_data_id = ". $fileAlias. ".item_data_id ";
			}
		}

		if ($actionName == "registration_view_edit_data_list"
				|| $actionName == "registration_view_edit_data_csv") {
			$sql = "SELECT D.data_id, D.insert_time". $select. " ".
					"FROM {registration_data} D ". $join;
		} else {
			$sql = "SELECT R.registration_id, R.registration_name, ".
						"R.regist_user_send, R.chief_send, R.rcpt_to, R.mail_subject, R.mail_body, ".
						"D.data_id, D.insert_time". $select. " ".
					"FROM {registration} R ".
					"INNER JOIN {registration_data} D ".
					"ON R.registration_id = D.registration_id ". $join;
		}

		return $sql;
	}

	/**
	 * 入力データ一覧データを取得する
	 *
	 * @param	string	$limi	件数
	 * @param	string	$offset	取得開始行
	 * @return array	入力データ一覧データ配列
	 * @access	public
	 */
	function &getDataList($limit = null, $offset = null)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$blockID = $this->_request->getParameter("block_id");
		$sortItemID = $session->getParameter("registration_sort_item". $blockID);

		$items = $this->_request->getParameter("items");
		if (empty($sortItemID)
			|| $sortItemID == REGISTRATION_ALBUM_SORT_DESCEND) {
			$sortColumn = "D.insert_time";
			$sortDirection = "DESC";
		} elseif ($sortItemID == REGISTRATION_ALBUM_SORT_ASCEND) {
			$sortColumn = "D.data_id";
			$sortDirection = "ASC";
		} elseif ($items[$sortItemID]["item_type"] == REGISTRATION_TYPE_FILE) {
			$sortColumn = "file_name". $sortItemID;
			$sortDirection = "ASC";
		} else {
			$sortColumn = "item_data_value". $sortItemID;
			$sortDirection = "ASC";
		}
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("registration_id"));
		$sql = $this->_getDataSQL().
				"WHERE D.registration_id = ? ".
				$this->_db->getOrderSQL($orderParams);


		$datas = $this->_db->execute($sql, $params ,$limit, $offset, true, array($this, "_makeItemDatas"));
		if ($datas === false ) {
			$this->_db->addError();
		}

		return $datas;
	}

	/**
	 * 入力データ一覧データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	入力データ一覧データ配列
	 * @access	private
	 */
	function &_makeItemDatas(&$recordSet)
	{
		$items = $this->_request->getParameter("items");
		$datas = array();
		while ($row = $recordSet->fetchRow()) {
			$data = array();
			$data["itemDatas"] = array();
			foreach ($items as $item) {
				$itemData = array();
				$itemData["item_type"] = $item["item_type"];

				$key = "item_data_value". $item["item_id"];
				$itemData["item_data_value"] = $row[$key];

				if ($itemData["item_type"] == REGISTRATION_TYPE_FILE) {
					$key = "file_name". $item["item_id"];
					$itemData["file_name"] = $row[$key];
				}

				$data["itemDatas"][] = $itemData;
			}

			$data["data_id"] = $row["data_id"];
			$data["insert_time"] = $row["insert_time"];

			$datas[] = $data;
		}

		return $datas;
	}

	/**
	 * メール送信データを取得する
	 *
	 * @param	string	$dataID	入力データID
	 * @return array	メール送信データ配列
	 * @access	public
	 */
	function &getMail($dataID) {
		$params = array($dataID);
		$sql = $this->_getDataSQL().
				"WHERE D.data_id = ?";

		$mail = $this->_db->execute($sql, $params , null, null, true, array($this, "_makeMail"));
		if ($mail === false ) {
			$this->_db->addError();
		}

		return $mail;
	}

	/**
	 * メール送信データ配列を作成する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return array	メール送信データ配列
	 * @access	private
	 */
	function &_makeMail(&$recordSet)
	{
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$dataFormat = $smartyAssign->getLang("registration_mail_data_format");
		$dataIDLanguage = $smartyAssign->getLang("registration_data_id");

		$items = $this->_request->getParameter("items");
		$mail = array();
		while ($row = $recordSet->fetchRow()) {
			$mail["data"] = sprintf($dataFormat,  $dataIDLanguage, $row["data_id"]);
			foreach ($items as $item) {
				if ($item["item_type"] == REGISTRATION_TYPE_FILE) {
					$key = "file_name". $item["item_id"];
				} else {
					$key = "item_data_value". $item["item_id"];
				}

				$value = htmlspecialchars($row[$key]);
				if ($item["item_type"] == REGISTRATION_TYPE_TEXTAREA) {
					$value = nl2br($value);
				}

				if ($item['item_type'] == REGISTRATION_TYPE_EMAIL) {
					$mail['regist_user_email'] = $row[$key];
				}

				$mail["data"] .= sprintf($dataFormat,  htmlspecialchars($item["item_name"]), $value);
			}

			$mail["registration_id"] = $row["registration_id"];
			$mail["registration_name"] = $row["registration_name"];
			$mail["regist_user_send"] = $row["regist_user_send"];
			$mail["chief_send"] = $row["chief_send"];
			$mail["rcpt_to"] = $row["rcpt_to"];
			$mail["mail_subject"] = $row["mail_subject"];
			$mail["mail_body"] = $row["mail_body"];
			$mail["data_id"] = $row["data_id"];
			$mail["insert_time"] = $row["insert_time"];
		}

		return $mail;
	}

	/**
	 * CSVデータを作成する
	 *
	 * @return boolean	true:正常、false:異常
	 * @access	public
	 */
	function setCSV()
	{
		$sortColumn = "D.insert_time";
		$sortDirection = "DESC";
		$orderParams[$sortColumn] = $sortDirection;

		$params = array($this->_request->getParameter("registration_id"));
		$sql = $this->_getDataSQL().
				"WHERE D.registration_id = ? ".
				$this->_db->getOrderSQL($orderParams);


		$datas = $this->_db->execute($sql, $params ,null, null, true, array($this, "_makeCSV"));
		if ($datas === false ) {
			$this->_db->addError();
		}

 		return true;
	}

	/**
	 * CSVデータを設定する
	 *
	 * @param	array	$recordSet	ADORecordSet
	 * @return boolean	true:正常、false:異常
	 * @access	private
	 */
	function _makeCSV(&$recordSet)
	{

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$csvMain =& $container->getComponent("csvMain");

		$items = $this->_request->getParameter("items");
		$data = array();
		$data[] = $smartyAssign->getLang('registration_data_id');
		foreach ($items as $item) {
			$data[] = $item["item_name"];
		}
		$data[] = $smartyAssign->getLang("registration_entry_date");
		$csvMain->add($data);

		$datas = array();
		$fileDataFormat = $smartyAssign->getLang("registration_file_data_format");
		while ($row = $recordSet->fetchRow()) {
			$data = array();
			$data[] = $row['data_id'];
			foreach ($items as $item) {
				$key = "item_data_value". $item["item_id"];
				$value = $row[$key];

				if (!empty($value)
						&& $item["item_type"] == REGISTRATION_TYPE_FILE) {
					$key = "file_name". $item["item_id"];
					$value = $row[$key]. sprintf($fileDataFormat, $value);
				}

				$data[] = $value;
			}
			$data[] = timezone_date_format($row["insert_time"], _FULL_DATE_FORMAT);

			$csvMain->add($data);
		}

		return $datas;
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($room_id, $block_id_arr)
	{
		$sql = "SELECT regist.registration_id" .
				" FROM {registration} regist" .
				" INNER JOIN {registration_item} item ON (regist.registration_id=item.registration_id)" .
				" WHERE regist.room_id = ?" .
				" AND regist.active_flag = ?" .
				" AND item.item_type = ?" .
				" GROUP BY regist.registration_id";
		$reg_no_list = $this->_db->execute($sql, array($room_id, _ON, REGISTRATION_TYPE_FILE), null, null, true, array($this, "_getNoListForMobile"));
		if ($reg_no_list === false) {
			$this->_db->addError();
			return false;
		}

		$sql = "SELECT regist.*, block.block_id" .
				" FROM {registration} regist" .
				" INNER JOIN {registration_block} block ON (regist.registration_id=block.registration_id)" .
				" WHERE block.block_id IN (".implode(",", $block_id_arr).")" .
				" AND regist.active_flag = ". _ON .
				(!empty($reg_no_list) ? " AND regist.registration_id NOT IN (".implode(",", $reg_no_list).")" : "") .
	 			" GROUP BY regist.registration_id, block.block_id" .
				" ORDER BY block.insert_time DESC, block.registration_id DESC";

		return $this->_db->execute($sql, null);
	}
	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function &_getNoListForMobile(&$recordSet)
	{
		$result = array();
		while ($row = $recordSet->fetchRow()) {
			$result[] = $row["registration_id"];
		}
		return $result;
	}

}
?>
