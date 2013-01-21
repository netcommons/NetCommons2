<?php
/**
 * @author	    Ryuji Masukawa
 * @copyright	copyright (c) 2006 NetCommons.org
 */

/**
 * デバッグDB情報クラス
 * 
 * @abstract
 * 
 * @author      Ryuji Masukawa
 */
class DebugDb
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * DBオブジェクトをセットする
	 *　
	 * @param	object	$db
	 * @access	public
	 */
	function setDb(&$db) {
		$this->_db =& $db;	
	}
	
	/**
	 * DBオブジェクトがセットされているかどうか
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function hasDb() {
		if(is_object($this->_db))
			return true;
		else
			return false;	
	}
	
	/**
	 * DEBUG情報を登録する
	 *　
	 * @param	array	$debug_array
	 * @return boolean true or false
	 * @access	public
	 */
	function insDebug($params=array()) {
		$date = date("Ymd");
        $time = date("His");
        $user_name = isset($_SESSION['_login_user']) ? $_SESSION['_login_user'] : "";
        if(!isset($params['id'])){
        	$params_header = array(
				"id" =>session_id()
			);
			$params = array_merge($params_header,$params);
        }
        if(!isset($params['update_date'])){
	        $params_footer = array(
				"update_date" =>$date,
				"update_time" => $time,
				"update_user" => $user_name
			);
			$params = array_merge($params,$params_footer);
        }
		if($this->getDebugValue($params['id'],$params['debug_type'],$params['param'])) {
			$params_update = array(
				"action_name" =>$params['action_name'],
				"debug_value" => $params['debug_value'],
				"update_date" => $params['update_date'],
				"update_time" =>$params['update_time'],
				"update_user" => $params['update_user'],
				"id" => $params['id'],
				"debug_type" =>$params['debug_type'],
				"param" => $params['param']
			);
			$sql = "UPDATE {debug} SET ".
						    "action_name=?,".
							"debug_value=?,".
							"update_date=?,".
							"update_time=?,".
							"update_user=? ".
					"WHERE id=? AND debug_type=? AND param=?";
			$result = $this->_db->execute($sql,$params_update);
		} else {
			$sql = "INSERT INTO {debug} (".
							"id,".
							"debug_type,".
							"param,".
							"action_name,".
							"debug_value,".
							"update_date,".
							"update_time,".
							"update_user) ".
						"VALUES(".
							"?,?,?,?,?,?,?,?" .
							")";
			$result = $this->_db->execute($sql,$params);
		}		
        
        if(!$result) {
        	//エラーが発生しても処理しない
        	return false;
		}
		
		return true;
	}
	
	/**
	 * DEBUG情報を取得する
	 * 
	 * @param	int	$id,int $debug_type
	 * @return array debug_object
	 * @access	public
	 */
	function getDebugValue($id=null, $debug_type, $param=null) {
		if($param == null) {
			//DBからの取得処理
			$params = array( 
				"id" => isset($id) ? $id : session_id(),
				"debug_type" => $debug_type
			);
			$result = $this->_db->execute("SELECT * FROM {debug} WHERE id=? AND debug_type=?",$params);
			if($result) {
				$result_main = array();
				foreach ($result as $recArr) {
					$result_main[$recArr['param']][$recArr['action_name']] = $recArr;
				}
				return $result_main;
			}
			return false;
		} else {
			//DBからの取得処理
			$params = array( 
				"id" => $id,
				"debug_type" => $debug_type,
				"param" => $param
			);
			$result = $this->_db->execute("SELECT * FROM {debug} WHERE id=? AND debug_type=? AND param=?",$params);
			if($result) {
				return $result[0];
			}
			return false;	
		}
	}
	
	/**
	 * id(debug_type)毎の削除処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function delDebugById($id,$debug_type=null)
	{
		if($debug_type == null) {
			$params = array( 
				"id" => (isset($id)) ? $id : session_id()
			);
			
			$result = $this->_db->execute("DELETE FROM {debug} WHERE id=?" .
											" ",$params);
		} else {
			$params = array( 
				"id" => (isset($id)) ? $id : session_id(),
				"$debug_type" => $debug_type
			);
			
			$result = $this->_db->execute("DELETE FROM {debug} WHERE id=? AND debug_type=?" .
											" ",$params);	
			
		}
		if(!$result) {
			return false;
		}
		return true;
	}
}
?>