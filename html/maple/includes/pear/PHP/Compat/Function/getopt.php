<?php
// $Id: getopt.php,v 1.3 2008/11/24 04:18:49 aidan Exp $

define('PHP_COMPAT_GETOPT_NO_VALUE', 0);
define('PHP_COMPAT_GETOPT_VALUE_REQUIRED', 1);
define('PHP_COMPAT_GETOPT_VALUE_OPTIONAL', 2);

/**
 * Replace getopt()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.getopt
 * @author      Jim Wigginton <terrafrost@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 4.0.4
 */
function php_compat_getopt($options, $longopts = NULL)
{
    global $argv, $argc;
    
    $output = array();
    $opt = '';
    
    if (is_array($options)) {
        user_error('getopt() expects parameter 1 to be string, array given', E_USER_WARNING);
        return false;
    }
    
    for ($i = 1; $i < $argc; $i++) {
        switch (true) {
            case is_array($longopts) && substr($argv[$i], 0, 2) == '--':
                $pos = strpos($argv[$i], '=');
                $opt = is_int($pos) ? substr($argv[$i], 2, $pos - 2) : substr($argv[$i], 2);
                
                list($match) = array_values(preg_grep('#^' . preg_quote($opt, '#') . ':{0,2}$#', $longopts));
                if (is_null($match)) {
                    break;
                }
                $value = $pos === false ? null : substr($argv[$i], $pos + 1);
                $type = strlen(substr($match, strlen($opt)));
                
                php_compat_getopt_helper($opt, $value, $type, $output);
                break;
            case $argv[$i][0] == '-' && preg_match('#' . preg_quote($argv[$i][1], '#') . '(:{0,2})#', $options, $matches):
                $opt = $argv[$i][1];
                $type = strlen($matches[1]);
                $value = substr($argv[$i], 2);
                if ($type == 0) {
                    if ($value) {
                        $argv[$i] = '-' . $value;
                        --$i;
                    }
                    $value = null;
                }
                
                php_compat_getopt_helper($opt, $value, $type, $output);
                break;
            case !empty($opt):
                if (isset($output[$opt]) && is_array($output[$opt])) {
                    $output[$opt][count($output[$opt]) - 1] = $argv[$i];
                } else {
                    $output[$opt] = $argv[$i];
                }
                $opt = '';
        }
        
        if (!empty($value) || $type == PHP_COMPAT_GETOPT_NO_VALUE) {
            $opt = '';
        }
    }
    
    return $output;
}

function php_compat_getopt_helper($opt, $value, $type, &$output) {
    switch ($type) {
        case PHP_COMPAT_GETOPT_NO_VALUE:
            switch (true) {
                case !isset($output[$opt]):
                    $output[$opt] = false;
                    break;
                case is_array($output[$opt]):
                    $output[$opt][] = false;
                    break;
                default:
                    $output[$opt] = array($output[$opt], false);
            }
            break;
        case PHP_COMPAT_GETOPT_VALUE_REQUIRED:
            if (!empty($value)) {
                switch (true) {
                    case !isset($output[$opt]):
                        $output[$opt] = $value;
                        break;
                    case is_array($output[$opt]):
                        $output[$opt][] = $value;
                        break;
                    default:
                        $output[$opt] = array($output[$opt], $value);
                }
            }
            break;
        case PHP_COMPAT_GETOPT_VALUE_OPTIONAL:
            $value = !empty($value) ? $value : false;
            switch (true) {
                case !isset($output[$opt]):
                    $output[$opt] = $value;
                    break;
                case is_array($output[$opt]):
                    $output[$opt][] = $value;
                    break;
                default:
                    $output[$opt] = array($output[$opt], $value);
            }
    }
}

// Define
if (!function_exists('getopt')) {
    function getopt($options, $longopts = NULL) {
        return php_compat_getopt($options, $longopts);
    }
}
?>