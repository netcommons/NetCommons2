<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 自動登録が使われているかどうかのチェック
 * 項目テーブルの入力チェック(login_id, password,handle, email)
 * 必須チェック
 * 利用規約チェック
 *  リクエストパラメータ
 *  $items,$items_public,$items_reception,$items_password_confirm,$autoregist_disclaimer_ok
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Validator_MobileItemsInputs extends Validator
{
    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値(user_id, items, items_public, items_reception)
     *                  
     * @param   string  $errStr     エラー文字列(未使用：エラーメッセージ固定)
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
        $log =& LogFactory::getLog();
        $log->trace("MobileItemsInputsの処理にはいりました", "Validator_MobileItemsInputs.class.php#validate");


    	// container取得
		$container =& DIContainerFactory::getContainer();
    	$session =& $container->getComponent("Session");
		$usersView =& $container->getComponent("usersView");
		$configView =& $container->getComponent("configView");
////		$fileUpload =& $container->getComponent("FileUpload");
		$commonMain =& $container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$userinfCheck =& $container->getComponent("userinfCheck");
////		$uploadAction =& $container->getComponent("uploadAction");
		


        // 編集するuser情報
        // 現在は基本的に自分の情報しか編集できないことになっている

        // 自分
        $user_id = $session->getParameter("_user_id");
        if($user_id == "0")  return $errStr;
        // 自分の権限
        $_user_auth_id = $session->getParameter("_user_auth_id");
        // 自分の情報
        $user =& $usersView->getUserById($user_id);
        if($user === false) return $errStr;

        //
        // 編集対象の人物のuser_id
        //
        if(isset($attributes['user_id'])) {
            $edit_user_id = $attributes['user_id'];
        } else {
            $edit_user_id = $session->getParameter("_user_id");
        }
        if( $user_id != $edit_user_id ) {
            return $errStr;
        }

        //
        // システムユーザーID
        //
        $system_user_id = $session->getParameter("_system_user_id");

        // エラーリターンすると、元の編集画面に戻るので
        // その場合、userデータが必要になるから
        $edit_user =& $usersView->getUserById($edit_user_id, array($usersView, "_getUsersFetchcallback"));
        BeanUtils::setAttributes($action, array("user"=>$edit_user));


        // システム管理者がシステム管理者以外で編集されそうになっている場合、エラー
        if($edit_user_id == $system_user_id && $user_id != $system_user_id) {
            return _INVALID_INPUT;
        }


// ここではまだFileUploadが実現化されてない
//        // UPloadされるアバター画像
//    	$files = $fileUpload->getOriginalName();
//    	$files_key = array_keys($files);			

    	
    	
        $item_err_msg = array();

        //
        // 自分自身か、自分自身より低い権限か、高い権限かをセット
        //
        if($edit_user_id == $user_id) { // 自分自身
            $public_flag_colname = "self_public_flag";
        } 
        else if($edit_user['user_authority_id'] >= $_user_auth_id){ // 自分と同じか高い権限
            $public_flag_colname = "over_public_flag";
        }
        else { // 低い権限
            $public_flag_colname = "under_public_flag";
        }

        $tmp_show_items =& $usersView->getShowItems( $edit_user_id, $_user_auth_id );
        if( $tmp_show_items === false ) {
            return $errStr;
        }
        $show_items = array();
        foreach( $tmp_show_items as $col ) {
            foreach( $col as $it ) {
                $show_items[$it['item_id']] = $it;
            }
        }
        // 必須チェックはshowItemsをベースに考えないといけない
        foreach( $show_items as $s_i ) {
            // 必須入力チェック
            // passwordは未入力は「変更なし」を意味するので特にチェックしない
            // type==fileの場合も未入力は「変更なし」を意味することに
            // またtype==fileの場合は、ここではチェックできない
            //     ActionChainの並びのため、このvalidate、まだFileUpLoadが実行できない。（らしい）
            if( $s_i['tag_name'] == 'password' ) {
                continue;
            }
            if( $s_i['type'] == 'file' ) {
                continue;
            }
            //
            // login_id, authority, active_flagは管理者といえども変更できないようにする
            // なのでチェックは入れない
            //
            if( $s_i['tag_name'] == 'login_id' || $s_i['tag_name']=='active_flag_lang' || $s_i['tag_name']=='role_authority_name' ) {
                continue;
            }
            if( $s_i['require_flag'] == _ON ) {
                // 編集されようとしているのが管理者ではない　または　自分が管理者
                // かつ、項目は編集可能と設定されている
                // かつ、管理者以外の編集で状態や権限項目ではない
                // かつ、状態が使用可能以外ではない
                if( ($edit_user_id != $system_user_id || $user_id == $system_user_id)
                    &&
                    $s_i[$public_flag_colname] == USER_EDIT
                    &&
                    !($user_id == $system_user_id && ($s_i['tag_name']=="active_flag_lang"||$s_i['tag_name']=='role_authority_name'))
                    &&
                    !($s_i['tag_name']=="active_flag_lang" && ($edit_user['active_flag_lang']==USER_ITEM_ACTIVE_FLAG_PENDING || $edit_user['active_flag_lang']==USER_ITEM_ACTIVE_FLAG_MAILED)) 
                ) {
                    if( !isset( $attributes['userinf_items'][ $s_i['item_id'] ] ) ) {
                        $item_err_msg[] = $s_i['item_id'] . ":" . sprintf(_REQUIRED, $s_i['item_name']);
                        continue;
                    }
                    if( is_array( $attributes['userinf_items'][ $s_i['item_id'] ] ) ) {
                        if( count( $attributes['userinf_items'][ $s_i['item_id'] ] ) == 0 ) {
                            $item_err_msg[] = $s_i['item_id'] . ":" . sprintf(_REQUIRED, $s_i['item_name']);
                        }
                    }
                    else {
                        if( $attributes['userinf_items'][ $s_i['item_id'] ] == "" ) {
                            $item_err_msg[] = $s_i['item_id'] . ":" . sprintf(_REQUIRED, $s_i['item_name']);
                        }
                    }
                }
            }
        }

    	foreach($attributes['userinf_items'] as $userinf_items_key => $input_item) {

            // 入力項目IDに該当する情報がない
            if( !isset( $show_items[ $userinf_items_key ] ) ) {
                return _INVALID_INPUT;
            }
            $items =& $show_items[ $userinf_items_key ];
            if( !isset( $items['item_id'] ) ) {
                return _INVALID_INPUT;
            }

            if( $items['define_flag'] == _ON && defined( $items['item_name'] ) ) {
                $items['item_name'] = constant($items['item_name']);
            }

            // 入力情報がlogin_idだったら
            if($items['tag_name'] == "login_id") {
                $ret = $userinfCheck->checkLoginId( $input_item );
                if( $ret != "" ) {
                    //return $ret;
                    $item_err_msg[] = $userinf_items_key . ":" . $ret;
                }
            }
            else if($items['tag_name'] == "password") {
                $new_password = $input_item;
                $cur_password = $attributes['userinf_items_currentpwd'][$userinf_items_key];
                $confirm_password = $attributes['userinf_items_confirmpwd'][$userinf_items_key];

                if( $new_password != "" ) {
                    $ret = $userinfCheck->checkPassword( $edit_user_id, $items['item_name'], $new_password, $cur_password, $confirm_password );
                    if( $ret != "" ) {
                        //return $ret;
                        $item_err_msg[] = $userinf_items_key . ":" . $ret;
                    }
                }
            }
            else if($items['tag_name'] == "handle") {
                $ret = $userinfCheck->checkHandle( $edit_user_id, $items['item_name'], $input_item );
                if( $ret != "" ) {
                    //return $ret;
                    $item_err_msg[] = $userinf_items_key . ":" . $ret;
                }
            
            }
            else if($items['tag_name'] == "role_authority_name") {
                $ret = $userinfCheck->checkRoleAuth( $edit_user_id, $items['item_name'], $input_item );
                if( $ret != "" ) {
                    //return $ret;
                    $item_err_msg[] = $userinf_items_key . ":" . $ret;
                }
            }
            else if($items['tag_name'] == "active_flag_lang") {
                $ret = $userinfCheck->checkActiveFlag( $edit_user_id, $items['item_name'], $input_item );
                if( $ret != "" ) {
                    //return $ret;
                    $item_err_msg[] = $userinf_items_key . ":" . $ret;
                }
            }

            // それぞれの入力項目について、メールタイプであると設定されているならば
            // メールアドレス用のチェックを通す
            if($items['type'] == "email" || $items['type'] == "mobile_email") {
                if( !isset( $attributes['userinf_items_email_reception_flag'][$userinf_items_key] ) ) {
                    $email_reception_flag = false;
                }
                else {
                    $email_reception_flag = $attributes['userinf_items_email_reception_flag'][$userinf_items_key];
                }
                $ret = $userinfCheck->checkEmail( $edit_user_id, $items, $input_item, $email_reception_flag );
                if( $ret != "" ) {
                    //return $ret;
                    $item_err_msg[] = $userinf_items_key . ":" . $ret;
                }
            }
            // それぞれの入力項目について、公開非公開の設定があるならば
            if( isset(  $attributes['userinf_items_public_flag'][$userinf_items_key] ) ) {
                $ret = $userinfCheck->checkPublicFlag( $edit_user_id, $items, $attributes['userinf_items_public_flag'][$userinf_items_key] );
                if( $ret != "" ) {
                    //return $ret;
                    $item_err_msg[] = $userinf_items_key . ":" . $ret;
                }
            }
            
/*
			if($items['type'] == "file") {
				// File
				// 必須入力チェック
				if($items['require_flag'] == _ON || (isset($autoregist_use_items_req[$items['item_id']]) && $autoregist_use_items_req[$items['item_id']] == _ON)) {
					
					$error_flag = true;
					foreach($files_key as $file_key) {
						if($items['item_id'] == $file_key && ($files[$file_key] != "" && $files[$file_key] != null)) {
							$error_flag = false;
							break;
						}
					}
					if($error_flag) {
						//ファイルアップロード未対応携帯なのに、このファイルは必須扱いになっている...
						//
						//つまり、この携帯からは登録できないので、PCから登録していただくか、
						//管理者にお願いして、必須から任意にかえていただくことを薦めます。
						//
						if (empty($files)) {
							return $err_prefix.LOGIN_ERR_FILE_UPLOAD_NOABILITY;
						} else {
							return $err_prefix.sprintf(_REQUIRED, $items['item_name']);	
						}
					}
				}
				continue;
			}
*/
    		
    	}
        if( count( $item_err_msg ) > 0 ) {
            $item_err_msg_str = implode( "|", $item_err_msg );
            return $item_err_msg_str;
        }
    	
/*
    	
    	// File
    	$garbage_flag = _OFF;
    	$filelist = $uploadsAction->uploads($garbage_flag);
    	
		foreach($filelist as $key => $file) {
			if(isset($file['error_mes']) && $file['error_mes'] != "" && $file['error_mes'] != _FILE_UPLOAD_ERR_UPLOAD_NOFILE) {
				$err_prefix = $key.":";
				return $err_prefix.$file['error_mes'];
            }
		}
    	
*/		
    	// actionChain取得
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();
		BeanUtils::setAttributes($action, array("show_items"=>$show_items));
		return;
    }
        
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array items
	 * @access	private
	 */
	function &_getItemsFetchcallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['item_id']] = $row;
		}
		return $ret;
	}
}
?>
