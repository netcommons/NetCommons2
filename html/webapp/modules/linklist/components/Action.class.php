<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンクリスト登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Components_Action
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
	function Linklist_Components_Action()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * リンクリストデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setLinklist()
	{
		$params = array(
			"linklist_name" => $this->_request->getParameter("linklist_name"),
			"category_authority" => intval($this->_request->getParameter("category_authority")),
			"link_authority" => intval($this->_request->getParameter("link_authority"))
		);

		$linklistID = $this->_request->getParameter("linklist_id");
		if (empty($linklistID)) {
			$params["room_id"] = intval($this->_request->getParameter("room_id"));
			$result = $this->_db->insertExecute("linklist", $params, true, "linklist_id");
		} else {
			$params["linklist_id"] = $linklistID;
			$result = $this->_db->updateExecute("linklist", $params, "linklist_id", true);
		}
		if (!$result) {
			return false;
		}

		if (!empty($linklistID)) {
        	return true;
        }

		$linklistID = $result;
		$this->_request->setParameter("linklist_id", $linklistID);
        if (!$this->setBlock()) {
			return false;
		}

        if (!$this->setCategory()) {
			return false;
		}

		return true;
	}

	/**
	 * リンクリストデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteLinklist()
	{
		$params = array(
			"linklist_id" => $this->_request->getParameter("linklist_id")
		);

    	if (!$this->_db->deleteExecute("linklist_block", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("linklist_link", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("linklist_category", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("linklist", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * リンクリスト用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {linklist_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"linklist_id" => $this->_request->getParameter("linklist_id")
		);

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($blockIDs)
				&& $actionName == "linklist_action_edit_current") {
			if (!$this->_db->updateExecute("linklist_block", $params, "block_id", true)) {
				return false;
			}

			return true;
		}
		if ($actionName == "linklist_action_edit_current") {
			$linklistView =& $container->getComponent("linklistView");
			$linklist = $linklistView->getDefaultLinklist();
		}
		if ($actionName == "linklist_action_edit_entry") {
			$linklist = $this->_request->getParameter("linklist");
		}
		if (!empty($linklist)) {
			$this->_request->setParameter("display", $linklist["display"]);
			$this->_request->setParameter("target_blank_flag", $linklist["target_blank_flag"]);
			$this->_request->setParameter("view_count_flag", $linklist["view_count_flag"]);
			$this->_request->setParameter("line", $linklist["line"]);
			$this->_request->setParameter("mark", $linklist["mark"]);
		} else {
			$display = intval($this->_request->getParameter("display"));
			if ($display == LINKLIST_DISPLAY_LIST &&
					$this->_request->getParameter("display_description") == _ON) {
				$display = LINKLIST_DISPLAY_DESCRIPTION;
			}
			$this->_request->setParameter("display", $display);
		}

		$params["display"] = intval($this->_request->getParameter("display"));
		$params["target_blank_flag"] =  intval($this->_request->getParameter("target_blank_flag"));
		$params["view_count_flag"] = intval($this->_request->getParameter("view_count_flag"));
		$params["line"] = $this->_request->getParameter("line");
		$params["mark"] = $this->_request->getParameter("mark");

		if (!empty($blockIDs)) {
			$result = $this->_db->updateExecute("linklist_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("linklist_block", $params, true);
		}
        if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * カテゴリデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setCategory()
	{
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();

		$categoryID = $this->_request->getParameter("category_id");

		if ($actionName == "linklist_action_edit_entry") {
			$container =& DIContainerFactory::getContainer();
			$filterChain =& $container->getComponent("FilterChain");
			$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");

			$params = array(
				"linklist_id" => $this->_request->getParameter("linklist_id"),
				"category_name" => $smartyAssign->getLang("linklist_category_none"),
				"category_sequence" => 1,
				"default_flag" => _ON
			);
		} elseif (empty($categoryID)) {
			$params = array($this->_request->getParameter("linklist_id"));
			$sql = "SELECT MAX(category_sequence) ".
					"FROM {linklist_category} ".
					"WHERE linklist_id = ?";
			$sequences = $this->_db->execute($sql, $params, null, null, false);
			if ($sequences === false) {
				$this->_db->addError();
				return false;
			}

			$params = array(
				"linklist_id" => $this->_request->getParameter("linklist_id"),
				"category_name" => $this->_request->getParameter("category_name"),
				"category_sequence" => $sequences[0][0] + 1,
				"default_flag" => _OFF
			);
		} else {
			$params = array(
				"category_id" => $categoryID,
				"category_name" => $this->_request->getParameter("category_name"),
			);
		}


		$linklistID = $this->_request->getParameter("linklist_id");
		if (empty($params["category_id"])) {
			$result = $this->_db->insertExecute("linklist_category", $params, true, "category_id");
		} else {
			$result = $this->_db->updateExecute("linklist_category", $params, "category_id", true);
		}
		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * カテゴリ番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateCategorySequence()
	{
		$dragSequence = $this->_request->getParameter("drag_sequence");
		$dropSequence = $this->_request->getParameter("drop_sequence");

		$params = array(
			$this->_request->getParameter("linklist_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {linklist_category} ".
					"SET category_sequence = category_sequence + 1 ".
					"WHERE linklist_id = ? ".
					"AND category_sequence < ? ".
					"AND category_sequence > ?";
        } else {
        	$sql = "UPDATE {linklist_category} ".
					"SET category_sequence = category_sequence - 1 ".
					"WHERE linklist_id = ? ".
					"AND category_sequence > ? ".
					"AND category_sequence <= ?";
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
			$this->_request->getParameter("drag_category_id")
		);

    	$sql = "UPDATE {linklist_category} ".
				"SET category_sequence = ? ".
				"WHERE category_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * カテゴリデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteCategory()
	{
		$params = array(
			"category_id" => $this->_request->getParameter("category_id")
		);
    	if (!$this->_db->deleteExecute("linklist_link", $params)) {
    		return false;
    	}

    	$sql = "SELECT category_sequence ". 
				"FROM {linklist_category} ".
				"WHERE category_id = ?";
		$sequences = $this->_db->execute($sql, $params, 1, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return false;
		}
		$sequence = $sequences[0][0];

    	if (!$this->_db->deleteExecute("linklist_category", $params)) {
    		return false;
    	}
    	
		$params = array(
			"linklist_id" => $this->_request->getParameter("linklist_id"),
		);
		$sequenceParam = array(
			"category_sequence" => $sequence
		);
		if (!$this->_db->seqExecute("linklist_category", $params, $sequenceParam)) {
			return false;
		}
		
		return true;
	}

	/**
	 * リンクデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setLink()
	{
		$linkID = $this->_request->getParameter("link_id");
		$title = $this->_request->getParameter("title");
		$url = $this->_request->getParameter("url");
		$description = $this->_request->getParameter("description");

		if (empty($linkID)) {
			$params = array(
				"linklist_id" => $this->_request->getParameter("linklist_id"),
				"category_id" => $this->_request->getParameter("category_id")
			);
			$sql = "SELECT MAX(link_sequence) ".
					"FROM {linklist_link} ".
					"WHERE linklist_id = ? ".
					"AND category_id = ?";
			$sequences = $this->_db->execute($sql, $params, null, null, false);
			if ($sequences === false) {
				$this->_db->addError();
				return false;
			}

			$params["link_sequence"] = $sequences[0][0] + 1;
			$params["title"] = $title;
			$params["url"] = $url;
			$params["description"] = $description;

			$result = $this->_db->insertExecute("linklist_link", $params, true, "link_id");
		} else {
			$params = array(
				"link_id" => $linkID
			);
			if (isset($title)) {
				$params["title"] = $title;
			}
			if (isset($url)) {
				$params["url"] = $url;
			}
			if (isset($description)) {
				$params["description"] = $description;
			}

			$result = $this->_db->updateExecute("linklist_link", $params, "link_id", true);
		}

		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * リンクデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteLink()
	{
		$params = array(
			"link_id" => $this->_request->getParameter("link_id")
		);

		$sql = "SELECT category_id, link_sequence ". 
				"FROM {linklist_link} ".
				"WHERE link_id = ?";
		$sequences = $this->_db->execute($sql, $params, 1, null, false);
		if ($sequences === false) {
			$this->_db->addError();
			return false;
		}
		$categoryID = $sequences[0][0];
		$sequence = $sequences[0][1];

    	if (!$this->_db->deleteExecute("linklist_link", $params)) {
    		return false;
    	}
    	
		$params = array(
			"linklist_id" => $this->_request->getParameter("linklist_id"),
			"category_id" => $categoryID
		);
		$sequenceParam = array(
			"link_sequence" => $sequence
		);
		if (!$this->_db->seqExecute("linklist_link", $params, $sequenceParam)) {
			return false;
		}
		
		return true;
	}

	/**
	 * 参照数をインクリメントする
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function incrementViewCount()
	{
		$params = array(
			"link_id" => $this->_request->getParameter("link_id"),
			"linklist_id" => $this->_request->getParameter("linklist_id")
		);
		$sql = "UPDATE {linklist_link} ".
						"SET view_count = view_count + 1 ".
						"WHERE link_id = ? ".
						"AND linklist_id = ?";
		$result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * リンク番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateLinkSequence()
	{
		$drag = $this->_request->getParameter("drag");
		$drop = $this->_request->getParameter("drop");

		if ($drag["category_id"] == $drop["category_id"]) {
			$params = array(
				$this->_request->getParameter("linklist_id"),
				$drag["category_id"],
				$drag["link_sequence"],
				$drop["link_sequence"]
			);

	        if ($drag["link_sequence"] > $drop["link_sequence"]) {
	        	$sql = "UPDATE {linklist_link} ".
						"SET link_sequence = link_sequence + 1 ".
						"WHERE linklist_id = ? ".
						"AND category_id = ? ".
						"AND link_sequence < ? ".
						"AND link_sequence > ?";
	        } else {
	        	$sql = "UPDATE {linklist_link} ".
						"SET link_sequence = link_sequence - 1 ".
						"WHERE linklist_id = ? ".
						"AND category_id = ? ".
						"AND link_sequence > ? ".
						"AND link_sequence <= ?";
	        }

			$result = $this->_db->execute($sql, $params);
			if($result === false) {
				$this->_db->addError();
				return false;
			}

			if ($drag["link_sequence"] > $drop["link_sequence"]) {
				$drop["link_sequence"]++;
			}

			$params = array(
				$drop["link_sequence"],
				$drag["link_id"]
			);
	    	$sql = "UPDATE {linklist_link} ".
					"SET link_sequence = ? ".
					"WHERE link_id = ?";
	        $result = $this->_db->execute($sql, $params);
			if($result === false) {
				$this->_db->addError();
				return false;
			}
		} else {
			$params = array(
				$this->_request->getParameter("linklist_id"),
				$drop["category_id"],
				$drop["link_sequence"]
			);
			$sql = "UPDATE {linklist_link} ".
					"SET link_sequence = link_sequence + 1 ".
					"WHERE linklist_id = ? ".
					"AND category_id = ? ".
					"AND link_sequence > ?";
			$result = $this->_db->execute($sql, $params);
			if($result === false) {
				$this->_db->addError();
				return false;
			}

			$params = array(
				$this->_request->getParameter("linklist_id"),
				$drag["category_id"],
				$drag["link_sequence"]
			);
			$sql = "UPDATE {linklist_link} ".
					"SET link_sequence = link_sequence - 1 ".
					"WHERE linklist_id = ? ".
					"AND category_id = ? ".
					"AND link_sequence > ?";
			$result = $this->_db->execute($sql, $params);
			if($result === false) {
				$this->_db->addError();
				return false;
			}

			$drop["link_sequence"]++;
			$params = array(
				$drop["link_sequence"],
				$drop["category_id"],
				$drag["link_id"]
			);
			$sql = "UPDATE {linklist_link} ".
					"SET link_sequence = ?, ".
						"category_id = ? ".
					"WHERE link_id = ?";
			$result = $this->_db->execute($sql, $params);
			if($result === false) {
				$this->_db->addError();
				return false;
			}
		}

		return true;
	}
}
?>
