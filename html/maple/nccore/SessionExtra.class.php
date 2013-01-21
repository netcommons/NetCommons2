<?php
//
// $Id: SessionExtra.class.php,v 1.44 2008/07/09 05:45:36 Ryuji.M Exp $
//
require_once MAPLE_DIR.'/core/Session.class.php';

 /**
 * Sessionを管理するクラス
 * 多次元配列でも登録できるように継承:配列対応
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class SessionExtra extends Session {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	var $old_session_id = "";
	var $new_session_id = "";
	var $regenerate_flag = true;
	var $old_session_time = 300; 		//	古いセッションIDの有効時間(秒)
	var $regenerate_session_num = 4; 	//	古いセッションIDの削除処理の確立(1/$regenerate_session_num)

	var $sess_read_flag = false;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function SessionExtra() {
		$this->Session();
		//
    	// 共通関数
    	//
    	if (!function_exists("timezone_date")) {
    		include_once MAPLE_DIR  . '/nccore/common_func.php';
    	}
	}

	/**
     * 設定されている値を返却
     *
     * @param   string  $key or array($key,$key,...) $key  パラメータ名
     * 			 object  $smarty_obj Smartyオブジェクト：テンプレートから呼ばれる場合
     * @return  string  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function getParameter($key, $smarty_obj=null)
    {
    	if(is_object($smarty_obj)){
    		foreach($key as $key_value) {
    			$key = $key_value;
    			break;
    		}

    	}
    	if(is_array($key)) {
    		$temp = $_SESSION;
    		foreach($key as $key_value) {
    			if(isset($temp[$key_value])) {
    				$temp = $temp[$key_value];
    			} else {
    				return null;
    			}
    		}
    		return $temp;
    	} else {
        	if (isset($_SESSION[$key])) {
            	return $_SESSION[$key];
        	} else {
        		return null;
        	}
    	}
    }


    /**
     * 設定されている値を返却(オブジェクトを返却)
     *
     * @param   string  $key or array($key,$key,...) $key  パラメータ名
     * @return  Object  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function &getParameterRef($key)
    {
        if(is_array($key)) {
    		$temp = $_SESSION;
    		foreach($key as $key_value) {
    			$temp = $temp[$key_value];
    		}
    		return $temp;
    	} else {
        	if (isset($_SESSION[$key])) {
            	return $_SESSION[$key];
        	}
    	}
    }

    /**
     * 値をセット（置換）
     *
     * @param  array $_Session
     * @access  public
     * @since   3.0.0
     */
    function setParameters($session)
    {
        if (isset($session)) {
            $_SESSION = $session;
        }
    }

    /**
     * 値をセット
     *
     * @param   string  $key or array($key,$key,...) $key  パラメータ名
     * @param   string  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameter($key, $value)
    {
    	if(is_array($key)) {
    		$sessionArg =& $_SESSION;
			foreach($key as $key_value) {
				$sessionArg =& $sessionArg[$key_value];
	    	}
	    	$sessionArg = $value;
    	} else {
        	$_SESSION[$key] = $value;
    	}
    }

    /**
     * 値をセット(オブジェクトをセット)
     *
     * @param   string  $key or array($key,$key,...) $key  パラメータ名
     * @param   Object  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameterRef($key, &$value)
    {
        if(is_array($key)) {
    		$sessionArg =& $_SESSION;
			foreach($key as $key_value) {
	    		$sessionArg =& $sessionArg[$key_value];
	    	}
	    	$sessionArg =& $value;
    	} else {
        	$_SESSION[$key] =& $value;
    	}
    }

    /**
     * 値を削除する
     *
     * @param   string  $key    パラメータ名
     * @access  public
     * @since   3.0.0
     */
    function removeParameter($key)
    {
    	if(is_array($key)) {
    		$sessionArg =& $_SESSION;
			foreach($key as $key_value) {
				$sessionArg =& $sessionArg[$key_value];
	    	}
	    	$sessionArg = null;
	    	//unset($sessionArg);
    	} else {
        	unset($_SESSION[$key]);
    	}

    }

    /**
     * セッション処理を開始
     *
     * @access  public
     * @since   3.0.0
     */
    function start($regenerate_flag = _ON)
    {
    	@session_set_save_handler(array(&$this, 'sess_open'), array(&$this, 'sess_close'), array(&$this, 'sess_read'), array(&$this, 'sess_write'), array(&$this, 'sess_destroy'), array(&$this, 'sess_gc'));
		@register_shutdown_function('session_write_close');
		@session_start();

		//$_noregenerate_sess_flag = $this->getParameter("_noregenerate_sess_flag");
		//if($_noregenerate_sess_flag && $this->sess_updated != null) {
		//if($this->regenerate_sess_id != null) {
			//
			// 新しいセッションIDに変換($this->old_session_time以内であれば)
			//
			//@register_shutdown_function('session_write_close');
			//@session_id($this->regenerate_sess_id);
			//@session_start();
			//$this->regenerate_sess_id = null;
			//$this->start();
			//return;
		//}
		$_base_sess_id = $this->getParameter("_base_sess_id");
		if(!isset($_base_sess_id)) {
			$_base_sess_id = session_id();
			$this->setParameter("_base_sess_id", $_base_sess_id);
		}
		if($regenerate_flag) {
			//古いセッションに対してregenerateしないようにする
			//$this->setParameter("_noregenerate_sess_flag", _ON);
			$old_session_id = session_id();
			//$this->setParameter("_old_sess_flag", true);
			if($this->regenerate_flag && session_regenerate_id()) {
				//$this->setParameter("_old_sess_flag", false);
				//$this->setParameter("_noregenerate_sess_flag", _OFF);
				$this->new_session_id = session_id();
				$this->old_session_id = $old_session_id;
				// smarty_cacheのセッションIDも同時に更新
				// Cacheフィルターがうまく動作しなくなるため
				$adodb =& $this->_db->getAdoDbObject();
				if(is_object($adodb)) {
					$this->_db->updateExecute("smarty_cache", array("session_id" => $this->new_session_id), array("session_id" => $this->old_session_id));
					if(rand(0, $this->regenerate_session_num) == 0) {
						// regenerate_session_num分の1の確立で、regenerate前のセッションデータ削除
						$params = array(
							"base_sess_id" => $_base_sess_id,
							"sess_updated_old_session" => date("YmdHis",date("U") - $this->old_session_time)
						);
						$result = $this->_db->execute("DELETE FROM {session} " .
						" WHERE old_flag = 1 AND base_sess_id = ? AND sess_updated < ? ",$params);
					}
				}
			}
		}
		//-------------------------------------------------------------------------------------
		// セッションID変更
		// Ajaxや画像ファイルのダウンロードのリクエストによりセッションIDを切り替えてしまうと
		//（ 同じタイミングで複数リクエストがくる）
		// セッション情報がうまく変更されないため
		// ページを切り替えた場合のみ実装
		//-------------------------------------------------------------------------------------
		/*
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		//$actionList  = explode("_", $actionChain->getCurActionName());
		if($actionChain->getCurActionName() == DEFAULT_ACTION || $actionChain->getCurActionName() == "control_view_main") {
			$sessionids_remove = @$_SESSION['old_session_id'];

			$old_session_id = session_id();
			if(!empty($sessionids_remove)) {
				// ページ切り替えの連続的に行うとセッションが切れてしまうため、次のリクエストまでセッションを保持しておく
				$this->sess_destroy($sessionids_remove);
			}
			if(session_regenerate_id()) {
				$_SESSION['old_session_id'] = $old_session_id;

				$new_session_id = session_id();
				//$this->sess_destroy($old_session_id);
				// smarty_cacheのセッションIDも同時に更新
				// Cacheフィルターがうまく動作しなくなるため
				$db =& $container->getComponent("DbObject");
				$db->updateExecute("smarty_cache", array("session_id" => $new_session_id), array("session_id" => $old_session_id));
			}
		}
		*/
    }

    /**
     * セッション処理を終了
     * デバッグ情報のみ残す
     * @access  public
     */
    function close()
    {
    	//$previous_name = $this->getName();
    	$php_debug = $this->getParameter("_php_debug");
	    $sql_debug = $this->getParameter("_sql_debug");
	    $smarty_debug = $this->getParameter("_smarty_debug");
	    $maple_debug = $this->getParameter("_maple_debug");
	    $trace_log_level = $this->getParameter("_trace_log_level");
	    $sess_common = $this->getParameter("_session_common");
	    $meta = $this->getParameter("_meta");
	    $lang = $this->getParameter("_lang");

	    $_base_sess_id = $this->getParameter("_base_sess_id");

        //
        // base_sess_idが同じものを削除
        //
        if(!empty($_base_sess_id)) {
	        $sql = "DELETE FROM {session} WHERE base_sess_id = ?";
			$params = array(
				"base_sess_id" => $_base_sess_id
			);

			$result = $this->_db->execute($sql,$params);
        }
        $_SESSION = array();
        session_destroy();

        if($php_debug != null) {
        	@session_id($_base_sess_id);
        	$this->start(false);
        	//$this->setName($previous_name);
	        //再セット
	        $this->setParameter("_user_id","0");
	        $this->setParameter("_php_debug",$php_debug);
			$this->setParameter("_sql_debug",$sql_debug);
			$this->setParameter("_smarty_debug",$smarty_debug);
			$this->setParameter("_maple_debug",$maple_debug);
			$this->setParameter("_trace_log_level",$trace_log_level);

			$this->setParameter("_meta",$meta);

			if(is_array($sess_common) && count($sess_common) > 0) {
				$this->setParameter("_session_common",$sess_common);
			}

			//$this->setParameter("_base_sess_id", $_base_sess_id);
        }
    }


    /**
     * セッションの会員の権限関連をlogoutした状態にする
     * @access  public
     */
    /*
    function logout()
    {
    	// 初期化
    	$this->setParameter("_user_id", "0");
    	$this->removeParameter("_login_id");
    	$this->setParameter("_handle",'');
    	$this->setParameter("_user_auth_id", _AUTH_OTHER);
    	$this->setParameter("_role_auth_id", _AUTH_OTHER);
    	$this->removeParameter("_role_authority_name");
    	$this->setParameter("_allow_attachment_flag", _OFF);
	    $this->setParameter("_allow_htmltag_flag", _OFF);
	    $this->setParameter("_allow_layout_flag", _OFF);
	    $this->setParameter("_private_max_size", 0);
    }
    */

    /**
     * Open a session
     *
     * @param	string  $save_path
     * @param	string  $session_name
     *
     * @return	bool
     */
    function sess_open($save_path, $session_name)
	{
		$this->_container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
		$this->_db =& $this->_container->getComponent("DbObject");
        return true;
    }

    /**
     * Close a session
     *
     * @return	bool
     */
    function sess_close()
	{
        return true;
    }

    /**
     * Read a session from the database
     *
     * @param	string  &sess_id    ID of the session
     *
     * @return	array   Session data
     */
    function &sess_read($sess_id = null)
	{
		$getdata =& $this->_container->getComponent("GetData");
		$config = $getdata->getParameter("config");
		// timezone_date関数は、サーバタイムゾーンを変更してしまうとセッションがうまく取得できなくなるため、使用しない。
		$time = date("YmdHis",date("U") - $config[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'] * 60);

		$sql = "SELECT base_sess_id, old_flag, sess_updated, sess_data FROM {session} WHERE sess_id = ? AND sess_updated >= ?";
		$params = array(
			"sess_id" => $sess_id,
			"sess_updated" => $time
		);
		$result = $this->_db->execute($sql,$params);
		if(!$result && !is_array($result)) {
			return $result;
		}

		// Aが古いセッションであると確認された  (SELECT old_flagがある)

		if($result[0]["old_flag"] == _ON && $this->sess_read_flag == false) {
			if($result[0]["sess_updated"] >= date("YmdHis",date("U") - $this->old_session_time)) {
				$sql = "SELECT sess_id FROM {session} WHERE base_sess_id = ? ORDER BY sess_updated DESC ";
				$params = array(
					"base_sess_id" => $result[0]["base_sess_id"]
				);
				$sub_result = $this->_db->execute($sql,$params, 1);
				if($sub_result === false || !isset($sub_result[0])) {
					exit;
				}
				$this->regenerate_flag = false;
				$this->sess_read_flag = true;
				//$this->sess_destroy($sess_id);
				$_SESSION = array();
        		session_destroy();

				@session_id($sub_result[0]["sess_id"]);
				//return $sub_result[0]["sess_data"];
				@session_start();

				//$result[0]["sess_data"] = $sub_result[0]["sess_data"];
				//var_dump($sub_result[0]["sess_id"]);
				//$this->regenerate_flag = false;
				//@register_shutdown_function('session_write_close');
				//@session_id($sub_result[0]["sess_id"]);
				//@session_start();
			} else {
				$this->regenerate_flag = false;
			}
		}

		/*
		if($result[0]["regenerate_sess_id"] != '') {
			if($result[0]["sess_updated"] >= date("YmdHis",date("U") - $this->old_session_time)) {
				@register_shutdown_function('session_write_close');
				@session_id($result[0]["regenerate_sess_id"]);
				@session_start();
			}
			$this->regenerate_flag = false;
		}
		*/
		/*
		if($result[0]["regenerate_sess_id"] != '') {
			$ret = false;
			$this->regenerate_flag = false;
			if($result[0]["sess_updated"] >= date("YmdHis",date("U") - $this->old_session_time)) {
				return $this->sess_read($result[0]["regenerate_sess_id"]);
			} else {
				// $this->old_session_time秒を越えてしまったため、セッション無効
				$this->sess_destroy($sess_id);
				exit;
			}
		}
		*/
		/*else {
			// session_gc_maxlifetimeを過ぎていれば、初期化（GCでは100分の1の確立でしか削除処理を行わないため、毎回チェック）
			$getdata =& $this->_container->getComponent("GetData");
			$config = $getdata->getParameter("config");
			$time = date("YmdHis",date("U") - $config[_GENERAL_CONF_CATID]['session_gc_maxlifetime']['conf_value'] * 60);
			if($result[0]["sess_updated"] < $time) {
				$this->sess_destroy($sess_id);
				$result = array();
				return $result;
			}
		}
		*/


		return $result[0]["sess_data"];
    }

    /**
     * Read a session from the database
     *
     * @param	string  &sess_id    ID of the session
     *
     * @return	array   Session data
     */
    function &sess_getUpdated($sess_id)
	{
        $sql = "SELECT sess_updated FROM {session} WHERE sess_id = ?";
        $params = array(
			"sess_id" => $sess_id
		);

		$result = $this->_db->execute($sql,$params);
		if(!$result && !is_array($result)) {
			return $result;
		}
		return $result[0]["sess_data"];
    }

    /**
     * Write a session to the database
     *
     * @param   string  $sess_id
     * @param   string  $sess_data
     *
     * @return  bool
     **/
    function sess_write($sess_id, $sess_data)
	{
//var_dump("WRITE");
//var_dump($_SESSION);
		$time = date("YmdHis");
		$count_sql = "SELECT sess_id FROM {session} WHERE sess_id = ?";
		$params = array(
			"sess_id" => $sess_id
		);
		//get one record
		//$result_count = $this->_db->execute($count_sql,$params,0,1,false);
		$result_count = $this->_db->execute($count_sql,$params);

		// Aが古いセッションであると確認されました  (SELECT regenerate_sess_idがある)
		//if(isset($result_count[0]) && $result_count[0]["regenerate_sess_id"] != '') {
		//	$sess_id = $result_count[0]["regenerate_sess_id"];
			/*
			$ret = false;
			//$time = date("YmdHis");
			$int_time = mktime(date("H"), date("i"), intval(date("s")) - 30, date("m"), date("d"), date("Y"));
			$chk_time = date("YmdHis", $int_time);
			if($result_count[0]["sess_updated"] >= timezone_date($chk_time)) {
				$sess_id = $result_count[0]["regenerate_sess_id"];
			} else {
				// 30秒を越えてしまったため、セッション無効
				$this->sess_destroy($sess_id);
				return $ret;
			}
			*/
		//}

		if(isset($result_count[0])) {
			$params = array(
				"sess_updated" => $time,
				"sess_data" => $sess_data,
				"sess_id" => $sess_id
			);
			$sql = "UPDATE {session} SET sess_updated = ?, sess_data = ? WHERE sess_id = ?";
			$result = $this->_db->execute($sql,$params);
	        if(!$result) {
	        	return false;
			}
		} else {

			//$_old_sess_flag = $this->getParameter("_old_sess_flag");
			//if($_old_sess_flag == true) {
			//if($this->new_session_id == "") {
				$_base_sess_id = $this->getParameter("_base_sess_id");
			//} else {
			//	$_base_sess_id = "";
			//}
			//if($this->set_regenerate_flag == false) {
			//	$_base_sess_id = $this->getParameter("_base_sess_id");
			//	if(!isset($_base_sess_id)) $_base_sess_id = "";	//$sess_id;
			//} else {
			//	$_base_sess_id = "";
			//}
			$params = array(
				"sess_id" => $sess_id,
				"base_sess_id" => $_base_sess_id,	//($sess_id == $this->new_session_id) ? "" : $this->new_session_id,
				"old_flag" => _OFF,
				"sess_updated" => $time,
				"sess_ip" => $_SERVER['REMOTE_ADDR'],
				"sess_data" => $sess_data
			);
			$sql = "INSERT INTO {session} (sess_id, base_sess_id, old_flag, sess_updated, sess_ip, sess_data) VALUES (?, ?, ?, ?, ?, ?)";
			$result = $this->_db->execute($sql,$params);
	        if(!$result) {
	        	return false;
			}
			if($this->new_session_id != "") {
				$params = array(
					"old_flag" => _ON,			//$this->getParameter("_base_sess_id"),
					"where_sess_id" => $this->old_session_id
				);

				$sql = "UPDATE {session} SET old_flag = ? WHERE sess_id = ? ";
				$result = $this->_db->execute($sql, $params);
				if($result === false) {
		        	return false;
				}

				$this->new_session_id = "";
				$this->old_session_id = "";
			}
			/*
			if($this->new_session_id != "") {
				$params = array(
					"regenerate_sess_id" => $this->new_session_id,
					"where_regenerate_sess_id" => $this->old_session_id,
					"where_sess_id" => $this->old_session_id
				);

				$sql = "UPDATE {session} SET regenerate_sess_id = ? WHERE regenerate_sess_id = ? OR sess_id = ? ";
				$result = $this->_db->execute($sql, $params);
				if($result === false) {
		        	return false;
				}

				$this->new_session_id = "";
				$this->old_session_id = "";
			}
			*/
		}

		return true;
    }

    /**
     * Destroy a session
     *
     * @param   string  $sess_id
     *
     * @return  bool
     **/
    function sess_destroy($sess_id)
    {
		$sql = "DELETE FROM {session} WHERE sess_id = ?";
		$params = array(
			"sess_id" => $sess_id
		);

		$result = $this->_db->execute($sql,$params);
        if(!$result) {
        	return false;
		}

		return true;
    }

    /**
     * Garbage Collector
     *
     * @param   int $expire Time in seconds until a session expires
	 * @return  bool
     **/
    function sess_gc($expire)
    {
    	// サーバの時間を引いたものから計算
    	$time = date("YmdHis",date("U") - $expire);
    	if ( $time < 0 ) {
        	$time = 0;
        }
        $old_flag_time = date("YmdHis",date("U") - 10*60);	// old_flagが立っているものは、10分で削除
    	if ( $old_flag_time < 0 ) {
        	$old_flag_time = 0;
        }

        $params = array(
			"sess_updated" => $time,
			"old_flag" => _ON,
			"old_flag_sess_updated" => $old_flag_time,
		);

        $result = $this->_db->execute("DELETE FROM {session} " .
						" WHERE sess_updated <? OR (old_flag = ? AND sess_updated < ?) ",$params);
		if(!$result) {
        	return false;
		}
		//
		//uploadsテーブルのgarbage_flagが1　かつ　セッションが使用中でないもののテーブル、ファイルを削除
		//
		$uploads_str =  " FROM {uploads} LEFT JOIN {session} ON {uploads}.sess_id = {session}.sess_id " .
				"WHERE ISNULL({session}.sess_id) AND {uploads}.garbage_flag = "._ON .
				" AND {uploads}.update_time<'".date("YmdHis",date("U") - _CLEANUP_DEL_DAY*60*60) ."'";
		$sql = "SELECT {uploads}.upload_id,{uploads}.file_name,{uploads}.physical_file_name,{uploads}.file_path,{uploads}.extension,{session}.sess_id " . $uploads_str;
		$result_uploads = $this->_db->execute($sql);
		$uploads_str ="";
		foreach($result_uploads as $result_upload) {
			//ファイル削除
			$file_name = $result_upload['physical_file_name'];
			$path = FILEUPLOADS_DIR.$result_upload['file_path'].$file_name;
			if(file_exists($path)) {
				@chmod($path, 0777);
				unlink($path);
			}

			// サムネイル画像があれば、削除
			$thumbnail_path = FILEUPLOADS_DIR.$result_upload['file_path'].$result_upload['upload_id']."_thumbnail.".$result_upload['extension'];
			if(file_exists($thumbnail_path)) {
				@chmod($thumbnail_path, 0777);
				unlink($thumbnail_path);
			}

			if($uploads_str != "") {
				$uploads_str .= ",";
			}
			$uploads_str .= $result_upload['upload_id'];
		}
		if($uploads_str != "") {
			$this->_db->execute("DELETE FROM {uploads} WHERE upload_id IN (" .
				$uploads_str . ");");
		}
		//
		// 期間を過ぎたsmarty_cacheを削除する
		//
		$time = date("YmdHis");
		$this->_db->execute("DELETE FROM {smarty_cache} WHERE expire_time <'" . $time . "'");
		//$del_time = date("YmdHis",date("U") - _SMARTY_CACHE_EXPIRE*60);
		//$this->_db->execute("DELETE FROM {smarty_cache} WHERE update_time <'" . $del_time . "'");

		//昔の月別一覧回数削除処理
		if(_MONTHLYNUMBER_DELETE_YEARSAGO > 0) {
			$container =& DIContainerFactory::getContainer();
			$monthlynumberAction =& $container->getComponent("monthlynumberAction");
			$year = date("Y");
			$target_year = $year-intval(_MONTHLYNUMBER_DELETE_YEARSAGO);
		    $monthlynumberAction->delMonthlynumberByYear($target_year);
		}
        return $result;
    }
}
?>
