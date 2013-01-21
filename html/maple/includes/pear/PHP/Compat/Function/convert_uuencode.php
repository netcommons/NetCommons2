<?php
// $Id: convert_uuencode.php,v 1.9 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace convert_uuencode()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.convert_uuencode
 * @author      Michael Wallner <mike@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.9 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_convert_uuencode($string)
{
    // Sanity check
    if (!is_scalar($string)) {
        user_error('convert_uuencode() expects parameter 1 to be string, ' .
            gettype($string) . ' given', E_USER_WARNING);
        return false;
    }

    $u = 0;
    $encoded = '';
    
    while ($c = count($bytes = unpack('c*', substr($string, $u, 45)))) {
        $u += 45;
        $encoded .= pack('c', $c + 0x20);

        while ($c % 3) {
            $bytes[++$c] = 0;
        }

        foreach (array_chunk($bytes, 3) as $b) {
            $b0 = ($b[0] & 0xFC) >> 2;
            $b1 = (($b[0] & 0x03) << 4) + (($b[1] & 0xF0) >> 4);
            $b2 = (($b[1] & 0x0F) << 2) + (($b[2] & 0xC0) >> 6);
            $b3 = $b[2] & 0x3F;
            
            $b0 = $b0 ? $b0 + 0x20 : 0x60;
            $b1 = $b1 ? $b1 + 0x20 : 0x60;
            $b2 = $b2 ? $b2 + 0x20 : 0x60;
            $b3 = $b3 ? $b3 + 0x20 : 0x60;
            
            $encoded .= pack('c*', $b0, $b1, $b2, $b3);
        }

        $encoded .= "\n";
    }
    
    // Add termination characters
    $encoded .= "\x60\n";

    return $encoded;    
}


// Define
if (!function_exists('convert_uuencode')) {
    function convert_uuencode($string)
    {
        return php_compat_convert_uuencode($string);
    }
}
