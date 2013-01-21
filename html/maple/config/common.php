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
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: common.php,v 1.2 2008/07/22 11:55:14 Ryuji.M Exp $
 */

define('VALIDATE_ERROR_TYPE', 'input_error');
define('TOKEN_ERROR_TYPE', 'token_error');
define('VALIDATE_ERROR_NONEREDIRECT_TYPE', 'input_noneredirect_error');

define('UPLOAD_ERROR_TYPE', 'upload_error');
//キャッシュを使用
define('USE_CACHE', 'use_cache');

define('GLOBAL_CONFIG', 'global-config.ini');
define('CONFIG_FILE',   'maple.ini');
define('BASE_INI',      '/config/base.ini');

define('VALIDATOR_DIR_NAME', '/validator');
define('CONVERTER_DIR_NAME', '/converter');
define('FILTER_DIR',    MAPLE_DIR . '/filter');
define('CONVERTER_DIR', MAPLE_DIR . CONVERTER_DIR_NAME);
define('VALIDATOR_DIR', MAPLE_DIR . VALIDATOR_DIR_NAME);
define('LOGGER_DIR',    MAPLE_DIR . '/logger');

//定義済みエラー定数として2048まで使用されているので2048を加算。
define('LEVEL_SQL', 2048+7);
define('LEVEL_FATAL', 2048+6);
define('LEVEL_ERROR', 2048+5);
define('LEVEL_WARN',  2048+4);
define('LEVEL_INFO',  2048+3);
define('LEVEL_DEBUG', 2048+2);
define('LEVEL_TRACE', 2048+1);

if (!defined('PATH_SEPARATOR')) {
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		define('PATH_SEPARATOR', ';');
	} else {
		define('PATH_SEPARATOR', ':');
	}
}


?>
