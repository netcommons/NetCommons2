<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報モジュール編集時チェック用クラス
 *
 */
class Userinf_Components_Check 
{
	/**
	 * @var ConfigViewオブジェクトを保持
	 *
	 * @access	private
	 */
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	var	$_usersView = null;
	var	$_session = null;

	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Userinf_Components_Check() 
	{
		$log =& LogFactory::getLog();
		$log->trace("component userinf check のコンストラクタが実行されました", "UserinfCheck#Userinf_Components_Check");

		$this->_container =& DIContainerFactory::getContainer();
		$this->_usersView =& $this->_container->getComponent("usersView");
		$this->_session =& $this->_container->getComponent("Session");
	}
	
	/**
	 * 入力されたlogin_idチェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkLoginId($login_id) 
    {
        // 入力文字チェック
        $login_len = strlen($content);
        if($login_len < USER_LOGIN_ID_MINSIZE || $login_len > USER_LOGIN_ID_MAXSIZE) {
            return sprintf(_MAXRANGE_ERROR, USER_ITEM_LOGIN, USER_LOGIN_ID_MINSIZE, USER_LOGIN_ID_MAXSIZE);
        }

        // 半角英数または、記号
        if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $login_id)) {
            return sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_LOGIN);
        }

        // 重複チェック
        $where_params = array("login_id" => $login_id);
        $users =& $this->_usersView->getUsers($where_params);
        $count = count($users);
        if($count >= 1 && $users[0]['user_id'] != $edit_user_id) {
            return sprintf(USERINF_MES_ERROR_DUPLICATE, USER_ITEM_LOGIN, USER_ITEM_LOGIN);
        }
		return "";
	}
	/**
	 * 入力されたpasswordチェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkPassword( $edit_user_id, $item_name, $new_pwd, $cur_pwd, $confirm_pwd )
	{

		$err_mes = "";

		//必須チェック
		//管理者の場合、現在のパスワードのチェックは行わない
		$edit_user =& $this->_usersView->getUserById($edit_user_id);
		
		// 自分の権限
		$user_auth_id = $this->_session->getParameter("_user_auth_id");

		// 自分がシステム管理者じゃなくてかつ修正対象の人の権限が自分の権限以上の人だったら
		// （つまり自分の編集時は必須）
		if($this->_session->getParameter("_system_user_id") != $this->_session->getParameter("_user_id") && $edit_user['user_authority_id'] >= $user_auth_id) {
			if((!isset($cur_pwd) || $cur_pwd == "")) {
				$err_mes .= sprintf(_REQUIRED, USERINF_CURRENT_PASS);
			}
		}
		// 新パスワードが設定されてなければ、エラー
		if( $new_pwd == "" ) {
			if($err_mes != "") {
				$err_mes .= "<br />";
			}
			$err_mes .= sprintf(_REQUIRED, USERINF_NEW_PASS);
		}
		// 確認のための再入力が設定されてなければ
		if($confirm_pwd == "") {
			if($err_mes != "") {
				$err_mes .= "<br />";
			}
			$err_mes .= sprintf(_REQUIRED, USERINF_CONFIRM_NEW_PASS);
		}
		// ここまでのチェックですでにエラーがあれば
		if($err_mes != "") {
			return $err_mes;
		}

		//管理者の場合、現在のパスワードのチェックは行わない
		if($this->_session->getParameter("_system_user_id") != $this->_session->getParameter("_user_id") && $edit_user['user_authority_id'] >= $user_auth_id) {
			if($edit_user['password'] != md5($cur_pwd)) {
				return USERINF_ERR_CURRENT_PASS_DISACCORD;
			}
		}
		// 新パスワードと確認パスワードが一致しない
		if($new_pwd != $confirm_pwd) {
			return USERINF_ERR_PASS_DISACCORD;
		}
		// 入力文字チェック
		$pass_len = strlen($new_pwd);
		if($pass_len < USER_PASSWORD_MINSIZE || $pass_len > USER_PASSWORD_MAXSIZE) {
			return sprintf(_MAXRANGE_ERROR, USER_ITEM_PASSWORD, USER_PASSWORD_MINSIZE, USER_PASSWORD_MAXSIZE);
		}
		// 半角英数または、記号
		if(preg_match(_REGEXP_ALLOW_HALFSIZE_SYMBOL, $new_pwd)) {
			return sprintf(_HALFSIZESYMBOL_ERROR, USER_ITEM_PASSWORD);
		}
		return "";
	}
	/**
	 * 入力されたhandleチェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkHadle( $edit_user_id, $item_name, $handle )
	{
		// 重複チェック
		$where_params = array("handle" => $handle);
		$users =& $this->_usersView->getUsers($where_params);
		$count = count($users);
		if($count >= 1 && $users[0]['user_id'] != $edit_user_id) {
			return sprintf(USERINF_MES_ERROR_DUPLICATE, USER_ITEM_HANDLE, USER_ITEM_HANDLE);
		}
		return "";
	}
	/**
	 * 入力された権限チェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkRoleAuth( $edit_user_id, $item_name, $new_auth )
	{
		if($this->_session->getParameter("_system_user_id") == $edit_user_id) {
			return _INVALID_INPUT;
		}
		return "";
	}
	/**
	 * 入力された状態チェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkActiveFlag( $edit_user_id, $item_name, $new_flag )
	{
		if($this->_session->getParameter("_system_user_id") == $edit_user_id) {
			return _INVALID_INPUT;
		}
		return "";
	}
	/**
	 * 入力されたハンドルチェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkHandle( $edit_user_id, $item_name, $new_handle )
	{
		// 重複チェック
		$where_params = array("handle" => $new_handle);
		$users =& $this->_usersView->getUsers($where_params);
		$count = count($users);
		if($count >= 1 && $users[0]['user_id'] != $edit_user_id) {
			return sprintf(USERINF_MES_ERROR_DUPLICATE, USER_ITEM_HANDLE, USER_ITEM_HANDLE);
		}
	}
	/**
	 * 入力されたメールアドレスチェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkEmail( $edit_user_id, $items, $email, $email_reception_flag )
	{
		// 入力文字チェック
		if ( $email != "" && !strpos($email, "@") ) {
			return  sprintf(_FORMAT_WRONG_ERROR, $items['item_name']);
		}
		// 重複チェック
		if($email != "") {
			$where_param = array(
				"({items}.type = 'email' OR {items}.type = 'mobile_email')  " => null,
				"{users_items_link}.content" => $email
			);
			$chk_items =& $this->_usersView->getItems($where_param);
			$count = count($chk_items);
			if($count >= 1 && $chk_items[0]['user_id'] != $edit_user_id) {
				return sprintf(USERINF_MES_ERROR_DUPLICATE, $items['item_name'] , $items['item_name'] );
			}
		}
		if( $email_reception_flag !== false ) {
			// メール受信可否
			if( !($email_reception_flag == _ON || $email_reception_flag == _OFF) ) {
				return  _INVALID_INPUT;
			}
			if($email_reception_flag == _OFF && $items['allow_email_reception_flag'] == _OFF) {
				//受信可否を設定できないにも関わらず、受信拒否をしようとした
				return  sprintf(USERINF_ERR_RECEPTION, $items['item_name']);
			}
		}
		return "";
	}
	/**
	 * 入力された項目の公開非公開チェック
	 * @param 
	 * @return 
	 * @access	public
	 */
	function checkPublicFlag( $edit_user_id,$items,$public_flag )
	{
		// 公開設定
		if( !($public_flag == _ON || $public_flag == _OFF)) {
			return  _INVALID_INPUT;
		}
		if($public_flag == _OFF && $items['allow_public_flag'] == _OFF) {
			//公開可否を設定できないにも関わらず、公開拒否をしようとした
			return  sprintf(USERINF_ERR_PUBLIC, $items['item_name']);
		}
		return "";
	}
}
?>
