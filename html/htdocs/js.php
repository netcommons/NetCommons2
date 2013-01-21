<?php
/**
 * NetCommons2.0 JSファイル読み込み
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
// BASE_DIR
if(!defined("BASE_DIR")) {
	exit;
}
require_once BASE_DIR.'/maple/nccore/db/DbObjectAdodb.class.php';
$db =& new DbObjectAdodb;
if(defined(DATABASE_PCONNECT) && DATABASE_PCONNECT == _ON) {
	$db->setOption('persistent', DATABASE_PCONNECT);
}
$db->setDsn(DATABASE_DSN);
$result = $db->connect();
if(!$result) {
	exit;
}
//テーブルPrefix
$db->setPrefix(DATABASE_PREFIX);

$dir_name = isset($_GET['dir_name']) ? $_GET['dir_name'] : '';
$add_block_flag = isset($_GET['add_block_flag']) ? $_GET['add_block_flag'] : _OFF;
$system_flag = isset($_GET['system_flag']) ? $_GET['system_flag'] : _OFF;
$smapho_flag = isset($_GET['smapho_flag']) ? $_GET['smapho_flag'] : _OFF;

if($dir_name == "install") {
	// インストーラならば、includeしない
	exit;
}

$dir_name_arr = array();
if($dir_name != null && $dir_name != "") {
	$dir_name_arr = explode("|", addslashes($dir_name));
}

$where_str = "(";
if($add_block_flag != _ON && $smapho_flag != _ON) {
	$dir_name_arr[] = "pm";
	if($system_flag == _ON) {
		// 管理系モジュール
		$where_str .= "common_admin_flag = " . _ON ." OR ";
	} else {
		// 一般系モジュール
		$where_str .= "common_general_flag = " . _ON ." OR ";
	}
}
if(count($dir_name_arr) > 0) {
	$where_str .= "dir_name IN ('". implode("','", $dir_name_arr). "') ";
} else {
	$where_str .= "1 = 0 ";
}
$where_str .= ")";

$where_params = array(
	$where_str => null
);
$order_params = array("read_order" => "ASC");
$result = $db->selectExecute("javascript_files", $where_params, $order_params, null, null, "_fetchcallbackJs");
if($result === false) {
	exit;
}

list($rec_sets, $max_update_time) = $result;
if(is_array($rec_sets)) {
	$timestamp = mktime(substr($max_update_time, 8,2), substr($max_update_time, 10,2), substr($max_update_time, 12,2), substr($max_update_time, 4,2), substr($max_update_time, 6,2), substr($max_update_time, 0,4));
	if((!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
		($timestamp==_str2Time($_SERVER['HTTP_IF_MODIFIED_SINCE'])))
        // || (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && ($etag==$_SERVER['HTTP_IF_NONE_MATCH']))
        ){
        	header('HTTP/1.1 304 Not Modified');
			exit;
    }

	$conf_where_params = array(
		"conf_modid" => _SYS_CONF_MODID,
		"conf_name" => "script_compress_gzip"
	);
	$config_script_compress_gzip = $db->selectExecute("config", $conf_where_params);
	if($config_script_compress_gzip === false) exit;
	if(isset($config_script_compress_gzip[0]) && isset($config_script_compress_gzip[0]['conf_value'])) {
		if($config_script_compress_gzip[0]['conf_value'] == _ON) {
			$gzip_flag = true;
		} else {
			$gzip_flag = false;
		}
	} else {
		// default true
		$gzip_flag = true;
	}
	if(extension_loaded('zlib') && !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && preg_match('/gzip/i', $_SERVER['HTTP_ACCEPT_ENCODING']) && $gzip_flag) {
		ob_start ("ob_gzhandler");
		header("Content-Encoding: gzip");
	}

	// 有効期限セット（100日）
	$offset = 100 * 24 * 60 * 60;

	header("Content-type: text/javascript; charset=UTF-8");
	header("Cache-Control: max-age=".$offset.",public");
	//header("Cache-Control: cache");
	header("Vary: Accept-Encoding");
	/*header("Cache-Control: must-revalidate");*/

	$ExpStr = "Expires: " .
	gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
	header($ExpStr);

	// 最終更新日付セット
	$ExpStr = "Last-Modified: " .
	gmdate("D, d M Y H:i:s", $timestamp) . " GMT";
	header($ExpStr);

	foreach($rec_sets as $data) {
		print $data."\n";
	}

	exit;
} else {
	header('HTTP/1.1 204 No Content');
}
exit;

/**
 * fetch時コールバックメソッド
 * @param result adodb object
 * @access	private
 */
function _fetchcallbackJs($result) {
	$ret = array();
	$max_update_time = 0;
	while ($row = $result->fetchRow()) {
		// 最終更新日付をセット
		if($row['update_time'] > $max_update_time) {
			$max_update_time = $row['update_time'];
		}
		$ret[] =  $row['data'];
	}
	return array($ret, $max_update_time);
}

function _str2Time( $str ) {
	$str = preg_replace( '/;.*$/', '', $str );
	if ( strpos( $str, ',' ) === false ) $str .= ' GMT';
	return strtotime( $str );
}
?>