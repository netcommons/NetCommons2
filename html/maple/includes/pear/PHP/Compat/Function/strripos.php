<?php
// $Id: strripos.php,v 1.26 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace strripos()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.strripos
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.26 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_strripos($haystack, $needle, $offset = null)
{
    // Sanity check
    if (!is_scalar($haystack)) {
        user_error('strripos() expects parameter 1 to be scalar, ' .
            gettype($haystack) . ' given', E_USER_WARNING);
        return false;
    }

    if (!is_scalar($needle)) {
        user_error('strripos() expects parameter 2 to be scalar, ' .
            gettype($needle) . ' given', E_USER_WARNING);
        return false;
    }

    if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
        user_error('strripos() expects parameter 3 to be long, ' .
            gettype($offset) . ' given', E_USER_WARNING);
        return false;
    }

    // Initialise variables
    $needle         = strtolower($needle);
    $haystack       = strtolower($haystack);
    $needle_fc      = $needle{0};
    $needle_len     = strlen($needle);
    $haystack_len   = strlen($haystack);
    $offset         = (int) $offset;
    $leftlimit      = ($offset >= 0) ? $offset : 0;
    $p              = ($offset >= 0) ?
                            $haystack_len :
                            $haystack_len + $offset + 1;

    // Reverse iterate haystack
    while (--$p >= $leftlimit) {
        if ($needle_fc === $haystack{$p} &&
            substr($haystack, $p, $needle_len) === $needle) {
            return $p;
        }
    }

    return false;
}


// Define
if (!function_exists('strripos')) {
    function strripos($haystack, $needle, $offset = null)
    {
        return php_compat_strripos($haystack, $needle, $offset);
    }
}
