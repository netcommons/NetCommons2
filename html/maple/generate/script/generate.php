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
 * @package     Maple.script
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: generate.php,v 1.1 2006/10/13 08:52:17 Ryuji.M Exp $
 */
define('BASE_DIR', dirname(dirname(dirname(dirname(__FILE__)))));


error_reporting(E_ALL ^ E_DEPRECATED);
//error_reporting(0);

define('DEBUG_MODE', 0);
define('WORKING_DIR',       getcwd());

/**
 * Mapleの設定ファイルの読込み
 **/
require_once BASE_DIR."/"."maple/generate/config/maple.inc.php";

/**
 * このアプリの独自設定
 **/
define('DEFAULT_ACTION', 'maple_generate_usage');
define('LOG_LEVEL',      LEVEL_WARN);

/**
 * フレームワーク起動
 **/
$controller =& new Controller();
$controller->execute();
?>
