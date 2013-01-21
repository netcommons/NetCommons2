<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システムConfig登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class System_Action_Membership extends Action
{
	
	// リクエストパラメータを受け取るため
	var $autoregist_use = null;
	var $autoregist_approver = null;
	var $autoregist_defroom = null;
	var $autoregist_author = null;
	var $autoregist_use_items = null;
	var $autoregist_disclaimer = null;
	var $autoregist_use_input_key = null;
	var $autoregist_input_key = null;
	var $mail_approval_subject = null;
	var $mail_approval_body = null;
	var $mail_add_user_subject = null;
	var $mail_add_user_body = null;
	var $mail_add_announce_subject = null;
	var $mail_add_announce_body = null;
	var $mail_get_password_subject = null;
	var $mail_get_password_body = null;
	var $mail_new_password_subject = null;
	var $mail_new_password_body = null;
	var $withdraw_membership_use = null;
	var $withdraw_membership_send_admin = null;
	var $withdraw_disclaimer = null;
	var $mail_withdraw_membership_subject = null;
	var $mail_withdraw_membership_body = null;
	
	//使用コンポーネント
	var $config = null;
	var $systemView = null;
	
    /**
     * DB登録
     *
     * @access  public
     */
    function execute()
    { 
        $value = ($this->autoregist_use) ? _ON : _OFF;
		if (!$this->_update('autoregist_use', $value)) return 'error';
		
		$value = ($this->autoregist_approver) ? $this->autoregist_approver : _AUTOREGIST_SELF;
		if (!$this->_update('autoregist_approver', $value)) return 'error';
		
		$value = "";
		$use_items = array();
        if (isset($this->autoregist_use_items)) {
            // 1:1|2:1|3:1|4:0| -> {1 => 1, 2 => 1, 3 => 1, 4 => 0}       
        	$default_use_items = $this->systemView->parseUseItems(SYSTEM_DEFAULT_AUTOREGIST_USE_ITEMS);
        	$use_items += $default_use_items; // array_merge
        	
        	// disabledのINPUT要素は送られないがEnableにされる可能性もあるため、デフォルトでMustなものはそのまま残す
        	foreach($this->autoregist_use_items as $use_item) {
        		// $use_item:汚染
        		if (!$use_item) continue;
        		$list = explode(":", $use_item);
        		if (count($list) == 2) {
        			list($id, $input) = $list; // 汚染
        			if (!$this->systemView->itemStorable($id)) {
        				// 不正
        				continue;
        			}
        			// 汚染は除去された
        			if (isset($default_use_items[$id])) {
        				if ($default_use_items[$id] == _ON) {
        					// 強制的にシステムデフォルトを使用する($use_itemsは$defautl_use_itemsがマージされている)
        				} elseif ($input == SYSTEM_AUTOREGIST_DEFAULT_MUST_ITEM || $input == SYSTEM_AUTOREGIST_CHECKED_ITEM) {
        					$use_items[$id] = _ON;
        				} elseif ($input == SYSTEM_AUTOREGIST_HIDE_ITEM) {
        					unset($use_items[$id]);
        				} else {
        					// システムデフォルトが使われる
        				}
        			} else {
        				// ユーザ入力を新規追加
        				if ($input == SYSTEM_AUTOREGIST_DEFAULT_MUST_ITEM || $input == SYSTEM_AUTOREGIST_CHECKED_ITEM) {
        					$use_items[$id] = _ON;
	        			} elseif ($input == SYSTEM_AUTOREGIST_SELECTABLE_ITEM) {
	        				$use_items[$id] = _OFF;
	        			} else {
	        				// SYSTEM_AUTOREGIST_HIDE_ITEMや不正値は追加しない
	        			}
        			}
        		} else {
        			// フォーマットが正しくない
        		}
        	} // end of foreach
        	// format (ex 1:1|2:1|3:1|4:1|5:1|8:1)
        	ksort($use_items);
	        while (list($id, $flag) = each($use_items)) {
	        	$value .= "$id:$flag|";
	        }
        } else {
        	// システムデフォルトをそのまま保存する
        	$value = SYSTEM_DEFAULT_AUTOREGIST_USE_ITEMS;
        }
        
        if (!$this->_update('autoregist_use_items', $value)) return 'error';

		$value = ($this->autoregist_author) ? $this->autoregist_author : _AUTH_GENERAL;
		if (!$this->_update('autoregist_author', $value)) return 'error';
		
		$value = ($this->autoregist_defroom) ? _ON : _OFF;
		if (!$this->_update('autoregist_defroom', $value)) return 'error';
		
		$value = ($this->autoregist_disclaimer) ? $this->autoregist_disclaimer : "";	//SYSTEM_DEFAULT_AUTOREGIST_DISCLAIMER;
		if (!$this->_update('autoregist_disclaimer', $value)) return 'error';
		
		//入力キー使用有無
		$value = ($this->autoregist_use_input_key) ? _ON : _OFF;
		if (!$this->_update('autoregist_use_input_key', $value)) return 'error';
		
		//入力キー
		$value = ($this->autoregist_input_key) ? $this->autoregist_input_key : "";
		if (!$this->_update('autoregist_input_key', $value)) return 'error';
		
		$value = ($this->mail_approval_subject) ? $this->mail_approval_subject : '';
		if (!$this->_update('mail_approval_subject', $value)) return 'error';
		
		$value = ($this->mail_approval_body) ? $this->mail_approval_body : 'mail_approval_body';
		if (!$this->_update('mail_approval_body', $value)) return 'error';
		
		$value = ($this->mail_add_user_subject) ? $this->mail_add_user_subject : '';
		if (!$this->_update('mail_add_user_subject', $value)) return 'error';
		
		$value = ($this->mail_add_user_body) ? $this->mail_add_user_body : '';
		if (!$this->_update('mail_add_user_body', $value)) return 'error';
		
		$value = ($this->mail_add_announce_subject) ? $this->mail_add_announce_subject : '';
		if (!$this->_update('mail_add_announce_subject', $value)) return 'error';
		
		$value = ($this->mail_add_announce_body) ? $this->mail_add_announce_body : '';
		if (!$this->_update('mail_add_announce_body', $value)) return 'error';
		
		$value = ($this->mail_get_password_subject) ? $this->mail_get_password_subject : '';
		if (!$this->_update('mail_get_password_subject', $value)) return 'error';
		
		$value = ($this->mail_get_password_body) ? $this->mail_get_password_body : '';
		if (!$this->_update('mail_get_password_body', $value)) return 'error';
		
		$value = ($this->mail_new_password_subject) ? $this->mail_new_password_subject : '';
		if (!$this->_update('mail_new_password_subject', $value)) return 'error';
		
		$value = ($this->mail_new_password_body) ? $this->mail_new_password_body : '';
		if (!$this->_update('mail_new_password_body', $value)) return 'error';
		
		$value = ($this->withdraw_membership_use) ? $this->withdraw_membership_use : '0';
		if (!$this->_update('withdraw_membership_use', $value)) return 'error';
		
		$value = ($this->withdraw_membership_send_admin) ? $this->withdraw_membership_send_admin : '0';
		if (!$this->_update('withdraw_membership_send_admin', $value)) return 'error';
		
		$value = ($this->withdraw_disclaimer) ? $this->withdraw_disclaimer : '';
		if (!$this->_update('withdraw_disclaimer', $value)) return 'error';
		
		$value = ($this->mail_withdraw_membership_subject) ? $this->mail_withdraw_membership_subject : '0';
		if (!$this->_update('mail_withdraw_membership_subject', $value)) return 'error';
		
		$value = ($this->mail_withdraw_membership_body) ? $this->mail_withdraw_membership_body : '0';
		if (!$this->_update('mail_withdraw_membership_body', $value)) return 'error';
		
    	return 'success';
    }
    
    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _ENTER_EXIT_CONF_CATID);
    	return $status;
    }
}
?>
