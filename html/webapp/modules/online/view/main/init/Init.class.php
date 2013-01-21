<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * オンライン状況メイン画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Online_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    //var $module_id = null;
    
    // 使用コンポーネントを受け取るため
    var $onlineView = null;
    
    // validatorから受け取るため
    var $user_flag = null;
    var $member_flag = null;
    var $total_member_flag = null;
   
    // 値をセットするため
    var $userCount = false;
    var $memberCount = false;
    var $totalMemberCount = false;
    
    /**
     * オンライン状況メイン画面表示
     *
     * @access  public
     */
    function execute()
    {	
    	if($this->user_flag || $this->member_flag){
    		$user = $this->onlineView->getUserMember();
    		if($this->user_flag){
    			$this->userCount = $user["user"];
    		}
    		if($this->member_flag){
    			$this->memberCount = $user["member"];
    		}
    	}
    	
    	if($this->total_member_flag){
    		$this->totalMemberCount = $this->onlineView->getTotalMember();
    	}
    				
		return "success";
    }
}
?>
