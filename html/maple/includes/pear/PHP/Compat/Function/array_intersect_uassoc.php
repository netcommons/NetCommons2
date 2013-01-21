<?php
// $Id: array_intersect_uassoc.php,v 1.9 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_intersect_uassoc()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_intersect_uassoc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.9 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
function php_compat_array_intersect_uassoc()
{
    // Sanity check
    $args = func_get_args();
    if (count($args) < 3) {
        user_error('Wrong parameter count for array_intersect_ukey()', E_USER_WARNING);
        return;
    }

    // Get compare function
    $compare_func = array_pop($args);
    if (!is_callable($compare_func)) {
        if (is_array($compare_func)) {
            $compare_func = $compare_func[0] . '::' . $compare_func[1];
        }
        user_error('array_intersect_uassoc() Not a valid callback ' .
            $compare_func, E_USER_WARNING);
        return;
    }

    // Check arrays
    $array_count = count($args);
    for ($i = 0; $i !== $array_count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_intersect_uassoc() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    // Compare entries
    $result = array();
    foreach ($args[0] as $k => $v) {
        for ($i = 0; $i < $array_count; $i++) {
            $match = false;
            foreach ($args[$i] as $kk => $vv) {
                $compare = call_user_func_array($compare_func, array($k, $kk));
                if ($compare === 0 && $v == $vv) {
                    $match = true;
                    continue 2;
                }
            }

            if ($match === false) { 
                continue 2;
            }
        }

        if ($match === true) {
            $result[$k] = $v;
        }
    }

    return $result;
}


// Define
if (!function_exists('array_intersect_uassoc')) {
    function array_intersect_uassoc()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_intersect_uassoc', $args);
    }
}
