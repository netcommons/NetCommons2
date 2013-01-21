<?php
// $Id: floatval.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace floatval()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.floatval
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.4 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error) (Type Casting)
 */
function php_compat_floatval($var)
{
    return (float) $var;
}


// Define
if (!function_exists('floatval')) {
    function floatval($var)
    {
        return php_compat_floatval($var);
    }
}
