<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システム関連情報取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_Components_View
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
	 * コンストラクター
	 *
	 * @access	public
	 */
	function System_Components_View()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$this->_usersView = & $this->_container->getComponent("usersView");
	}

	/**
	 * itemを取得する
	 * 
	 * @param   int   $item_id  項目ID
	 * @return array
	 * @access	public
	 */
	function &getItems($where_params=null, $order_params=null, $limit = null, $offset = null, $func=null, $func_param=null)
	{
		$sql = "SELECT {items}.*, {items_options}.options,{items_options}.default_selected".
				" FROM {items} ".
				" LEFT JOIN {items_options} ON ({items}.item_id={items_options}.item_id)";
		$sql .= $this->_db->getWhereSQL($params, $where_params);
		$sql .= $this->_db->getOrderSQL($order_params);
	
		$result =$this->_db->execute($sql, $params, $limit, $offset, true,  $func, $func_param);
		if($result === false) {
			// エラーが発生した場合、エラーリストに追加
			$this->_db->addError();
			return $result;
		}
		return $result;
	}

	/**
	 * autoregist_use_itemsから変更不可のIDだけを取り出す
	 * 1:1|2:1|3:1|4:0| -> (1, 2, 3)
	 * 
	 * @param   string	
	 * @return array
	 * @access	public
	 */
	function useItemsKeys($use_item)
	{
		return array_filter( array_map(
        	create_function('$mustflag',
        		'if(!$mustflag) return false;
        		list($item, $flag)=explode(":", $mustflag);
        		return ($flag)? $item : false;'
        	),
        	explode("|", $use_item)
    	));
	}
	
	/**
	 * autoregist_use_itemsをパースして連想配列を返す
	 * 1:1|2:1|3:1|4:0| -> {1 => 1, 2 => 1, 3 => 1, 4 => 0}
	 * 
	 * @param   string	
	 * @return array
	 * @access	public
	 */
	function parseUseItems($use_item)
	{
		$use_items = array();
		foreach(explode("|", $use_item) as $element) {
			$list=explode(":", $element);
			if (count($list) == 2) {
				$use_items[intval($list[0])] = intval($list[1]);
			}
		}
		return $use_items;
	}
	
	/**
	 * itemが存在するかどうか
	 * 
	 * @param  int item_id	
	 * @return bool
	 * @access	public
	 */
	function itemStorable($item_id)
	{
		$item =& $this->_usersView->getItemById($item_id);
		if ($item === false) {
			return false;
		}
		// || $item["type"] == "system")
		// if ($item["type"] == "label" || $item["define_flag"] != _ON) {
		if ($item["type"] == "label") {
			return false;
		}
		return true;
	}
}
?>
