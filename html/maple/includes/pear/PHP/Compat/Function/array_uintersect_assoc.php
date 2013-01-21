<?php
// $Id: array_uintersect_assoc.php,v 1.13 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_uintersect_assoc()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_uintersect_assoc
 * @author      Tom Buskens <ortega@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
function php_compat_array_uintersect_assoc()
{
    $args = func_get_args();
    if (count($args) < 3) {
        user_error('wrong parameter count for array_uintersect_assoc()', E_USER_WARNING);
        return;
    }

    // Get compare function
    $user_func = array_pop($args);
    if (!is_callable($user_func)) {
        if (is_array($user_func)) {
            $user_func = $user_func[0] . '::' . $user_func[1];
        }
        user_error('array_uintersect_assoc() Not a valid callback ' .
            $user_func, E_USER_WARNING);
        return;
    }

    // Check arrays
    $array_count = count($args);
    for ($i = 0; $i < $array_count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_uintersect_assoc() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Compare entries
    $output = array();
    foreach ($args[0] as $key => $item) {
        for ($i = 1; $i !== $array_count; $i++) {
            if (array_key_exists($key, $args[$i])) {
                 $compare = call_user_func($user_func, $item, $args[$i][$key]);
                 if ($compare === 0) {
                     $output[$key] = $item;
                 }
            }
        }
    }

    return $output;

}


// Define
if (!function_exists('array_uintersect_assoc')) {
    function array_uintersect_assoc()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_uintersect_assoc', $args);      
    }
}
