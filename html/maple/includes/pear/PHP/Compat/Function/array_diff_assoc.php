<?php
// $Id: array_diff_assoc.php,v 1.16 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_diff_assoc()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_diff_assoc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.16 $
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_diff_assoc()
{
    // Check we have enough arguments
    $args = func_get_args();
    $count = count($args);
    if (count($args) < 2) {
        user_error('Wrong parameter count for array_diff_assoc()', E_USER_WARNING);
        return;
    }

    // Check arrays
    for ($i = 0; $i < $count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_diff_assoc() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Get the comparison array
    $array_comp = array_shift($args);
    --$count;

    // Traverse values of the first array
    foreach ($array_comp as $key => $value) {
        // Loop through the other arrays
        for ($i = 0; $i < $count; $i++) {
            // Loop through this arrays key/value pairs and compare
            foreach ($args[$i] as $comp_key => $comp_value) {
                if ((string)$key === (string)$comp_key &&
                    (string)$value === (string)$comp_value)
                {

                    unset($array_comp[$key]);
                }
            }
        }
    }

    return $array_comp;
}


// Define
if (!function_exists('array_diff_assoc')) {
    function array_diff_assoc()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_diff_assoc', $args);   
    }
}
