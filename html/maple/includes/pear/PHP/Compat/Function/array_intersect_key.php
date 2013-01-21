<?php
// $Id: array_intersect_key.php,v 1.9 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_intersect_key()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_intersect_key
 * @author      Tom Buskens <ortega@php.net>
 * @version     $Revision: 1.9 $
 * @since       PHP 5.0.2
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_intersect_key()
{
    $args = func_get_args();
    $array_count = count($args);
    if ($array_count < 2) {
        user_error('Wrong parameter count for array_intersect_key()', E_USER_WARNING);
        return;
    }

    // Check arrays
    for ($i = $array_count; $i--;) {
        if (!is_array($args[$i])) {
            user_error('array_intersect_key() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Intersect keys
    $arg_keys = array_map('array_keys', $args);
    $result_keys = call_user_func_array('array_intersect', $arg_keys);
    
    // Build return array
    $result = array();
    foreach($result_keys as $key) {
        $result[$key] = $args[0][$key];
    }
    return $result;
}


// Define
if (!function_exists('array_intersect_key')) {
    function array_intersect_key()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_intersect_key', $args);   
    }
}
