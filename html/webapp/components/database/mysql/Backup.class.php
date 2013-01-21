<?php
// $Id: Backup.class.php,v 1.3 2008/07/11 09:27:45 Ryuji.M Exp $
// sqlutility.php - defines utility class for MySQL database
/**
 * DB操作のバックアップで役に立つ関数郡コンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 **/
class Database_Backup
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	/**
	 * @var containerオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Database_Backup() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * テーブル名から、バックアップファイルのDumpファイルを作成する
	 *
	 * @param   string	 $table_name
	 * @param   boolean $droptable_flag
	 * @param   boolean	 $createtable_flag
	 * @param   boolean	 $insertrow_flag
	 * @return  string	 Dump文字列
	 * @access	public	
	 */
	function getBackupSqlDump($table_name, $droptable_flag = true, $createtable_flag = true, $insertrow_flag = true) {
		$sql_dump = "";
		if($droptable_flag) {
			$sql_dump .= $this->getDropTableSqlDump($table_name);
		}
		if($createtable_flag) {
			$sql_dump .= $this->getCreateTableSqlDump($table_name);
		}
		if($insertrow_flag) {
			$sql_dump .= $this->getInsertRowSqlDump($table_name);
		}
		return $sql_dump;
	}
	
	/**
	 * テーブル名から、DROP TABLEのDumpファイルを作成する
	 *
	 * @param   string	 $table_name
	 * @return  string	 Dump文字列
	 * @access	public	
	 */
	function getDropTableSqlDump($table_name) {
		$sql_dump = "DROP TABLE IF EXISTS `$table_name`;\n";
		return $sql_dump;
	}
	
	
	/**
	 * テーブル名から、CREATE TABLEのDumpファイルを作成する
	 * MYSQL以外でも動作するように汎用的に作成するつもりだったが、現状、MYSQL専用
	 * @param   string	 $table_name
	 * @return  string	 Dump文字列
	 * @access	public	
	 */
	function getCreateTableSqlDump($table_name) {
		$sql_dump = "";
		//$adodb = $this->_db->getAdoDbObject();
		//$table_columns = $adodb->MetaColumns($table_name, false);
		$sql_describe = "DESCRIBE `$table_name`;";
		$table_columns = $this->_db->execute($sql_describe);
		if(is_array($table_columns) && count($table_columns) > 0) {
			$sql_dump .= "CREATE TABLE IF NOT EXISTS `$table_name` (";
			$column_string = "";
			foreach($table_columns as $table_column) {
				//  `block_id` int(11) unsigned NOT NULL default '0',
				$column_name = $table_column['Field'];
				$column_type = $table_column['Type'];
				$column_not_null = ($table_column['Null'] == "YES") ? "" : " NOT NULL";
				if ($table_column['Default'] == "CURRENT_TIMESTAMP") {
					$column_default = "";
				} elseif ($table_column['Default'] === NULL || $table_column['Default'] == "NULL") {
					if ($table_column['Extra'] == 'auto_increment') { 
						$column_default = "";
					} elseif (strpos(strtolower($column_type), "text") !== false || 
							strpos(strtolower($column_type), "blob") !== false) {
						$column_default = "";
						$column_not_null = "";
					} elseif ($table_column['Null'] == "YES") {
						$column_default = " default NULL";
					} else {
						$column_default = "";
					}
				} else {
					if (strpos(strtolower($column_type), "text") !== false || 
							strpos(strtolower($column_type), "blob") !== false) {
						$column_not_null = "";
						$column_default = "";
					} elseif (strpos(strtolower($column_type), "int") !== false || 
							strpos(strtolower($column_type), "float") !== false || 
							strpos(strtolower($column_type), "double") !== false) {
						$column_default = sprintf(" default %s", intval($table_column['Default']));
					} else {
						$column_default = sprintf(" default '%s'", $table_column['Default']);
					}
				}

				$column_auto_increment = ($table_column['Extra'] == NULL) ? "" : " ".$table_column['Extra'];
				
				$column_string .= ($column_string != "" ? "," : ""). "\n `".$column_name."` ".$column_type.$column_not_null.$column_auto_increment.$column_default;
				
				
				/* MetaColumnsの場合だが、うまく動作しないためコメント
				$column_name = $table_column->name;
				$column_type = $table_column->type;
				if($table_column->max_length === -1) {
					$column_type .= "(".$table_column->max_length.")";
				}
				if($table_column->unsigned) {
					$column_unsigned = "unsigned";
				} else {
					$column_unsigned = "";
				}
				if($table_column->not_null) {
					$column_not_null = "NOT NULL";
				} else {
					$column_not_null = "";
				}
				if($table_column->has_default !== false) {
					if(gettype($table_column->has_default) == "string" ) {
						$column_has_default = "default '".$table_column->has_default."',";
					} else {
						$column_has_default = "default ".$table_column->has_default.",";
					}
				} else {
					if($table_column->not_null) {
							
					} else {
						$column_has_default = "";
					}
				}
				*/
			}
			$sql_show_index = "SHOW INDEX FROM `$table_name`;";
			$index_columns = $this->_db->execute($sql_show_index);
			foreach($index_columns as $index_column) {
				$key_name = $index_column['Key_name'];
				$sub_part = (isset($index_column['Sub_part'])) ? $index_column['Sub_part'] : '';
//				$comment  = (isset($index_column['Comment'])) ? $index_column['Comment'] : '';
				$non_unique = $index_column['Non_unique'];
				
				if ($key_name != 'PRIMARY' && $index_column['Non_unique'] == 0) {
		            $key_name = "UNIQUE|$key_name";
		        }
		        if ($index_column['Index_type'] == 'FULLTEXT') {
		            $key_name = "FULLTEXT|$key_name";
		        }
				if (!isset($index[$key_name])) {
		            $index[$key_name] = array();
		        }
		        
		        if ($sub_part > 1) {
		            $index[$key_name][] = "`".$index_column['Column_name']."` (" . $sub_part . ")"; 
		        } else {
		            $index[$key_name][] = "`".$index_column['Column_name']."`";
		        }
			}
			$schema_index = "";
			$count_index = 0;
		    while (list($key_name, $columns) = @each($index)) {
		        $schema_index .= ", \n";
		        if ($key_name == 'PRIMARY') {
		            $schema_index .= '  PRIMARY KEY (';
		        } else if (substr($key_name, 0, 6) == 'UNIQUE') {
		            $schema_index .= '  UNIQUE KEY ' ."`".substr($key_name, 7)."`" . ' (';
		        } else if (substr($key_name, 0, 8) == 'FULLTEXT') {
		            $schema_index .= '  FULLTEXT KEY ' ."`". substr($key_name, 9) . "`" . ' (';
		        } else {
		            $schema_index .= '  KEY ' . $key_name . ' (';
		        }
		        $schema_index     .= implode($columns, ', ') . ')';
		
		        $count_index++;
		    }
		
		    if($schema_index != ''){
		    	$column_string .= $schema_index;
		    }
		    
		    $column_string = substr($column_string, 2);
			
			$engine_str = "";
			//if(strstr($this->_db->getDsn(), "mysql")) {
				//MYSQLならば、	ストレージエンジンをつける
				$engine_str = $this->_db->execute("SHOW TABLE STATUS", array(), null, null, false, array($this, '_fetchcallbackCreateTable'), array($table_name));
			//}
			$sql_dump .= $column_string."\n) $engine_str;\n";
		}
		return $sql_dump;
	}
	
	/**
	 * fetch時コールバックメソッド(CreateTable)
	 * @param result adodb object
	 * @access	private
	 */
	function _fetchcallbackCreateTable($result, $params) {
		$table_name = $params[0];
		$ret = "";
		while ($row = $result->fetchRow()) {
			if ($row[0] == $table_name) {
				return "ENGINE=".$row[1];
			}
		}
		return $ret;
	}
	
	/**
	 * テーブル名から、INSERTのDumpファイルを作成する
	 * @param   string	 $table_name
	 * @return  string	 Dump文字列
	 * @access	public	
	 */
	function getInsertRowSqlDump($table_name) {
		$sql_dump = "";
		$adodb = $this->_db->getAdoDbObject();
		// prefixを取り除く
		$prefix_table_name = $table_name;
		$table_name = preg_replace("/^".preg_quote($this->_db->getPrefix(), "/")."/", "", $table_name);
		$arrFields = $this->_db->selectExecute($table_name);
		
		if($arrFields !== false && is_array($arrFields)) {
			foreach($arrFields as $arrField) {
				$sql_dump .= $adodb->GetInsertSQL($prefix_table_name, $arrField, false, ADODB_FORCE_VALUE). ";\n";
			}
		}
		return $sql_dump;
	}
}
?>
