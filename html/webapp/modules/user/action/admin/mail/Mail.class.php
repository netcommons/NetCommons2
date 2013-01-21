<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * メール送信アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_Action_Admin_Mail extends Action
{
    // リクエストパラメータを受け取るため
 	var $user_id = null;
 	var $email = null;
 	var $mail_subject = null;
 	var $mail_content = null;
 	
    // 使用コンポーネントを受け取るため
 	var $mailMain = null;
 	var $session = null;
 	var $usersView = null;
 	var $request = null;

    /**
     * メール送信アクション
     *
     * @access  public
     */
    function execute()
    {
		//$user = $this->usersView->getUserById($this->user_id);
	    //if($user === false) return 'error';
	    
	    $where_params = array(
    						"tag_name" => "email",
    						"{users_items_link}.user_id" => $this->user_id
    					);
		$items =& $this->usersView->getItems($where_params, null, 1);
		if($items === false || !isset($items[0])) return 'error';
		
		if($items[0]['content'] != $this->email)  return 'error';
		
		$this->mailMain->setSubject($this->mail_subject);
		$this->mailMain->setBody(htmlspecialchars($this->mail_content));
		$user = array();
		$user['email'] = $this->email;
		$user['type'] = "text";	// Text固定(html or text)
		$this->mailMain->addToUser($user);
		
		$this->mailMain->send();
		
		// 初期化
		$this->request->removeParameters();
		
		return "success";
    }
}
?>
