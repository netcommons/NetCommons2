<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * お知らせデータ取得コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Announcement_Components_View
{
	/**
	 * @var DIコンテナーを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	/**
	 * @var を保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Announcement_Components_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function getBlocksForMobile($block_id_arr) 
	{
		$params = array("block_id IN (".implode(",",$block_id_arr).")" => null);
		$order_params = array("insert_time" => "DESC");
		return $this->_db->selectExecute("announcement", $params, $order_params, null, null, array($this, "_getBlocksForMobile"));
	}

	/**
	 * 携帯用ブロックデータを取得
	 *
	 * @access	public
	 */
	function &_getBlocksForMobile(&$recordSet) 
	{
		$commonMain =& $this->_container->getComponent("commonMain");
		$convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
			
		$result = array();
		while ($row = $recordSet->fetchRow()) {
	   		$content = $convertHtml->convertHtmlToText($row["content"]);
	    	$content = trim(preg_replace("/\\\n/", " ", $content));
	    	$row["title"] = mb_substr($content, 0, ANNOUNCEMENT_LENGTH + 1, INTERNAL_CODE);
	    	$result[] = $row;
		}
		return $result;
	}
	
}
?>
