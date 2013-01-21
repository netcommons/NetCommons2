<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更登録アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Search_Action_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
    var $show_mode = null;
    var $target_modules = null;
    
    // コンポーネントを受け取るため
	var $db = null;
	var $session = null;

    /**
     * 表示方法変更登録アクション
     *
     * @access  public
     */
    function execute()
    {
    	$this->target_modules = ( $this->target_modules == null) ? "" :  implode(",", $this->target_modules);
    	$params = array(
			"show_mode" => intval($this->show_mode),
			"default_target_module" => $this->target_modules
		);

		$where_params = array(
			"block_id" => intval($this->block_id)
		);
    	$result = $this->db->updateExecute("search_blocks", $params, $where_params ,true);
    	if($result === false) {
    		return 'error';
    	}
		$this->session->removeParameter(array("search_select", $this->block_id));
    	$this->session->removeParameter(array("search_result", $this->block_id));
    	return 'success';
    }
}
?>
