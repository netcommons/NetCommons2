<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 新規作成画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_View_Edit_Create extends Action
{
    // リクエストパラメータを受け取るため
    var $room_id = null;

    // バリデートによりセット
	var $mdb_obj = null;

    // 使用コンポーネントを受け取るため
    var $db = null;

    // 値をセットするため
	var $oldMultidatabases = array();

    /**
     * execute処理
     *
     * @access  public
     */
    function execute()
    {
    	$count = $this->db->countExecute("multidatabase", array("room_id"=>$this->room_id));
    	$mdb_name = MDB_NEW_TITLE.($count+1);
    	$this->mdb_obj['multidatabase_name'] = $mdb_name;

    	//過去の汎用DB
		$params = array($this->room_id);
		$sql = "SELECT multidatabase_id, multidatabase_name ".
				"FROM {multidatabase} ".
				"WHERE room_id = ? ".
				"ORDER BY multidatabase_id DESC";

		$this->oldMultidatabases = $this->db->execute($sql, $params);
		if ($this->oldMultidatabases === false) {
			$this->db->addError();
		}

    	return 'success';
    }
}
?>