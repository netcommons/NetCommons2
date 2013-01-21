<?php
// $Id: pg_affected_rows.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace pg_affected_rows()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.pg_affectd_rows
 * @author      Ian Eure <ieure@php.net>
 * @version     $Revision@
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0
 */
function php_compat_pg_affected_rows($resource)
{
    return pg_cmdtuples($resource);
}


// Define
if (!function_exists('pg_affected_rows')) {
    function pg_affected_rows($resource)
    {
        return php_compat_pg_affected_rows($resource);
    }
}
