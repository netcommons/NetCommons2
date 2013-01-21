<?php
// $Id: htmlspecialchars.php,v 1.2 2008/11/24 09:57:03 aidan Exp $


/**
 * Replace function htmlspecialchars()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.htmlspecialchars
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_htmlspecialchars($string, $quote_style = null, $charset = null, $double_encode = true)
{
    // Sanity check
    if (!is_scalar($string)) {
        user_error('htmlspecialchars() expects parameter 1 to be string, ' .
            gettype($string) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_int($quote_style) && $quote_style !== null) {
        user_error('htmlspecialchars() expects parameter 2 to be integer, ' .
            gettype($quote_style) . ' given', E_USER_WARNING);
        return;
    }
	
    if (!is_scalar($charset)) {
        user_error('htmlspecialchars() expects parameter 3 to be string, ' .
				   gettype($charset) . ' given', E_USER_WARNING);
        return;
    }
	
    if (!is_bool($double_encode)) {
        user_error('htmlspecialchars() expects parameter 4 to be bool, ' .
				   gettype($double_encode) . ' given', E_USER_WARNING);
        return;
    }
	
	if ($double_encode === true) {
		$string = str_replace('&amp;', '&', $string);
	}
	
	$tf = array('&' => '&amp;',
				'<' => '&lt;',
				'>' => '&gt;');
	
	if ($quote_style & ENT_NOQUOTES) {
		$tf['"'] = '&quot';
	}
	
	if ($quote_style & ENT_QUOTES) {
		$tf["'"] = '&#039;';
	}
	
    return str_replace(array_keys($tf), array_values($tf), $string);
}


// Define
if (!function_exists('htmlspecialchars')) {
    function htmlspecialchars($string, $quote_style = null, $charset = null, $double_encode = true)
    {
        return php_compat_htmlspecialchars($string, $quote_style, $charset, $double_encode);
    }
}
