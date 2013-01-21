<?php
// $Id: str_ireplace.php,v 1.24 2007/12/03 22:02:37 arpad Exp $


/**
 * Replace str_ireplace()
 *
 * This function does not support the $count argument because
 * it cannot be optional in PHP 4 and the performance cost is
 * too great when a count is not necessary.
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.str_ireplace
 * @author      Aidan Lister <aidan@php.net>
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.24 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_str_ireplace($search, $replace, $subject)
{
    // Sanity check
    if (is_string($search) && is_array($replace)) {
        user_error('Array to string conversion', E_USER_NOTICE);
        $replace = (string) $replace;
    }

    // If search isn't an array, make it one
    $search = (array) $search;
    $length_search = count($search);

    // build the replace array
    $replace = is_array($replace)
	? array_pad($replace, $length_search, '')
	: array_pad(array(), $length_search, $replace);

    // If subject is not an array, make it one
    $was_string = false;
    if (is_string($subject)) {
        $was_string = true;
        $subject = array ($subject);
    }

    // Prepare the search array
    foreach ($search as $search_key => $search_value) {
        $search[$search_key] = '/' . preg_quote($search_value, '/') . '/i';
    }
    
    // Prepare the replace array (escape backreferences)
    $replace = str_replace(array('\\', '$'), array('\\\\', '\$'), $replace);

    $result = preg_replace($search, $replace, $subject);
    return $was_string ? $result[0] : $result;
}


// Define
if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subjectl)
    {
        return php_compat_str_ireplace($search, $replace, $subject);
    }
}
