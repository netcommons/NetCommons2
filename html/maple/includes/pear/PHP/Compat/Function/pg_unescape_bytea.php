<?php
// $Id: pg_unescape_bytea.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace pg_unescape_bytea()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.pg_unescape_bytea
 * @author      Ian Eure <ieure@php.net>
 * @version     $Revision@
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0
 */
function php_compat_pg_unescape_bytea(&$data)
{
    return str_replace(
        array('$',   '"'),
        array('\\$', '\\"'),
        $data);
}


// Define
if (!function_exists('pg_unescape_bytea')) {
    function pg_unescape_bytea(&$data)
    {
        return php_compat_pg_unescape_bytea($data);
    }
}
