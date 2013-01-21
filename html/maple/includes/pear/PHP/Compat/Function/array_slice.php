<?php
// $Id: array_slice.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_slice()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_slice
 * @author      Arpad Ray <arpad@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.0.2 (Added optional preserve keys parameter)
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_slice($array, $offset, $length = null, $preserve_keys = false)
{ 
    if (!$preserve_keys) {
        return array_slice($array, $offset, $length);
    }
    if (!is_array($array)) {
        user_error('The first argument should be an array', E_USER_WARNING);
        return;
    }
    $keys = array_slice(array_keys($array), $offset, $length);
    $ret = array();
    foreach ($keys as $key) {
        $ret[$key] = $array[$key];
    }
    return $ret;
}


// Define
if (!function_exists('array_slice')) {
    function array_slice($array, $offset, $length = null, $preserve_keys = false)
    { 
        return php_compat_array_slice($array, $offset, $length, $preserve_keys);
    }
}

?>