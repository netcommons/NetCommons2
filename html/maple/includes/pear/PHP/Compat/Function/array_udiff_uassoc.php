<?php
// $Id: array_udiff_uassoc.php,v 1.13 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_udiff_uassoc()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_udiff_uassoc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
function php_compat_array_udiff_uassoc()
{
    $args = func_get_args();
    if (count($args) < 4) {
        user_error('Wrong parameter count for array_udiff_uassoc()', E_USER_WARNING);
        return;
    }

    // Get key compare function
    $key_compare_func = array_pop($args);
    if (!is_callable($key_compare_func)) {
        if (is_array($key_compare_func)) {
            $key_compare_func = $key_compare_func[0] . '::' . $key_compare_func[1];
        }
        user_error('array_udiff_uassoc() Not a valid callback ' . $key_compare_func, E_USER_WARNING);
        return;
    }

    // Get data compare function
    $data_compare_func = array_pop($args);
    if (!is_callable($data_compare_func)) {
        if (is_array($data_compare_func)) {
            $data_compare_func = $data_compare_func[0] . '::' . $data_compare_func[1];
        }
        user_error('array_udiff_uassoc() Not a valid callback ' . $data_compare_func, E_USER_WARNING);
        return;
    }

    // Check arrays
    $count = count($args);
    for ($i = 0; $i < $count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_udiff_uassoc() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Traverse values of the first array
    $diff = array ();
    foreach ($args[0] as $key => $value) {
        // Check all arrays
        for ($i = 1; $i < $count; $i++) {
            foreach ($args[$i] as $args_key => $args_data) {
                $key_result = call_user_func($key_compare_func, $key, $args_key);
                $data_result = call_user_func($data_compare_func, $value, $args_data);
                if ($key_result === 0 && $data_result === 0) {
                    continue 3;
                }
            }
        }

        $diff[$key] = $value;
    }

    return $diff;

}


// Define
if (!function_exists('array_udiff_uassoc')) {
    function array_udiff_uassoc()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_udiff_uassoc', $args);     
    }
}
