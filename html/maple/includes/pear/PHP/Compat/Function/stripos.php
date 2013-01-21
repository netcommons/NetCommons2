<?php
// $Id: stripos.php,v 1.15 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace stripos()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.stripos
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.15 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_stripos($haystack, $needle, $offset = null)
{
    if (!is_scalar($haystack)) {
        user_error('stripos() expects parameter 1 to be string, ' .
            gettype($haystack) . ' given', E_USER_WARNING);
        return false;
    }

    if (!is_scalar($needle)) {
        user_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
        return false;
    }

    if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
        user_error('stripos() expects parameter 3 to be long, ' .
            gettype($offset) . ' given', E_USER_WARNING);
        return false;
    }

    // Manipulate the string if there is an offset
    $fix = 0;
    if (!is_null($offset)) {
        if ($offset > 0) {
            $haystack = substr($haystack, $offset, strlen($haystack) - $offset);
            $fix = $offset;
        }
    }

    $segments = explode(strtolower($needle), strtolower($haystack), 2);

    // Check there was a match
    if (count($segments) === 1) {
        return false;
    }

    $position = strlen($segments[0]) + $fix;
    return $position;
}


// Define
if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null)
    {
        return php_compat_stripos($haystack, $needle, $offset);
    }
}
