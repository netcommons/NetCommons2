<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RSSデータ取得コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Rss_Components_View
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Rss_Components_View()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 *
	 * RSS用データを取得し配列として返す
	 *
     * @return array	RSS用ブロックデータ配列
	 * @access	public
	 */
	function &getRss()
	{
    	$params = array($this->_request->getParameter("block_id"));
    	$sql = "SELECT site_name, url, encoding, cache_time, visible_row, imagine, xml, update_time_sec ".
				"FROM {rss_block} ".
				"WHERE block_id = ?";
    	$rsses = $this->_db->execute($sql, $params);
		if ($rsses === false) {
        	return $rsses;
        }
		if (count($rsses) > 0) {      
			$rss = $rsses[0];
		}

		if (!empty($rss["xml"])) {
			$rss["xml"] = unserialize($rss["xml"]);
		}
		
		return $rss;
	}
	
	/**
	 *
	 * RSS用ブロックデータが存在するかチェック
	 *
     * @return boolean	true:存在する,false:存在しない
	 * @access	public
	 */
	function rssExists()
	{
		$params = array($this->_request->getParameter("block_id"));
    	
    	$sql = "SELECT block_id ".
				"FROM {rss_block} ".
				"WHERE block_id = ?";
		$result = $this->_db->execute($sql, $params, 1);
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		if (count($result) > 0) {      
			return true;
		}
		
		return false;		
	}
}
?>
