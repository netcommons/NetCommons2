<?php
// $Id: pg_escape_bytea.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace pg_escape_bytea()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.pg_escape_bytea
 * @author      Ian Eure <ieure@php.net>
 * @version     $Revision@
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0
 */
function php_compat_pg_escape_bytea($data)
{
    return str_replace(
        array(chr(92),  chr(0),   chr(39)),
        array('\\\134', '\\\000', '\\\047'),
        $data);
}


// Define
if (!function_exists('pg_escape_bytea')) {
    function pg_escape_bytea($data)
    {
        return php_compat_pg_escape_bytea($data);
    }
}
