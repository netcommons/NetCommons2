<?php
// $Id: register_globals_on.php,v 1.7 2008/11/01 20:15:11 arpad Exp $


/**
 * Emulate enviroment register_globals=on
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/register_globals
 * @author      Aidan Lister <aidan@php.net>
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.7 $
 */
function php_compat_register_globals_on()
{
    $superglobals = array();
    $phpLt410 = PHP_VERSION < 4.1;

    // determine on which arrays to operate and in what order
    if ($phpLt410 || ini_get('register_long_arrays')) {
        global $HTTP_SERVER_VARS, $HTTP_ENV_VARS, $HTTP_COOKIE_VARS,
            $HTTP_SESSION_VARS, $HTTP_POST_VARS, $HTTP_POST_FILES, $HTTP_GET_VARS;
        $superglobals['S'][] = 'HTTP_SERVER_VARS';
        $superglobals['E'][] = 'HTTP_ENV_VARS';
        $superglobals['C'][] = 'HTTP_COOKIE_VARS';
        $superglobals['C'][] = 'HTTP_SESSION_VARS';
        $superglobals['G'][] = 'HTTP_GET_VARS';
        $superglobals['P'][] = 'HTTP_POST_VARS';
        $superglobals['P'][] = 'HTTP_POST_FILES';
    }
    if (!$phpLt410) {
        $superglobals['S'][] = '_SERVER';
        $superglobals['E'][] = '_ENV';
        $superglobals['C'][] = '_COOKIE';
        $superglobals['C'][] = '_SESSION';
        $superglobals['G'][] = '_GET';
        $superglobals['P'][] = '_POST';
        $superglobals['P'][] = '_FILES';
    }
    $order = ini_get('variables_order');
    $order_length = strlen($order);
    $inputs = array();

    for ($i = 0; $i < $order_length; $i++) {
        $key = strtoupper($order[$i]);
        if (!isset($superglobals[$key])) {
            continue;
        }
        foreach ($superglobals[$key] as $var) {
                if (isset($GLOBALS[$var])) {
                    $inputs[] = $GLOBALS[$var];
                }
        
        }
    }

    // build lookup array of predefined vars
    $allGlobals = array(
        'GLOBALS' => 1, 'HTTP_RAW_POST_DATA' => 1,
        'php_errormsg' => 1, 'http_response_header' => 1
    );
    foreach ($superglobals as $index => $vars) {
        foreach ($vars as $var) {
            $allGlobals[$var] = 1;
        }
    }

    // extract the specified arrays, reverse order since we're not overwriting
    for ($i = count($inputs); $i--;) {
        foreach ($inputs[$i] as $var => $value) {
            // ensure users can't set predefined vars or existing globals
            if (!isset($allGlobals[$var]) && !isset($GLOBALS[$var])) {
                $GLOBALS[$var] = $value;
            }
        }
    }

    // Register the change
    ini_set('register_globals', 'on');
}
if (!ini_get('register_globals')) {
    php_compat_register_globals_on();
}
