<?php
// $Id: constant.php,v 1.10 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace constant()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.constant
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.10 $
 * @since       PHP 4.0.4
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_constant($constant)
{
    if (!defined($constant)) {
        $error = sprintf('constant() Couldn\'t find constant %s', $constant);
        user_error($error, E_USER_WARNING);
        return false;
    }
    
    $value = null;
    eval("\$value=$constant;");

    return $value;    
}


// Define
if (!function_exists('constant')) {
    function constant($constant)
    {
        return php_compat_constant($constant);
    }
}
