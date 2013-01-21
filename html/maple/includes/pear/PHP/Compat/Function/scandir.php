<?php
// $Id: scandir.php,v 1.20 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace scandir()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.scandir
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.20 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_scandir($directory, $sorting_order = 0)
{
    if (!is_string($directory)) {
        user_error('scandir() expects parameter 1 to be string, ' .
            gettype($directory) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_int($sorting_order) && !is_bool($sorting_order)) {
        user_error('scandir() expects parameter 2 to be long, ' .
            gettype($sorting_order) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_dir($directory) || (false === $fh = @opendir($directory))) {
        user_error('scandir() failed to open dir: Invalid argument', E_USER_WARNING);
        return false;
    }

    $files = array ();
    while (false !== ($filename = readdir($fh))) {
        $files[] = $filename;
    }

    closedir($fh);

    if ($sorting_order == 1) {
        rsort($files);
    } else {
        sort($files);
    }

    return $files;

}


// Define
if (!function_exists('scandir')) {
    function scandir($directory, $sorting_order = 0)
    {
        return php_compat_scandir($directory, $sorting_order = 0);
    }
}
