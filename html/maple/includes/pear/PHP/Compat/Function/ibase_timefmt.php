<?php
// $Id: ibase_timefmt.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace function ibase_timefmt()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.ibase_timefmt
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.0.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_ibase_timefmt($format, $columntype = IBASE_TIMESTAMP)
{
    switch ($columntype) {
        case IBASE_TIMESTAMP:
            ini_set('ibase.dateformat', $format);
            break;

        case IBASE_DATE:
            ini_set('ibase.dateformat', $format);
            break;

        case IBASE_TIME:
            ini_set('ibase.timeformat', $format);
            break;

        default:
            return false;
    }

    return true;
}


// Define
if (!function_exists('ibase_timefmt')) {
    function ibase_timefmt($format, $columntype = IBASE_TIMESTAMP)
    {
        return php_compat_ibase_timefmt($format, $columntype);
    }
}
