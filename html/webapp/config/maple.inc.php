<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: maple.inc.php,v 1.14 2008/06/02 09:05:20 Ryuji.M Exp $
 */

//
//基本となるディレクトリの設定
//
if (!defined('BASE_DIR')) {
	define('BASE_DIR', dirname(dirname(dirname(__FILE__))));
}

define('WEBAPP_DIR', dirname(dirname(__FILE__)));
define('MAPLE_DIR',  'maple');



//追加
//
// PEAR関連のディレクトリ
//
define('PEAR_DIR', BASE_DIR."/".MAPLE_DIR  . '/includes/pear');

//
// Smarty関連のディレクトリ設定
// (注意)ディレクトリ指定での最後に「/」をつけること
//

define('SMARTY_DIR',               MAPLE_DIR  . '/smarty/');
define('SMARTY_COMPILE_DIR',       WEBAPP_DIR . '/templates_c/');
define('SMARTY_DEFAULT_MODIFIERS', 'escape:"html"');

//configテーブルにデータがない場合に使用されるデフォルト値
define('SMARTY_CACHING',           2);
define('SMARTY_CACHE_LIFETIME',    3600);	//1時間キャッシュを保持
define('SMARTY_COMPILE_CHECK',     false);
define('SMARTY_FORCE_COMPILE',     false);
//define('SMARTY_DEBUGGING',     false);

//
//基本となる定数の読み込み
//
require_once(BASE_DIR."/".MAPLE_DIR .'/config/common.php');
require_once(BASE_DIR."/".MAPLE_DIR .'/core/GlobalConfig.class.php');
GlobalConfig::loadConstantsFromFile(dirname(__FILE__) .'/'. GLOBAL_CONFIG);

//
//テンプレートシステムの設定など、コードベースの設定はここで行う
//
/*
require_once(MAPLE_DIR .'/core/Smarty4Maple.class.php');
Smarty4Maple::setOptions(array(
    "caching"           => false,
    "cache_lifetime"    => 3600,
    "compile_check"     => false,
    "force_compile"     => true,
    "default_modifiers" => array("escape:html")
));
*/

/*
require_once(MAPLE_DIR .'/flexy/Flexy_Flexy4Maple.class.php');
Flexy_Flexy4Maple::globalOptions(array(
    "allowPHP" => false,
    "globals"  => true,
    "debug"    => true
));
*/

/*
require_once(MAPLE_DIR .'/core/SimpleView4Maple.class.php');
SimpleView4Maple::setOptions(array(
    "aliasFuncName"           => 'h'
));
*/


//
//include_pathの設定とControllerの読み込み
//
ini_set('include_path', BASE_DIR . PATH_SEPARATOR . COMPONENT_DIR . PATH_SEPARATOR . PEAR_DIR . PATH_SEPARATOR . ini_get('include_path'));
//ini_set('include_path', COMPONENT_DIR . PATH_SEPARATOR . ini_get('include_path'));
?>
