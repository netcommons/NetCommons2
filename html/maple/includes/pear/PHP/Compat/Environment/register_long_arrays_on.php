<?php
// $Id: register_long_arrays_on.php,v 1.4 2008/11/01 20:15:11 arpad Exp $


/**
 * Emulate enviroment register_long_arrays=on
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/manual/en/ini.core.php#ini.register-long-arrays
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.4 $
 */
$GLOBALS['HTTP_GET_VARS']    = &$_GET;
$GLOBALS['HTTP_POST_VARS']   = &$_POST;
$GLOBALS['HTTP_COOKIE_VARS'] = &$_COOKIE;
$GLOBALS['HTTP_SERVER_VARS'] = &$_SERVER;
$GLOBALS['HTTP_ENV_VARS']    = &$_ENV;
$GLOBALS['HTTP_FILES_VARS']  = &$_FILES;

// Register the change
ini_set('register_long_arrays', 'on');
