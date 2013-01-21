<?php
// $Id: htmlspecialchars_decode.php,v 1.6 2008/06/29 12:30:57 arpad Exp $


/**
 * Replace function htmlspecialchars_decode()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.htmlspecialchars_decode
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.6 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_htmlspecialchars_decode($string, $quote_style = null)
{
    // Sanity check
    if (!is_scalar($string)) {
        user_error('htmlspecialchars_decode() expects parameter 1 to be string, ' .
            gettype($string) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_int($quote_style) && $quote_style !== null) {
        user_error('htmlspecialchars_decode() expects parameter 2 to be integer, ' .
            gettype($quote_style) . ' given', E_USER_WARNING);
        return;
    }

    // The function does not behave as documented
    // This matches the actual behaviour of the function
    if ($quote_style & ENT_COMPAT || $quote_style & ENT_QUOTES) {
        $from = array('&quot;', '&#039;', '&lt;', '&gt;', '&amp;');
        $to   = array('"', "'", '<', '>', '&');
    } else {
        $from = array('&lt;', '&gt;', '&amp;');
        $to   = array('<', '>', '&');
    }

    return str_replace($from, $to, $string);
}


// Define
if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string, $quote_style = null)
    {
        return php_compat_htmlspecialchars_decode($string, $quote_style);
    }
}
