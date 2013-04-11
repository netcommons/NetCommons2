<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新着登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Action
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
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * @var commonMainオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_commonMain = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Whatsnew_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
        $this->_request =& $this->_container->getComponent("Request");
        $this->_commonMain =& $this->_container->getComponent("commonMain");
        $this->_modulesView =& $this->_container->getComponent("modulesView");
	}

	/**
	 * 新着情報の新規
	 *
	 * @access	public
	 */
	function _default(&$whatsnew, $noblock=0)
	{
		if (!isset($whatsnew["module_id"])) {
			$whatsnew["module_id"] = $this->_request->getParameter("module_id");
		}
		if (!isset($whatsnew["user_id"])) {
			$whatsnew["user_id"] = 0;
		}
		if (!isset($whatsnew["authority_id"])) {
			$whatsnew["authority_id"] = _AUTH_GUEST;
		}
		if (!isset($whatsnew["unique_id"])) {
			$whatsnew["unique_id"] = 0;
		}
		if (!isset($whatsnew["action_name"])) {
			$whatsnew["action_name"] = DEFAULT_ACTION;
		}
		if (isset($whatsnew["description"])) {
	   		$convertHtml =& $this->_commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
			$description = $convertHtml->convertHtmlToText($whatsnew["description"]);
	    	$whatsnew["description"] = trim(preg_replace("/\\\n/", " ", $description));
		}
		if (!empty($whatsnew["title"])) {
		} elseif (!empty($whatsnew["description"])) {
			$whatsnew["title"] = mb_substr($whatsnew["description"], 0, _SEARCH_SUBJECT_LEN, INTERNAL_CODE);
		} else {
			$whatsnew["title"] = _SEARCH_SUBJECT_NONEXISTS;
		}
		if (!isset($whatsnew["count_num"])) {
			$whatsnew["count_num"] = 0;
		}
		if(isset($whatsnew["child_update_time"])) {
			$time = $whatsnew['child_update_time'];
		} else {
			$time = timezone_date();
		}
		if (isset($whatsnew["insert_time"])) {
			$whatsnew["update_time"] = $whatsnew["insert_time"];
		} else {
			$whatsnew["update_time"] = $time;
		}
		////$whatsnew["child_update_time"] = $time;
		if(isset($whatsnew["child_flag"])) {
			unset($whatsnew["child_flag"]);
		}

		if (empty($whatsnew["parameters"])) {
			$whatsnew["parameters"] = "";
		} elseif ($noblock == _OFF) {
			$whatsnew["parameters"] .= "&";
		}
		if ($noblock == _OFF) {
			$block_id = $this->_request->getParameter("block_id");

			$_id = $this->_session->getParameter("_id");
			$id = $this->_commonMain->getTopId($block_id, $whatsnew["module_id"], "");
			$this->_session->setParameter("_id", $_id);

			$whatsnew["parameters"] .= "block_id=".$block_id."#".$id;
		}
		return true;
	}

	/**
	 * 新着情報の新規
	 *
	 * @access	public
	 */
	function auto(&$whatsnew, $noblock=0)
	{
    	$module_whatsnew =& $this->_modulesView->getModuleByDirname("whatsnew");
		if (!$module_whatsnew) { return true; }

		$result = $this->_db->selectExecute("whatsnew", array("module_id"=>$this->_request->getParameter("module_id"), "unique_id"=>$whatsnew["unique_id"]),null, 1);
		if ($result === false) {
			return false;
		}
		if (count($result) > 0) {
			$result = $this->update($whatsnew, $noblock);
		} else {
			$result = $this->insert($whatsnew, $noblock);
		}
	}

	/**
	 * 新着情報の新規
	 *
	 * @access	public
	 */
	function insert(&$whatsnew, $noblock=0)
	{
    	$module_whatsnew =& $this->_modulesView->getModuleByDirname("whatsnew");
		if (!$module_whatsnew) { return true; }

        $site_id = $this->_session->getParameter("_site_id");
        $user_id = $this->_session->getParameter("_user_id");
        $user_name = $this->_session->getParameter("_handle");

		if (!isset($whatsnew["insert_user_id"])) {
			$whatsnew["insert_user_id"] = $user_id;
		}
		if (!isset($whatsnew["insert_user_name"])) {
			$whatsnew["insert_user_name"] = $user_name;
		}
		$whatsnew["update_user_id"] = $whatsnew["insert_user_id"];
		$whatsnew["update_user_name"] = $whatsnew["insert_user_name"];

		$result = $this->_default($whatsnew, $noblock);
		if(!isset($whatsnew["child_update_time"])) {
			$whatsnew["child_update_time"] = $whatsnew["update_time"];
		}

		$whatsnew["insert_time"] = $whatsnew["update_time"];

		if ($result === false) {
			return false;
		}

		$params = array(
			"room_id" => 0,
			"module_id" => $whatsnew["module_id"],
			"user_id" => $whatsnew["user_id"],
			"authority_id" => $whatsnew["authority_id"],
			"unique_id" => $whatsnew["unique_id"],
			"title" => $whatsnew["title"],
			"description" => $whatsnew["description"],
			"action_name" => $whatsnew["action_name"],
			"parameters" => $whatsnew["parameters"],
			"count_num" => $whatsnew["count_num"],
			"child_update_time" => $whatsnew["child_update_time"],
			"insert_time" => $whatsnew["insert_time"],
			"insert_site_id" => $site_id,
			"insert_user_id" => $whatsnew["insert_user_id"],
			"insert_user_name" => $whatsnew["insert_user_name"],
			"update_time" => $whatsnew["update_time"],
			"update_site_id" => $site_id,
			"update_user_id" => $whatsnew["update_user_id"],
			"update_user_name" => $whatsnew["update_user_name"]
		);

		if (isset($whatsnew["room_id"]) && is_array($whatsnew["room_id"])) {
			foreach ($whatsnew["room_id"] as $i=>$room_id) {
				$params["room_id"] = $room_id;
				$result = $this->_db->insertExecute("whatsnew", $params, false, "whatsnew_id");
				if ($result === false) {
					return false;
				}
			}
		} else {
			if (!isset($whatsnew["room_id"])) {
				$whatsnew["room_id"] = $this->_request->getParameter("room_id");
			}
			$params["room_id"] = $whatsnew["room_id"];
			$result = $this->_db->insertExecute("whatsnew", $params, false, "whatsnew_id");
			if ($result === false) {
				return false;
			}
		}
		return $this->_deletePeriod($whatsnew["module_id"], $whatsnew["room_id"]);
	}

	/**
	 * 新着情報の変更
	 *
	 * @access	public
	 */
	function update(&$whatsnew, $noblock=0)
	{
    	$module_whatsnew =& $this->_modulesView->getModuleByDirname("whatsnew");
		if (!$module_whatsnew) { return true; }

        $site_id = $this->_session->getParameter("_site_id");
        $user_id = $this->_session->getParameter("_user_id");
        $user_name = $this->_session->getParameter("_handle");
		if(!isset($user_name)) {
        	$user_name = "";
        }
		if (!isset($whatsnew["update_user_id"])) {
			$whatsnew["update_user_id"] = $user_id;
		}
		if (!isset($whatsnew["update_user_name"])) {
			$whatsnew["update_user_name"] = $user_name;
		}

		$default = array_merge(array(), $whatsnew);
		$result = $this->_default($default, $noblock);
		if ($result === false) {
			return false;
		}

		$params = array();
		$params["room_id"] = 0;
		if (!empty($whatsnew["module_id"])) {
			$params["module_id"] = $whatsnew["module_id"];
		}
		if (!empty($whatsnew["user_id"])) {
			$params["user_id"] = $whatsnew["user_id"];
		}
		if (!empty($whatsnew["authority_id"])) {
			$params["authority_id"] = $whatsnew["authority_id"];
		}
		if (isset($whatsnew["title"])) {
			if (!empty($whatsnew["title"])) {
				$params["title"] = $default["title"];
			} elseif (!empty($whatsnew["description"])) {
				$params["title"] = mb_substr($default["description"], 0, _SEARCH_SUBJECT_LEN, INTERNAL_CODE);
			} else {
				$params["title"] = _SEARCH_SUBJECT_NONEXISTS;
			}
		}
		if (isset($whatsnew["description"])) {
			$params["description"] = $default["description"];
		}
		if (isset($whatsnew["action_name"])) {
			$params["action_name"] = $default["action_name"];
		}
		if (isset($whatsnew["parameters"])) {
			$params["parameters"] = $default["parameters"];
		}

		if(isset($whatsnew["count_num"])){
			$params["count_num"] = $whatsnew["count_num"];
		}
		if(isset($whatsnew["insert_time"])){
			$params["insert_time"] = $whatsnew["insert_time"];
		}
		if (isset($whatsnew["insert_user_id"])) {
			$params["insert_user_id"] = $whatsnew["insert_user_id"];
		}
		if (isset($whatsnew["insert_user_name"])) {
			$params["insert_user_name"] = $whatsnew["insert_user_name"];
		}
		if(isset($default["child_update_time"])){
			$params["child_update_time"] = $default["child_update_time"];
		}
		$params["update_time"] = $default["update_time"];
		$params["update_site_id"] = $site_id;
		$params["update_user_id"] = $default["update_user_id"];
		$params["update_user_name"] = $default["update_user_name"];

		if (isset($whatsnew["room_id"]) && is_array($whatsnew["room_id"])) {
			foreach ($whatsnew["room_id"] as $i=>$room_id) {
				$params["room_id"] = $room_id;
				$result = $this->_db->updateExecute("whatsnew", $params, array("module_id"=>$this->_request->getParameter("module_id"), "unique_id"=>$whatsnew["unique_id"]));
				if ($result === false) {
					return false;
				}
			}
		} else {
			if (!isset($whatsnew["room_id"])) {
				$whatsnew["room_id"] = $this->_request->getParameter("room_id");
			}
			$params["room_id"] = $whatsnew["room_id"];
			$result = $this->_db->updateExecute("whatsnew", $params, array("module_id"=>$this->_request->getParameter("module_id"), "unique_id"=>$whatsnew["unique_id"]));
			if ($result === false) {
				return false;
			}
		}
		return $this->_deletePeriod($this->_request->getParameter("module_id"), $whatsnew["room_id"]);
	}

	/**
	 * 新着情報の変更
	 *
	 * @access	public
	 */
	function moveUpdate(&$whatsnew)
	{
		$module_whatsnew =& $this->_modulesView->getModuleByDirname("whatsnew");
		if (!$module_whatsnew) { return true; }

		if (!isset($whatsnew["unique_id"])) { return true; }

		if (!isset($whatsnew["room_id"])) {
			$whatsnew["room_id"] = $this->_request->getParameter("room_id");
		}
		$params["room_id"] = $whatsnew["room_id"];

		$where_params = array();
		if (is_array($whatsnew["unique_id"])) {
			$where_str = implode("','", $whatsnew["unique_id"]);
			$where_params = array("module_id"=>$this->_request->getParameter("module_id"), "unique_id IN ('".$where_str."')"=>null);
		} else {
			$where_params = array("module_id"=>$this->_request->getParameter("module_id"), "unique_id"=>$whatsnew["unique_id"]);
		}
		$result = $this->_db->updateExecute("whatsnew", $params, $where_params);
		if ($result === false) {
			return false;
		}
		return true;
	}

	/**
	 * 新着情報の削除
	 *
	 * @access	public
	 */
	function delete($unique_id, $child_flag = 0)
	{
		$module_id = $this->_request->getParameter("module_id");
    	$module_whatsnew =& $this->_modulesView->getModuleByDirname("whatsnew");
		if (!$module_whatsnew) { return true; }

		$sql = "SELECT whatsnew_id, insert_time FROM {whatsnew}" .
				" WHERE module_id=?" .
				" AND unique_id=?";

		$params = array("module_id"=>$module_id, "unique_id"=>$unique_id);
    	$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_deleteUser"));
		if ($result === false) {
			return false;
		}
		if($child_flag == _OFF) {
			$result = $this->_db->deleteExecute("whatsnew", array("module_id"=>$module_id, "unique_id"=>$unique_id));
		} elseif (isset($result[0])) {
			$result = $this->_db->updateExecute("whatsnew", array("child_update_time" => $result[0]['insert_time'], "count_num" => 0), array("module_id"=>$module_id, "unique_id"=>$unique_id));
		}
		return true;
	}

	/**
	 * 新着情報の削除
	 *
	 * @access	public
	 */
	function _deleteUser(&$recordSet)
	{
		$ret = array();
		while ($row = $recordSet->fetchRow()) {
			$result = $this->_db->deleteExecute("whatsnew_user", array("whatsnew_id"=>$row["whatsnew_id"]));
			if ($result === false) {
				return false;
			}
			$ret[] = $row;
		}
		return $ret;
	}

	/**
	 * ルーム、モジュール毎で、指定件数以上の古いデータを削除する
	 * 
	 * @param string $moduleId モジュールID
	 * @param mixed $roomIds ルームID、ルームID配列
	 * @return boolean true or false
	 * @access	public
	 */
	function _deletePeriod($moduleId, $roomIds)
	{
		if (rand(0, 10) != 0) { return true; }

		$modulesView =& $this->_container->getComponent('modulesView');
		$module = $modulesView->getModuleByDirname('whatsnew');

		$configView =& $this->_container->getComponent('configView');
		$config = $configView->getConfig($module['module_id'], false);
		if ($config === false) {
			return false;
		}
		$period = $config['whatsnew_period']['conf_value'];
		$maximumNumber = $config['whatsnew_delete_number']['conf_value'];

		$periodTime = timezone_date();
		$periodTime = mktime(intval(substr($periodTime, 8, 2)),
							intval(substr($periodTime, 10, 2)),
							intval(substr($periodTime, 12, 2)),
							intval(substr($periodTime, 4, 2)),
							intval(substr($periodTime, 6, 2)) - intval($period),
							intval(substr($periodTime,0,4)));
		$periodTime = date('YmdHis', $periodTime);

		if (!is_array($roomIds)) {
			$roomIds = array(
				$roomIds
			);
		}

		$sql = "SELECT room_id, COUNT(*) count "
				. "FROM {whatsnew} "
				. "WHERE room_id IN ('" . implode("','", $roomIds) . "') "
				. "AND module_id = ? "
				. "GROUP BY room_id "
				. "HAVING count > ?";
		$bindValues = array(
			$moduleId,
			$maximumNumber
		);
		$whatsnews = $this->_db->execute($sql, $bindValues);

		$inValue = '';
		foreach($whatsnews as $whatsnew){
			$sql = "SELECT whatsnew_id "
					. "FROM {whatsnew} "
					. "WHERE insert_time < ? "
					. "AND child_update_time < ? "
					. "AND module_id = ? "
					. "AND room_id = ? "
					. "ORDER BY child_update_time";
			$bindValues = array(
				$periodTime,
				$periodTime,
				$moduleId,
				$whatsnew['room_id']
			);
			$oldWhatsnews = $this->_db->execute($sql, $bindValues);

			$deleteNumber = $whatsnew['count'] - $maximumNumber;
			foreach ($oldWhatsnews as $oldWhatsnew) {
				$inValue .= $oldWhatsnew['whatsnew_id'] . ',';
				$deleteNumber--;
				if ($deleteNumber <= 0) {
					break;
				}
			}
		}				

		if (empty($inValue)) {
			return true;
		}
		$inValue = substr($inValue, 0, -1);

		if (!$this->_deleteByInOperator('whatsnew_user', $inValue)) {
			return false;
		}
		if (!$this->_deleteByInOperator('whatsnew', $inValue)) {
			return false;
		}

		return true;
	}

	/**
	 * 条件に該当する新着データを削除する。
	 * 
	 * @param string $whereClause where句文字列
	 * @param array $bindValues バインド値配列
	 * @return boolean true or false
	 * @access	public
	 */
	function deleteByWhereClause($whereClause, $bindValues)
	{
		$module =& $this->_modulesView->getModuleByDirname('whatsnew');
		if (!$module) {
			return true;
		}

		$sql = "SELECT whatsnew_id "
				. "FROM {whatsnew} "
				. "WHERE " . $whereClause;
		$inValue = $this->_db->execute($sql, $bindValues, null, null, false, array($this, '_createDelimitedString'));
		if ($inValue === false) {
			$this->_db->addError();
			return false;
		}

		if (!$this->_deleteByInOperator('whatsnew_user', $inValue)) {
			return false;
		}
		if (!$this->_deleteByInOperator('whatsnew', $inValue)) {
			return false;
		}

		return true;
	}

	/**
	 * ADORecordSetの1カラム目（ID）を指定文字区切りの文字列にする
	 * 
	 * @param object $recordSet ADORecordSetオブジェクト
	 * @param string $glue 区切り文字
	 * @return string 指定文字区切りの文字列
	 * @access private
	 */
	function &_createDelimitedString(&$recordSet, $glue = ',')
	{
		$string = '';
		while ($whatsnew = $recordSet->fetchRow()) {
			$string .= $whatsnew[0]. $glue;
		}
		if (strlen($string)
				&& strlen($glue)) {
			$string = substr($string, 0, strlen($glue) * -1);
		}

		return $string;
	}

	/**
	 * IN演算子でデータを削除する。
	 *
	 * @param string $tableName 対象テーブル名称
	 * @param string $inValue IN演算子の値（カンマ区切り文字列）
	 * @return boolean true or false
	 * @access public
	 */
	function _deleteByInOperator($tableName, $inValue)
	{
		if (!strlen($inValue)) {
			return true;
		}

		$sql = "DELETE FROM {" . $tableName . "} "
				. "WHERE whatsnew_id IN (" . $inValue . ")";
		if (!$this->_db->execute($sql)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}
}
?>