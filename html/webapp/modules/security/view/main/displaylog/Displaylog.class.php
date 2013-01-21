<?php

/**
 * ログ一覧表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Security_View_Main_Displaylog extends Action
{
	// リクエストパラメータを受け取るため
	
	// 使用コンポーネントを受け取るため
    var $db = null;
    var $usersView = null;
    var $session = null;
    
    // フィルタによりセット
    
    // 値をセットするため
	var $displaylog_tables = null;
	var $displaylog_count = null;
	
	/**
     * ログ一覧取得
     *
     * @access  public
     */
	function execute()
	{
        $sql = "SELECT {security_log}.*, {users}.handle".
               " FROM {security_log}".
               " LEFT JOIN {users} ON ({security_log}.uid={users}.user_id)";
		$order_params = array(
    		"{security_log}.insert_time" =>"DESC"
    	);
        $sql .= $this->db->getOrderSQL($order_params);
        $ret = $this->db->execute($sql, null, 0,0, true, array($this,"_fetchCallback"));
        //$ret = $this->db->execute($sql);
        if ($ret === false) {
        	return 'error';
        }
        $this->displaylog_tables = $ret;
        $this->displaylog_count = count($this->displaylog_tables);
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array
	 * @access	private
	 */
	function &_fetchCallback($result) {
		$ret = array();
		while ($row = $result->fetchRow()) {
			if(isset($row['insert_time'])) {
				$timezone_offset = $this->session->getParameter("_server_TZ");
				$time = $row['insert_time'];
				$int_time = mktime(intval(substr($time, 8, 2))+$timezone_offset, intval(substr($time, 10, 2)), intval(substr($time, 12, 2)), 
						intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));
				$row['insert_time'] = date(_FULL_DATE_FORMAT, $int_time);
			}
			$ret[] = $row;
		}
		return $ret;
	}
}
?>
