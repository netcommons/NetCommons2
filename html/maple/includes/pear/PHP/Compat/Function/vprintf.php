<?php
// $Id: vprintf.php,v 1.16 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace vprintf()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.vprintf
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.16 $
 * @since       PHP 4.1.0
 * @require     PHP 4.0.4 (call_user_func_array)
 */
function php_compat_vprintf($format, $args)
{
    if (count($args) < 2) {
        user_error('vprintf() Too few arguments', E_USER_WARNING);
        return;
    }

    array_unshift($args, $format);
    return call_user_func_array('printf', $args);
}


// Define
if (!function_exists('vprintf')) {
    function vprintf($format, $args)
    {
        return php_compat_vprintf($format, $args);
    }
}
