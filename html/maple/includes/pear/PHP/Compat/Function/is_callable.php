<?php

/**
 * Replace function is_callable()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.is_callable
 * @author      Gaetano Giunta <giunta.gaetano@sea-aeroportimilano.it>
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 4.0.6
 * @require     PHP 4.0.0 (true, false, etc...)
 * @todo        add the 3rd parameter syntax...
 */
function php_compat_is_callable($var, $syntax_only = false)
{
    if (!is_string($var)
            && !(is_array($var)
                && count($var) == 2
                && isset($var[0], $var[1])
                && is_string($var[1])
                && (is_string($var[0])
                    ||
                    is_object($var[0])
                )
            )
        ) {
        return false;    
    }
    if ($syntax_only) {
        return true;
    }
    if (is_string($var)) {
        return function_exists($var);
    } else if (is_array($var)) {
        if (is_string($var[0])) {
            $methods = get_class_methods($var[0]);
            $method = strtolower($var[1]);
            if ($methods) {
                foreach ($methods as $classMethod) {
                    if (strtolower($classMethod) == $method) {
                        return true;
                    }
                }
            }
        } else {
            return method_exists($var[0], $var[1]);
        }
    }
    return false;
}

// Define
if (!function_exists('is_callable')) {
    function is_callable($var, $syntax_only)
    {
        return php_compat_is_callable($var, $syntax_only);
    }
}

