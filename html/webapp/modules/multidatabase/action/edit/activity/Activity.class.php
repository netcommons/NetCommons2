<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 動作フラグ更新アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Action_Edit_Activity extends Action
{
    // リクエストパラメータを受け取るため
    var $multidatabase_id = null;
    var $active_flag = null;
    
	// コンポーネントを受け取るため
	var $db = null;
	
    /**
     * 動作フラグ更新アクション
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"multidatabase_id"=>intval($this->multidatabase_id)
		);
		
		$update_params = array(
    		"active_flag" => $this->active_flag
    	);
    		
	    $result = $this->db->updateExecute("multidatabase", $update_params, $params, true);
	    if($result === false) {
	    	return 'error';
	    }
	    
		return 'success';
    }
}
?>
