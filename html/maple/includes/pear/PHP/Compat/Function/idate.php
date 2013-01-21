<?php
// $Id: idate.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace idate()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/idate
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.4 $
 * @since       PHP 5.0.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_idate($format, $timestamp = false)
{
    if (strlen($format) !== 1) {
        user_error('idate format is one char', E_USER_WARNING);
        return false;
    }

    if (strpos('BdhHiILmstUwWyYzZ', $format) === false) {
        return 0;
    }

    if ($timestamp === false) {
        $timestamp = time();
    }

    return intval(date($format, $timestamp));
}


// Define
if (!function_exists('idate')) {
    function idate($format, $timestamp = false)
    {
        return php_compat_idate($format, $timestamp);
    }
}
