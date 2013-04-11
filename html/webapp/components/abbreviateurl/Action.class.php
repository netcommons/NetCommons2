<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * URL短縮用登録クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Abbreviateurl_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var Sessionオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_session = null;

	/**
	 * @var abbreviateurlViewオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_abbreviateurlView = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Abbreviateurl_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_abbreviateurlView =& $this->_container->getComponent("abbreviateurlView");
	}

	/**
	 * abbreviate_urlへ登録処理
	 *
	 * @param string $dir_name
	 * @param string $contents_id
	 * @param string $unique_id
	 * @param string $url
	 * @param string $room_id
	 *
	 * @return boolean
	 * @access  public
	 */
	function setAbbreviateUrl($contents_id, $unique_id, $dir_name=null, $module_id=null, $room_id=null)
	{
		//dir_nameが省略されている場合、実行アクションから取得
		if (!isset($dir_name)) {
			$dir_name = $this->_abbreviateurlView->getDefaultUniqueKey();
		}
		
		//module_idが省略されている場合、module_nameから取得
		$dirnameArray = explode("_", $dir_name);
		$module_name = $dirnameArray[0];
		if (empty($module_id)) {
			$module_id = $this->_abbreviateurlView->getDefaultModuleId($module_name);
		}

		//URL短縮形の重複チェック
		$params = array(
			'dir_name' => $dir_name,
			'unique_id' => $unique_id
		);
		$abbreviate = $this->_db->selectExecute('abbreviate_url', $params, null, 1);
		if ($abbreviate === false) {
			return $abbreviate;
		}
		
		if (empty($abbreviate)) {
			//登録する短縮URLの重複チェック
			$prefix = (count($dirnameArray) > 1 ? substr($dirnameArray[0],0,1).substr($dirnameArray[1],0,1) : substr($dirnameArray[0], 0, 2));
			for ($length=_ABBREVIATE_URL_LENGTH; $length<17; $length++) {
				for ($j=0; $j<50; $j++) {
					$short_url = $this->_abbreviateurlView->randString($length, $prefix);
					$params = array(
						"short_url" => $short_url
					);
					$countUrl = $this->_db->countExecute('abbreviate_url', $params);
					if ($countUrl === false) {
						return $countUrl;
					}
					if ($countUrl > 0) { continue; }
	
					$params = array(
						"permalink" => $short_url
					);
					$countPage = $this->_db->countExecute('pages', $params);
					if ($countPage === false) {
						return $countPage;
					}
					if ($countPage > 0) { continue; }
					
					break;
				}
				if ($countUrl == 0 && $countPage == 0) {
					break;
				}
			}
			if ($countUrl > 0 || $countPage > 0) {
				$result = false;
				return $result;
			}
			//URL短縮形の登録
			$setParams = array(
				'short_url' => $short_url,
				'dir_name' => $dir_name,
				'module_id' => $module_id,
				'contents_id' => $contents_id,
				'unique_id' => $unique_id
			);
			if (isset($room_id)) {
				$setParams['room_id'] = $room_id;
			}
			$result = $this->_db->insertExecute('abbreviate_url', $setParams, true);
			if ($result === false) {
				return $result;
			}
		}

		$result = true;
		return $result;
	}

	/**
	 * abbreviate_urlから削除処理
	 *
	 * @param string $dir_name
	 * @param string $unique_id
	 *
	 * @return boolean
	 * @access  public
	 */
	function deleteUrl($unique_id, $dir_name=null)
	{
		//dir_nameが省略されている場合、実行アクションから取得
		if (!isset($dir_name)) {
			$dir_name = $this->_abbreviateurlView->getDefaultUniqueKey();
		}

		$params = array(
			'dir_name' => $dir_name,
			'unique_id' => $unique_id
		);
		$result = $this->_db->deleteExecute('abbreviate_url', $params);
		if ($result === false) {
			return $result;
		}
		return $result;
	}

	/**
	 * abbreviate_urlから削除処理
	 *
	 * @param string $dir_name
	 * @param string $contents_id
	 *
	 * @return boolean
	 * @access  public
	 */
	function deleteUrlByContents($contents_id, $dir_name=null)
	{
		//dir_nameが省略されている場合、実行アクションから取得
		if (!isset($dir_name)) {
			$dir_name = $this->_abbreviateurlView->getDefaultUniqueKey();
		}

		$params = array(
			'dir_name' => $dir_name,
			'contents_id' => $contents_id
		);
		$result = $this->_db->deleteExecute('abbreviate_url', $params);
		if ($result === false) {
			return $result;
		}
		return $result;
	}

	/**
	 * abbreviate_urlから削除処理
	 *
	 * @param string $dir_name
	 * @param string $room_id
	 *
	 * @return boolean
	 * @access  public
	 */
	function deleteUrlByRoom($room_id, $dir_name=null)
	{
		//dir_nameが省略されている場合、実行アクションから取得
		if (!isset($dir_name)) {
			$dir_name = $this->_abbreviateurlView->getDefaultUniqueKey();
		}

		$params = array(
			'dir_name' => $dir_name,
			'room_id' => $room_id
		);
		$result = $this->_db->deleteExecute('abbreviate_url', $params);
		if ($result === false) {
			return $result;
		}
		return $result;
	}

	/**
	 * abbreviate_urlから削除処理
	 *
	 * @param string $dir_name
	 * @param string $unique_id
	 *
	 * @return boolean
	 * @access  public
	 */
	function moveRoom($contents_id, $org_room_id, $move_room_id)
	{
		if ($org_room_id == $move_room_id) {
			return true;
		}
		$dir_name = $this->_abbreviateurlView->getDefaultUniqueKey();

		$dirnameArray = explode("_", $dir_name);
		$module_name = $dirnameArray[0];
		$module_id = $this->_abbreviateurlView->getDefaultModuleId($module_name);

		$where_params = array(
			"module_id"=> intval($module_id),
			"contents_id"=> intval($contents_id),
			"room_id"=> intval($org_room_id)
		);

		$params = array(
			"room_id"=> intval($move_room_id)
		);
		$result = $this->_db->updateExecute("abbreviate_url", $params, $where_params, false);
		if ($result === false) {
			return false;
		}

		return true;
	}

	/**
	 * 条件に該当する短縮URLデータを削除する。
	 * 
	 * @param string $whereClause where句文字列
	 * @param array $bindValues バインド値配列
	 * @return boolean true or false
	 * @access	public
	 */
	function deleteByWhereClause($whereClause, $bindValues)
	{
		$sql = "DELETE FROM {abbreviate_url} "
				. "WHERE " . $whereClause;
		if (!$this->_db->execute($sql, $bindValues)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}
}
?>
