<?php
// $Id: array_product.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_product()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/time_sleep_until
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.4 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.1 (trigger_error)
 */
function php_compat_array_product($array)
{
    if (!is_array($array)) {
        trigger_error('The argument should be an array', E_USER_WARNING);
        return;
    }

    if (empty($array)) {
        return 0;
    }

    $r = 1;
    foreach ($array as $v) {
        $r *= $v;
    }

    return $r;
}


// Define
if (!function_exists('array_product')) {
    function array_product($array)
    {
        return php_compat_array_product($array);
    }
}
