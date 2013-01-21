<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォームデータ登録コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Components_Action
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
	function Registration_Components_Action()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * 登録フォームデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setRegistration()
	{
		$params = array(
			'registration_name' => $this->_request->getParameter('registration_name'),
			'image_authentication' => intval($this->_request->getParameter('image_authentication')),
			'limit_number' => intval($this->_request->getParameter('limit_number')),
			'period' => $this->_request->getParameter('period'),
			'accept_message' => $this->_request->getParameter('accept_message'),
			'mail_send' => intval($this->_request->getParameter('mail_send')),
			'regist_user_send' => intval($this->_request->getParameter('regist_user_send')),
			'chief_send' => intval($this->_request->getParameter('chief_send')),
			'rcpt_to' => $this->_request->getParameter('rcpt_to'),
			'mail_subject' => $this->_request->getParameter('mail_subject'),
			'mail_body' => $this->_request->getParameter('mail_body'),
			'image_authentication' => intval($this->_request->getParameter('image_authentication'))
		);

		$registration = $this->_request->getParameter("registration");
		$registrationID = $this->_request->getParameter("registration_id");
		if (empty($registrationID)) {
			$params["room_id"] = intval($this->_request->getParameter("room_id"));
			$result = $this->_db->insertExecute("registration", $params, true, "registration_id");
		} else {
			$params["registration_id"] = $registrationID;
			$result = $this->_db->updateExecute("registration", $params, "registration_id", true);
		}
		if (!$result) {
			return false;
		}

        if (!empty($registrationID)) {
        	return true;
        }

		$registrationID = $result;

		$this->_request->getParameter('registration_name');
		$old_use = $this->_request->getParameter('old_use');
    	if ($old_use == _ON) {
			$defaultItems =& $this->_db->selectExecute("registration_item", array("registration_id"=>intval($this->_request->getParameter('old_registration_id'))));
			if($defaultItems === false) {
				$this->_db->addError();
				return 'error';
			}
    	} else {
			$container =& DIContainerFactory::getContainer();
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			$defaultItem = $smartyAssign->getLang("registration_default_item");
			$defaultItems = explode("/", $defaultItem);
		}

    	$sequence = 0;
		foreach ($defaultItems as $defaultItem) {
			if ($old_use == _ON) {
				$params = array(
					"registration_id" => $registrationID,
					"item_name" => $defaultItem["item_name"],
					"item_sequence" => $defaultItem["item_sequence"],
					"item_type" => $defaultItem["item_type"],
					"option_value" => $defaultItem["option_value"],
					"require_flag" => $defaultItem["require_flag"],
					"list_flag" => $defaultItem["list_flag"],
					"sort_flag" => $defaultItem["sort_flag"],
					"description" => $defaultItem["description"],
				);
			} else {
				$sequence++;
				$items = explode(",", $defaultItem);
				$params = array(
					"registration_id" => $registrationID,
					"item_name" => $items[0],
					"item_sequence" => $sequence,
					"item_type" => constant($items[1]),
					"option_value" => $items[2],
					"require_flag" => constant($items[3]),
					"list_flag" => constant($items[4]),
					"sort_flag" => constant($items[5])
				);
			}
			if (!$this->_db->insertExecute("registration_item", $params, true, "item_id")) {
				return false;
			}
		}

		$this->_request->setParameter("registration_id", $registrationID);
        //if (!$this->setBlock()) {
			//return false;
		//}

		return true;
	}

	/**
	 * 入力データ用SQLを取得する
	 *
     * @return array	入力データ用SQL
	 * @access	public
	 */
	function &_getFileSQL()
	{
		$sql = "SELECT F.upload_id ".
					"FROM {registration_file} F ".
					"INNER JOIN {registration_item_data} ID ".
					"ON F.item_data_id = ID.item_data_id ";

		return $sql;
	}

	/**
	 * 登録フォームデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteRegistration()
	{
		$params = array(
			"registration_id" => $this->_request->getParameter("registration_id")
		);

    	$sql = $this->_getFileSQL().
				"WHERE ID.registration_id = ?";
		$files = $this->_db->execute($sql, $params);
		if ($files === false) {
			$this->_db->addError();
			return false;
		}
		if (!$this->deleteFile($files)) {
			return false;
		}

    	if (!$this->_db->deleteExecute("registration_block", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("registration_item_data", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("registration_data", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("registration_item", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("registration", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * アップロードファイルを削除する
	 *
	 * @param  string	$files	ファイルデータ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteFile($files)
	{
		if (empty($files)) {
			return true;
		}

		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$uploads =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		foreach ($files as $file) {
			if (!$uploads->delUploadsById($file["upload_id"])) {
				return false;
			}

			$uploadIDs[] = $file["upload_id"];
		}

		$sql = "DELETE FROM {registration_file} ".
					"WHERE upload_id IN (". implode(",", $uploadIDs). ")";
		if (!$this->_db->execute($sql)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 登録フォーム用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {registration_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"registration_id" => $this->_request->getParameter("registration_id")
		);

		if (!empty($blockIDs)) {
			$result = $this->_db->updateExecute("registration_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("registration_block", $params, true);
		}
        if (!$result) {
			return false;
		}

		$active_flag = $this->_request->getParameter("active_flag");
		if (!empty($active_flag)) {
			if (!$this->setActivity()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * 登録フォーム用動作／停止を登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setActivity()
	{
		$params = array(
			"registration_id"=>intval($this->_request->getParameter("registration_id"))
		);

		$update_params = array(
    		"active_flag" => intval($this->_request->getParameter("active_flag"))
    	);

	    $result = $this->_db->updateExecute("registration", $update_params, $params, true);
        if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * 項目データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setItem()
	{
    	$itemID = $this->_request->getParameter("item_id");
		if (empty($itemID)) {
			$params = array(
				"registration_id" => $this->_request->getParameter("registration_id")
			);
    		$count = $this->_db->countExecute("registration_item", $params);

			$params["item_sequence"] = $count + 1;
		}

    	$params["item_name"] = $this->_request->getParameter("item_name");
		$params["item_type"] = intval($this->_request->getParameter("item_type"));
		$params["option_value"] = $this->_request->getParameter("option_value");
		$params["require_flag"] = intval($this->_request->getParameter("require_flag"));
		$params["list_flag"] = intval($this->_request->getParameter("list_flag"));
		$params["sort_flag"] = intval($this->_request->getParameter("sort_flag"));
		$params["description"] = $this->_request->getParameter("description");

    	$itemID = $this->_request->getParameter("item_id");
		if (empty($itemID)) {
			$result = $this->_db->insertExecute("registration_item", $params, true, "item_id");
		} else {
			$params["item_id"] = $itemID;
			$result = $this->_db->updateExecute("registration_item", $params, "item_id", true);
		}
		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * 項目データを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteItem()
	{
		$params = array(
			"item_id" => $this->_request->getParameter("item_id")
		);

    	$sql = $this->_getFileSQL().
				"WHERE ID.item_id = ?";
		$files = $this->_db->execute($sql, $params);
		if ($files === false) {
			$this->_db->addError();
			return false;
		}
		if (!$this->deleteFile($files)) {
			return false;
		}

     	if (!$this->_db->deleteExecute("registration_item_data", $params)) {
    		return false;
    	}

		$sql = "SELECT item_sequence ".
				"FROM {registration_item} ".
				"WHERE item_id = ?";
		$sequences = $this->_db->execute($sql, $params, 1, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return false;
		}
		$sequence = $sequences[0][0];

    	if (!$this->_db->deleteExecute("registration_item", $params)) {
    		return false;
    	}

		$params = array(
			"registration_id" => $this->_request->getParameter("registration_id")
		);
		$sequenceParam = array(
			"item_sequence" => $sequence
		);
		if (!$this->_db->seqExecute("registration_item", $params, $sequenceParam)) {
			return false;
		}

    	return true;
	}

	/**
	 * 項目番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateItemSequence()
	{
		$dragSequence = $this->_request->getParameter("drag_sequence");
		$dropSequence = $this->_request->getParameter("drop_sequence");

		$params = array(
			$this->_request->getParameter("registration_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {registration_item} ".
					"SET item_sequence = item_sequence + 1 ".
					"WHERE registration_id = ? ".
					"AND item_sequence < ? ".
					"AND item_sequence > ?";
        } else {
        	$sql = "UPDATE {registration_item} ".
					"SET item_sequence = item_sequence - 1 ".
					"WHERE registration_id = ? ".
					"AND item_sequence > ? ".
					"AND item_sequence <= ?";
        }

		$result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		if ($dragSequence > $dropSequence) {
			$dropSequence++;
		}
		$params = array(
			$dropSequence,
			$this->_request->getParameter("drag_item_id")
		);

    	$sql = "UPDATE {registration_item} ".
				"SET item_sequence = ? ".
				"WHERE item_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 入力項目データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setData()
	{
		$registrationID = $this->_request->getParameter("registration_id");

		$params = array(
			"registration_id" => $registrationID
		);

		$dataID = $this->_db->insertExecute("registration_data", $params, true, "data_id");
		if (empty($dataID)) {
			return false;
		}
		$this->_request->setParameter('dataID', $dataID);

		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$session =& $container->getComponent("Session");
		$entryDatas =& $session->getParameter("registration_entry_datas". $this->_request->getParameter("block_id"));
		foreach ($entryDatas as $entryData) {
			$itemID = $entryData["item_id"];
			if ($entryData["item_type"] == REGISTRATION_TYPE_FILE) {
				if (!empty($entryData["item_data_value"]["upload_id"])) {
					$uploadID = $entryData["item_data_value"]["upload_id"];
					$itemDataValue = "?". ACTION_KEY. "=". $entryData["item_data_value"]["action_name"]. "&upload_id=". $uploadID;
					if (!$uploadsAction->updGarbageFlag($uploadID)) {
						return false;
					}
				} else {
					$itemDataValue = "";
				}

			} elseif ($entryData["item_type"] == REGISTRATION_TYPE_CHECKBOX && is_array($entryData["item_data_value"])) {
				$itemDataValue = implode(REGISTRATION_OPTION_SEPARATOR, $entryData["item_data_value"]);

			} elseif ($entryData["item_type"] == REGISTRATION_TYPE_EMAIL) {
				$itemDataValue = $entryData["item_data_value"]["first"];

			} else {
				$itemDataValue = $entryData["item_data_value"];

			}

			$params = array(
				"registration_id" => $registrationID,
				"item_id" => $entryData["item_id"],
				"data_id" => $dataID,
				"item_data_value" => $itemDataValue
			);

			$itemDataID = $this->_db->insertExecute("registration_item_data", $params, true, "item_data_id");
			if (empty($itemDataID)) {
				return false;
			}

			if ($entryData["item_type"] != REGISTRATION_TYPE_FILE ||
					empty($entryData["item_data_value"]["upload_id"])) {
				continue;
			}

			$params = array(
				"item_data_id" => $itemDataID,
				"upload_id" => $entryData["item_data_value"]["upload_id"],
				"file_name" => $entryData["item_data_value"]["file_name"],
				"room_id" => $this->_request->getParameter("room_id")
			);

			if (!$this->_db->insertExecute("registration_file", $params)) {
				return false;
			}
		}

		$registration = $this->_request->getParameter("registration");
		if ($registration["mail_send"] == _ON) {
			$session->setParameter("registration_mail_data_id", $dataID);
		}

		return true;
	}

	/**
	 * 入力データを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteData()
	{
		$dataID = $this->_request->getParameter("data_id");

		$params = array(
			"registration_id" => $this->_request->getParameter("registration_id")
		);
		$where = "WHERE ID.registration_id = ? ";
		if (!empty($dataID)) {
			$params["data_id"] = $dataID;
			$where .= "AND ID.data_id = ?";
		}

    	$sql = $this->_getFileSQL(). $where;
		$files = $this->_db->execute($sql, $params);
		if ($files === false) {
			$this->_db->addError();
			return false;
		}
		if (!$this->deleteFile($files)) {
			return false;
		}

    	if (!$this->_db->deleteExecute("registration_item_data", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("registration_data", $params)) {
    		return false;
    	}

		return true;
	}
}
?>
