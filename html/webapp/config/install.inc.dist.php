<?php
/**
 * インストール時configファイル
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
 
// ------------------------------------------
// 設定ファイルのパス(固定:変更不可)
// ------------------------------------------
define('INSTALL_INC_DIR', transPathSeparator(dirname(__FILE__)));
 
// ----------------------------
// ベースのURL値
// ----------------------------
define('BASE_URL', 'http://');
// -------------------------------------------------
// ベースのURL値(ソースがあるCoreのNetCommonsのURL)
// 基本：BASE_URLと同じ
// -------------------------------------------------
define('CORE_BASE_URL', BASE_URL);
// ----------------------------
// NetCommonsのBaseディレクトリの設定
// ソース格納場所
// ----------------------------
if(!defined("BASE_DIR")) {
	define('BASE_DIR', dirname(START_INDEX_DIR));
}
// ----------------------------
// NetCommonsのHTDOCSディレクトリの設定
// 画像ファイル、CSSファイル格納場所
// デフォルト(START_INDEX_DIR)
// ----------------------------
define('HTDOCS_DIR', START_INDEX_DIR);

// ----------------------------
// テーマ用ディレクトリ
// デフォルト(BASE_DIR."/webapp/style)
// ----------------------------
define('STYLE_DIR', BASE_DIR . '/webapp/style');

// ----------------------------
// ファイルアップロード関連のディレクトリ設定
// (注意)ディレクトリ指定での最後に「/」をつけること
// デフォルト(BASE_DIR  . '/webapp/uploads/')
// ----------------------------
define('FILEUPLOADS_DIR', dirname(INSTALL_INC_DIR)  . '/uploads/');

// ----------------------------
// データベース用設定値
// ----------------------------
define('DATABASE_DSN', '');
define('DATABASE_PREFIX', '');
define('DATABASE_PCONNECT', 0);
define('DATABASE_CHARSET',	'utf8');

require_once BASE_DIR . "/webapp/config/maple.inc.php";
require_once BASE_DIR . "/webapp/config/define.inc.php";
?>