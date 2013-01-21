<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 表示方法変更
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Whatsnew_Action_Edit_Style extends Action
{
    // リクエストパラメータを受け取るため
    var $block_id = null;
	var $display_type = null;
	var $display_days = null;
    var $display_modules = null;
	var $display_title = _ON;
	var $display_room_name = null;
	var $display_module_name = null;
	var $display_user_name = null;
	var $display_insert_time = null;
	var $display_description = null;
	var $select_room = null;
	var $allow_rss_feed = null;
	var $rss_title = null;
	var $rss_description = null;
	var $display_number = null;
	var $display_flag = null;

    // 使用コンポーネントを受け取るため
	var $db = null;
	var $request = null;
	var $session = null;
	var $whatsnewModAction = null;

    /**
     * execute実行
     *
     * @access  public
     */
    function execute()
    {
    	if ($this->select_room == _ON) {
	    	$myroom_flag = $this->session->getParameter(array("whatsnew", "myroom_flag", $this->block_id));
    	} else {
    		$myroom_flag = _OFF;
    	}
    	
		$this->session->removeParameter(array("whatsnew", $this->block_id));
    	$params = array(
			"display_type" => intval($this->display_type),
			"display_modules" => implode(",", $this->display_modules),
			"display_title" => intval($this->display_title),
			"display_room_name" => intval($this->display_room_name),
			"display_module_name" => intval($this->display_module_name),
			"display_user_name" => intval($this->display_user_name),
			"display_insert_time" => intval($this->display_insert_time),
			"display_description " => intval($this->display_description),
			"allow_rss_feed" => intval($this->allow_rss_feed),
			"select_room" => intval($this->select_room)
		);
    	if (isset($myroom_flag)) {
    		$params["myroom_flag"] = $myroom_flag;
    	}
    	if($this->display_flag == _ON){
			$params["display_number"] = intval($this->display_number);
			$params["display_flag"] = intval($this->display_flag);
    	}else{
    		$params["display_days"] = intval($this->display_days);
    		$params["display_flag"] = intval($this->display_flag);
    	}

		if ($this->allow_rss_feed == _ON) {
			$params["rss_title"] = $this->rss_title;
			$params["rss_description"] = $this->rss_description;
		}
		//ここで値をアップデート
    	$result = $this->db->updateExecute("whatsnew_block", $params, array("block_id"=>$this->block_id), true);
    	if ($result === false) {
    		return 'error';
    	}
 		$this->request->setParameter("display_module_id", null);
		
		if (isset($myroom_flag)) {
			if (!$this->whatsnewModAction->setSelectRoom()) {
				return 'error';
			}
		}

        return 'success';
    }
}
?>