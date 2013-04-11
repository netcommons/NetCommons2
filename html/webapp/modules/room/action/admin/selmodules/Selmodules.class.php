<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 使用可能モジュール配置修正
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Action_Admin_Selmodules extends Action
{
	// リクエストパラメータを受け取るため
	var $edit_current_page_id = null;
	var $parent_page_id = null;
	
	var $enroll_modules = null;
	
	// バリデートによりセット
	var $parent_page = null;
	var $page = null;
		
	// 使用コンポーネントを受け取るため
	var $pagesView = null;
	var $pagesAction = null;
	var $modulesView = null;
	var $blocksView = null;
	var $blocksAction = null;
	var $preexecuteMain = null;
	var $db = null;
	var $request = null;
	
	//値をセットするため
	
	/**
	 * 使用可能モジュール配置修正
	 *
	 * @access  public
	 */
	function execute()
	{
		$roomId = $this->edit_current_page_id;
		$usableModuleIds = $this->enroll_modules;
		if (!isset($usableModuleIds)) {
			$usableModuleIds = array();
		}
		$previousUsableModules = $this->pagesView->getUsableModulesByRoom($roomId, true);

		$isError = false;
		foreach ($usableModuleIds as $moduleId) {
			if (isset($previousUsableModules[$moduleId])) {
				unset($previousUsableModules[$moduleId]);
				continue;
			}

			$insertDatas = array(
				'room_id' => $roomId,
				'module_id' => $moduleId
			);

			if (!$this->pagesAction->insPagesModulesLink($insertDatas)) {
				$isError = true;
			}
		}

		if (!$this->pagesAction->deleteEachModule($roomId, $previousUsableModules)) {
			$isError = true;
		}

		$unusableModuleIds = array_keys($previousUsableModules);
		if (!$this->pagesAction->deleteRoomModule($roomId, $unusableModuleIds)) {
			$isError = true;
		}

		$sql = "SELECT B.block_id, "
					. "B.page_id "
				. "FROM {blocks} B "
				. "INNER JOIN {pages} P "
					. "ON B.page_id = P.page_id "
				. "WHERE P.room_id = ? "
				. "AND B.module_id IN ('" . implode("','", $unusableModuleIds) . "')";
		$blocks =& $this->db->execute($sql, $roomId);
		if ($blocks === false) {
			$this->db->addError();
		}

		foreach($blocks as $block) {
			$request = array(
				'block_id' => $block['block_id'],
				'page_id' => $block['page_id']
			);
			$result = $this->preexecuteMain->preExecute('pages_actionblock_deleteblock', $request);
			if ($result === false
					|| $result === 'false') {
				$isError = true;
			}
		}

		if($isError) {
			return 'error';
		}

		// ----------------------------------------------------------------------
		// --- 終了処理 ---
		// ----------------------------------------------------------------------
		// リスト表示のリクエストパラメータセット
		if(!isset($this->parent_page)) {
			$this->request->setParameter("show_space_type", $this->page['space_type']);
			$this->request->setParameter("show_private_flag", $this->page['private_flag']);
			$this->request->setParameter("show_default_entry_flag", $this->page['default_entry_flag']);
		} else {
			$this->request->setParameter("show_space_type", $this->parent_page['space_type']);
			$this->request->setParameter("show_private_flag", $this->parent_page['private_flag']);
			$this->request->setParameter("show_default_entry_flag", $this->parent_page['default_entry_flag']);
		}
		
		return 'success';
	}
}
?>
