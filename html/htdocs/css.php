<?php
/**
 * NetCommons2.0 CSSファイル読み込み
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
//header("Content-type: text/css; charset=UTF-8");
//header('HTTP/1.1 204 No Content');
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

//
// default type=_CSS_TYPE_BLOCK_CUSTOM
// 
$dir_name_arr = isset($_GET['dir_name']) ? explode("|", addslashes($_GET['dir_name'])) : array();
$page_theme = isset($_GET['page_theme']) ? addslashes($_GET['page_theme']) : "";

$where_str = "(";
$where_str .= "(dir_name IN ('". implode("','", $dir_name_arr). "') ";
$system_flag = isset($_GET['system_flag']) ? $_GET['system_flag'] : _OFF;
$header = isset($_GET['header']) ? $_GET['header'] : _ON;

if($header == _ON) {
	if($system_flag) {
		$where_str .= " OR common_admin_flag =" . _ON; 
	} else {
		$where_str .= " OR common_general_flag =" . _ON;
	}
}
$where_str .= ") ";
if(isset($_GET['block_id_str'])) {
	$block_id_arr = explode("|", addslashes($_GET['block_id_str']));
	$block_id_arr[] = "0";
	$where_str .= "AND block_id IN ('". implode("','", $block_id_arr). "') ";
} else {
	$where_str .= "AND block_id = 0 ";
}
$where_str .= " AND type!="._CSS_TYPE_PAGE_CUSTOM.") OR (dir_name = '".$page_theme."' AND type="._CSS_TYPE_PAGE_CUSTOM.")";

//$where_params = array(
//	$where_str => null,
//	"type" => $type,
//	$where_block_str => null
//);
$where_params = array(
	$where_str => null
);
 
$order_params = array("type" => "ASC");
//$order_params = array();
$result = $db->selectExecute("css_files", $where_params, $order_params, null, null, "_fetchcallbackCss");
if($result === false) {
	exit;
}
list($rec_sets, $max_update_time) = $result;
if(is_array($rec_sets) && isset($rec_sets[0])) {
	$timestamp = mktime(substr($max_update_time, 8,2), substr($max_update_time, 10,2), substr($max_update_time, 12,2), substr($max_update_time, 4,2), substr($max_update_time, 6,2), substr($max_update_time, 0,4));
	if((!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
		($timestamp==_str2Time($_SERVER['HTTP_IF_MODIFIED_SINCE'])))
	    ){
	    	header('HTTP/1.1 304 Not Modified'); 
			exit;
	}
	header("Content-type: text/css; charset=UTF-8");
	// 有効期限セット（100日）
	$offset = 100 * 24 * 60 * 60;
	//ob_start ("ob_gzhandler");
	//header("Content-Encoding: gzip");
	/*header("Cache-Control: cache");*/
	/*header("Cache-Control: must-revalidate");*/
	//header("Cache-Control: no-cache");
	header("Cache-Control: max-age=".$offset.",public");
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
function _fetchcallbackCss($result) {
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