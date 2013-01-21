<?php
// $Id: inet_pton.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace inet_pton()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/inet_pton
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.4 $
 * @since       PHP 5.1.0
 * @require     PHP 4.2.0 (array_fill)
 */
function php_compat_inet_pton($address)
{
    $r = ip2long($address);
    if ($r !== false && $r != -1) {
        return pack('N', $r);
    }

    $delim_count = substr_count($address, ':');
    if ($delim_count < 1 || $delim_count > 7) {
        return false;
    }

    $r = explode(':', $address);
    $rcount = count($r);
    if (($doub = array_search('', $r, 1)) !== false) {
        $length = (!$doub || $doub == $rcount - 1 ? 2 : 1);
        array_splice($r, $doub, $length, array_fill(0, 8 + $length - $rcount, 0));
    }

    $r = array_map('hexdec', $r);
    array_unshift($r, 'n*');
    $r = call_user_func_array('pack', $r);

    return $r;
}


// Define
if (!function_exists('inet_pton')) {  
    function inet_pton($address)
    {
        return php_compat_inet_pton($address);
    }
}
