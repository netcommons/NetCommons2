<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ルーム名称チェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Validator_RoomName extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	// mode : create or edit
    	$mode = "create";
        if(isset($params[0])) {
        	$mode = $params[0];
        }
        
    	$container =& DIContainerFactory::getContainer();
    	$pagesView =& $container->getComponent("pagesView");
    	$session =& $container->getComponent("Session");
    	
    	$room_name = $attributes[0];
    	$display_flag = $attributes[1];
    	$default_entry_flag = $attributes[2];
    	
    	$parent_page_id = intval($attributes[3]);
    	$current_page_id = intval($attributes[4]);
    	if(isset($attributes[5])) {
    		// サブグループ
    		$parent_page_id = $attributes[5];
    	}

    	if($room_name == "") {
    		//戻るボタン押下時
    		if($mode == "create") {
    			$room_name =& $session->getParameter(array("room", $current_page_id,"general","room_name"));
    			$display_flag =& $session->getParameter(array("room", $current_page_id,"general","display_flag"));
    			$default_entry_flag =& $session->getParameter(array("room", $current_page_id,"general","space_type_common"));
    		}
    		if($room_name == "" || $room_name == null) return $errStr;
    	}
    	if($parent_page_id != 0) {
    		$pages =& $pagesView->getPages(array("parent_id"=>$parent_page_id, "page_id=room_id"=>null, "page_name"=> $room_name, "page_id != ".$current_page_id => null));
    	} else {
    		$pages =& $pagesView->getPages(array("parent_id"=>$current_page_id, "page_id=room_id"=>null, "page_name"=> $room_name, "page_id != ".$current_page_id => null));
    	}
    	if($pages === false || isset($pages[0])) {
    		return $errStr;	
    	}
    	/*
    	if(isset($pages[0])) {
    		$error_flag = false;
    		foreach($pages as $page) {
    			if($page['page_name'] == $room_name) {
    				if($page['page_id'] != $current_page_id) {
	    				//同名称のルームあり
	    				$error_flag = true;
	    				break;
	    			} else {
	    				//変更していない
	    				$error_flag = false;
	    				break;
	    			}
    			}
    		}
    		if($error_flag) return $errStr;	
    	}
    	*/
    	//
    	// エラーメッセージを変更したほうがよいが、現状、不正な場合以外こちらのエラーにはならないため
    	// このままとする
    	//
    	if($parent_page_id != 0 ) {
        	$parent_page =& $pagesView->getPageById($parent_page_id);
        	if($parent_page === false || !isset($parent_page['page_id'])) {
	        	//親ページなし
	        	return $errStr;	
	        }
	        if($parent_page['space_type'] == _SPACE_TYPE_PUBLIC && $default_entry_flag == _OFF) {
	        	//パブリックスペースなのに、default_entry_flag=_OFF
	        	return $errStr;	
	        } else if($session->getParameter("_open_private_space") != _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP &&
	        		 $session->getParameter("_open_private_space") != _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC && 
	        		$parent_page['private_flag'] == _ON && $default_entry_flag == _ON) {
	        	//プライベートスペースなのに、default_entry_flag=_ON
	        	return $errStr;	
	        }
    	}
    	if($current_page_id != 0) {
        	$page =& $pagesView->getPageById($current_page_id);
        	if($page === false || !isset($page['page_id'])) {
	        	//カレントページなし
	        	return $errStr;	
	        }
	        if($page['thread_num'] == 0 && $display_flag == _OFF) {
	        	//深さが0なのに準備中
	        	return $errStr;	
	        }
	        if($page['space_type'] == _SPACE_TYPE_PUBLIC && $default_entry_flag == _OFF) {
	        	//パブリックスペースなのに、default_entry_flag=_OFF
	        	return $errStr;	
	        } else if($session->getParameter("_open_private_space") != _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP &&
	        		 $session->getParameter("_open_private_space") != _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC && 
	        		$page['private_flag'] == _ON && $default_entry_flag == _ON) {
	        	//プライベートスペースなのに、default_entry_flag=_ON
	        	return $errStr;	
	        }
    	}
    }
}
?>
