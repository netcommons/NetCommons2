<?php
// $Id: md5_file.php,v 1.5 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace md5_file()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/md5_file
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.5 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_md5_file($filename, $raw_output = false)
{
    // Sanity check
    if (!is_scalar($filename)) {
        user_error('md5_file() expects parameter 1 to be string, ' .
            gettype($filename) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_scalar($raw_output)) {
        user_error('md5_file() expects parameter 2 to be bool, ' .
            gettype($raw_output) . ' given', E_USER_WARNING);
        return;
    }

    if (!file_exists($filename)) {
        user_error('md5_file() Unable to open file', E_USER_WARNING);
        return false;
    }
    
    // Read the file
    if (false === $fh = fopen($filename, 'rb')) {
        user_error('md5_file() failed to open stream: No such file or directory',
            E_USER_WARNING);
        return false;
    }

    clearstatcache();
    if ($fsize = @filesize($filename)) {
        $data = fread($fh, $fsize);
    } else {
        $data = '';
        while (!feof($fh)) {
            $data .= fread($fh, 8192);
        }
    }

    fclose($fh);

    // Return
    $data = md5($data);
    if ($raw_output === true) {
        $data = pack('H*', $data);
    }

    return $data;
}


// Define
if (!function_exists('md5_file')) {
    function md5_file($filename, $raw_output = false)
    {
        return php_compat_md5_file($filename, $raw_output);
    }
}
