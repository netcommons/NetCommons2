<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ表示順変更アクション
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Main_Chgseq extends Action
{
    // リクエストパラメータを受け取るため
    var $multidatabase_id = null;
    var $content_id_arr = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * コンテンツ表示順変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	if(is_array($this->content_id_arr)) {
	    	$display_sequence = 1;
	        foreach($this->content_id_arr as $content_id) {
	        	//更新
		    	$params = array(
		    		"display_sequence" => $display_sequence
				);
				$where_params = array(
					"multidatabase_id"=>intval($this->multidatabase_id),
					"content_id" => intval($content_id)
				);
		    	$result = $this->db->updateExecute("multidatabase_content", $params, $where_params);
		    	if($result === false) {
		    		return 'error';
		    	}
		    	$display_sequence++;
	        }
    	}
    	
    	return 'success';
    }
}
?>
