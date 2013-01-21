<?php
// $Id: SecurityManager.class.php,v 1.8 2008/08/13 10:15:20 Ryuji.M Exp $
//
/**
 * Securityチェッククラス
 * (セキュリティ管理でsecurity_levelが「チェックしない」以外の場合使用)
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

/**
 * Security管理の処理を行うクラス
 *
 *
 * @author	Ryuji Masukawa
 * @package	nc.util
 */
class SecurityManager
{
    /**
     * @var DIコンテナ保持
     *
     * @access  private
     */
    var $_container;
    var $_actionChain;
    //var $_request;
    //var $_response;
    var $_session;
    //var $_className;
    var $_errorList;
    var $_db = null;

    var $_config = null;
    var $_logged = false ;

    var $message = '';

    var $bad_globals = array( 'GLOBALS' , '_SESSION' , 'HTTP_SESSION_VARS' , '_GET' , 'HTTP_GET_VARS' , '_POST' , 'HTTP_POST_VARS' , '_COOKIE' , 'HTTP_COOKIE_VARS' , '_SERVER' , 'HTTP_SERVER_VARS' , '_REQUEST' , '_ENV' , '_FILES' ) ;


    /**
     * コンストラクター
     * @param   array   $config($config[_SERVER_CONF_CATID])
     * @access  public
     */
    function SecurityManager(&$config)
    {
        $this->_container =& DIContainerFactory::getContainer();
		$this->_session =& $this->_container->getComponent("Session");
		$this->_actionChain =& $this->_container->getComponent("ActionChain");
        $this->_errorList =& $this->_actionChain->getCurErrorList();
        $this->_db =& $this->_container->getComponent("DbObject");

        $this->_config =& $config;

    }
    /**
     * IP拒否設定
     * @access  public
     */
    function chkEnableBadips()
    {
		$current_ip = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
    	if (isset($this->_config[_SECURITY_CONF_CATID]['enable_badips']) &&
	    	$this->_config[_SECURITY_CONF_CATID]['enable_badips']['conf_value'] == _ON &&
	    	isset($current_ip) && $current_ip != '') {

	    	//$bad_ips = explode("|",$this->_config[_SECURITY_CONF_CATID]['bad_ips']['conf_value']);
	    	$bad_ips = $this->_config[_SECURITY_CONF_CATID]['bad_ips']['conf_value'];
			if($bad_ips != "") {
				$bad_ips_arr = unserialize( $bad_ips ) ;
				foreach( $bad_ips_arr as $bi ) {
					if (!empty($bi) && preg_match("/".$bi."/", $current_ip)) {
						exit ;
					}
				}
			}
		}
    }

    /**
     * IP変動を禁止するベース権限チェック
     * @access  public
     * @return boolean true or false
     */
    function chkGroupsDenyipmove()
    {
        // check session hi-jacking
        $current_ip = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
		if($this->_session->getParameter("_last_ip") != null && $this->_session->getParameter("_last_ip") != $current_ip) {
			$user_id = $this->_session->getParameter("_user_id");
			if($user_id != "0") {
				$user_auth_id_arr = array();
				$user_auth_id_arr[] = $this->_session->getParameter("_user_auth_id");
				if( $this->_config[_SECURITY_CONF_CATID]['groups_denyipmove']['conf_value'] != '' &&
					count( array_intersect( $user_auth_id_arr , unserialize( $this->_config[_SECURITY_CONF_CATID]['groups_denyipmove']['conf_value'] ) ) ) ) {
					// ログアウトし、リダイレクト
					// 強制ログアウト
					$this->_session->close();

					//エラー
					$this->message = _SECURITY_GROUPS_DENYIPMOVE_ERROR;
					$this->outputLog( 'DENYIPMOVE', $user_id) ;
					$this->_errorList->add("groups_denyipmove", _INVALID_INPUT);
					$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
					return false;
				}
			}
		}
		$this->_session->setParameter("_last_ip", $current_ip);
		return true;
    }

    /**
     * 変数汚染が見つかった時の処理
     * NetCommonsのシステムグローバルを上書きしようとする攻撃を見つけた場合
     * @access  public
     */
    function chkContamiAction($key)
    {
    	// Variables contamination
    	if( $this->_config[_SECURITY_CONF_CATID]['contami_action']['conf_value'] &&
				in_array($key, $this->bad_globals) ) {
			$this->message = sprintf(_SECURITY_CONTAMI_ACTION_ERROR, $key);
			$user_id = $this->_session->getParameter("_user_id");
			$this->outputLog( 'CONTAMI', $user_id) ;
			if($this->_config[_SECURITY_CONF_CATID]['contami_action']['conf_value'] >=_SECURITY_VALUE_CRASH) {
				// 強制終了
				$this->_errorList->add("contami_action", _INVALID_INPUT);
				$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
			}
		}
    }


    /**
     * ヌル文字列をスペースに変更する
     * @param string $value
     * @access  public
     */
    function chkNullByte(&$value)
    {
    	// check nullbyte attack
    	if( $this->_config[_SECURITY_CONF_CATID]['san_nullbyte']['conf_value'] && strstr( $value , chr(0) ) ) {
			$value = str_replace( chr(0) , ' ' , $value ) ;

			$this->message = sprintf(_SECURITY_NULL_BYTE_ERROR, $value);
			$user_id = $this->_session->getParameter("_user_id");
			$this->outputLog( 'NullByte' , $user_id , false , 64 ) ;
		}
    }

    /**
     * 疑わしいファイル指定の禁止
     * ファイルを指定していると判断できるリクエスト文字列から、".." というパターンを取り除きます
     * @param string $value
     * @access  public
     */
    function chkParentDir(&$value)
    {
    	// eliminate '..' from requests looks like file specifications
    	if( $this->_config[_SECURITY_CONF_CATID]['file_dotdot']['conf_value'] &&
				strstr( $value , '../' ) ) {
				//(substr( trim( $value ) , 0 , 3 ) == '../' || strstr( $value , '../../' )) ) {
			$this->message = sprintf(_SECURITY_DOUBTFUL_FILE_ERROR, $value) ;
			$user_id = $this->_session->getParameter("_user_id");
			$this->outputLog( 'ParentDir' , $user_id , false , 128 ) ;
			$value = str_replace( chr(0) , '' , $value ) ;
			if( substr( $value , -2 ) != ' .' ) $value .= ' .' ;
		}
    }


    /**
     * 孤立コメントが見つかった時の処理
     * SQLインジェクション対策：
     * 		ペアになる*／のない／*を見つけた時の処理を決めます。
     * 		無害化方法：最後に *／をつけます
     * 		「無害化」がお勧めです
     * @param string  $value
     * @access  public
     */
    function chkIsocomAction(&$value)
    {
    	if( $this->_config[_SECURITY_CONF_CATID]['isocom_action']['conf_value']) {
	    	$str = $value ;
			while( $str = strstr( $str , '/*' ) ) { /* */
				$str = strstr( substr( $str , 2 ) , '*/' ) ;
				if( $str === false ) {
					$this->message = sprintf(_SECURITY_ISOLATED_COMMENT_ERROR, $value) ;
					$user_id = $this->_session->getParameter("_user_id");
					$this->outputLog( 'ISOCOM' , $user_id , false , 64 ) ;

					if($this->_config[_SECURITY_CONF_CATID]['isocom_action']['conf_value'] >= _SECURITY_VALUE_DETOX) {
						$value = $value . '*/';
					}

					if($this->_config[_SECURITY_CONF_CATID]['isocom_action']['conf_value'] >= _SECURITY_VALUE_CRASH) {
						// 強制終了
						$this->_errorList->add("isocom_action", _INVALID_INPUT);
						$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
					}
				}
			}
    	}
    }

    /**
     * 孤立コメントが見つかった時の処理
     * SQLインジェクション対策：
     * 		ペアになる*／のない／*を見つけた時の処理を決めます。
     * 		無害化方法：最後に *／をつけます
     * 		「無害化」がお勧めです
     * @param string  $value
     * @access  public
     */
    function chkUnionAction(&$value)
    {
    	if( $this->_config[_SECURITY_CONF_CATID]['union_action']['conf_value']) {
    		$str = str_replace( array( '/*' , '*/' ) , '' , preg_replace( '?/\*.+\*/?sU' , '' , $value ) ) ;
			if( preg_match( '/\sUNION\s+(ALL|SELECT)/i' , $str ) ) {
				$this->message = sprintf(_SECURITY_UNION_ERROR, $value) ;
				$user_id = $this->_session->getParameter("_user_id");
				$this->outputLog( 'UNION' , $user_id , false , 64 ) ;

				if($this->_config[_SECURITY_CONF_CATID]['union_action']['conf_value'] >= _SECURITY_VALUE_DETOX) {
					$value = preg_replace( '/union/i' , 'uni-on' , $value );
				}

				if($this->_config[_SECURITY_CONF_CATID]['union_action']['conf_value'] >= _SECURITY_VALUE_CRASH) {
					// 強制終了
					$this->_errorList->add("union_action", _INVALID_INPUT);
					$this->_errorList->setType(VALIDATE_ERROR_TYPE);	//VALIDATE_ERRORとする
				}
			}
    	}
    }

    /**
     * Dos攻撃チェック
     * DoS Attack
     * @access  public
     * @return boolean true or false
     */
    function chkDosAttack() {
    	$current_ip = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
    	if($this->_config[_SECURITY_CONF_CATID]['dos_f5action']['conf_value'] == _SECURITY_VALUE_NONE) {
    		// F5アタック対処なし
    		return true;
    	}
    	$_user_auth_id = $this->_session->getParameter("_user_auth_id");
    	$bip_except = $this->_config[_SECURITY_CONF_CATID]['bip_except']['conf_value'];
    	$is_not_bip = false;
		if($bip_except != "") {
			$reliable_groups = unserialize( $bip_except ) ;
			foreach( $reliable_groups as $reliable_group ) {
				if( ! empty( $reliable_group ) && $reliable_group == $_user_auth_id) {
					// 正常終了
					$is_not_bip = true;
					break;
				}
			}
		}

    	$ip = $current_ip ;
		$uri = $_SERVER['REQUEST_URI'] ;
		$time = timezone_date();
		$user_id = $this->_session->getParameter("_user_id");

		if( empty( $ip ) || $ip == '' ) return true ;

		// 現在時刻 -dos_expireのデータ削除
		$int_time = mktime(date("H"), date("i"), intval(date("s")) - intval($this->_config[_SECURITY_CONF_CATID]['dos_expire']['conf_value']), date("m"), date("d"), date("Y"));
		$del_time = date("YmdHis", $int_time);

		$where_params = array("insert_time < ".timezone_date($del_time) => null);
		$result = $this->_db->deleteExecute("security_access", $where_params);
		if($result === false) return false;

		$insert_params = array(
						"ip"  => $ip,
						"request_uri" => $uri,
						"insert_time" => $time
					);

		// F5 attack check (High load & same URI)
		$request =& $this->_container->getComponent("Request");
		if($request->getMethod() == "GET") {
			// F5アタックはGETのみチェック
			$where_params = array(
							"ip"  => $ip,
							"request_uri" => $uri
						);
			$f5_count = $this->_db->countExecute("security_access", $where_params);
		} else {
			$f5_count = 0;
		}
		if( $f5_count > $this->_config[_SECURITY_CONF_CATID]['dos_f5count']['conf_value'] ) {
			// F5アタックとみなす
			$result = $this->_db->insertExecute("security_access", $insert_params, false);
	        if ($result === false) {
		       	return false;
			}
			$this->message = sprintf(_SECURITY_F5ATTACK_ERROR, $uri);
			switch( $this->_config[_SECURITY_CONF_CATID]['dos_f5action']['conf_value'] ) {
				default :
				case _SECURITY_VALUE_EXIT :
					$this->outputLog( 'DoS' , $user_id , true , 16 ) ;
					exit ;
				case _SECURITY_VALUE_REJECT_IP :
					if( !$is_not_bip ) {
						$this->registerBadIps() ;
					}
					$this->outputLog( 'DoS' , $user_id , true , 16 ) ;
					exit ;
				case _SECURITY_VALUE_LOGONLY :
					// ログのみとる
					$this->outputLog( 'DoS' , $user_id , true , 16 ) ;
					break;
			}
		}

		// Check its Agent
		if( trim( $this->_config[_SECURITY_CONF_CATID]['dos_crsafe']['conf_value'] ) != '' &&
			preg_match( $this->_config[_SECURITY_CONF_CATID]['dos_crsafe']['conf_value'] , @$_SERVER['HTTP_USER_AGENT'] ) ) {
			// welcomed crawler
			return true ;
		}


		// Crawler check (High load & different URI)
		$where_params = array(
						"ip"  => $ip
					);
		$crawler_count = $this->_db->countExecute("security_access", $where_params);

		// Insert
		$result = $this->_db->insertExecute("security_access", $insert_params, false);
        if ($result === false) {
	       	return false;
		}

		if( $crawler_count > intval($this->_config[_SECURITY_CONF_CATID]['dos_crcount']['conf_value']) ) {

			// actions for bad Crawler
			$this->message = _SECURITY_CRAWLER_ERROR;
			switch( $this->_config[_SECURITY_CONF_CATID]['dos_craction']['conf_value'] ) {
				default :
				case _SECURITY_VALUE_EXIT :
					$this->outputLog( 'CRAWLER' , $user_id , true , 16 ) ;
					exit ;
				case _SECURITY_VALUE_REJECT_IP :
					if( !$is_not_bip ) {
						$this->registerBadIps() ;
					}
					$this->outputLog( 'CRAWLER' , $user_id , true , 16 ) ;
					exit ;
				case _SECURITY_VALUE_LOGONLY :
					// ログのみとる
					$this->outputLog( 'CRAWLER' , $user_id , true , 16 ) ;
					break;
			}
		}

		return true;
    }

    function registerBadIps( $ip = null )
	{
		$current_ip = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];

		if( empty( $ip ) ) $ip = $current_ip ;
		if( empty( $ip ) ) return false ;

		$bad_ips = $this->_config[_SECURITY_CONF_CATID]['bad_ips']['conf_value'];
		$bad_ips_arr = array();
		if($bad_ips != "") {
			$bad_ips_arr = unserialize( $bad_ips ) ;
			foreach( $bad_ips_arr as $bi ) {
				if (!empty($bi) && preg_match("/".$bi."/", $ip)) {
					// 既に登録済み
					// IPアクセス拒否が有効になっていない
					return true;
				}
			}
		}
		$bad_ips_arr[] = $ip;
		$params = array('conf_value' => serialize($bad_ips_arr));
		$where_params = array(
								'conf_modid' => _SYS_CONF_MODID,
								'conf_catid' => _SECURITY_CONF_CATID,
								'conf_name' => 'bad_ips'
							);

		$result = $this->_db->updateExecute("config", $params, $where_params, false);
		if ($result === false) {
	       	return false;
		}
		return true ;
	}

    /**
     * ログ書き込み
     * @param   string    $type    UNKNOWN, NullByte, ParentDir,CONTAMI,ISOCOM,UNION,DoS,CRAWLER
     * 					NullByte:  level=64     全ロギングを有効にした場合、書き込む
     * 					ParentDir: level=128	高いログ～全ロギングを有効にした場合、書き込む
     *  				CONTAMI:   level=1		全ロギングを有効にした場合、書き込む
     *  				ISOCOM:    level=64		全ロギングを有効にした場合、書き込む
     *  				UNION:     level=64		全ロギングを有効にした場合、書き込む
     *  				DoS:       level=16		低いログ～全ロギングを有効にした場合、書き込む
     *  				CRAWLER:   level=16		低いログ～全ロギングを有効にした場合、書き込む
     *
     * @param   int       $user_id
     * @param   boolean   $unique_check
     * @param   int       $level
     * @return boolean true or false
     * @access  public
     */
    function outputLog( $type = 'UNKNOWN' , $uid = 0 , $unique_check = false , $level = 1 )
	{
		$current_ip = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];

		$uid = is_null($uid) ? "0" : $uid;
		if( $this->_logged ) {
			//　１リクエスト内でログを書き込んでいれば、それ以上、書き込まない
			return true ;
		}

		if( ! ( $this->_config[_SECURITY_CONF_CATID]['log_level']['conf_value'] & $level ) ) return true ;

		$ip = $current_ip ;
		$agent = $_SERVER['HTTP_USER_AGENT'] ;
		$time = timezone_date();

		if( $unique_check ) {
			$order_params = array(
									"insert_time" => "DESC"
								);
			$result = $this->_db->selectExecute("security_log", null, $order_params, 1);
			if ($result === false) {
		       	return $result;
			}
			if(isset($result[0])) {
				$last_ip = $result[0]['ip'];
				$last_type = $result[0]['type'];
				if( $last_ip == $ip && $last_type == $type ) {
					$this->_logged = true ;
					return true ;
				}
			}
		}
		$params = array(
						"uid" => $uid,
						"ip"  => $ip,
						"type" => $type,
						"agent" => $agent,
						"description" => $this->message,
						"extra" => "",
						"insert_time" => $time
					);
		$result = $this->_db->insertExecute("security_log", $params, false, "lid");
        if ($result === false) {
	       	return false;
		}
		$this->_logged = true ;
		return true ;
	}
}
?>