<?php
// $Id: ini_get_all.php,v 1.7 2008/11/24 09:56:46 aidan Exp $


/**
 * Replace ini_get_all()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.ini_get_all
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_ini_get_all($extension = null)
{
    // Sanity check
    if ($extension !== null && !is_scalar($extension)) {
        user_error('ini_get_all() expects parameter 1 to be string, ' .
            gettype($extension) . ' given', E_USER_WARNING);
        return false;
    }
    
    // Get the location of php.ini
    ob_start();
    phpinfo(INFO_GENERAL);
    $info = ob_get_contents();
    ob_clean();
    $info = explode("\n", $info);
    $line = array_values(preg_grep('#php\.ini#', $info));

    // Plain vs HTML output
    if (substr($line[0], 0, 4) === '<tr>') {
        list (, $value) = explode('<td class="v">', $line[0], 2);
        $inifile = trim(strip_tags($value));
    } else {
        list (, $value) = explode(' => ', $line[0], 2);
        $inifile = trim($value);
    }

    // Check the file actually exists
    if (!file_exists($inifile)) {
        user_error('ini_get_all() Unable to find php.ini', E_USER_WARNING);
        return false;
    }
	
	// Check the file is readable
    if (!is_readable($inifile)) {
        user_error('ini_get_all() Unable to open php.ini', E_USER_WARNING);
        return false;
    }

    // Parse the ini
    if ($extension !== null) {
        $ini_all = parse_ini_file($inifile, true);

        // Lowercase extension keys
        foreach ($ini_all as $key => $value) {
            $ini_arr[strtolower($key)] = $value;
        }

        // Check the extension exists
        if (isset($ini_arr[$extension])) {
            $ini = $ini_arr[$extension];
        } else {
            user_error("ini_get_all() Unable to find extension '$extension'",
                E_USER_WARNING);
            return false;
        }
    } else {
        $ini = parse_ini_file($inifile);
    }

    // Order
    $ini_lc = array_map('strtolower', array_keys($ini));
    array_multisort($ini_lc, SORT_ASC, SORT_STRING, $ini);

    // Format
    $info = array();
    foreach ($ini as $key => $value) {
        $info[$key] = array(
            'global_value'  => $value,
            'local_value'   => ini_get($key),
            // No way to know this
            'access'        => -1
        );
    }

    return $info;
}


// Define
if (!function_exists('ini_get_all')) {
    function ini_get_all($extension = null)
    {
        return php_compat_ini_get_all($extension);
    }
}
