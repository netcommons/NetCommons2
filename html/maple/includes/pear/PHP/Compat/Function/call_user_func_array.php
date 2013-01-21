<?php
// $Id: call_user_func_array.php,v 1.17 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace call_user_func_array()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.call_user_func_array
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.17 $
 * @since       PHP 4.0.4
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_call_user_func_array($function, $param_arr)
{
    $param_arr = array_values((array) $param_arr);

    // Sanity check
    if (!is_callable($function)) {
        if (is_array($function) && count($function) > 2) {
            $function = $function[0] . '::' . $function[1];
        }
        $error = sprintf('call_user_func_array() First argument is expected ' .
            'to be a valid callback, \'%s\' was given', $function);
        user_error($error, E_USER_WARNING);
        return;
    }

    // Build argument string
    $arg_string = '';
    $comma = '';
    for ($i = 0, $x = count($param_arr); $i < $x; $i++) {
        $arg_string .= $comma . "\$param_arr[$i]";
        $comma = ', ';
    }

    // Determine method of calling function
    $retval = null;
    if (is_array($function)) {
        $object =& $function[0];
        $method = $function[1];

        // Static vs method call
        if (is_string($function[0])) {
            eval("\$retval = $object::\$method($arg_string);");
        } else {
            eval("\$retval = \$object->\$method($arg_string);");
        }
    } else {
        eval("\$retval = \$function($arg_string);");
    }

    return $retval;
}


// Define
if (!function_exists('call_user_func_array')) {
    function call_user_func_array($function, $param_arr)
    {
        return php_compat_call_user_func_array($function, $param_arr);
    }
}
