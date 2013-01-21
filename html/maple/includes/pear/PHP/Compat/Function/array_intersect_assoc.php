<?php
// $Id: array_intersect_assoc.php,v 1.8 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_intersect_assoc()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_intersect_assoc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.8 $
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_intersect_assoc()
{
    // Sanity check
    $args = func_get_args();
    if (count($args) < 2) {
        user_error('wrong parameter count for array_intersect_assoc()', E_USER_WARNING);
        return;
    }

    // Check arrays
    $array_count = count($args);
    for ($i = 0; $i !== $array_count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_intersect_assoc() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Compare entries
    $intersect = array();
    foreach ($args[0] as $key => $value) {
        $intersect[$key] = $value;

        for ($i = 1; $i < $array_count; $i++) {
            if (!isset($args[$i][$key]) || $args[$i][$key] != $value) {
                unset($intersect[$key]);
                break;
            }
        }
    }

    return $intersect;
}


// Define
if (!function_exists('array_intersect_assoc')) {
    function array_intersect_assoc()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_intersect_assoc', $args);
    }
}
