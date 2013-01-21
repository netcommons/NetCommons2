<?php

/**
 * Common include for magic quotes files
 *
 * "Correct" behaviour is defined as that of PHP 5.2.2 and (so far) above:
 *  - $_GET, $_POST, $_COOKIE and $_REQUEST are treated
 *  - All of the above variables's keys and values are treated
 *  
 * Fixed exceptions:
 *  - PHP < 5.2.2             keys of order 1 array values are not escaped
 *  - 5.0.0 >= PHP < 5.1.0    keys are escaped even when magic quotes is off
 *  - PHP < 5.0.0             keys of order 1 scalar values are not escaped
 *  - PHP < 4.3.4             keys of all scalar values are not escaped  
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/magic_quotes
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.3 $
 */

// version tests
$phpLt522 = version_compare(PHP_VERSION, '5.2.2', '<');
$phpLt51  = version_compare(PHP_VERSION, '5.1.0', '<');
$phpLt50  = version_compare(PHP_VERSION, '5.0.0', '<');
$phpLt434 = version_compare(PHP_VERSION, '4.3.4', '<');
$phpLt41  = version_compare(PHP_VERSION, '4.1.0', '<');

// so far, so good
$allWorks = !$phpLt522;

// build the array of variables to process
if ($phpLt41) {
    $inputs = array();
} else {
    // superglobals were added in PHP 4.1.0
    $inputs = array(&$_POST, &$_GET, &$_COOKIE, &$_REQUEST);
}
if ($phpLt50 || ini_get('register_long_arrays')) {
    // the old style globals are toggled by register_long_arrays since PHP 5.0.0
    // note the need for $GLOBALS here, since this file is included inside functions
    $inputs[] = &$GLOBALS['HTTP_GET_VARS'];
    $inputs[] = &$GLOBALS['HTTP_POST_VARS'];
    $inputs[] = &$GLOBALS['HTTP_COOKIE_VARS'];
    $inputs[] = &$GLOBALS['HTTP_SERVER_VARS'];
    $inputs[] = &$GLOBALS['HTTP_ENV_VARS'];

    if (    $phpLt50	// these superglobals haven't been escaped since PHP 5.0.0
	&& !$phpLt41	// and didn't exist before PHP 4.1.0
	&&  $stripping)	// so we only want them if we're stripping the inputs
    {        
        $inputs[] = &$_SERVER;
        $inputs[] = &$_ENV;
    }
}
