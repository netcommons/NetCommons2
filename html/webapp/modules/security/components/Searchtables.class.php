<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DBテーブル名リスト取得コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Security_Components_Searchtables
{
	// 値をセットするため
	var $db = null;
	var $dbtables = null;

	/**
	 * コンストラクター
	 * @access	public
	 */
	function Security_Components_Searchtables()
	{
		$container =& DIContainerFactory::getContainer();
		$this->db =& $container->getComponent("DbObject");
	}

	/**
	 * DBテーブルリスト取得
	 * @access	public
	 */
	function SearchTables()
	{
        // 使用中のプレフィックス名を取得
        $used_prefix = $this->db->getPrefix();
		// プレフィックスがないテーブルの場合
        if ($used_prefix == "") {
        	$used_prefix = "NONE_PREFIX";
        }
		// DBテーブル名リストを取得
		$sql_cmd = "show table status;";
        $db_alltables = $this->db->execute($sql_cmd);
        if ($db_alltables == false || empty($db_alltables)) return false;

        $adodb = $this->db->getAdoDbObject();

        // 全プレフィックス名取得
        foreach($db_alltables as $db_table) {
			// "config" テーブルから，プレフィックス名を取得
        	if(substr($db_table["Name"], -6) === 'config') {
        		$metaColumns = $adodb->MetaColumns($db_table["Name"]);
				if(!isset($metaColumns["CONF_ID"]) || !isset($metaColumns["CONF_NAME"]) || !isset($metaColumns["CONF_VALUE"])) {
					continue;
				}
				$prefix = substr($db_table["Name"], 0, (strrpos($db_table["Name"], "config")));
				$prefixes[] = $prefix;		// プレフィックス名リスト
				if ($prefix == "") {
        			$prefix = "NONE_PREFIX";
        		}
        		// サイト名を取得
        		$sql_cmd = sprintf("select `conf_value` from %s where (`conf_name` = 'sitename')", $db_table["Name"]);
        		$sitename = $this->db->execute($sql_cmd);
		        if ($sitename == false || empty($sitename)) return false;
	       		$db_sitename[$prefix] = $sitename[0]['conf_value'];		// サイト名

	       		if (!strcmp($prefix, $used_prefix)) {
        			$db_use_prefix[$prefix] = 1;				// 使用中のプレフィックスの場合
					$used_sitename = $db_sitename[$prefix];		// 使用中のサイト名
        		} else {
        			$db_use_prefix[$prefix] = 0;				// 使用していないプレフィックスの場合
        		}
       			$db_tables[$prefix] = null;
				$db_tables_num[$prefix] = 0;
        		$db_update_time[$prefix] = null;
        	}
		}
		$prefix_count = count($prefixes);
		rsort($prefixes);

        // プレフィックスごとにテーブル名を振り分け
       	foreach($db_alltables as $db_table) {
       		$db_table_found = FALSE;
       		for ($idx=0; $idx<$prefix_count; $idx++) {
       			if (empty($prefixes[$idx])) continue;

       			if (!strncmp($prefixes[$idx], $db_table["Name"], strlen($prefixes[$idx]))) {
	       			$db_tables[$prefixes[$idx]][] = $db_table;
					$db_table_found = TRUE;
					break;
       			}
			}
			// プレフィックスがないテーブルの場合
			if ($db_table_found == FALSE) {
				$db_tables["NONE_PREFIX"][] = $db_table;
			}
       	}

       	// プレフィックスごとのテーブル名リスト作成
		sort($prefixes);
       	foreach($prefixes as $prefix) {
			$same_dbtable = 0;
       		// プレフィックスがないテーブルの場合
       		if ($prefix == "") {
		        // 最終更新時間
	   			usort($db_tables["NONE_PREFIX"], create_function('$a,$b', 'return $a["Update_time"] < $b["Update_time"] ? 1 : -1 ;'));
		       	$db_update_time["NONE_PREFIX"] = $db_tables["NONE_PREFIX"][0]["Update_time"];

			    // テーブル数
		       	$db_tables_num["NONE_PREFIX"] = count($db_tables["NONE_PREFIX"]);

	       		if (!strcmp($used_sitename, $db_sitename["NONE_PREFIX"])) {
					$same_dbtable = 1;
				}
      			$this->dbtables[] = array(
		       		'useprefix' => $db_use_prefix["NONE_PREFIX"],
					'prefix' => "(none)",
        			'count' => $db_tables_num["NONE_PREFIX"],
					'updated' => $db_update_time["NONE_PREFIX"],
        			'tables' => $db_tables["NONE_PREFIX"],
        			'usedtable' => $same_dbtable
      			);
       		} else {
				// 最終更新時間
		       	usort($db_tables[$prefix], create_function('$a,$b', 'return $a["Update_time"] < $b["Update_time"] ? 1 : -1 ;'));
		       	$db_update_time[$prefix] = $db_tables[$prefix][0]["Update_time"];

		       	// テーブル数
		       	$db_tables_num[$prefix] = count($db_tables[$prefix]);

				if (!strcmp($used_sitename, $db_sitename[$prefix])) {
					$same_dbtable = 1;
				}
		       	$this->dbtables[] = array(
			    	'useprefix' => $db_use_prefix[$prefix],
			    	'prefix' => $prefix,
	        		'count' => $db_tables_num[$prefix],
					'updated' => $db_update_time[$prefix],
	        		'tables' => $db_tables[$prefix],
        			'usedtable' => $same_dbtable
				);
			}
        }

        return $this->dbtables;
	}
}
?>
