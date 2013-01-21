<?php
/**
 * 月別一覧回数表示用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Monthlynumber_View {
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
	function Monthlynumber_View() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 月別一覧回数一覧を取得
	 * @param string year
	 * @param string month
	 * @param int room_id
	 * @param int user_id
	 * @return array  array(login_label, monthly_list, monthly_numbers)
	 * @access	public
	 */
	function get($year = null, $month = null, $room_id = null, $user_id = null, $role_auth_id = null) {
		$login_label = null;
		$time = timezone_date();
    	$year = ($year == null) ? intval(substr($time, 0, 4)) : intval($year);
    	$month = ($month == null) ? intval(substr($time, 4, 2)) : intval($month);
    	//$def_month = $month;
    	$monthly_list = array();
    	$monthly_login_list = array();
    	if($room_id == null) {
    		//ルームIDの指定がなければ、ログイン回数を取得(会員管理)
    		$monthly_login_list =& $this->getLoginNumber($year, $user_id);
    		if ($monthly_login_list === false) {
	       	 	return $monthly_login_list;
			}
    	}
    	// アクセス回数-投稿回数等
    	$result =& $this->getMonthlyNumberList($year, $room_id, $user_id, $role_auth_id);
    	if ($result === false) {
       	 	return $result;
		}
		list($monthly_list, $pages_list, $monthly_row_exists, $rowspan_list) = $result;
    	////$monthly_list = array_merge($monthly_list, $monthly_numbers);

		return array($month, $monthly_list, $pages_list, $monthly_row_exists, $rowspan_list, $monthly_login_list);
	}

	/**
	 * 月別一覧回数オブジェクト一覧を取得(ログイン回数)
	 * @param string year
	 * @param int user_id
	 * @return array Monthly_Number Object
	 * @access	public
	 */
	function getLoginNumber($year, $user_id = null) {
		if($user_id == "0" || $user_id == null) {
			$session =& $this->_container->getComponent("Session");
			$user_id = $session->getParameter("_user_id");
		}
		if($user_id != "0") {
			$params = array(
								"user_id"=>$user_id,
    							"year"=>$year,
    							"year2"=>$year - 1
							);
			$sql = "SELECT 0 AS page_id, '' AS page_name, {monthly_number}.name, {monthly_number}.year, {monthly_number}.month, {monthly_number}.number " .
						" FROM {monthly_number} " .
						" WHERE {monthly_number}.room_id = 0 " .
						" AND user_id = ? " .
						" AND ({monthly_number}.year = ? OR {monthly_number}.year = ?) ";
			$result = $this->_db->execute($sql, $params, null, null, true, array($this, "_fetchcallbackLoginNumber"));
			if ($result === false) {
	       	 	$this->_db->addError();
	       		return null;
			}
			return $result;
		}
		//ログインしていない
		return null;
	}

	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $pages
	 * @access	private
	 */
	function &_fetchcallbackLoginNumber($result) {
		$monthly_list = array();
		// name, thread_num, parent_id, display_sequence
		while ($row = $result->fetchRow()) {
			$monthly_list["nc".$row['name']][$row['page_id']][$row['month']] = intval($row['number']);
		}
		return $monthly_list;
	}

	/**
	 * 月別一覧回数オブジェクト一覧を取得
	 * @param string year
	 * @param string month
	 * @param int    room_id (nullの場合、表示可能なすべての一覧を取得)
	 * @param string user_id (nullの場合、ログイン会員)
	 * @param int    role_auth_id (nullの場合、user_idから取得)
	 * @return array Monthly_Number Object
	 * @access	public
	 */
	function getMonthlyNumberList($year, $room_id = null, $user_id = null, $role_auth_id = null, $limit=0, $start=0) {
		if($user_id == null) {
			$session =& $this->_container->getComponent("Session");
			$user_id = $session->getParameter("_user_id");
		}
		if($user_id != "0") {
			if($role_auth_id == null) {
				$usersView =& $this->_container->getComponent("usersView");
				$user = $usersView->getUserById($user_id);
				if($user === false) {
					return null;
				}
			}
			$authoritiesView =& $this->_container->getComponent("authoritiesView");
			$authority = $authoritiesView->getAuthorityById($role_auth_id);
    		if($authority === false) {
				return null;
			}

			//$_user_auth_id = $session->getParameter("_user_auth_id");
    		if($room_id != null) {
    			//ルーム管理：ルーム毎のSUM
    			$params = array(
    							//"user_id"=>$user_id,
    							"room_id"=>$room_id
    							//"sub_room_id"=>$room_id
							);
    			$sql = "SELECT {pages}.page_id,{pages}.root_id, {pages}.parent_id,{pages}.thread_num, {pages}.display_sequence, {pages}.page_name, {pages}.private_flag, {pages}.space_type, {monthly_number}.name, {monthly_number}.year, {monthly_number}.month, SUM({monthly_number}.number) AS number " .
						" FROM {pages} ";
				$sql .= " LEFT JOIN {monthly_number} ON {pages}.room_id = {monthly_number}.room_id ";
				//$sql .= " LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.room_id = ? ";

    			$sql .= " WHERE 1=1 ";

    		} else {
    			$params = array(
								"user_id"=>$user_id,
								"user_id_monthly"=>$user_id
							);
				$sql = "SELECT {pages}.page_id, {pages}.root_id, {pages}.parent_id, {pages}.thread_num, {pages}.display_sequence, {pages}.page_name, {pages}.private_flag, {pages}.space_type, {monthly_number}.name, {monthly_number}.year, {monthly_number}.month, {monthly_number}.number " .
						" FROM {pages} ";
				$sql .= " LEFT JOIN {monthly_number} ON {pages}.room_id = {monthly_number}.room_id AND {monthly_number}.user_id = ? ";
    			$sql .= " LEFT JOIN {pages_users_link} ON {pages}.room_id = {pages_users_link}.room_id AND {pages_users_link}.user_id = ? ";

    			$sql .= " WHERE (({pages}.private_flag = "._ON." " .
						"AND {pages_users_link}.user_id IS NOT NULL) OR ({pages}.private_flag = "._OFF." AND ({pages}.space_type = "._SPACE_TYPE_GROUP." OR {pages}.space_type ="._SPACE_TYPE_PUBLIC."))) ";
    		}

			//ルームのみ
			$sql .= " AND {pages}.node_flag = ". _ON . " AND {pages}.room_id = {pages}.page_id ";
			if($authority['myroom_use_flag'] ==_OFF) {
				$sql .= " AND {pages}.private_flag = ". _OFF ." ";
			}
			if($room_id != null) {
				//ルーム管理：ルーム毎のSUM
				//$sql .= " AND ({pages}.page_id = ? OR {pages}.root_id = ?) ";
				$sql .= " AND {pages}.page_id = ? ";
				$sql .= " GROUP BY {pages}.page_id,{pages}.root_id, {pages}.parent_id, {pages}.thread_num, {pages}.display_sequence, {pages}.page_name, {pages}.private_flag, {pages}.space_type, {monthly_number}.name, {monthly_number}.year, {monthly_number}.month ";
			}

			$sql .= " ORDER BY {pages}.root_id, {pages}.thread_num, {pages}.display_sequence , {monthly_number}.year,{monthly_number}.month";
			$result = $this->_db->execute($sql,$params,$limit, $start, true, array($this, "_fetchcallbackMonthlyNumberList"), array($room_id));
			if ($result === false) {
		       	$this->_db->addError();
		       	return null;
			}
			return $result;
		}
		//ログインしていない
		return null;
	}


	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $pages
	 * @access	private
	 */
	function _fetchcallbackMonthlyNumberList($result, $func_params) {
		$room_id = (isset($func_params[0])) ? $func_params[0] : 0;

		$monthly_list = array();
		$pages_list = array();
		$monthly_row_exists = array();
		$set_count = -1;
    	$pre_page_id = "";
    	$pre_name = "";
    	$rowspan_list = array();

    	$row_num = 0;
		while ($row = $result->fetchRow()) {
			////if($row['name'] == "" && $row['thread_num'] == 0) $row['name'] = "_hit_number";
			//if(!isset($monthly_list["nc".$row['name']])) {
			//	//$monthly_list[$row['name']][$row['page_id']] = array();
			//	$set_count++;
			//	$rowspan_list[$set_count] = 1;
			//}
			//var_dump($row['page_id']);

			//if($row['name'] != "") {
			//	if(!isset($rowspan_list["nc".$row['name']])) $rowspan_list["nc".$row['name']] = 1;
			//	//if($row['name'] == $pre_name && $row['page_id'] != $pre_page_id) {
			//	if($row['page_id'] != $pre_page_id) {
			//		//ページIDが異なる場合、rowspan++
			//		$rowspan_list["nc".$row['name']]++;
			//	}
			//}

			// name, thread_num, parent_id, display_sequence

			$monthly_row_exists["nc".$row['name']][$row['page_id']] = true;
			if(!empty($monthly_list["nc".$row['name']][$row['page_id']][$row['month']])) {
				$monthly_list["nc".$row['name']][$row['page_id']][$row['month']] = $monthly_list["nc".$row['name']][$row['page_id']][$row['month']] + intval($row['number']);
			} else {
				$monthly_list["nc".$row['name']][$row['page_id']][$row['month']] = intval($row['number']);
			}

			if($room_id == 0) {
				//root_id,parent_idしか考慮しないため、サブグループが２つ以上作れる仕様にしてしまうと
				//問題となる
				if($row['parent_id']) {
					$monthly_row_exists["nc".$row['name']][$row['parent_id']] = true;
				}

				if($row['root_id']) {
					$monthly_row_exists["nc".$row['name']][$row['root_id']] = true;
				}
			}
			if(!isset($pages_list[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])])) {
				$pages_list[intval($row['thread_num'])][intval($row['parent_id'])][intval($row['display_sequence'])] = $row;
				$row_num++;
			}
			//現状、加算せずに表示
			//子グループが１つまでの場合は問題ないが、２つ以上作れる仕様にしてしまうと
			//root_idだけの加算だけでは、問題となる
			//if(isset($monthly_list["nc".$row['name']][$row['root_id']][$row['month']])) {
			//	$monthly_list["nc".$row['name']][$row['root_id']][$row['month']] += intval($row['number']);
		    //}

			$pre_page_id = $row['page_id'];
			//$pre_name = $row['name'];
		}
		if(count($monthly_row_exists) > 0) {
			foreach($monthly_row_exists as $key => $monthly_row_exist) {
				$rowspan_list[$key] = count($monthly_row_exist);
			}
		}
		return array($monthly_list, $pages_list, $monthly_row_exists, $rowspan_list);
	}


	/**
	 * 会員のルームアクセス状況一覧を取得する
	 * @param int user_id
	 * @return array key:room_id last_access_time
	 * @access	public
	 */
	function &getUserAccessTime($user_id)
	{
		$params = array(
			"user_id" => $user_id
		);
		$sql = "SELECT {monthly_number}.user_id, {monthly_number}.room_id, {monthly_number}.update_time AS last_access_time " .
					" FROM {monthly_number} " .
					" WHERE {monthly_number}.user_id=? ";

        $result =  $this->_db->execute($sql, $params, null, null, true, array($this, "_fetchcallbackUserAccessTime"));
        if ($result === false) {
	       	 $this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $pages
	 * @access	private
	 */
	function &_fetchcallbackUserAccessTime($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			$ret[$row['room_id']] = $row;
		}
		return $ret;
	}

}
?>
