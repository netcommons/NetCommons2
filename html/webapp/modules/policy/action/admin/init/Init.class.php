<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 個人情報管理　登録処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Policy_Action_Admin_Init extends Action
{
	// リクエストパラメータを受け取るため
	var $user_auth_id = null;
	var $public_flag  = null;
	
	// 使用コンポーネントを受け取るため
	var $usersView = null;
	var $usersAction = null;
	var $actionChain = null;
	
    /**
     * 個人情報管理　登録処理
     *
     * @access  public
     */
    function execute()
    {
    	$this->user_auth_id = ($this->user_auth_id == null || $this->user_auth_id == 0) ? _AUTH_ADMIN : intval($this->user_auth_id);
    	
    	$where_params = array(
			"user_authority_id" => $this->user_auth_id
		);
    	$items =& $this->usersView->getItems($where_params, null, null, null, array($this, "_fetchcallback"));
    	if($items === false) return 'error';

		if(is_array($this->public_flag)) {
			$handle_item_id = $this->usersView->getItemIdByTagName('handle');
			foreach($this->public_flag as $item_id => $public_flag) {
				if($this->user_auth_id == _AUTH_GUEST) {
					$public_flag['under_public_flag'] = USER_NO_PUBLIC;
					if ($item_id == $handle_item_id) {
						$public_flag['under_public_flag'] = USER_PUBLIC;
					}
				}
				if(isset($public_flag['over_public_flag'])
					&& isset($public_flag['self_public_flag']) 
					&& isset($public_flag['under_public_flag'])) {
	    			if(isset($items[$item_id])) {
	    				if($items[$item_id]['type'] == USER_TYPE_LABEL || $items[$item_id]['type'] == USER_TYPE_SYSTEM) {
	    					// Typeがラベルかシステムの場合、書き込みは許さない
	    					if($public_flag['over_public_flag'] == USER_EDIT)  $public_flag['over_public_flag'] = USER_PUBLIC;
	    					if($public_flag['self_public_flag'] == USER_EDIT)  $public_flag['self_public_flag'] = USER_PUBLIC;
	    					if($public_flag['under_public_flag'] == USER_EDIT) $public_flag['under_public_flag'] = USER_PUBLIC;
	    				}
	    				// 自分より高い権限で管理者ではない場合、書き込みは許さない
	    				if($public_flag['over_public_flag'] == USER_EDIT && $this->user_auth_id != _AUTH_ADMIN) {
	    					$public_flag['over_public_flag'] = USER_PUBLIC;
	    				}
	    			}
		    		$params = array(
		    			"over_public_flag" => $public_flag['over_public_flag'],
			    		"self_public_flag" => $public_flag['self_public_flag'],
			    		"under_public_flag" => $public_flag['under_public_flag']
		    		);
		    		$where_params = array("item_id" => $item_id, "user_authority_id" => $this->user_auth_id);
		    		$result = $this->usersAction->updItemsAuthLink($params, $where_params);
		    		if($result === false) {
		    			return 'error';
		    		}
	    		}
	    	}
    	}
    	// 正常終了
    	$errorList =& $this->actionChain->getCurErrorList();
		$errorList->add(get_class($this), _UPDATE_COMP);
					
		return 'success';
    }
    
    
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}
	
}
?>
