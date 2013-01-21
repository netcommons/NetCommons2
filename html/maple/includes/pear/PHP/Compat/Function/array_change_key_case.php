<?php
// $Id: array_change_key_case.php,v 1.13 2007/04/17 10:09:56 arpad Exp $


if (!defined('CASE_LOWER')) {
    define('CASE_LOWER', 0);
}

if (!defined('CASE_UPPER')) {
    define('CASE_UPPER', 1);
}


/**
 * Replace array_change_key_case()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_change_key_case
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_change_key_case($input, $case = CASE_LOWER)
{
    if (!is_array($input)) {
        user_error('array_change_key_case(): The argument should be an array',
            E_USER_WARNING);
        return false;
    }

    $output   = array ();
    $keys     = array_keys($input);
    $casefunc = ($case == CASE_LOWER) ? 'strtolower' : 'strtoupper';

    foreach ($keys as $key) {
        $output[$casefunc($key)] = $input[$key];
    }

    return $output;
}


// Define
if (!function_exists('array_change_key_case')) {
    function array_change_key_case($input, $case = CASE_LOWER)
    {
        return php_compat_array_change_key_case($input, $case);
    }
}
