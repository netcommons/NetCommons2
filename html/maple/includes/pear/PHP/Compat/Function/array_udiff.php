<?php
// $Id: array_udiff.php,v 1.14 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_udiff()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_udiff
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.14 $
 * @since       PHP 5
 * @require     PHP 4.0.6 (is_callable)
 */
function php_compat_array_udiff()
{
    $args = func_get_args();

    if (count($args) < 3) {
        user_error('Wrong parameter count for array_udiff()', E_USER_WARNING);
        return;
    }

    // Get compare function
    $compare_func = array_pop($args);
    if (!is_callable($compare_func)) {
        if (is_array($compare_func)) {
            $compare_func = $compare_func[0] . '::' . $compare_func[1];
        }
        user_error('array_udiff() Not a valid callback ' .
            $compare_func, E_USER_WARNING);
        return;
    }

    // Check arrays
    $cnt = count($args);
    for ($i = 0; $i < $cnt; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_udiff() Argument #' .
                ($i + 1). ' is not an array', E_USER_WARNING);
            return;
        }
    }

    $diff = array ();
    // Traverse values of the first array
    foreach ($args[0] as $key => $value) {
        // Check all arrays
        for ($i = 1; $i < $cnt; $i++) {
            foreach ($args[$i] as $cmp_value) {
                $result = call_user_func($compare_func, $value, $cmp_value);
                if ($result === 0) {
                    continue 3;
                }
            }
        }
        $diff[$key] = $value;
    }

    return $diff;
}


// Define
if (!function_exists('array_udiff')) {
    function array_udiff()
    {
        $args = func_get_args();
        return call_user_func_array('php_compat_array_udiff', $args);      
    }
}
