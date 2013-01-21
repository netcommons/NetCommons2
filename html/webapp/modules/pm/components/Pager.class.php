<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [pm表示用クラス]
 */
class Pm_Components_Pager
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	
	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;
	
	// 値をセットするため
    var $total_pages = null;
	var $total_rows = null;
	
	var $prev_page = null;
	var $current_page = null;
	var $next_page = null;
	
	var $display_count = null;
	var $display_rows = null;
	var $display_from = null;
	var $display_to = null;
	
	var $count_key = null;
	var $page_holder = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pm_Components_Pager() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_request =& $this->_container->getComponent("Request");
	}
	
	function &pager($query = array(), $display_count = 20, $count_key = '*', $page_holder = 'page'){
		$this->display_count = $display_count;
		$this->count_key = $count_key;
		$this->page_holder = $page_holder;
		$pager = array();
		
		$sql = $query['sql'];
		$params = $query['params'];
		
		$page = $this->_request->getParameter($this->page_holder);
		if (empty($page) || !is_numeric($page)) {
			$page = 1;
		}
		$this->current_page = $page;
		
		$pos_to = strlen($sql);
		$pos_from = strpos($sql, ' FROM', 0);
		$pos_group_by = strpos($sql, ' GROUP BY', $pos_from);
		if (($pos_group_by < $pos_to) && ($pos_group_by != false)) $pos_to = $pos_group_by;
		$pos_having = strpos($sql, ' HAVING', $pos_from);
		if (($pos_having < $pos_to) && ($pos_having != false)) $pos_to = $pos_having;
		$pos_order_by = strpos($sql, ' ORDER BY', $pos_from);
		if (($pos_order_by < $pos_to) && ($pos_order_by != false)) $pos_to = $pos_order_by;
		if (strpos(strtoupper($sql), 'DISTINCT') || strpos(strtoupper($sql), 'GROUP BY')) {
			$count_string = 'DISTINCT ' . trim($this->count_key);
		} else {
			$count_string = trim($this->count_key);
		}
		
		$sql = "SELECT count(" . $count_string . ") as cnt " . substr($sql, $pos_from, ($pos_to - $pos_from));
		$counts = $this->_db->execute($sql, $params);
		
		if (!$counts) {
			$this->_db->addError();
			return $pager;
		}
		
		$this->total_rows = $counts[0]['cnt'];
		$this->total_pages = ceil($this->total_rows / $this->display_count);
		if ($this->current_page > $this->total_pages) {
			$this->current_page = $this->total_pages;
		}

		$this->prev_page = $this->current_page - 1;
		if($this->prev_page <= 0){
			$this->prev_page = 0;
		}
		
		$this->next_page = $this->current_page + 1;
		if($this->next_page > $this->total_pages){
			$this->next_page = 0;
		}
		
		if($this->total_rows > 0){
			$this->display_from = ($this->current_page - 1) * $this->display_count + 1;
			
			if($this->current_page < $this->total_pages){
				$this->display_rows = $this->display_count;
			}else{
				$this->display_rows = $this->total_rows - ($this->current_page - 1) * $this->display_count;
			}
			$this->display_to = $this->display_from + $this->display_rows - 1;
		}else{
			$this->display_rows = 0;
			$this->display_from = 0;
			$this->display_to = 0;
		}
		
		$pager = array(
			'prev_page' => $this->prev_page, 
			'next_page' => $this->next_page, 
			'current_page' => $this->current_page, 
			'total_rows' => $this->total_rows,
			'total_pages' => $this->total_pages,
			'display_count' => $this->display_count,
			'display_rows' => $this->display_rows,
			'display_from' => $this->display_from,
			'display_to' => $this->display_to
		);
		
		return $pager;
	}
	
	function limit(){
		return $this->display_count;
	}
	
	function offset(){
		$offset = ($this->display_count * ($this->current_page - 1));
		if($offset <= 0){
			$offset = 0;
		}
		return $offset;
	}
}
?>