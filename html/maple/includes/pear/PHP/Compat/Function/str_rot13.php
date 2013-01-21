<?php
// $Id: str_rot13.php,v 1.6 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace str_rot13()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.str_rot13
 * @author      Alan Morey <alan@caint.com>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.6 $
 * @since       PHP 4.0.0
 */
function php_compat_str_rot13($str)
{
    $from = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $to   = 'nopqrstuvwxyzabcdefghijklmNOPQRSTUVWXYZABCDEFGHIJKLM';

    return strtr($str, $from, $to);
}


// Define
if (!function_exists('str_rot13')) {
    function str_rot13($str)
    {
        return php_compat_str_rot13($str);
    }
}
