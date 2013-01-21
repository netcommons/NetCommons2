<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Metadataseq extends Action
{
    // リクエストパラメータを受け取るため
    var $drag_metadata_id = null;
    var $drop_metadata_id = null;
    var $position = null;
    var $display_pos = null;
    
    // バリデートによりセット
	var $drag_metadata = null;
	var $drop_metadata = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * @access  public
     */
    function execute()
    {
    	$multidatabase_id = $this->drag_metadata['multidatabase_id'];
    	// 移動元
		$where_params = array(
			"multidatabase_id" => $multidatabase_id,
			"display_pos" => $this->drag_metadata['display_pos']
		);
		$sequence_param = array("display_sequence" => $this->drag_metadata['display_sequence']);
		$result = $this->db->seqExecute("multidatabase_metadata", $where_params, $sequence_param);
		if ($result === false) {
			return 'error';
		}
		if($this->drag_metadata['display_pos'] == $this->drop_metadata['display_pos'] &&
			$this->drag_metadata['display_sequence'] < $this->drop_metadata['display_sequence']) {
			$this->drop_metadata['display_sequence']--;	
		}
		// 移動先
		$position = $this->position;
		if($position != "top" && $position != "bottom") $position = "top";
		if($this->drag_metadata_id == $this->drop_metadata_id) {
			//新規ポジションへ配置した場合
			if($this->display_pos != $this->drag_metadata['display_pos'] ){
				$display_sequence = 1;
			} else {
				//同じポジションの同じ行へ配置した場合
				$display_sequence = $this->drag_metadata['display_sequence'];
			}
		}else if($position == "top"){
			$display_sequence = $this->drop_metadata['display_sequence'];
		}else {
			$display_sequence = $this->drop_metadata['display_sequence'] + 1;
		}
		$where_params = array(
			"multidatabase_id" => $multidatabase_id,
			"display_pos" => $this->display_pos
		);
		$sequence_param = array("display_sequence" => $display_sequence);
		$result = $this->db->seqExecute("multidatabase_metadata", $where_params, $sequence_param, 1);
		if ($result === false) {
			return 'error';
		}
		// metadata更新
		$params = array(
			"display_pos" => $this->display_pos,
			"display_sequence" => $display_sequence
		);
		$where_params = array("metadata_id" => $this->drag_metadata_id);
		$result = $this->db->updateExecute("multidatabase_metadata", $params, $where_params);
		if ($result === false) {
			return 'error';
		}
		return 'success';
    }
}
?>
