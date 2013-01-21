<?php
// $Id: html_entity_decode.php,v 1.11 2007/04/17 10:09:56 arpad Exp $


if (!defined('ENT_NOQUOTES')) {
    define('ENT_NOQUOTES', 0);
}

if (!defined('ENT_COMPAT')) {
    define('ENT_COMPAT', 2);
}

if (!defined('ENT_QUOTES')) {
    define('ENT_QUOTES', 3);
}


/**
 * Replace html_entity_decode()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.html_entity_decode
 * @author      David Irvine <dave@codexweb.co.za>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.11 $
 * @since       PHP 4.3.0
 * @internal    Setting the charset will not do anything
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_html_entity_decode($string, $quote_style = ENT_COMPAT, $charset = null)
{
    if (!is_int($quote_style)) {
        user_error('html_entity_decode() expects parameter 2 to be long, ' .
            gettype($quote_style) . ' given', E_USER_WARNING);
        return;
    }

    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);

    // Add single quote to translation table;
    if ($quote_style === ENT_QUOTES) {
        $trans_tbl['&#039;'] = '\'';
    }

    // Not translating double quotes
    if ($quote_style === ENT_NOQUOTES) {
        // Remove double quote from translation table
        unset($trans_tbl['&quot;']);
    }

    return strtr($string, $trans_tbl);
}


// Define
if (!function_exists('html_entity_decode')) {
    function html_entity_decode($string, $quote_style = ENT_COMPAT, $charset = null)
    {
        return php_compat_html_entity_decode($string, $quote_style, $charset);
    }
}
