<?php
// $Id: Sqlutility.class.php,v 1.9 2008/04/14 09:21:25 Ryuji.M Exp $
// sqlutility.php - defines utility class for MySQL database
/**
 * DB操作で役に立つ関数郡コンポーネント
 *
 * @author  Ryuji.M
 **/
class Database_Sqlutility
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
	function Database_Sqlutility() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * モジュールDirからテーブルリスト取得
	 *
	 * @param   string	$dirname モジュールのディレクリ名称
	 * @return  array	テーブル名称の配列
	 * @access	public	
	 */
	function &getTableList($dirname, $prefix_flag = true)
	{
		$table_list = false;
		$configView =& $this->_container->getComponent("configView");
		$config_obj = $configView->getConfigByConfname(_SYS_CONF_MODID, "db_kind");	//conf_name=db_kind(mysql等がvalueに入る)
		if($config_obj) {
			$db_kind = $config_obj["conf_value"];
		} else {
			$db_kind = _DEFAULT_SQL_KIND;
		}
		$file_path = "/".$dirname."/sql/".$db_kind."/"._SYS_TABLE_INI;
		if (@file_exists(MODULE_DIR.$file_path) && filesize(MODULE_DIR.$file_path) != 0) {
			//モジュールに使用するテーブルあり
	        // SQLファイルの読み込み
	        $handle = fopen(MODULE_DIR.$file_path, 'r');
			$sql_query = fread($handle, filesize(MODULE_DIR.$file_path));
			fclose($handle);
			$sql_query = trim($sql_query);
			// SQLユーティリティクラスにて各クエリを配列に格納する
			$this->splitMySqlFile($pieces, $sql_query);
			
			//$created_tables = array();
			$table_list = array();
			foreach ($pieces as $piece) {
				// SQLユーティリティクラスにてテーブル名にプレフィックスをつける
				// 配列としてリターンされ、				
	            // 	[0] プレフィックスをつけたクエリ
	            // 	[4] プレフィックスをつけないテーブル名
				// が格納されている
				$prefixed_query = $this->prefixQuery($piece, $this->_db->getPrefix());
				if ( !$prefixed_query ) {
					//エラー
					$table_list = false;
					break;
				}
				
				//既にTABLEが存在しなければ、次へ
				//if(in_array($this->_db->getPrefix().$prefixed_query[4],$created_tables)) {
				//	continue;
				//}
				if($prefix_flag) {
					$table_list[] = $this->_db->getPrefix().$prefixed_query[4];
				} else {
					$table_list[] = $prefixed_query[4];
				}
			}
		}
		return $table_list;
	}
	
	/**
	* 各クエリを配列に格納
	* TODO:今後、自作、メソッドに入れ替え予定
 	* 
 	* @param   array    the splitted sql commands
 	* @param   string   the sql commands
 	* @return  boolean  always true
 	* @access  public
 	*/
	function splitMySqlFile(&$ret, $sql)
	{
		$sql               = trim($sql);
		$sql_len           = strlen($sql);
		$char              = '';
    	$string_start      = '';
    	$in_string         = false;

    	for ($i = 0; $i < $sql_len; ++$i) {
        	$char = $sql[$i];

           // We are in a string, check for not escaped end of
		   // strings except for backquotes that can't be escaped
           if ($in_string) {
           		for (;;) {
               		$i         = strpos($sql, $string_start, $i);
					// No end of string found -> add the current
					// substring to the returned array
                	if (!$i) {
						$ret[] = $sql;
                    	return true;
                	}
					// Backquotes or no backslashes before 
					// quotes: it's indeed the end of the 
					// string -> exit the loop
                	else if ($string_start == '`' || $sql[$i-1] != '\\') {
						$string_start      = '';
                   		$in_string         = false;
                    	break;
                	}
                	// one or more Backslashes before the presumed 
					// end of string...
                	else {
						// first checks for escaped backslashes
                    	$j                     = 2;
                    	$escaped_backslash     = false;
						while ($i-$j > 0 && $sql[$i-$j] == '\\') {
							$escaped_backslash = !$escaped_backslash;
                        	$j++;
                    	}
                    	// ... if escaped backslashes: it's really the 
						// end of the string -> exit the loop
                    	if ($escaped_backslash) {
							$string_start  = '';
                        	$in_string     = false;
							break;
                    	}
                    	// ... else loop
                    	else {
							$i++;
                    	}
                	} // end if...elseif...else
            	} // end for
        	} // end if (in string)
        	// We are not in a string, first check for delimiter...
        	else if ($char == ';') {
				// if delimiter found, add the parsed part to the returned array
            	$ret[]    = substr($sql, 0, $i);
            	$sql      = ltrim(substr($sql, min($i + 1, $sql_len)));
           		$sql_len  = strlen($sql);
            	if ($sql_len) {
					$i      = -1;
            	} else {
                	// The submited statement(s) end(s) here
                	return true;
				}
        	} // end else if (is delimiter)
        	// ... then check for start of a string,...
        	else if (($char == '"') || ($char == '\'') || ($char == '`')) {
				$in_string    = true;
				$string_start = $char;
        	} // end else if (is start of string)

        	// for start of a comment (and remove this comment if found)...
        	else if ($char == '#' || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
            	// starting position of the comment depends on the comment type
           		$start_of_comment = (($sql[$i] == '#') ? $i : $i-2);
            	// if no "\n" exits in the remaining string, checks for "\r"
            	// (Mac eol style)
           		$end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
                              ? strpos(' ' . $sql, "\012", $i+2)
                              : strpos(' ' . $sql, "\015", $i+2);
           		if (!$end_of_comment) {
                // no eol found after '#', add the parsed part to the returned
                // array and exit
					// RMV fix for comments at end of file
               		$last = trim(substr($sql, 0, $i-1));
					if (!empty($last)) {
						$ret[] = $last;
					}
               		return true;
				} else {
                	$sql     = substr($sql, 0, $start_of_comment) . ltrim(substr($sql, $end_of_comment));
                	$sql_len = strlen($sql);
                	$i--;
            	} // end if...else
        	} // end else if (is comment)
    	} // end for

    	// add any rest to the returned array
    	if (!empty($sql) && trim($sql) != '') {
			$ret[] = $sql;
    	}
    	return true;
	}

	/**
	 * add a prefix.'_' to all tablenames in a query
	 * TODO:今後、自作、メソッドに入れ替え予定
     * @param   string  $query  valid SQL query string
     * @param   string  $prefix prefix to add to all table names
	 * @return  mixed   FALSE on failure
	 */
	function prefixQuery($query, $prefix)
	{
		//"/^(INSERT INTO|CREATE TABLE|ALTER TABLE|UPDATE)(\s)+([`]?)([^`\s]+)\\3(\s)+/siU"
		$pattern = "/^(INSERT INTO|CREATE TABLE|ALTER TABLE|UPDATE)(\s)+([`]?)([^`\s]+)\\3(\s|\()+/siU";
		$pattern2 = "/^(DROP TABLE)(\s)+([`]?)([^`\s]+)\\3(\s)?$/siU";
		if (preg_match($pattern, $query, $matches) || preg_match($pattern2, $query, $matches)) {
			$replace = "\\1 ".$prefix."\\4\\5";
			//$replace = "\\1 ".$prefix."_\\4\\5";
			$matches[0] = preg_replace($pattern, $replace, $query);
			return $matches;
		}
		return false;
	}
}
?>
