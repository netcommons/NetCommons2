<?php
/**
 * カウンタテーブル表示用クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Counter_Components_View {
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
	function Counter_Components_View() {
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * カウンタが存在するか判断する
	 *
     * @return boolean	true:存在する、false:存在しない
	 * @access	public
	 */
	function counterExists()
	{
		$params = array(
			$this->_request->getParameter("block_id")
		);
		$sql = "SELECT block_id ".
				"FROM {counter} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return $blockIDs;
		}

		if (count($blockIDs) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * ブロックIDからカウンタデータ取得
	 *
	 * @access	public
	 */
	function getCounter() {
		$params = array(
			$this->_request->getParameter("block_id")
		);
		$result = $this->_db->execute("SELECT " .
									" block_id," .
									" counter_digit," .
									" counter_num," .
									" show_type, " .
									" show_char_before," .
									" show_char_after," .
									" comment " .
									" FROM {counter} " .
									" WHERE block_id=?" ,$params);
		if(!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * カウンタ画像データ配列を取得する
	 *
	 * @param	array	$counter	カウンタデータ配列
     * @return	array	カウンタ画像データ配列
	 * @access	public
	 */
	function getImgSrcs($counter)
	{
    	$imgPath = get_image_url(). "/images/counter/common/". $counter["show_type"]. "/";
    	$strNum = sprintf("%0" . $counter["counter_digit"] ."d", $counter["counter_num"]);

    	$imgSrcs = array();
    	for ( $i=0; $i < strlen($strNum); $i++ ){
    		$n = substr($strNum, $i, 1);
			$imgSrcs[] = $imgPath. $n. ".gif";;
    	}

    	return $imgSrcs;
	}
}
?>