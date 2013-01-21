<?php
// $Id: php_ini_loaded_file.php,v 1.1 2008/11/24 04:18:49 aidan Exp $


/**
 * Replace php_ini_loaded_file()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/php_ini_loaded_file
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.1 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.0 (ob_start)
 */
function php_compat_php_ini_loaded_file()
{
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
        return false;
    }
}


// Define
if (!function_exists('php_ini_loaded_file')) {
    function php_ini_loaded_file()
    {
        return php_compat_php_ini_loaded_file();
    }
}
