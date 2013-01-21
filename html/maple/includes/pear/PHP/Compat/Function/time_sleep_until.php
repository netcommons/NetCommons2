<?php
// $Id: time_sleep_until.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace time_sleep_until()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/time_sleep_until
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.4 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.1 (trigger_error)
 */
function php_compat_time_sleep_until($timestamp)
{
    list($usec, $sec) = explode(' ', microtime());
    $now = $sec + $usec;
    if ($timestamp <= $now) {
        user_error('Specified timestamp is in the past', E_USER_WARNING);
        return false;
    }

    $diff = $timestamp - $now;
    usleep($diff * 1000000);
    return true;

}


// Define
if (!function_exists('time_sleep_until')) {
    function time_sleep_until($timestamp)
    {
        return php_compat_time_sleep_until($timestamp);
    }
}
