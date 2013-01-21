<?php
// $Id: mkdir.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace mkdir()
 *
 * Stream contexts aren't supported prior to PHP 5, reverts
 * to native function (to support contexts) in PHP 5+.
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.mkdir
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.0.0 (Added optional recursive and context parameters)
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_mkdir($pathname, $mode = 0777, $recursive = true, $context = null) {
    if (version_compare(PHP_VERSION, '5.0.0', 'gte')) {
        // revert to native function
        return (func_num_args() > 3)
            ? mkdir($pathname, $mode, $recursive, $context)
            : mkdir($pathname, $mode, $recursive);
    }
    if (!strlen($pathname)) {
        user_error('No such file or directory', E_USER_WARNING);
        return false;
    }
    if (is_dir($pathname)) {
        if (func_num_args() == 5) {
            // recursive call
            return true;
        }
        user_error('File exists', E_USER_WARNING);
        return false;
    }
    $parent_is_dir = php_compat_mkdir(dirname($pathname), $mode, $recursive, null, 0);
    if ($parent_is_dir) {
        return mkdir($pathname, $mode);
    }
    user_error('No such file or directory', E_USER_WARNING);
    return false;
}


// Define
if (!function_exists('mkdir')) {
    function mkdir($pathname, $mode, $recursive = false, $context = null)
    { 
        return php_compat_mkdir($pathname, $mode, $recursive, $context);
    }
}

?>