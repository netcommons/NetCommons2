<?php
// $Id: is_scalar.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace is_scalar()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.is_scalar
 * @author      Gaetano Giunta
 * @version     $Revision: 1.3 $
 * @since       PHP 4.0.5
 * @require     PHP 4 (is_bool) 
 */
function php_compat_is_scalar($val)
{
    // Check input
    return (is_bool($val) || is_int($val) || is_float($val) || is_string($val));
}


// Define
if (!function_exists('is_scalar')) {
    function is_scalar($val)
    {
        return php_compat_is_scalar($val);
    }
}
