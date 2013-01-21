<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索条件クリア
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Action_Main_Result_Clear extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;

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
		$this->center_flag = $this->session->getParameter(array("search_select", $this->block_id, "center_flag"));
		$this->session->removeParameter(array("search_select", $this->block_id));
		$this->session->removeParameter(array("search_result", $this->block_id));
		if ($this->center_flag == _ON) {
			return 'center';
		} else {
			return 'success';
		}
	}
}
?>