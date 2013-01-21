<?php
// $Id: array_key_exists.php,v 1.9 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_key_exists()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_key_exists
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.9 $
 * @since       PHP 4.1.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_key_exists($key, $search)
{
    if (!is_scalar($key)) {
        user_error('array_key_exists() The first argument should be either a string or an integer',
            E_USER_WARNING);
        return false;
    }

    if (is_object($search)) {
        $search = get_object_vars($search);
    }

    if (!is_array($search)) {
        user_error('array_key_exists() The second argument should be either an array or an object',
            E_USER_WARNING);
        return false;
    }

    return in_array($key, array_keys($search));
}


// Define
if (!function_exists('array_key_exists')) {
    function array_key_exists($key, $search)
    {
        return php_compat_array_key_exists($key, $search);
    }
}
