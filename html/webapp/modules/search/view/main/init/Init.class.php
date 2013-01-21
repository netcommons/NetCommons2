<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 検索表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_View_Main_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $show_mode = null;
	var $center_flag = null;
	var $return_center = null;
	
	var $target_module = null;

	// 使用コンポーネントを受け取るため
	var $searchView = null;
	var $session = null;
	var $preexecute = null;

	// Filterにより値をセット
	var $pages_list = null;
	
	// 値をセットするため
	var $modules = null;
	var $search_blocks = null;
	var $fm_target_date = null;
	var $to_target_date = null;
	var $result_condition = null;
	var $result_contents = null;
	
    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		if($this->block_id == null || $this->block_id == 0) {
			// ブロックに配置していない場合
			$this->show_mode = 0;
			$this->search_blocks['show_mode'] = $this->show_mode;
			
			$this->target_module = ($this->target_module == null) ? SEARCH_DEFAULT_MODULES : $this->target_module;
			$target_module_arr = explode(",", $this->target_module);
			
			foreach($target_module_arr as $target_module) {
				$this->search_blocks["default_target_module_arr"][] = $target_module;
			}
			$this->session->setParameter(array("search_select", 0, "center_flag"), _ON);
		
		
		} else {
			if (!isset($this->show_mode)) {
				$this->show_mode = $this->session->getParameter(array("search_select", $this->block_id, "show_mode"));
			}
			$this->search_blocks = $this->searchView->getBlock($this->block_id, $this->show_mode);
		}
		$this->modules = $this->searchView->getModules($this->search_blocks);
		
		$today = timezone_date(null, false, "Ymd");
		$fm_target_date = $this->session->getParameter(array("search_select", $this->block_id, "fm_target_date"));
		if (!isset($fm_target_date)) {
			//$timestamp = mktime(0, 0, 0, substr($today,4,2)-1, substr($today,6,2), substr($today,0,4));
			//$fm_target_date = date("Ymd", $timestamp);
			//$this->session->setParameter(array("search_select", $this->block_id, "fm_target_date"), $fm_target_date);
		}
		if (!empty($fm_target_date)) {
			$timestamp = mktime(0, 0, 0, substr($fm_target_date,4,2), substr($fm_target_date,6,2), substr($fm_target_date,0,4));
			$this->fm_target_date = date(_INPUT_DATE_FORMAT, $timestamp);
		}

		$to_target_date = $this->session->getParameter(array("search_select", $this->block_id, "to_target_date"));	
		if (!isset($to_target_date)) {
			$to_target_date = $today;
			$this->session->setParameter(array("search_select", $this->block_id, "to_target_date"), $to_target_date);
		}
		if (!empty($to_target_date)) {
			$timestamp = mktime(0, 0, 0, substr($to_target_date,4,2), substr($to_target_date,6,2), substr($to_target_date,0,4));
			$this->to_target_date = date(_INPUT_DATE_FORMAT, $timestamp);
		}
		
		if (!isset($this->center_flag)) {
			$this->center_flag = $this->session->getParameter(array("search_select", $this->block_id, "center_flag"));
		} else {
			$this->center_flag = _OFF;
		}
		$result_flag = $this->session->getParameter(array("search_select", $this->block_id, "result_flag"));
		if ($result_flag == _ON && $this->center_flag != _ON) {
	    	$params = array(
				"block_id" => $this->block_id,
				"init_flag" => _ON,
				"_header" => _OFF,
				"_output" => _OFF,
				"_noscript" => _ON
	    	);
	    	$this->result_condition = $this->preexecute->preExecute("search_view_main_result_condition", $params);
			$target_modules = $this->session->getParameter(array("search_select", $this->block_id, "target_modules"));
			$var = $this->session->getParameter(array("search_result", $this->block_id));
			$target_room = intval($this->session->getParameter(array("search_select", $this->block_id, "target_room")));
			$this->result_contents = array();
			foreach ($target_modules as $i=>$target_module) {
				$params = array(
					"block_id" => $this->block_id,
					"target_module" => $target_module,
					"target_room" => $target_room,
					"_header" => _OFF,
					"_output" => _OFF,
					"_noscript" => _ON
	    		);
	    		$this->result_contents[$target_module] = $this->preexecute->preExecute("search_view_main_result_contents", $params);
			}
		}
		if ($this->return_center == _ON) {
			return 'center';
		} else {
			return 'success';
		}
	}
}
?>