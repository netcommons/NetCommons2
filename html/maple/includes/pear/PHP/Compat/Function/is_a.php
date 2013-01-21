<?php
// $Id: is_a.php,v 1.19 2008/06/29 13:22:14 arpad Exp $


/**
 * Replace function is_a()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.is_a
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.19 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error) (is_subclass_of)
 */
function php_compat_is_a($object, $class)
{
    if (!is_object($object)) {
        return false;
    }

    if (strtolower(get_class($object)) == strtolower($class)) {
        return true;
    } else {
        return is_subclass_of($object, $class);
    }
}


// Define
if (!function_exists('is_a')) {
    function is_a($object, $class)
    {
        return php_compat_is_a($object, $class);
    }
}
