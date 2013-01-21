<?php
/**
 * @author	    Ryuji Masukawa
 * @copyright	copyright (c) 2006 NetCommons.org
 */

/**
 * adodb class
 */
include_once MAPLE_DIR.'/nccore/db/adodb/adodb.inc.php';

/**
 * データベース接続
 *
 * @abstract
 *
 * @author      Ryuji Masukawa
 */
class DbObjectAdodb
{
	/**
	 * @var DB接続文字列を保持
	 *
	 * @access	private
	 */
	var $_dsn = null;

	/**
	 * データベース接続
	 * @var resource
	 */
	var $_conn;

	/**
	 * テーブル接頭語
	 * @var resource
	 */
	var $_prefix;

	/**
	 * SQL文字列
	 * @var resource
	 */
	var $_bck_sql;

	/**
	 * @var DB接続時のオプションを保持
	 *
	 * @access	private
	 */
	var $_options = array(
		'persistent' => null,
		'debug' => 0
	);

	/**
	 * DSNをセットする
	 *　
	 * @param	string	$dsn	DSN　$driver://$username:$password@hostname/$database
	 * @access	public
	 */
	function setDsn($dsn) {
		$this->_dsn = $dsn;
	}

	/**
	 * DSNをゲットする
	 *　
	 * @return	string	$dsn	DSN　$driver://$username:$password@hostname/$database
	 * @access	public
	 */
	function getDsn() {
		return $this->_dsn;
	}

	/**
	 * Prefixをセットする
	 *　
	 * @param	string	$prefix
	 * @access	public
	 */
	function setPrefix($prefix) {
		$this->_prefix = $prefix;
	}

	/**
	 * Prefixをゲットする
	 *　
	 * @return	string	$prefix
	 * @access	public
	 */
	function getPrefix() {
		return $this->_prefix;
	}

	/**
	 * 接続時のoptionをセットする
	 *
	 * @param	string	$key	接続時のoptionのキー
	 * @param	string	$value	接続時のoptionの値
	 * @access	public
	 */
	function setOption($key, $value) {
		$this->_options[$key] = $value;
	}

	/**
	 * 接続時のoptionをゲットする
	 *　
	 * @return	array	$option
	 * @access	public
	 */
	function getOption() {
		return $this->_options;
	}

	/**
	 * デバッグモードをセットする
	 *
	 * @param	bool	$debugMode	デバッグモードをセットする
	 * @access	public
	 */
	function setDebugMode($debugMode=true) {
		if($debugMode)
			$this->_options['debug'] = 1;
		else
			$this->_options['debug'] = 0;
		$this->_conn->debug = $debugMode;
	}

	/**
	 * デバッグモードをゲットする
	 *　
	 * @return	bool	$debugMode	デバッグモードをセットする
	 * @access	public
	 */
	function getDebugMode() {
		return $this->_options['debug'];
	}

	/**
	 * DBに接続する
	 *
	 * @return	boolean	DB接続の処理結果(true/false)
	 * @access	public
	 */
	function connect() {
		if ($this->_conn != null) {
			return true;
		}

		if (!$this->_dsn) {
			return false;
		}

		$option = "";
		$prefix = "?";
		foreach ($this->_options as $key => $value) {
			if (is_bool($value)) {
				$option .= "$prefix$key";
			} else {
				$option .= "$prefix$key=$value";
			}
			if ($prefix == "?")
				$prefix = "&";
		}
		$this->_conn =& NewADOConnection($this->_dsn.$option);
		if (!is_object($this->_conn)) {
			return false;
		}

		$this->_conn->SetFetchMode(ADODB_FETCH_ASSOC);
		if(strstr($this->_dsn, "mysql")) {
			$server_info = $this->_conn->ServerInfo();
			//if($server_info["version"] > )
			if(floatval($server_info["version"]) >= 4.01) {
				$result = $this->_conn->execute("SET NAMES ".DATABASE_CHARSET.";");
				if(!$result) {
					$log =& LogFactory::getLog();
					$log->error("文字コード変換に失敗しました", "DbObjectAdodb#connect");
				}
			}

			// クライアントの文字セットを設定する。
			// mysql_set_charset は PHP 5.2.3、MySQL 5.0.7 以降
			// http://php.net/manual/ja/function.mysql-set-charset.php
			if (version_compare(phpversion(), '5.2.3', '>=')
				&& $server_info['version'] >= '5.0.7') {
				mysql_set_charset(DATABASE_CHARSET);
			}
		}

		//$this->_conn->SetCharSet(DATABASE_CHARSET);

		return true;
	}

	/**
	 * トランザクション処理をする
	 * example:
	 * 	$adodb->StartTrans();
	 *	$adodb->executeQuery($sql);
	 *	if (!CheckRecords()) $adodb->FailTrans();
	 *	$conn->executeQuery($Sql2);
	 *	$conn->CompleteTrans();
	 * @access	public
	 */
	function StartTrans() {
		$this->_conn->StartTrans();
	}
	function FailTrans() {
		$this->_conn->FailTrans();
	}
	function CompleteTrans() {
		$this->_conn->CompleteTrans();
	}

	/**
	 * トランザクションが失敗したかどうかのチェック
	 * @access	public
	 * @return	booleanトランザクションの処理結果(true/false)
	 */
	function HasFailedTrans() {
		return $this->_conn->HasFailedTrans();
	}

	/**
	 * SQL文雛形をセットする プレースフォルダー利用時に使用
	 *
	 * @param	string	$sql	実行するSQL文
	 * @access	public
	 */
	function setSql($sql) {
		$this->_bck_sql = $sql;
	}

	/**
	 * デバッグモードをゲットする
	 *　
	 * @return		string	$sql	実行するSQL文
	 * @access	public
	 */
	function getSql() {
		return $this->_bck_sql;
	}

	/**
	 * SQL文雛形をfileからセットする プレースフォルダー利用時に使用
	 *
	 * @param	string	$filename	SQL文が書かれたfilename
	 * @access	public
	 */
	function setSqlFile($filename) {
		if (!@file_exists($filename)) {
			return false;
		}
		$fp = fopen($filename,"r");
		$sql = fread($fp,filesize($filename));
		fclose($fp);

		$this->_bck_sql = $sql;
	}

	/**
	 * 検索処理(SELECT)を実行する
	 *
	 * @param	string		$sql	実行するSQL文
	 * @param	array		$params	SQL文内のブレースフォルダに対する値
	 * @param	integer		$offset	取得し始めるレコードのオフセット
	 * @param	integer		$limit	取得する件数
	 * @param  boolean 		true:ADODB_FETCH_ASSOC false:ADODB_FETCH_NUM
	 * @param	function	$func	各レコード処理で実行させるメソッド
	 * @param	array		$func_param	各レコード処理で実行させるメソッドの引数
	 * @return	mixed	検索処理の結果
	 * @access	public
	 */
	function execute($sql, $params = array(), $limit = 0, $offset = 0, $key_flag = true, $func = null, $func_param = null) {
		$limit = ($limit == null) ? 0 : $limit;
		$offset = ($offset == null) ? 0 : $offset;

		if (!$sql)
			$sql = $this->_bck_sql;
		else
			$this->_bck_sql = $sql;

		if (!$sql || !$this->_conn) {
			return false;
		}

		$sql = strtr($sql, array('{' => $this->_prefix."", '}' => ''));

		//フェッチモード変更
		if($key_flag)
			$this->_conn->SetFetchMode(ADODB_FETCH_ASSOC);
		else
			$this->_conn->SetFetchMode(ADODB_FETCH_NUM);

		if ($limit || $offset) {
			$result =& $this->_conn->SelectLimit($sql, $limit, $offset, $params);
		} else {
			$result =& $this->_conn->Execute($sql, $params);
		}

		//フェッチモードを元に戻す
		if($key_flag)
			$this->_conn->SetFetchMode(ADODB_FETCH_ASSOC);

		if (!is_object($result)) {
			return false;
		}
		$rows = array();
		if ($func !== null) {
			$paramArray = array(&$result);
			if (isset($func_param)) {
				$paramArray[] = &$func_param;
			}
			$rows = call_user_func_array($func, $paramArray);
		} else {
			while ($row = $result->fetchRow()) {
				$rows[] = $row;
			}
		}
		if(method_exists($result,"free")) {
			$result->free();
			return $rows;
		} else
			return $result;
	}


	/**
	 * 最後の状態あるいはエラーNoを返します。
	 *
	 * @return	int	エラーNo
	 * @access	public
	 */
	function ErrorNo() {
		if (!is_object($this->_conn)) {
			return false;
		}
		return $this->_conn->ErrorNo();
	}

	/**
	 * 最後の状態あるいはエラーメッセージを返します。
	 *
	 * @return	srting	エラーメッセージ
	 * @access	public
	 */
	function ErrorMsg() {
		if (!is_object($this->_conn)) {
			return false;
		}
		return $this->_conn->ErrorMsg();
	}

	/**
	 * シーケンス番号を生成
	 *
	 * @return	int	シーケンスNo
	 * @access	public
	 */
	//TODO:後に削除
	//function GenID($seqName = 'adodbseq',$startID=1) {
	//	return $this->_conn->GenID($seqName, $startID);
	//}

	/**
	 * シーケンス番号を生成
	 *
	 * @return	int	シーケンスNo
	 * @param string  $tableName
	 * @param int     $startID
	 * @param boolean $chk_flag
	 * @access	public
	 */
	function nextSeq($tableName, $startID = 1, $chk_flag = true) {
		if($chk_flag) {
			$metaColumnNames = $this->_conn->MetaColumnNames($this->getPrefix().$tableName);
			if($metaColumnNames === false) {
				// Error
				// テーブルが存在しない
				return 0;
			}
		}
		return $this->_conn->GenID($this->getPrefix().$tableName._SYS_TABLE_SEQID_POSTFIX, $startID);
	}

	/**
	 * レコードセットのメモリを解放
	 * TODO:入れた場合と入れなかった場合で違いがでる可能性もあるので、最終的にチェック予定
     * @param resource query result
     * @return bool TRUE on success or FALSE on failure.
	 */
	function freeRecordSet($result)
	{
		return mysql_free_result($result);
	}

    /**
	 * パラメータよりinsert文を生成し返す
	 *
	 * @param	string	$tableName	対象テーブル名称
	 * @param	array	$params		insertするデータ配列
	 * @param  boolean $footer_flag insert_site_id-update_user_nameまでを付与する場合、true
     * @return string	insert文
	 * @access	public
	 */
	function getInsertSQL($tableName, &$params, $footer_flag = false)
	{
		if($footer_flag) {
			$time = timezone_date();

			$container =& DIContainerFactory::getContainer();
	        $session =& $container->getComponent("Session");
	        $site_id = $session->getParameter("_site_id");
	        $user_id = $session->getParameter("_user_id");
	        $user_name = $session->getParameter("_handle");
	        if($user_name === null) $user_name = "";
	        $metaColumns = $this->_conn->MetaColumns($this->getPrefix().$tableName);
	        if(isset($metaColumns["ROOM_ID"]) && !isset($params['room_id'])) {
	        	$request =& $container->getComponent("Request");
	        	$params['room_id'] = $request->getParameter("room_id");
	        }
	        $params_footer = array();
	        if(isset($metaColumns["INSERT_TIME"])) {
	        	$params_footer["insert_time"] = $time;
	        }
	        if(isset($metaColumns["INSERT_SITE_ID"])) {
	        	$params_footer["insert_site_id"] = $site_id;
	        }
	        if(isset($metaColumns["INSERT_USER_ID"])) {
	        	$params_footer["insert_user_id"] = $user_id;
	        }
	        if(isset($metaColumns["INSERT_USER_NAME"])) {
	        	$params_footer["insert_user_name"] = $user_name;
	        }
			if(isset($metaColumns["UPDATE_TIME"])) {
	        	$params_footer["update_time"] = $time;
	        }
	        if(isset($metaColumns["UPDATE_SITE_ID"])) {
	        	$params_footer["update_site_id"] = $site_id;
	        }
	        if(isset($metaColumns["UPDATE_USER_ID"])) {
	        	$params_footer["update_user_id"] = $user_id;
	        }
	        if(isset($metaColumns["UPDATE_USER_NAME"])) {
	        	$params_footer["update_user_name"] = $user_name;
	        }

	        //$params_footer = array(
			//		"insert_time" =>$time,
			//		"insert_site_id" => $site_id,
			//		"insert_user_id" => $user_id,
			//		"insert_user_name" => $user_name,
			//		"update_time" =>$time,
			//		"update_site_id" => $site_id,
			//		"update_user_id" => $user_id,
			//		"update_user_name" => $user_name
			//	);
			$params = array_merge($params, $params_footer);
		}
        $columns = array_keys($params);
        $valueStr = str_repeat(",?", count($columns));
        $sql = "INSERT INTO {". $tableName. "} (". implode(",", $columns). ") ".
        		"VALUES(". substr($valueStr, 1). ")";
        return $sql;
	}

	/**
	 * パラメータよりupdate文を生成し返す
	 *
	 * @param	string	$tableName	                対象テーブル名称
	 * @param	array	$params		                updateするデータ配列
	 * @param	string or array	$where_params		キー名称配列、whereデータ配列
     * @return string	update文
	 * @access	public
	 */
	function getUpdateSQL($tableName, &$params, $where_params = null, $footer_flag = false)
	{
		if($footer_flag) {
			$time = timezone_date();
			$container =& DIContainerFactory::getContainer();
	        $session =& $container->getComponent("Session");
	        $site_id = $session->getParameter("_site_id");
	        $user_id = $session->getParameter("_user_id");
	        $user_name = $session->getParameter("_handle");
	        $metaColumns = $this->_conn->MetaColumns($this->getPrefix().$tableName);
	        //if(isset($metaColumns["ROOM_ID"]) && !isset($params['room_id'])) {
	        //	$request =& $container->getComponent("Request");
	        //	$params['room_id'] = $request->getParameter("room_id");
	        //}
	        $params_footer = array();
	        if(isset($metaColumns["UPDATE_TIME"])) {
	        	$params_footer["update_time"] = $time;
	        }
	        if(isset($metaColumns["UPDATE_SITE_ID"])) {
	        	$params_footer["update_site_id"] = $site_id;
	        }
	        if(isset($metaColumns["UPDATE_USER_ID"])) {
	        	$params_footer["update_user_id"] = $user_id;
	        }
	        if(isset($metaColumns["UPDATE_USER_NAME"])) {
	        	$params_footer["update_user_name"] = $user_name;
	        }
	        $params = array_merge($params,$params_footer);
		}
		$setStr = implode("=?,", array_keys($params)). "=?";
		$setWhere = "1=1";

        if (is_array($where_params)) {
        	foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
	        		$params[] = $item;
					$setWhere .= " AND ".$key."=?";
				} else {
					$setWhere .= " AND ".$key;
				}
	        }
        } else {
        	$setWhere .= " AND ". $where_params. "=?";
        	$params[] = $params[$where_params];
        }

        $sql = "UPDATE {". $tableName. "} SET ". $setStr. " ".
	        		"WHERE ". $setWhere;
        return $sql;
	}

	/**
	 * パラメータよりselect文を生成し返す
	 *  @param	string	$tableName	                対象テーブル名称
	 * @param	array	$params		                WHERE句のデータ配列
	 * @param	array   $where_params		        キー名称配列、whereデータ配列
     * @param	array   $order_params		        キー名称配列、orderデータ配列
     * @return string	select文
	 * @access	public
	 */
	function &getSelectSQL($tableName, &$params, &$where_params, &$order_params)
	{
		$sql_where = $this->getWhereSQL($params, $where_params);
		$sql_order = $this->getOrderSQL($order_params);

        $sql = "SELECT {".$tableName."}.* " .
				" FROM {".$tableName."} ";
        $sql .= $sql_where;
		$sql .= $sql_order;

		return $sql;
	}
	/**
	 * パラメータよりwhere文を生成し返す
	 * @param	array	$params		                WHERE句のデータ配列
	 * @param	array   $where_params		        キー名称配列、whereデータ配列
	 * @param	array   $where_prefix_flag		    接頭語句をANDでなくwhereにして返す場合、true defaut：true
     * @return string	where文
	 * @access	public
	 */
	function &getWhereSQL(&$params, &$where_params, $where_prefix_flag = true)
	{
		if(!is_array($params)) $params = array();
        $sql_where = "";
        if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
					$params[] = $item;
					$sql_where .= " AND ".$key."=?";
				} else {
					$sql_where .= " AND ".$key;
				}
	        }
        }
        if($where_prefix_flag) {
        	$sql_where = ($sql_where ? " WHERE ".substr($sql_where,5) : "");
        }
		return $sql_where;
	}

	/**
	 * パラメータよりorder文を生成し返す
	 * @param	array   $order_params		        キー名称配列、orderデータ配列
     * @return string	order文
	 * @access	public
	 */
	function &getOrderSQL(&$order_params)
	{
        $sql_order = "";
        if (!empty($order_params)) {
	        foreach ($order_params as $key=>$item) {
	        	$sql_order .= ",".$key." ".(empty($item) ? "ASC" : $item);
	        }
        }
        $sql_order = ($sql_order ? " ORDER BY ".substr($sql_order,1) : "");

		return $sql_order;
	}

	/**
	 * テーブルSelect
	 * @param	string	$tableName       対象テーブル名称
	 * @param	array   $where_params		        キー名称配列、whereデータ配列
     * @param	array   $order_params		        キー名称配列、orderデータ配列
     * @param	array	function array  コールバック関数
     * @param	array	function param  コールバック引数
     * @return array	select結果
	 * @access	public
	 */
	function &selectExecute($tableName, $where_params=null, $order_params=null, $limit = 0, $offset = 0, $func=null, $func_param=null)
	{
		$params = array();
 		$sql = $this->getSelectSQL($tableName, $params, $where_params,$order_params);
        $result = $this->execute($sql, $params, $limit, $offset, true,  $func, $func_param);
		if ($result === false) {
	       	$this->addError();
	       	return $result;
		}
		return $result;
	}


	/**
	 * テーブルInsert
	 *　
	 * @param	string	$tableName       対象テーブル名称
	 * @param	array   $params          キー名称配列、パラメータ配列
	 * @param	boolean true or false   insert_time-update_user_nameを自動的に付与する
     * @param	string  $create_key      主キーを作成する場合、キー名称を指定
	 * @return boolean true or false or 主キー($create_key指定の場合のみ)
	 * @access	public
	 */
	function insertExecute($tableName, $params=array(), $footer_flag=false, $create_key=null, $startID = 1)
	{
		$id = 0;
		if($create_key != null) {
			$id = $this->nextSeq($tableName, $startID, $chk_flag = false);
			$params = array_merge(array($create_key => $id), $params);
		}
 		$sql = $this->getInsertSQL($tableName, $params, $footer_flag);
        $result = $this->execute($sql, $params);
		if (!$result) {
	       	$this->addError();
	       	return false;
		}
		if($id != 0) return $id;
		return true;
	}

	/**
	 * テーブルUpdate
	 *　
	 * @param	string	$tableName       対象テーブル名称
	 * @param	array   $params          キー名称配列、更新カラム配列
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @return boolean true or false
	 * @access	public
	 */
	function updateExecute($tableName, $params=array(), $where_params=array(), $footer_flag=false)
	{
		$sql = $this->getUpdateSQL($tableName, $params, $where_params, $footer_flag);
        $result = $this->execute($sql, $params);
		if (!$result) {
			$this->addError();
			return false;
		}
		return true;
	}

	/**
	 * テーブルDelete
	 *　
	 * @param	string	$tableName       対象テーブル名称
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @return boolean true or false
	 * @access	public
	 */
	function deleteExecute($tableName, $where_params=array())
	{
		$setWhere = "";
		$params = array();
		if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
					$params[] = $item;
					$setWhere .= " AND ".$key."=?";
				} else {
					$setWhere .= " AND ".$key;
				}
	        }
        }
		//$setWhere = implode("=? AND ", array_keys($where_params)). "=? ";
		$sql = "DELETE FROM {".$tableName."} ". ($setWhere ? " WHERE ".substr($setWhere,5) : "");
		$result = $this->execute($sql, $params);
		if (!$result) {
			$this->addError();
			return false;
		}
		return true;
	}

	/**
	 * テーブル、レコード数取得
	 * @param	string	$tableName       対象テーブル名称
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @return int レコード数
	 * @access	public
	 */
	function countExecute($tableName, $where_params=null)
	{
		$setWhere = "";
		$params = array();
		if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
					$params[] = $item;
					$setWhere .= " AND ".$key."=?";
				} else {
					$setWhere .= " AND ".$key;
				}
	        }
        }
        //if($where_params != null) $setWhere = implode("=? AND ", array_keys($where_params)). "=? ";
		//else $setWhere = "1=1 ";
		$sql = "SELECT COUNT(*) FROM {".$tableName."} ". ($setWhere ? " WHERE ".substr($setWhere,5) : "");
		$result = $this->execute($sql, $params, null,null,false);
		if ($result === false) {
			$this->addError();
			return false;
		}
		return $result[0][0];
	}

	/**
	 * テーブル、レコードMAX取得
	 * @param	string	$tableName       対象テーブル名称
	 * @param	string	$columnName      対象カラム名称
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @return int レコード数
	 * @access	public
	 */
	function maxExecute($tableName, $columnName, $where_params=null)
	{
		$setWhere = "";
		$params = array();
		if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
					$params[] = $item;
					$setWhere .= " AND ".$key."=?";
				} else {
					$setWhere .= " AND ".$key;
				}
	        }
        }
		//if($where_params != null) $setWhere = implode("=? AND ", array_keys($where_params)). "=? ";
		//else $setWhere = "1=1 ";
		$sql = "SELECT MAX(".$columnName.") FROM {".$tableName."} ". ($setWhere ? " WHERE ".substr($setWhere,5) : "");
		$result = $this->execute($sql, $params,null,null,false);
		if ($result === false) {
			$this->addError();
			return false;
		}
		return $result[0][0];
	}

	/**
	 * テーブル、レコードMIN取得
	 * @param	string	$tableName       対象テーブル名称
	 * @param	string	$columnName      対象カラム名称
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @return int レコード数
	 * @access	public
	 */
	function minExecute($tableName, $columnName, $where_params=null)
	{
		$setWhere = "";
		$params = array();
		if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
					$params[] = $item;
					$setWhere .= " AND ".$key."=?";
				} else {
					$setWhere .= " AND ".$key;
				}
	        }
        }
		//if($where_params != null) $setWhere = implode("=? AND ", array_keys($where_params)). "=? ";
		//else $setWhere = "1=1 ";
		$sql = "SELECT MIN(".$columnName.") FROM {".$tableName."} ". ($setWhere ? " WHERE ".substr($setWhere,5) : "");;
		$result = $this->execute($sql, $params,null,null,false);
		if ($result === false) {
			$this->addError();
			return false;
		}
		return $result[0][0];
	}

	/**
	 * テーブル、レコードSUM取得
	 * @param	string	$tableName       対象テーブル名称
	 * @param	string	$columnName      対象カラム名称
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @return int レコード数
	 * @access	public
	 */
	function sumExecute($tableName, $columnName, $where_params=null)
	{
		if($where_params != null) $setWhere = implode("=? AND ", array_keys($where_params)). "=? ";
		else $setWhere = "1=1 ";
		$sql = "SELECT SUM(".$columnName.") FROM {".$tableName."} WHERE ".$setWhere;
		$result = $this->execute($sql, $where_params,null,null,false);
		if ($result === false) {
			$this->addError();
			return false;
		}
		return $result[0][0];
	}

	/**
	 * sequenceカラム更新処理(decrement、increment処理)
	 * @param	string	$tableName       対象テーブル名称
	 * @param	array   $where_params    キー名称配列、whereデータ配列
	 * @param	array   削除(追加)対象,表示順キー配列 例：array("display_sequence" => $current_display_sequence)
	 * @param	int     足す数、または、引く数(1,-1等)
	 * @return boolean true or false
	 * @access	public
	 */
	function seqExecute($tableName, $where_params, $sequence_param, $sequence = -1) {
		$keys_arr = array_keys($sequence_param);
		$key = $keys_arr[0];
		if (!empty($where_params)) {
			$setWhere = implode("=? AND ", array_keys($where_params)). "=? ";
			$setWhere .= " AND ". $key . ">=? ";
		} else {
			$setWhere = " ". $key . ">=? ";
		}

		$sql = "UPDATE {".$tableName."} SET ".$key."=".$key." + (". $sequence .") WHERE ".$setWhere;
		$where_params = array_merge($where_params, $sequence_param);
		$result = $this->execute($sql, $where_params);
		if (!$result) {
			$this->addError();
			return false;
		}
		return true;
	}

	/**
	 * LOB更新用
	 * @param	string	 $tableName       対象テーブル名称
	 * @param	string   $column          カラム名称
	 * @param	string   $path　　　　　　パス
	 * @param	array    $where
	 * @param	string   $blobtype
	 * @return boolean true or false
	 * @access	public
	 */
	function updateBlobFile($tableName, $column, $path, $where, $blobtype='BLOB') {
    	$result = $this->_conn->UpdateBlobFile($this->_prefix . $tableName, $column, $path, $where, $blobtype);
    	if (!$result) {
			$this->addError();
			return false;
		}
		return true;
	}

    /**
　 	 * 更新・削除レコード数取得
　 	 * @return integer : 更新/削除レコード数
　 	 *         bool    : false 更新/削除レコード無し または未サポート
　 	 * @access public
　 	 */
	function affectedRows() {
		$result = $this->_conn->Affected_Rows();
		return $result;
	}

	/**
	 * AdoDbObject取得用
	 * @return object adodb
	 * @access	public
	 */
	function &getAdoDbObject() {
		return $this->_conn;
	}

	/**
	 * エラーメッセージ追加処理
	 * @param	string	error_no
     * @param	string	error_mes
	 * @access	public
	 */
	function addError($error_no=null, $error_mes=null)
	{
        $container =& DIContainerFactory::getContainer();
	    $actionChain =& $container->getComponent("ActionChain");
		$errorList =& $actionChain->getCurErrorList();
		if($error_no == null && $error_mes == null) {
			$session =& $container->getComponent("Session");
			if(isset($session) && $session->getParameter("_php_debug") == _ON) {
				$errorList->add($this->ErrorNo(), $this->ErrorMsg(). ":\n". $this->_bck_sql);
			} else {
				$errorList->add($this->ErrorNo(), "SQL Error!");
			}
		} else {
			$errorList->add($error_no, $error_mes);
		}
	}

	/**
	 * MATCH AGAINST用の文字列に変換する
	 *　
	 * @return	bool	$debugMode	デバッグモードをセットする
	 * @access	public
	 */
	function stringMatchAgainst($str) {
		return preg_replace('/[\+\-\<\>~\(\)\*"\'\.\\\]{1}/uU','\\\\$0',$str);
	}
}

//グローバル宣言（位置移動するかも)
define('ADODB_OUTP',"db_outp");

/**
 * adodb.inc.phpのoutp内使用関数
 * DBへのエラー登録処理：define('$ADODB_OUTP',"db_outp");
 *
 * @abstract
 *
 * @author      Ryuji Masukawa
 */
function db_outp($msg,$newline=true)
{
	//if ($newline) $msg .= "<br/>\n";
	$container =& DIContainerFactory::getContainer();
	$actionChain =& $container->getComponent("ActionChain");
	$filterChain =& $container->getComponent("FilterChain");
	$log =& LogFactory::getLog();
	$log->sql_trace("CurrentFilterName:[<span class='bold'>". $filterChain->getCurFilterName()."</span>]   CurrentActionName:[<span class='bold'>".$actionChain->getCurActionName()."</span>] ".$msg);
}
?>