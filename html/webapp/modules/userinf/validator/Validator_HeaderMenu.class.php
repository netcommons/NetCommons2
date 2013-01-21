<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 参加ルーム、アクセス状況、レポート、退会機能を表示できるかどうか
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_HeaderMenu extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値		user_id
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数 - 	[0]	:		HeaderMenu Filter名称 権限によってタブの表示・非表示を切り替える。
     * 																切り替える場合、エラーとはしない(main_init時等)
     * 注意：チェックするタブテキストとアクション名を一致させている
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	if(!is_array($params) || count($params) != 1) {
    		//パラメータは1つ
    		return $errStr;
    	}
    	
    	$container =& DIContainerFactory::getContainer();
    	$session =& $container->getComponent("Session");
        $filterChain =& $container->getComponent("FilterChain");
        $usersView =& $container->getComponent("usersView");
        $actionChain =& $container->getComponent("ActionChain");
        $configView =& $container->getComponent("configView");
		$action =& $actionChain->getCurAction();
		$action_name =& $actionChain->getCurActionName();
		
        //
        // 自分(Self)
        //
    	$user_id = $session->getParameter("_user_id");
    	if($user_id == "0") return $errStr;
    	$user_auth_id = $session->getParameter("_user_auth_id");
        
        //
        // 編集先
        //
        $edit_user_id = $attributes;
        if($edit_user_id == "0") {
        	$edit_user_id = $session->getParameter("_user_id");
        }
    	
    	$edit_user =& $usersView->getUserById($edit_user_id, array($usersView, "_getUsersFetchcallback"));
    	if($edit_user === false || !isset($edit_user['user_id'])) return $errStr; //会員が削除された可能性あり
    	
    	$edit_user_auth_id = $edit_user['user_authority_id'];
    	
    	$where_params = array(
    						"user_authority_id" => $user_auth_id,
    						"type" => USER_TYPE_SYSTEM
    					);
    	$items =& $usersView->getItems($where_params, null, null, null, array($this, "_fetchcallback"));
    	$headerMenu =& $filterChain->getFilterByName($params[0]);
		if(!$headerMenu) {
			//headerMenuフィルター指定なし
			return $errStr;
		}
		$err_return_flag = false;
    	foreach($items as $tag_name => $item) {
    		if($item['type'] != "system") continue;
    		
			//
			// 表示できるかどうかチェック
			//
			$error_flag = false;	//初期化
			if ($user_id != $edit_user_id) {
 				// 他人
 				if($user_auth_id <= $edit_user_auth_id && $item['over_public_flag'] != USER_PUBLIC) {
 					// 自分の権限より大きいものを編集しようとしている
 					$error_flag = true;
 				}else if($user_auth_id > $edit_user_auth_id && $item['under_public_flag'] != USER_PUBLIC) {
 					// 自分の権限と同じか、小さいものを編集しようとしている
 					$error_flag = true;
 				}
 			} else {
 				//自分自身(self)
 				if($item['self_public_flag'] != USER_PUBLIC) {
 					$error_flag = true;
 				}
 			}
 			
			//チェック対象のタブがアクティブなタブかどうか
			if($error_flag == true && $tag_name  == $action_name) {
				$err_return_flag = true;
			} else if($error_flag == true) {
				//タブを非表示にする
				$headerMenu->removeText($tag_name);
			}	
    	}
    	
    	//
    	// 退会機能
    	//
    	$withdraw_membership_use = $configView->getConfigByConfname(_SYS_CONF_MODID, "withdraw_membership_use");
    	if($withdraw_membership_use === false) {
			return $errStr;
		}
		if($user_id != $edit_user_id || $user_auth_id == _AUTH_ADMIN || $withdraw_membership_use['conf_value'] == _OFF) {
    		// 自分自身以外、管理者は退会できない	
    		$headerMenu->removeText("userinf_view_main_withdraw_init");
    		if($action_name == "userinf_view_main_withdraw_init") {
    			$err_return_flag = true;
    		}
    	}
    	if($err_return_flag == true) {
    		return $errStr;
    	}
    	BeanUtils::setAttributes($action, array("user"=>$edit_user));
    	return;
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
			$ret[$row['tag_name']] = $row;
		}
		return $ret;
	}
}
?>
