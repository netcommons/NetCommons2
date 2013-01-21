<?php
// $Id: debug_print_backtrace.php,v 1.6 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace debug_print_backtrace()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.debug_print_backtrace
 * @author      Laurent Laville <pear@laurent-laville.org>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.6 $
 * @since       PHP 5
 * @require     PHP 4.3.0 (debug_backtrace)
 */
function php_compat_debug_print_backtrace()
{
    // Get backtrace
    $backtrace = debug_backtrace();

    // Unset call to debug_print_backtrace
    array_shift($backtrace);
    if (empty($backtrace)) {
        return '';
    }

    // Iterate backtrace
    $calls = array();
    foreach ($backtrace as $i => $call) {
        if (!isset($call['file'])) {
            $call['file'] = '(null)';
        }
        if (!isset($call['line'])) {
            $call['line'] = '0';
        }
        $location = $call['file'] . ':' . $call['line'];
        $function = (isset($call['class'])) ?
            $call['class'] . (isset($call['type']) ? $call['type'] : '.') . $call['function'] :
            $call['function'];

        $params = '';
        if (isset($call['args'])) {
            $args = array();
            foreach ($call['args'] as $arg) {
                if (is_array($arg)) {
                    $args[] = print_r($arg, true);
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $params = implode(', ', $args);
        }

        $calls[] = sprintf('#%d  %s(%s) called at [%s]',
            $i,
            $function,
            $params,
            $location);
    }

    echo implode("\n", $calls), "\n";
}

// Define
if (!function_exists('debug_print_backtrace')) {
    function debug_print_backtrace()
    {
        return php_compat_debug_print_backtrace();
    }
}
