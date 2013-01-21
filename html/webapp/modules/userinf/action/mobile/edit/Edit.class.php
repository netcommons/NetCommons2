<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報-携帯からの編集
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Userinf_Action_Mobile_Edit extends Action
{
	// リクエストパラメータを受け取るため
	var	$user_id = null;
	var $userinf_items = null;
	var	$userinf_items_public_flag = null;
	var	$userinf_items_email_reception_flag = null;

    // validatorでset
    var $show_items = null;
	
	// 使用コンポーネントを受け取るため
	var $session = null;
	var $request = null;
	var $uploadsAction = null;
	var $usersView = null;
	var $usersAction = null;
	var $userinfAction = null;

	
	// 値をセットするため
	var	$unique_id = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		// まずvalidateでチェックできなかった画像ファイルについて処理を行う
		
		//ガーベージフラグ更新
		$garbage_flag = _ON;

		// ファイル取り込み
		$filelist = $this->uploadsAction->uploads($garbage_flag, "", array(_UPLOAD_THUMBNAIL_MAX_WIDTH_IMAGE, _UPLOAD_THUMBNAIL_MAX_HEIGHT_IMAGE));

		foreach( $filelist as $item_id=>$input_item ) {

			$content = "?action=".$input_item['action_name']."&upload_id=".$input_item['upload_id'];

			// Upload画像があった
			$users_item_links =& $this->usersView->getUserItemLinkById($this->user_id, $item_id);

			if(isset($users_item_links['user_id'])) {
				//以前のアバターの画像削除
				$upload_path = $users_item_links['content'];
				$pathList = explode("&", $upload_path);
				if( isset($pathList[1]) ) {
					$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
					$result = $this->uploadsAction->delUploadsById($upload_id);
					if ($result === false) return false;
				}
				//更新
				$where_params = array("user_id" => $this->user_id, "item_id" => $item_id);
				$result = $this->usersAction->updUsersItemsLink(array("content" => $content), $where_params);
				if ($result === false) return false;
			} 
			else {
				$params = array(
							"user_id" => $this->user_id,
							"item_id" => $item_id,
							"public_flag" => intval($this->userinf_items_public_flag[$item_id]),
							"email_reception_flag" => _OFF,
							"content" => $content
							);
				//新規追加
				$result = $this->usersAction->insUserItemLink($params);
				if ($result === false) return false;
			}
			//更新日時更新
			$where_params = array("user_id" => $this->user_id);
			$result = $this->usersAction->updUsers(array(), $where_params, true);
			if ($result === false) return false;

			//ガーベージフラグ更新
			$garbage_flag = _OFF;
			$result = $this->uploadsAction->updGarbageFlag($input_item['upload_id'], $garbage_flag);
			if ($result === false) return false;
		}

		$user_auth_id = $this->session->getParameter("_user_auth_id");


		foreach( $this->userinf_items as $index=>$input ) {

			$is_users_tbl_fld = $this->usersView->isUsersTableField( $this->show_items[$index]['tag_name'] );

			//$showItems = $this->usersView->getItemById( $index );
			if( !isset($this->show_items[$index]) ) {
				continue;
			}

			// role_authority_name,active_flag_lang,login_id は携帯からは変えさせない
			if( $this->show_items[$index]['tag_name'] == "timezone_offset_lang" ) {
				$this->userinfAction->updTimezone( $this->user_id, $input,  $this->show_items[$index]['content'] );
			}
			else if( $this->show_items[$index]['tag_name'] == "role_authority_name" ) {
//				$this->userinfAction->updRoleAuthority( $this->user_id, $input,  $this->show_items[$index]['content'] );
			}
			else if( $this->show_items[$index]['tag_name'] == "lang_dirname_lang" ) {
				$this->userinfAction->updLangDirname( $this->user_id, $input,  $this->show_items[$index]['content'] );
			}
			else if( $this->show_items[$index]['tag_name'] == "active_flag_lang" ) {
//				$this->userinfAction->updActiveFlag( $this->user_id, $input, $this->show_items[$index]['content'] );
			}
			else if( $this->show_items[$index]['tag_name'] == "password" ) {
				if( $input != '' ) {
					$this->userinfAction->updPassword( $this->user_id, $input, $this->show_items[$index]['content'] );
				}
			}
			else if( $this->show_items[$index]['tag_name'] == "handle" ) {
				$this->userinfAction->updHandle( $this->user_id, $input, $this->show_items[$index]['content'] );
			}
			else if( $this->show_items[$index]['tag_name'] == "login_id" ) {
			}
			else if( $this->show_items[$index]['tag_name'] == "" || $is_users_tbl_fld == false ) {
				$users = $this->usersView->getUserItemLinkById( $this->user_id, $index );
				switch( $this->show_items[$index]['type'] ) {
					case "checkbox":
							if( is_array( $input ) ) {
								$input = implode( "|", $input );
							}
					case "select":
					case "radio":
						if($this->show_items[$index]['define_flag'] == _ON) {
							//
							//定義名称がある場合、そちらで登録
							//英語と日本語の選択されたものが同じであると認識させる必要があるため
							//
							$options_arr = explode("|", $this->show_items[$index]['options']);
							$buf_options_arr = $options_arr;
							$count = 0;
							foreach($options_arr as $options) {
								if(defined($options)) {
									$options_arr[$count] = constant($options);
								}
								$count++;
							}

							$content_options_arr = explode("|", $input);
							$count = 0;
							foreach($content_options_arr as $content_options) {
								if($content_options != "") {
									$count_sub = 0;
									foreach($options_arr as $options) {
										if($content_options == $options) {
											$content_options_arr[$count] = $buf_options_arr[$count_sub];
										}
										$count_sub++;
									}
								}
								$count++;
							}
							$input = implode("|", $content_options_arr) . "|";
						}
					case "text":
					case "textarea":
					case "email":
					case "mobile_email":
						if( isset( $this->userinf_items_email_reception_flag[$index] ) ) {
							$email_reception_flag = $this->userinf_items_email_reception_flag[$index];
						}
						else {
							$email_reception_flag = _OFF;
						}
						if( isset( $this->userinf_items_public_flag[$index] ) ) {
							$public_flag = $this->userinf_items_public_flag[$index];
						}
						else {
							$public_flag = _OFF;
						}
						
						if( $users != false ) {
							$this->userinfAction->updOthers( $this->user_id, $index, $input, $email_reception_flag, $public_flag, 
														$this->show_items[$index]['content'], $users['email_reception_flag'], $users['public_flag'] );
						}
						else {
							$this->userinfAction->insOthers( $this->user_id, $index, $input, $email_reception_flag, $public_flag );
						}
						break;
				}

			}
			else {
				$this->userinfAction->updSimple( $this->user_id, $this->show_items[$index]['tag_name'], $input, $this->show_items[$index]['content'] );
			}
		}
		return 'success';
	}
    function chkFileRequired( $show_items, $files, $filelist )
    {
        foreach( $show_items as $item ) {
            if( $item['type'] == "file" ) {
                if( $item['require_flag'] == _ON ) {
                    if( !isset( $files[ $item['item_id'] ] ) ) {
                        return LOGIN_ERR_FILE_UPLOAD_NOABILITY;
                    }
                    else {
                        if( !isset( $filelist[ $item['item_id'] ] ) ) {
                            sprintf(_REQUIRED, $item['item_name']);
                        }
                    }
                }
            }
        }
        return "";
    }
}
?>
