<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iframeテーブル登録用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Iframe_Components_Action
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
	function Iframe_Components_Action() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * iframeモジュールInsert
	 * @param array(block_id-url-frame_width-frame_height-scrollbar_show-scrollframe_show)
	 * @return boolean true or false
	 * @access	public
	 */
	function insIframe($params=array())
	{
		$result = $this->_db->insertExecute("iframe", $params, true);
        if ($result === false) {
			return false;
		}
		return $result;
	}
	
	/**
	 * iframeモジュールUpdate
	 * @param array(block_id-url-frame_width-frame_height-scrollbar_show-scrollframe_show)
	 * @return boolean true or false
	 * @access	public
	 */
	function updIframe($params=array())
	{
		$result = $this->_db->updateExecute("iframe", $params, "block_id", true);
        if ($result === false) {
			return false;
		}
		return true;
	}
}
?>
