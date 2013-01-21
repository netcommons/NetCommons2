<?php
// $Id: array_diff_key.php,v 1.9 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_diff_key()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_diff_key
 * @author      Tom Buskens <ortega@php.net>
 * @version     $Revision: 1.9 $
 * @since       PHP 5.0.2
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_diff_key()
{
    $args = func_get_args();
    if (count($args) < 2) {
        user_error('Wrong parameter count for array_diff_key()', E_USER_WARNING);
        return;
    }

    // Check arrays
    $array_count = count($args);
    for ($i = 0; $i !== $array_count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_diff_key() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    $result = $args[0];
    if (function_exists('array_key_exists')) {
        // Optimize for >= PHP 4.1.0
        foreach ($args[0] as $key => $value) {
            for ($i = 1; $i !== $array_count; $i++) {
                if (array_key_exists($key,$args[$i])) {
                    unset($result[$key]);
                    break;
                }
            }
        }
    } else {
        foreach ($args[0] as $key1 => $value1) {
            for ($i = 1; $i !== $array_count; $i++) {
                foreach ($args[$i] as $key2 => $value2) {
                    if ((string) $key1 === (string) $key2) {
                        unset($result[$key2]);
                        break 2;
                    }
                }
            }
        }
    }
    return $result; 
}


// Define
if (!function_exists('array_diff_key')) {
    function array_diff_key()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_diff_key', $args);
    }
}
