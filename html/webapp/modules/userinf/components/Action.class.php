<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 会員情報モジュール編集時更新用クラス
 *
 */
class Userinf_Components_Action 
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
	var	$_usersAction = null;
	var	$_session = null;

	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Userinf_Components_Action() 
	{
		$log =& LogFactory::getLog();
		$log->trace("component userinf check のコンストラクタが実行されました", "UserinfAction#Userinf_Components_Action");

		$this->_container =& DIContainerFactory::getContainer();
		$this->_usersView =& $this->_container->getComponent("usersView");
		$this->_session =& $this->_container->getComponent("Session");
		$this->_usersAction =& $this->_container->getComponent("usersAction");
	}
	
	/**
	 * タイムゾーンの更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updTimezone($edit_user_id,$input,$old_data) 
    {
		$commonMain =& $this->_container->getComponent("commonMain");
        $timezoneMain =& $commonMain->registerClass(WEBAPP_DIR.'/components/timezone/Main.class.php', "Timezone_Main", "timezoneMain");

		$tag_name = "timezone_offset";
		//$input = substr($input, 0, strlen($input) - 1); //語尾の|除去
		$input = $timezoneMain->getFloatTimeZone($input);
		$params = array($tag_name => $input);

		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );
		if( $ret == '' ) {
			$this->_session->setParameter("_timezone_offset",$input);
		}
		return $ret;
	}
	/**
	 * 権限の更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updRoleAuthority($edit_user_id,$input,$old_data) 
	{
		$tag_name = "role_authority_id";
		//$input = substr($input, 0, strlen($input) - 1); //語尾の|除去
		$input = $input;
		$params = array($tag_name => $input);
		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );

		if( $ret == '' && $input != $old_data ) {
			// ベース権限が管理者に変更された場合、すべてのルームに主担として参加させる

			// 本当だったら、ここで権限が管理者に変更された場合、全てのルームに主坦として参加させる
			// プライペートスペースが新たにできたらpagesのdisplay_flagも変更する、などの措置を入れる
			// 現在携帯からは自分自身のデータしか変更できない
			// すなわち権限変更はされることがないので、この部分の処理は現状省略する
		}
		return $ret;
	}
	/**
	 * 言語の更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updLangDirname($edit_user_id,$input,$old_data) 
	{
		$commonMain =& $this->_container->getComponent("commonMain");
        $languagesView =& $commonMain->registerClass(WEBAPP_DIR.'/components/languages/View.class.php', "Languages_View", "languagesView");

		$tag_name = "lang_dirname";
		//$content_lang = substr($input, 0, strlen($input) - 1);  //語尾の|除去
		$content_lang = $input;

		$languages =& $languagesView->getLanguagesList();
		$params = array($tag_name => "japanese");                       //初期値-固定値
		$input = "japanese";
		foreach($languages as $key => $language) {
			if($content_lang == $language) {
				$params = array($tag_name => $key);
				$input = $key;
				break;
			}
		}
		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );
		if( $ret == '' ) {
			$this->_session->setParameter("_lang", $input);
		}
		return $ret;
	}
	/**
	 * 状態の更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updActiveFlag($edit_user_id,$input,$old_data) 
	{
		$tag_name = "active_flag";
		//$input = substr($this->content, 0, strlen($this->content) - 1); //語尾の|除去
		$input = ($input == _ON) ? _ON : _OFF;
		$params = array($tag_name => $input);
		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );
		return $ret;
	}
	/**
	 * パスワードの更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updPassword($edit_user_id,$input,$old_data) 
	{
		$tag_name = "password";
 		$input = md5($input);
		// パスワード変更日時更新
		$params = array(
			$tag_name => $input,
			"password_regist_time" => timezone_date()
		);
		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );
		return $ret;
	}
	/**
	 * ハンドルの更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updHandle($edit_user_id,$input,$old_data) 
	{
		$tag_name = "handle";
		$params = array(
			$tag_name => $input
		);
		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );

		if( $ret == "" && $input!=$old_data ) {
			$this->_session->setParameter("_handle", $input);
			$private_where_params = array(
                "{pages}.insert_user_id" => $edit_user_id,
                "{pages}.private_flag" => _ON,
                "{pages}.page_id={pages}.room_id" => null
			);

			$commonMain =& $this->_container->getComponent("commonMain");
        	$pagesView =& $commonMain->registerClass(WEBAPP_DIR.'/components/pages/View.class.php', "Pages_View", "paegsView");
			$private_pages = $pagesView->getPagesUsers($private_where_params, array("default_entry_flag" => "ASC"), 2);
			if($private_pages === false) return 'error';

			if( isset($private_pages[0]) ) {
        		$pagesAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/pages/Action.class.php', "Pages_Action", "paegsAction");
				$result = $pagesAction->updPermaLink($private_pages[0], $input);
                if($result === false)  {
                    return 'error';
                }
      			$configView =& $commonMain->registerClass(WEBAPP_DIR.'/components/config/View.class.php', "Config_View", "configView");
                $config = $configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
                if($config === false) return 'error';
                if(isset($private_pages[1]) &&
                    $config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_GROUP ||
                    $config['open_private_space']['conf_value'] == _OPEN_PRIVATE_SPACE_MYPORTAL_PUBLIC) {
                    $result = $pagesAction->updPermaLink($private_pages[1], $input);
                    if($result === false)  {
                        return 'error';
                    }
                }
			}
		}

		return $ret;
	}
	/**
	 * その他パターンの更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updOthers($edit_user_id,$item_id,$input,$email_reception_flag, $public_flag,$old_data,$old_email_reception, $old_public) 
	{
		$update_flag = false;
		if($input != $old_data || $email_reception_flag != $old_email_reception || $public_flag != $old_public) {
			$update_flag = true;
			//更新
			$params = array(
					"public_flag" => intval($public_flag),
					"email_reception_flag" => intval($email_reception_flag),
					"content" => $input
			);
			$where_params = array("user_id" => $edit_user_id,"item_id" => $item_id);
			$result = $this->_usersAction->updUsersItemsLink($params, $where_params);
			if ($result === false) return 'error';
		}
		//更新日時更新
		if($update_flag) {
			$where_params = array("user_id" => $edit_user_id);
			$result = $this->_usersAction->updUsers(array(), $where_params, true);
			if ($result === false) return 'error';
		}
		return '';
	}
	/**
	 * その他パターンの新規
	 * @param 
	 * @return 
	 * @access	public
	 */
	function insOthers($edit_user_id,$item_id,$input,$email_reception_flag, $public_flag) 
	{
		$params = array(
			"user_id" => $edit_user_id,
			"item_id" => $item_id,
			"public_flag" => intval($public_flag),
			"email_reception_flag" => intval($email_reception_flag),
			"content" => $input
		);
		//新規追加
		$result = $this->_usersAction->insUserItemLink($params);
		if ($result === false) return 'error';

		//更新日時更新
		$where_params = array("user_id" => $edit_user_id);
		$result = $this->_usersAction->updUsers(array(), $where_params, true);
		if ($result === false) return 'error';

		return '';
	}
	/**
	 * 単純なパターンの更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function updSimple($edit_user_id,$tag_name,$input,$old_data) 
	{
		$params = array($tag_name => $input);
		$ret = $this->_updData( $edit_user_id, $input, $old_data, $params );
		return $ret;
	}
	/**
	 * DB更新
	 * @param 
	 * @return 
	 * @access	public
	 */
	function _updData( $user_id, $input, $old_data, $params )
	{
		if( $input == $old_data ) {
			return true;
		}
 		$where_params = array("user_id" => $user_id);
		$result = $this->_usersAction->updUsers($params, $where_params);
		if ($result === false) {
			return 'error';
		}
		return '';
	}
}
?>
