<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RSSデータ登録コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Rss_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Rss_Components_Action() 
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
	}
	
	/**
	 * RSS用ブロックデータを登録する
	 *
	 * @param	array	$params	登録するRSS用ブロックデータ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function insert($params = array()) 
	{
		$params = $this->_serializeXml($params);	
		$result = $this->_db->insertExecute("rss_block", $params, true);
        if (!$result) {
			$this->_db->addError();
			return false;
		}
		return true;
	}
	
	/**
	 * RSS用ブロックデータを更新する
	 *
	 * @param	array	$params	変更するRSS用ブロックデータ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function update($params = array()) 
	{
		$key = "block_id";
    	$params = $this->_serializeXml($params);
    	$result = $this->_db->updateExecute("rss_block", $params, $key, true);
    	if($result === false) {
    		return false;
    	}
    	return true;
	}

	/**
	 * xml配列をserializeする
	 *
	 * @param	array	$params	RSS用ブロックデータ配列
     * @return boolean	xml配列をserializeしたRSS用ブロックデータ配列
	 * @access	private
	 */
	function _serializeXml($params = array()) 
	{
		if (is_array($params["xml"])) { 
			$params["xml"] = serialize($params["xml"]);
		}
		return $params;
	}
}
?>