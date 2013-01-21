<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索内容のページ遷移
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Action_Main_Result_Transition extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $target_module = null;
	var $target_room = null;
	var $limit = null;
	var $offset = null;

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
		$params = array(
			"module_id" => $this->target_module,
			"room_id" => intval($this->target_room),
			"offset" => (isset($this->offset) ? $this->offset : 0),
			"limit" => (isset($this->limit) ? $this->limit : SEARCH_DEF_FIRST_LIMIT)
		);
		$this->session->setParameter(array("search_result", $this->block_id, $this->target_module), $params);
		return 'success';
	}
}
?>