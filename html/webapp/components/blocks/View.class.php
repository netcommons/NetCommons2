<?php
/**
 *  ブロック表示用クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Blocks_View {
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
	function Blocks_View() {
		$this->_container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
    	$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	
	/**
	 * blockテーブルからblock_objectの配列を取得する
	 * @param int      page_id or array page_id_array
	 * @param int      module_id
	 * @param function func
	 * @param array    func_param
	 * @return array
	 * @access	public
	 */
	function &getBlockByPageId($params, $where_params = null, $func = null, $func_param = null) 
	{
		$sql_where = "";
		$count = 0;
		if(is_array($params)) {$count = count($params);}
		if (!empty($where_params)) {
	        foreach ($where_params as $key=>$item) {
	        	if (isset($item)) {
					$params[$key] = $item;
					$sql_where .= " AND ".$key."=?";
				} else {
					$sql_where .= " AND ".$key;
				}
	        }
        }
		if($count > 0) {
			
			$sql = "SELECT {blocks}.* FROM {blocks} " .
											" WHERE ({blocks}.page_id=?";
			for($i = 1; $i < $count; $i++) {
				$sql .= " OR {blocks}.page_id=?";
			}
			$sql .= ")";
			$sql .= $sql_where;
			$sql .= " ORDER BY {blocks}.page_id, {blocks}.thread_num,{blocks}.col_num,{blocks}.row_num ";
			
			$result = $this->_db->execute($sql, $params, null, null, true, $func, $func_param);
		} else {
			$id = $params;
			$params = array(
				"page_id" => $id
			);
			$sql = "SELECT {blocks}.* FROM {blocks} " .
					" WHERE {blocks}.page_id=? ";
			$sql .= $sql_where;
			$sql .= " ORDER BY {blocks}.page_id, {blocks}.thread_num,{blocks}.col_num,{blocks}.row_num ";
			
			$result = $this->_db->execute($sql, $params, null, null, true, $func, $func_param);	
		}
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		//if(!isset($result[0])) {
		//	$result= false;
		//}
		return $result;
	}
	 /*
	function &getBlockByPageId($id) 
	{
		//パラメータセット　引数の配列になければリクエストパラメータの値を使用
		//if(is_array($param)) 
		//	$id = isset($param['page_id']) ? $param['page_id'] : $param['id'];
		//else
		//	$id = $param;
		
		$session =& $this->_container->getComponent("Session");
		
		$params = array(
			"user_id" => $session->getParameter("_user_id"),
			"page_id" => $id
		);
		
		$result = $this->_db->execute("SELECT {blocks}.*,{location_link}.auth_id,{location_link}.show_flag,{location_link}.left_size,{location_link}.top_size FROM {blocks} " .
										" LEFT JOIN {location_link} ON {blocks}.block_id = {location_link}.block_id AND {location_link}.user_id = ?" .
										" WHERE {blocks}.page_id=? ORDER BY {blocks}.thread_num,{blocks}.col_num,{blocks}.row_num ",$params);
		//$result = $this->_db->execute("SELECT {blocks}.*,{location_link}.auth_id,{location_link}.show_flag,{location_link}.left_size,{location_link}.top_size,{location_link}.thread_num FROM {blocks} " .
		//								" LEFT JOIN {location_link} ON {blocks}.block_id = {location_link}.block_id AND {location_link}.user_id = ?" .
		//								" WHERE {blocks}.page_id=? ORDER BY {blocks}.thread_num,{blocks}.col_num,{blocks}.row_num ",$params);
		
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		if(isset($result[0])) {
			return $result;
		}else{
			return false;
		}
	}
	
	*/
	/**
	 * block_idからblock_objectを取得する
	 * @param int block_id
	 * @return object block_object
	 * @access	public
	 */
	function &getBlockById($id)
	{
		$params = array(
			"block_id" => $id
		);
		
		$result = $this->_db->execute("SELECT {blocks}.* FROM {blocks} " .
										" WHERE {blocks}.block_id=?",$params,1,0);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		return $result[0];
	}
	
	/**
	 * module_idからblock_objectを取得する
	 * @param int module_id
	 * @return object block_object
	 * @access	public
	 */
	function &getBlockByModuleId($module_id)
	{
		$params = array(
			"module_id" => $module_id
		);
		
		$result = $this->_db->execute("SELECT {blocks}.* FROM {blocks} " .
										" WHERE {blocks}.module_id=?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		return $result;
	}
	
	/**
	 * parent_idからblock_object配列を取得する
	 * @param int parent_id
	 * @return object block_object
	 * @access	public
	 */
	function &getBlockByParentId($parent_id, $page_id = null)
	{
		if($page_id == null) {
			$params = array( 
				"parent_id" => $parent_id
			);
			$result = $this->_db->execute("SELECT * FROM {blocks} WHERE parent_id=?",$params);
		} else {
			$params = array( 
				"parent_id" => $parent_id,
				"page_id" => $page_id
			);
			$result = $this->_db->execute("SELECT * FROM {blocks} WHERE parent_id=? AND page_id=?",$params);	
		}
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		
		return $result;
	}
	
	/**
	 * root_idからblock_object配列を取得する
	 * @param int root_id
	 * @return object block_object
	 * @access	public
	 */
	function &getBlockByRootId($root_id, $page_id = null)
	{
		if($page_id == null) {
			$params = array( 
				"root_id" => $root_id
			);
			$result = $this->_db->execute("SELECT * FROM {blocks} WHERE root_id=? ORDER BY thread_num",$params);
		} else {
			$params = array( 
				"root_id" => $root_id,
				"page_id" => $page_id
			);
			$result = $this->_db->execute("SELECT * FROM {blocks} WHERE root_id=? AND page_id=? ORDER BY thread_num",$params);	
		}
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		
		return $result;
	}
	
	/**
	 * col_numの行数の取得
	 * @param array (key:page_id,parent_id,col_num)
	 * @return int 指定列の合計行数
	 * @access	public
	 */
	function &getCountRownumByColnum($params=array())
	{
		////パラメータセット　引数の配列になければリクエストパラメータの値を使用
		//$params = array( 
		//			"page_id" => (isset($param_arr['page_id'])) ? $param_arr['page_id'] : $this->page_id,
		//			"parent_id" => (isset($param_arr['parent_id'])) ? $param_arr['parent_id'] : $this->parent_id,
		//			"col_num" => (isset($param_arr['col_num'])) ? $param_arr['col_num'] : $this->col_num
		//);
		
		$result = $this->_db->execute("SELECT COUNT(*) FROM {blocks} WHERE page_id=?" .
										" AND parent_id=?" .
										" AND col_num=?" .
										" ",$params,null,null,false);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		
		return $result[0][0];
	}
	
	/**
	 * parent_idから同じ深さのブロックをカウントする
	 * @param int parent_id
	 * @return int count_num
	 * @access	public
	 */
	function getCountByParentid($parent_id)
	{
		$params = array( 
			"parent_id" => $parent_id
		);
		
		$result = $this->_db->execute("SELECT COUNT(*) FROM {blocks} WHERE parent_id=?" .
										" ",$params,null,null,false);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		
		return $result[0][0];
	}
	
	/*
	function &getBlockById($id)
	{
		//パラメータセット　引数の配列になければリクエストパラメータの値を使用
		//if(is_array($param)) 
		//	$id = isset($param['block_id']) ? $param['block_id'] : $param['id'];
		//else
		//	$id = $param;
		
		$session =& $this->_container->getComponent("Session");
		
		$params = array(
			"user_id" => $session->getParameter("_user_id"),
			"block_id" => $id
		);
		
		$result = $this->_db->execute("SELECT {blocks}.*,{location_link}.auth_id,{location_link}.show_flag,{location_link}.left_size,{location_link}.top_size,{location_link}.thread_num FROM {blocks} " .
										" LEFT JOIN {location_link} ON {blocks}.block_id = {location_link}.block_id AND {location_link}.user_id = ?" .
										" WHERE {blocks}.block_id=?",$params);
		if($result === false) {
			//エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
	       	return $result;
		}
		return $result[0];
	}
	*/
}
?>
