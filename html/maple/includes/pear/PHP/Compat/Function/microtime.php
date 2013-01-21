<?php
// $Id: microtime.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace microtime()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.microtime
 * @author      Aidan Lister <aidan@php.net>
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.0.0 (Added optional get_as_float parameter)
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_microtime($get_as_float = false)
{
    if (!function_exists('gettimeofday')) {
        $time = time();
        return $get_as_float ? ($time * 1000000.0) : '0.00000000 ' . $time;
    } 
    $gtod = gettimeofday();
    $usec = $gtod['usec'] / 1000000.0;
    return $get_as_float
        ? (float) ($gtod['sec'] + $usec)
        : (sprintf('%.8f ', $usec) . $gtod['sec']);
}


// Define
if (!function_exists('microtime')) {
    function microtime($get_as_float = false)
    { 
        return php_compat_microtime($get_as_float);
    }
}
