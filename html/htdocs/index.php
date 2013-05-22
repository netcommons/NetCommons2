<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * NetCommons2.0
 *
 * Use Maple - PHP Web Application Framework
 * PHP versions 4 and 5
 *
 * @package     NetCommons.filter
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
if (version_compare(phpversion(), '5.3.0', '>=')) {
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
} else {
	error_reporting(E_ALL);
}

/**
 * パス変換処理
 *　 Windows環境で動作させるため
 * @param string $path
 * @return array
 * @access	public
 */
function transPathSeparator($path) {
	if ( DIRECTORY_SEPARATOR != '/' ) {
 		// IIS6 doubles the \ chars
		$path = str_replace( strpos( $path, '\\\\', 2 ) ? '\\\\' : DIRECTORY_SEPARATOR, '/', $path);
	}
	return $path;
}

/**
 * NetCommonsのindex.phpの場所
 */
define('START_INDEX_DIR', transPathSeparator(dirname(__FILE__)));

/**
 * NetCommonsのBaseディレクトリの設定
 * 他のソースを読み込む場合、コメントをはずし、手動で変更
 */
//define('BASE_DIR', '');

/**
 * Debugフィルターを発動させるかどうかの設定
 */
//define('DEBUG_MODE', 0);	//TODO:現状1に設定するとエラーとなる

/**
 * NetCommonsの設定ファイルの読込み
 */
define('INSTALLINC_PATH', dirname(START_INDEX_DIR) . "/webapp/config/install.inc.php");
require_once INSTALLINC_PATH;
//if(!is_writeable(INSTALLINC_PATH) && basename(BASE_URL) != basename(START_INDEX_DIR)) {
//	header('HTTP/1.1 204 No Content');
//	exit;
//}

/**
 * フレームワーク起動
 */
if(isset($_GET['action']) && $_GET['action'] == 'common_download_css') {
	require_once HTDOCS_DIR . "/css.php";
} else if(isset($_GET['action']) && $_GET['action'] == 'common_download_js') {
	require_once HTDOCS_DIR . "/js.php";
} else {
	require_once(MAPLE_DIR .'/core/Controller.class.php');
	$controller = new Controller();
	$controller->execute();
}
?>
