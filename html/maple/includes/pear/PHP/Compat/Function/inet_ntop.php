<?php
// $Id: inet_ntop.php,v 1.5 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace inet_ntop()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/inet_ntop
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.5 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.0 (long2ip)
 */
function php_compat_inet_ntop($in_addr)
{
    switch (strlen($in_addr)) {
        case 4:
            list(,$r) = unpack('N', $in_addr);
            return long2ip($r);

        case 16:
            $r = substr(chunk_split(bin2hex($in_addr), 4, ':'), 0, -1);
            $r = preg_replace(
                array('/(?::?\b0+\b:?){2,}/', '/\b0+([^0])/e'),
                array('::', '(int)"$1"?"$1":"0$1"'),
                $r);
            return $r;
    }

    return false;
}


// Define
if (!function_exists('inet_ntop')) {
    function inet_ntop($in_addr)
    {
        return php_compat_inet_ntop($in_addr);
    }
}
