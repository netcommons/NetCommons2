<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 言語テーブル表示用クラス
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Languages_View 
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
	function Languages_View() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * languageリストを取得する
	 * 
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @param   array   $func          関数
	 * @param   array   $func_params   Funcパラメータ引数
	 * @return array
	 * @access	public
	 */
	function &getLanguages($where_params=null, $order_params=null, $func=null, $func_param=null)
	{
		if (!isset($order_params)) {
        	$order_params = array("{language}.display_sequence"=>"ASC");	
        }

		$db_params = array();
		$sql = $this->_db->getSelectSQL("language", $db_params, $where_params, $order_params);
		$result = $this->_db->execute($sql, $db_params, null, null, true, $func, $func_param);
		if (!$result) {
	       	$this->_db->addError();
	       	return $result;
		}
		return $result;
	}

	/**
	 * languageリストを取得する
	 * 
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @return array
	 * @access	public
	 */
	function &getLanguagesList($where_params=null, $order_params=null)
	{
		$func = array($this,"_languagesList");
		$result = $this->getLanguages($where_params, $order_params, $func);
		return $result;
	}

	/**
	 * countryリストを取得する
	 * 
	 * @return array
	 * @access	public
	 */
	function &_languagesList(&$result)
	{
		$data = array();
		while ($obj = $result->fetchRow()) {
			if (defined($obj["display_name"])) {
				$name = constant($obj["display_name"]);
			} else {
				$name = ucfirst($obj["lang_dirname"]);
			}
			$data[$obj["lang_dirname"]] = $name;
		}
		return $data;
	}
}
?>
