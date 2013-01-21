<?php
// $Id: array_walk_recursive.php,v 1.12 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_walk_recursive()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_walk_recursive
 * @author      Tom Buskens <ortega@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.12 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
function php_compat_array_walk_recursive(&$input, $funcname)
{
    if (!is_callable($funcname)) {
        if (is_array($funcname)) {
            $funcname = $funcname[0] . '::' . $funcname[1];
        }
        user_error('array_walk_recursive() Not a valid callback ' . $funcname,
            E_USER_WARNING);
        return;
    }

    if (!is_array($input)) {
        user_error('array_walk_recursive() The argument should be an array',
            E_USER_WARNING);
        return;
    }

    $args = func_get_args();

    foreach ($input as $key => $item) {
        $callArgs = $args;
        if (is_array($item)) {
            $thisCall = 'php_compat_array_walk_recursive';
            $callArgs[1] = $funcname;
        } else {
            $thisCall = $funcname;
            $callArgs[1] = $key;
        }
        $callArgs[0] = &$input[$key];
        call_user_func_array($thisCall, $callArgs);
    }    
}


// Define
if (!function_exists('array_walk_recursive')) {
    function array_walk_recursive(&$input, $funcname)
    {
        return php_compat_array_walk_recursive($input, $funcname);
    }
}
