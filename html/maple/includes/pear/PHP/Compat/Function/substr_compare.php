<?php
// $Id: substr_compare.php,v 1.7 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace substr_compare()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.substr_compare
 * @author      Tom Buskens <ortega@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_substr_compare($main_str, $str, $offset, $length = null, $case_insensitive = false)
{
    if (!is_string($main_str)) {
        user_error('substr_compare() expects parameter 1 to be string, ' .
            gettype($main_str) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_string($str)) {
        user_error('substr_compare() expects parameter 2 to be string, ' .
            gettype($str) . ' given', E_USER_WARNING);
        return;
    }
    
    if (!is_int($offset)) {
        user_error('substr_compare() expects parameter 3 to be long, ' .
            gettype($offset) . ' given', E_USER_WARNING);
        return;
    }
    
    if (is_null($length)) {
        $length = strlen($main_str) - $offset;
    } elseif ($offset >= strlen($main_str)) {
        user_error('substr_compare() The start position cannot exceed initial string length',
            E_USER_WARNING);
        return false;
    }

    $main_str = substr($main_str, $offset, $length);
    $str = substr($str, 0, strlen($main_str));

    if ($case_insensitive === false) {
        return strcmp($main_str, $str);
    } else {
        return strcasecmp($main_str, $str);
    }
}


// Define
if (!function_exists('substr_compare')) {
    function substr_compare($main_str, $str, $offset, $length = null, $case_insensitive = false)
    {
        return php_compat_substr_compare($main_str, $str, $offset, $length, $case_insensitive);
    }
}
