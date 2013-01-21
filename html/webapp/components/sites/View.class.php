<?php
/**
 * Sitesクラス
 *
 * @package     [[package名]]
 * @author      Ryuji.M
 * @copyright   copyright (c) 2006 NetCommons.org
 * @license     [[license]]
 * @access      public
 */
class Sites_View {
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
	function Sites_View() {
		$container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
		$this->_db =& $container->getComponent("DbObject");
	}
	
	/**
	 * site_idからサイト情報取得
	 * @return array
	 * @access	public
	 */
	function &getSitesById($id)
	{
		$params = array( 
			"site_id" => $id
		);
		
		$result = $this->_db->execute("SELECT * FROM {sites} WHERE site_id=?",$params);
		if($result === false) {
			//エラー
			$this->_db->addError();
			return $result;
		}
		if($result[0]['url'] === "BASE_URL") {
			$result[0]['url'] = BASE_URL;
		}
		return $result[0];
	}
	
	/**
	 * urldからサイト情報取得
	 * @return array
	 * @access	public
	 */
	function &getSitesByUrl($url)
	{
		if($url === BASE_URL) {
			//$url = "BASE_URL";
			$params = array( 
				"self_flag" => _ON
			);
			
			$result = $this->_db->execute("SELECT * FROM {sites} WHERE self_flag=?",$params);
		} else {
			$params = array( 
				"url" => $url
			);
			
			$result = $this->_db->execute("SELECT * FROM {sites} WHERE url=?",$params);
		}
		if($result === false) {
			//エラー
			$this->_db->addError();
			return $result;
		}
		if(isset($result[0])) {
			return $result[0];
		}
		$result = array();
		return $result;
	}
	
	
	/**
	 * 自サイト情報取得
	 * @return array
	 * @access	public
	 */
	function &getSelfSite()
	{
		$params = array( 
			"self_flag" => _ON
		);
		
		$result = $this->_db->execute("SELECT * FROM {sites} WHERE self_flag=?",$params);
		if($result === false) {
			//エラー
			$this->_db->addError();
			return $result;
		}
		if($result[0]['url'] === "BASE_URL") {
			$result[0]['url'] = BASE_URL;
		}
		return $result[0];
	}
	
	
	/**
	 * sitesの一覧を取得する
	 * 
	 * @param   array   $where_params  Whereパラメータ引数
	 * @param   array   $order_params  Orderパラメータ引数
	 * @param   int     $limit
	 * @param   int     $start
	 * @return array サイトリスト
	 * @access	public
	 */
	function &getSites($where_params=null, $order_params = array("{sites}.self_flag"=>"DESC", "{sites}.url"=>"ASC"), $limit=null, $offset=null)
	{
		$result = $this->_db->selectExecute("sites", $where_params, $order_params, $limit, $offset, array($this, "_getSites"));
		if($result === false) {
			$this->_db->addError();
			return $result;
		}
		return $result;
	}
	
	
	/**
	 * fetch時コールバックメソッド
	 * @param result adodb object
	 * @return array configs
	 * @access	private
	 */
	function _getSites(&$result)
	{
		$data = array();
		while ($row = $result->fetchRow()) {
			if($row["self_flag"] == _ON) {
				$row["url"] = BASE_URL;
			}
			$data[] = $row;
		}
		return $data;
	}
}
?>
