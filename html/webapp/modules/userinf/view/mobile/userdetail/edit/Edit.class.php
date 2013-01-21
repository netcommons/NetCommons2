<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 携帯会員情報編集画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_View_Mobile_Userdetail_Edit extends Action
{
    // リクエストパラメータを受け取るため
    var $user_id = null;
    var $prefix_id_name = null;
   
    // バリデートによりセット
    var $user = null;
    
    // 使用コンポーネントを受け取るため
    var $usersView = null;
    var $session = null;
    var $pagesView = null;
    var $configView = null;
    var $actionChain = null;
    
    // 値をセットするため
    var $items = null;
    var $public_flag_colname = null;
    
    var $private_page_id = null;
    var $private_page_name = null;
    var $private_permalink = null;

	var	$main_errmsg = null;
	var	$items_errmsg = null;
	var $error_exists = null;

	var	$userinf_items = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
		$items_errList =& $this->actionChain->getErrorListByName( "userinf_action_mobile_edit" );
		if( $items_errList != null ) {
			if( $items_errList->isExists() ) {
				$this->error_exists = _ON;
				$errmsg_arr = $items_errList->getSelfMessages();
				foreach( $errmsg_arr as $errmsg_1 ) {
					$errmsg_2 = explode( "|", $errmsg_1 );
					foreach( $errmsg_2 as $errmsg ) {
						$tmp_errmsg = explode( ":", $errmsg );
						if( count( $tmp_errmsg ) == 2 ) {
							$this->items_errmsg[ $tmp_errmsg[0] ] = $tmp_errmsg[1] . "<br />";
						}
						else {
							$this->main_errmsg = $tmp_errmsg[0];
						}
					}
				}
			}
		}
    	if($this->user_id == "0" || !isset($this->user_id)) {
    		$this->user_id = $this->session->getParameter("user_id");
    	}
    	
    	//
    	// 自分自身か、自分自身より低い権限か、高い権限かをセット
    	//
    	if($this->session->getParameter("_user_id") == $this->user_id) {
    		// 自分自身
    		$this->public_flag_colname = "self_public_flag";
    	} else if($this->user['user_authority_id'] >= $this->session->getParameter("_user_auth_id")){
    		// 自分と同じか高い権限
    		$this->public_flag_colname = "over_public_flag";
    	} else {
    		// 低い権限
    		$this->public_flag_colname = "under_public_flag";
    	}

        //
    	// ハンドル名チェック
        // (update_user_name-insert_user_nameが正しい保障がないため、会員管理ではチェックする)
    	// (作成者、最終更新者にリンクがはっていないため)
        //
    	if($this->user['insert_user_id'] != $this->user['user_id']) {
    		$update_user =& $this->usersView->getUserById($this->user['insert_user_id']);
    		$this->user['insert_user_name'] = $update_user['handle'];
    	}
    	if($this->user['update_user_id'] != $this->user['user_id']) {
    		if($this->user['insert_user_id'] != $this->user['update_user_id']) {
    			$update_user =& $this->usersView->getUserById($this->user['update_user_id']);
    		} else if(!isset($update_user)) {
    			$update_user['handle'] = $this->user['handle'];
    		}
    		$this->user['update_user_name'] = $update_user['handle'];
    	}
    	
    	if($this->user === false) return 'error';
    	$this->items =& $this->usersView->getShowItems($this->user_id, $this->session->getParameter("_user_auth_id"));
    	if($this->items === false) return 'error';
    	
    	//
    	// プライベートスペースへ
    	//
    	$config_open_private_space = $this->configView->getConfigByConfname(_SYS_CONF_MODID, "open_private_space");
		if($config_open_private_space === false) return 'error';
		
    	if($this->session->getParameter("_user_id") == $this->user_id || $config_open_private_space['conf_value'] == _ON) {
	    	$buf_page_private =& $this->pagesView->getPrivateSpaceByUserId($this->user_id, 1);
			if($buf_page_private === false) return 'error';
			$this->private_page_id = $buf_page_private[0]['page_id'];
			$this->private_page_name = $buf_page_private[0]['page_name'];
			$this->private_permalink = $buf_page_private[0]['permalink'];
			if($this->private_permalink != '') $this->private_permalink .= '/';
    	}


		if( $this->error_exists == _ON ) {
			// エラーがあるときは、前に入力した値が存在するはず
			// それを通常データに上かぶせする
		}
        return 'success';
    }
}
?>
