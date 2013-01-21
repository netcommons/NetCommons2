<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [iframe表示用クラス]
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Iframe_Components_View
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
	function Iframe_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * ブロックIDからiframeデータ取得
	 * @param int block_id
	 * @access	public
	 */
	function &getIframeById($id) {
		$params = array(
			"block_id" => $id
		);
		
		$result = $this->_db->execute("SELECT * " . 
									" FROM {iframe} " .
									" WHERE {iframe}.block_id=?" ,$params);
		
		if(!$result) {
			return $result;
		}
		return $result[0];
	}
}
?>
