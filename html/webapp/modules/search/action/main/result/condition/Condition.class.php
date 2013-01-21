<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索条件
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Action_Main_Result_Condition extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $keyword = null;
	var $select_kind = null;
	var $handle = null;
	var $fm_target_date = null;
	var $to_target_date = null;
	var $target_room = null;
	var $target_modules = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	
	// 値をセットするため
	
    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		if ($this->target_modules == "" || $this->target_modules == null) {
			$this->target_modules = array();
		}
		$this->session->setParameter(array("search_select", $this->block_id, "keyword"), $this->keyword);
		$this->session->setParameter(array("search_select", $this->block_id, "select_kind"), $this->select_kind);
		$this->session->setParameter(array("search_select", $this->block_id, "handle"), $this->handle);
		$this->session->setParameter(array("search_select", $this->block_id, "fm_target_date"), $this->fm_target_date);
		$this->session->setParameter(array("search_select", $this->block_id, "to_target_date"), $this->to_target_date);
		$this->session->setParameter(array("search_select", $this->block_id, "target_room"), $this->target_room);
		$this->session->setParameter(array("search_select", $this->block_id, "target_modules"), $this->target_modules);
		$this->session->setParameter(array("search_select", $this->block_id, "result_flag"), _ON);
		$this->session->removeParameter(array("search_result", $this->block_id));
		
		return 'success';
	}
}
?>