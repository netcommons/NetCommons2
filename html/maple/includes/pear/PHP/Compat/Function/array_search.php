<?php
// $Id: array_search.php,v 1.8 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace array_search()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.array_search
 * @author      Aidan Lister <aidan@php.net>
 * @author      Thiemo Mättig (http://maettig.com/)
 * @version     $Revision: 1.8 $
 * @since       PHP 4.0.5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_search($needle, $haystack, $strict = false)
{
    if (!is_array($haystack)) {
        user_error('array_search() Wrong datatype for second argument', E_USER_WARNING);
        return false;
    }

    foreach ($haystack as $key => $value) {
        if ($strict ? $value === $needle : $value == $needle) {
            return $key;
        }
    }

    return false;
}


// Define
if (!function_exists('array_search')) {
    function array_search($needle, $haystack, $strict = false)
    {
        return php_compat_array_search($needle, $haystack, $strict);
    }
}
