<?php
// $Id: fprintf.php,v 1.15 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace fprintf()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.fprintf
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.15 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_fprintf()
{
    $args = func_get_args();

    if (count($args) < 2) {
        user_error('Wrong parameter count for fprintf()', E_USER_WARNING);
        return;
    }

    $resource_handle = array_shift($args);
    $format = array_shift($args);

    if (!is_resource($resource_handle)) {
        user_error('fprintf() supplied argument is not a valid stream resource',
            E_USER_WARNING);
        return false;
    }

    return fwrite($resource_handle, vsprintf($format, $args));
}


// Define
if (!function_exists('fprintf')) {
    function fprintf()
    {
        return php_compat_fprintf();
    }
}
