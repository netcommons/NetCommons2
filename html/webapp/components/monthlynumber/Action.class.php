<?php
/**
 * 月別一覧回数登録用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Monthlynumber_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Monthlynumber_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * 月別一覧回数インクリメント処理
	 * @param array (user_id, room_id, module_id, name , year, month)
	 * @return boolean
	 * @access	public
	 */
	function incrementMonthlynumber($params, $increment=1) {
		$session =& $this->_container->getComponent("Session");
		$request =& $this->_container->getComponent("Request");
		
		
		if(!isset($params['user_id'])) {
			$params_user_id = $session->getParameter("_user_id");
		} else {
			$params_user_id	= $params['user_id'];
		}
		if(!isset($params['room_id'])) {
			$params_room_id = $request->getParameter("room_id");	
		} else {
			$params_room_id	= $params['room_id'];
		}
		if(!isset($params['module_id'])) {
			$params_module_id = $request->getParameter("module_id");	
		} else {
			$params_module_id = $params['module_id'];
		}
		if(!isset($params['name'])) {
			$params_name = "_posting_number";	
		} else {
			$params_name = $params['name'];
		}
		$time = timezone_date();
		if(!isset($params['year'])) {
			$params_year = intval(substr($time, 0, 4));
		} else {
			$params_year = intval($params['year']);
		}
		if(!isset($params['month'])) {
			$params_month = intval(substr($time, 4, 2));
		} else {
			$params_month = intval($params['month']);
		}
		$ins_params = array(
			"user_id" => $params_user_id,
			"room_id" => $params_room_id,
			"module_id" => $params_module_id,
			"name" => $params_name,
			"year" => $params_year,
			"month" => $params_month
		);
		$insert_flag = false;
		$number = $this->getNumber($ins_params);
		if($number === false) {
			// データなし
			$insert_flag = true;
			$number = 0;
		}
		$number = $number + $increment;
		if ($number < 0) {
			$number = 0;
		}
		$ins_params = array_merge($ins_params, array('number'=>$number));
		if($insert_flag) {
			//insert
			$result = $this->insMonthlynumber($ins_params);
		} else {
			//update
			$result = $this->updMonthlynumber($ins_params);
		}
		return $result;
	}
	
	/**
	 * 月別一覧回数オブジェクトを取得
	 * @param array (user_id,room_id, module_id, name , year, month)
	 * @return int number or false
	 * @access	public
	 */
	function getNumber($params) {
		$sql = "SELECT {monthly_number}.number FROM {monthly_number}" .
					" WHERE {monthly_number}.user_id = ?" .
					" AND {monthly_number}.room_id = ?" . 
					" AND {monthly_number}.module_id = ?" . 
					" AND {monthly_number}.name = ?" . 
					" AND {monthly_number}.year = ?" . 
					" AND {monthly_number}.month = ?" . 
					" ";
		$result = $this->_db->execute($sql,$params,1,0,false);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		if(isset($result[0][0])) {
			return $result[0][0];
		}else {
			return false;
		}
	}
	
	/**
	 * Monthlynumber Insert
	 * @param array (user_id,room_id, module_id, name, year, month, number)
	 * @return boolean true or false
	 * @access	public
	 */
	function insMonthlynumber($params)
	{
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        if($user_name === null) $user_name = "";
        if(!isset($params['update_time'])){
	        $params_footer = array(
	        	"update_time" =>$time,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name
			);
			$params = array_merge($params,$params_footer);
        }
        
		$sql = "INSERT INTO {monthly_number} (".
						"user_id,".
						"room_id,".
						"module_id,".
						"name,".
						"year,".
						"month,".
						"number,".
						"update_time,".
						"update_user_id,".
						"update_user_name) ".
					"VALUES(".
						"?,?,?,?,?,?,?,?,?,?" .
						")";
						
        $result = $this->_db->execute($sql,$params);
        if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		
		return true;
	}
	
	
	/**
	 * Monthlynumber Update
	 * @param array (user_id,room_id, module_id, name, year, month, number)
	 * @return boolean true or false
	 * @access	public
	 */
	function updMonthlynumber($upd_params)
	{
		$time = timezone_date();
        $session =& $this->_container->getComponent("Session");
        $user_id = $session->getParameter("_user_id");
        $user_name = $session->getParameter("_handle");
        if($user_name === null) $user_name = "";
        if(!isset($upd_params['update_time'])){
	        $params_footer = array(
	        	"update_time" =>$time,
				"update_user_id" => $user_id,
				"update_user_name" => $user_name
			);
			$upd_params = array_merge($upd_params,$params_footer);
        }
		//$params = array_merge(array('upload_id' => $upload_id),$params);
		
		$params = array(
        	"number" => $upd_params['number'],
        	"update_time" => $upd_params['update_time'],
        	"update_user_id" => $upd_params['update_user_id'],
        	"update_user_name" => $upd_params['update_user_name'],
        	"user_id" => $upd_params['user_id'],
        	"room_id" => $upd_params['room_id'],
        	"module_id" => $upd_params['module_id'],
        	"name" => $upd_params['name'],
        	"year" => $upd_params['year'],
        	"month" =>$upd_params['month']
		);
		
		$sql = "UPDATE {monthly_number} SET ".
						"number=?,".
						"update_time=?,".
						"update_user_id=?,".
						"update_user_name=?".
					" WHERE {monthly_number}.user_id = ?" .
					" AND {monthly_number}.room_id = ?" . 
					" AND {monthly_number}.module_id = ?" . 
					" AND {monthly_number}.name = ?" . 
					" AND {monthly_number}.year = ?" . 
					" AND {monthly_number}.month = ?" . 
					" ";
			
        $result = $this->_db->execute($sql,$params);
        if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		
		return true;
	}
	
	/**
	 * 過去のMonthlynumberデータ削除処理
	 * 指定年より以前のデータを削除
	 * @param int year
	 * @return boolean true or false
	 * @access	public
	 */
	function delMonthlynumberByYear($year)
	{
		$params = array( 
			"year" => $year
		);
		$result = $this->_db->execute("DELETE FROM {monthly_number} WHERE year<=?" .
										" ",$params);
		
		if ($result === false) {
			$this->_db->addError();
			return $result;
		}
		
		return true;
	}
}
?>
