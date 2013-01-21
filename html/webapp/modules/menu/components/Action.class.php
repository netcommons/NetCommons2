<?php
/**
 * メニューテーブル登録用クラス
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Menu_Components_Action {
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
	function Menu_Components_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * MenuDetail Insert
	 * @param array (block_id,page_id,visibility_flag)
	 * @return boolean true or false
	 * @access	public
	 */
	function insMenuDetail($params)
	{
		$result = $this->_db->insertExecute("menu_detail", $params, true);
        if ($result === false) {
			return false;
		}
		return $result;
	}
	
	
	/**
	 * MenuDetail Update
	 * @param array (block_id,page_id,theme_name,temp_name,visibility_flag)
	 * @return boolean true or false
	 * @access	public
	 */
	function updMenuDetail($params)
	{
		$set_params = array("visibility_flag" => $params['visibility_flag']);
		$where_params = array(
			"block_id" =>$params['block_id'],
			"page_id" =>$params['page_id']
		);
		$result = $this->_db->updateExecute("menu_detail", $set_params, $where_params, true);
        if ($result === false) {
			return false;
		}
		return true;
	}
	
	/**
	 * block_idによるMenuDetail削除処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function delMenuDetailById($block_id,$page_id)
	{
		$params = array( 
			"block_id" => $block_id,
			"page_id" => $page_id
		);
		
		$result = $this->_db->execute("DELETE FROM {menu_detail} WHERE block_id=? AND page_id=? ", $params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return false;
		}
		
		return true;
	}
	
	/**
	 * block_idによるMenuDetail削除処理
	 *　
	 * @return boolean true or false
	 * @access	public
	 */
	function delMenuDetailByPageId($page_id)
	{
		$params = array( 
			"page_id" => $page_id
		);
		
		$result = $this->_db->execute("DELETE FROM {menu_detail} WHERE page_id=?" .
										" ",$params);
		if ($result === false) {
	       	$this->_db->addError();
	       	return false;
		}
		
		return true;
	}
	/**
	 * block_idによるMenuDetail削除処理
	 *　
	 * @return boolean true or false
	 * @access  public
	 */
	function delMobileMenuDetailByPageId($page_id)
	{
		$params = array(
			"page_id" => $page_id
		);

		$result = $this->_db->execute("DELETE FROM {mobile_menu_detail} WHERE page_id=? " .
                                        " ",$params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}
}
?>
