<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Online_Action_Edit_Style extends Action
{
	
	// リクエストパラメータを受け取るため
    var $block_id = null;
    
    var $user_flag = null;
    var $member_flag = null;
    var $total_member_flag = null;
    

	// 使用コンポーネントを受け取るため
	var $db = null;

	// validatorから受け取るため
    var $exists = null;

    /**
     * 表示方法変更アクション
     *
     * @access  public
     */
    function execute()
    {
    	$params = array(
			"block_id" => $this->block_id,
			"user_flag" => empty($this->user_flag) ? 0 : $this->user_flag,
    		"member_flag" => empty($this->member_flag) ? 0 : $this->member_flag,
    		"total_member_flag" => empty($this->total_member_flag) ? 0 : $this->total_member_flag
		);
    	if ($this->exists == _ON) {
    		$result = $this->db->updateExecute("online", $params, "block_id", true);
		} else {
    		$result = $this->db->insertExecute("online", $params, true);
		}
	    if (!$result) {
	    	return "error";
	    }
	    
		return "success";
    }
}
?>
