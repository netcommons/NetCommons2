<?php
/**
 * Sitesクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Sites_Action {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	var $_session = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Sites_Action() {
		$container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
		$this->_db =& $container->getComponent("DbObject");
		$this->_session =& $container->getComponent("Session");
	}
	
	
	/**
	 * インストール時、site_id作成処理
	 * @access	public
	 */
	function insertSite($url="BASE_URL", $self_flag = _ON)
	{
		$sessionID = $this->_session->getID();
		// $new_site_id = crc32($sessionID).crc32(microtime());
		
		while(1) {
			$new_site_id = sha1(uniqid($sessionID.microtime(), true));
			// Hash値で同じものがないか念のためチェック
			$result = $this->_db->selectExecute("sites", array("site_id" => $new_site_id));
			if ($result === false) {
		       	return false;
			}
			if(!isset($result[0]['site_id'])) {
				break;
			}
		}
		
		$params = array(
						"site_id" => $new_site_id,
						"url" => $url,
						"self_flag" => $self_flag,
						"commons_flag" => _ON,
						"certify_flag" => _ON
					);
		
		$result = $this->_db->insertExecute("sites", $params, true);
		if($result === false) {
			return false;
		}
		return $new_site_id;
	}
}
?>
