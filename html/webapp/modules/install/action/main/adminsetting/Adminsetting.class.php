<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install サイト管理者についての設定セッション登録
 * DBにも登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_Action_Main_Adminsetting extends Action
{
    // リクエストパラメータを受け取るため
    var $handle = null;
    var $login_id = null;
    var $password = null;
    var $confirm_pass = null;
    
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    var $actionChain = null;
    
    // 値をセットするため
    
    /**
     * Install サイト管理者についての設定セッション登録
     *
     * @access  public
     */
    function execute()
    {
    	$base_dir = $this->session->getParameter("base_dir");
    	
    	$this->session->setParameter("install_handle", $this->handle);
    	$this->session->setParameter("install_login_id", $this->login_id);
    	$this->session->setParameter("install_pass", $this->password);
    	$this->session->setParameter("install_confirm_pass", $this->confirm_pass);
    	
    	if(!$this->installCompmain->getSessionDb($database, $dbhost, $dbusername, $dbpass, $dbname, $dbprefix, $dbpersist, $dsn)) {
    		// DB接続失敗
			$errorList->add(get_class($this), sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname));
			return 'error';
    	}
    	
    	$errorList =& $this->actionChain->getCurErrorList();
    	
    	//
		// DB接続
		//	
    	//include_once $base_dir.'/maple/nccore/db/DbObjectAdodb.class.php';
    	include_once BASE_DIR.'/maple/nccore/db/DbObjectAdodb.class.php';
    	
    	$dbObject = new DbObjectAdodb();
    	$dbObject->setPrefix($dbprefix);
    	$dbObject->setDsn($dsn);
    	$conn_result = @$dbObject->connect();
    	
		if ($conn_result == false) {
			// DB接続失敗
			$errorList->add(get_class($this), sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname));
			return 'error';
		}
		
		$result = $dbObject->selectExecute("users", array("system_flag" => _ON), null, 1);
		if($result === false) {
			return 'error';
		}
		
		if(isset($result[0])) {
			// 既にデータが存在
			// ありえないので、エラーとする
			$errorList->add(get_class($this), sprintf(INSTALL_DBCHECK_NOT_CONNECT, $dbname));
			return 'error';
		} else {
			// インサート
			// insUserと同等の処理だが、_dbが使えないため、ここに記述
			// サイトIDもまだ生成されていないのでサイトIDから生成(insertSite)と同等の処理
			$sessionID = $this->session->getID();
			$new_site_id = sha1(uniqid($sessionID.microtime(), true));
			$id = $dbObject->nextSeq("users");
			$user_id = sha1(uniqid($new_site_id.$id, true));
			
			$this->session->setParameter("install_self_site_id", $new_site_id);
    		$this->session->setParameter("install_user_id", $user_id);
    	
			
			$params = array(
							"site_id" => $new_site_id,
							"url" => "BASE_URL",
							"self_flag" => _ON,
							"commons_flag" => _ON,
							"certify_flag" => _ON,
							"insert_time " => "",
							"insert_site_id " => $new_site_id,
							"insert_user_id " => $user_id,
							"insert_user_name " => "",
							"update_time " => "",
							"update_site_id " => $new_site_id,
							"update_user_id " => $user_id,
							"update_user_name " => ""
						);
			
			$result = $dbObject->insertExecute("sites", $params);
			if($result === false) {
				return 'error';
			}
			$timezone_offset = -1 * INSTALL_DEFAULT_TIMEZONE;
			$time = date("YmdHis");
			$int_time = mktime(intval(substr($time, 8, 2)) + $timezone_offset, intval(substr($time, 10, 2)), intval(substr($time, 12, 2)), 
			intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));
			$insert_time = date("YmdHis", $int_time);
			
			$params = array(
						"user_id" => $user_id,
						"login_id" => $this->login_id,
						"password" => md5($this->password),
						"handle" => $this->handle,
						"role_authority_id" => _SYSTEM_ROLE_AUTH_ID,
						"active_flag" => _ON,
						"system_flag" => _ON,
						"activate_key" => "",
						"lang_dirname" => $this->session->getParameter("_lang"),
						"timezone_offset" => INSTALL_DEFAULT_TIMEZONE,
						"password_regist_time" => $insert_time,
						"last_login_time " => "",
						"previous_login_time " => "",
						"insert_time " => $insert_time,
						"insert_site_id " => $new_site_id,
						"insert_user_id " => $user_id,
						"insert_user_name " => $this->handle,
						"update_time " => $insert_time,
						"update_site_id " => $new_site_id,
						"update_user_id " => $user_id,
						"update_user_name " => $this->handle
					);
			$result = $dbObject->insertExecute("users", $params, false);
			if ($result === false) {
				return 'error';
			}
		}
    	return 'success';
    }
}
?>
