<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

include_once MAPLE_DIR.'/includes/pear/XML/Unserializer.php';

/**
 * 検索内容
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_View_Main_Result_Contents extends Action
{
	// リクエストパラメータを受け取るため
	var $block_id = null;
	var $target_module = null;

	// 使用コンポーネントを受け取るため
	var $session = null;
	var $preexecute = null;

	// Varidatorにより値をセット
	var $module_obj = null;

	// 値をセットするため
	var $search_result = null;
	var $search_select = null;
	var $xml_parse = null;

    /**
     * execute処理
     *
     * @access  public
     */
	function execute()
	{
		$this->search_result = $this->session->getParameter(array("search_result", $this->block_id, $this->target_module));
		if (!isset($this->search_result)) {
			$this->search_result = array(
				"module_id" => $this->module_obj['module_id'],
				"offset" => 0,
				"limit" => SEARCH_DEF_FIRST_LIMIT
			);
			$this->session->setParameter(array("search_result", $this->block_id, $this->target_module), $this->search_result);
		}

		$this->search_select = $this->session->getParameter(array("search_select", $this->block_id));
    	$params = array(
			"block_id" => $this->block_id,
			"keyword" => $this->search_select["keyword"],
			"select_kind" => $this->search_select["select_kind"],
			"handle" => $this->search_select["handle"],
			"fm_target_date" => $this->search_select["fm_target_date"],
			"to_target_date" => $this->search_select["to_target_date"],
			"target_module" => $this->search_result["module_id"],
			"target_room" => isset($this->search_result["room_id"]) ? $this->search_result["room_id"] : null,
			"offset" => $this->search_result["offset"],
			"limit" => $this->search_result["limit"],
			"_header" => _OFF,
			"_output" => _OFF
    	);
    	$xml = $this->preexecute->preExecute($this->module_obj["search_action"], $params);
		$xml = preg_replace("/nc_rss:/iu", "", $xml);

		$options = array( 
			XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE => 'parseAttributes' 
		); 
		$unserializer = new XML_Unserializer($options); 
		$unserializer->unserialize($xml); 
		$this->xml_parse = $unserializer->getUnserializedData();
		if ($this->search_result["offset"] == 0) {
			$limit = SEARCH_DEF_FIRST_LIMIT;
		} else {
			$limit = SEARCH_DEF_NEXT_LIMIT;
		}
		$this->next_offset = (($this->search_result["offset"] + $limit) > $this->xml_parse["count"]) ? $this->xml_parse["count"] : ($this->search_result["offset"] + $limit);
		$this->prev_offset = (($this->search_result["offset"] - $limit) < 0) ? 0 : ($this->search_result["offset"] - $limit);

		return 'success';
	}
}
?>