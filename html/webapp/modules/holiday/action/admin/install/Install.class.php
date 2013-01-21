<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 祝日設定インストール時アクション
 * 祝日を登録する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Holiday_Action_Admin_Install extends Action
{
	// パラメータを受け取るため
	var $module_id = null;
	
	
	// コンポーネントを受け取るため
	var $db = null;
	var $databaseSqlutility = null;
	
	function execute()
	{
		$file_path = "/holiday/sql/".HOLIDAY_INSERT_FILE_NAME;
		if (!@file_exists(MODULE_DIR.$file_path)) {
    		return "false";	
    	}
    	
    	// SQLファイルの読み込み
 	    $handle = fopen(MODULE_DIR.$file_path, 'r');
		$sql_query = fread($handle, filesize(MODULE_DIR.$file_path));
		fclose($handle);
		$sql_query = trim($sql_query);
		// SQLユーティリティクラスにて各クエリを配列に格納する
		$this->databaseSqlutility->splitMySqlFile($pieces, $sql_query);
		foreach ($pieces as $piece) {
			// SQLユーティリティクラスにてテーブル名にプレフィックスをつける
			// 配列としてリターンされ、				
            // 	[0] プレフィックスをつけたクエリ
            // 	[4] プレフィックスをつけないテーブル名
			// が格納されている
			$prefixed_query = $this->databaseSqlutility->prefixQuery($piece, $this->db->getPrefix());
			if ( !$prefixed_query ) {
				return "false";	
				//continue;
			}
			// 実行
			if ( !$this->db->execute($prefixed_query[0]) ) {
				return "false";	
				//continue;
			}
		}
		return "true";
	}
}
?>