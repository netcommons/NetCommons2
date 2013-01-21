<?php
// $Id: var_export.php,v 1.19 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace var_export()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.var_export
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.19 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_var_export($var, $return = false, $level = 0, $inObject = false)
{
    // Init
    $indent      = '  ';
    $doublearrow = ' => ';
    $lineend     = ",\n";
    $stringdelim = '\'';
    $newline     = "\n";
    $find        = array(null, '\\', '\'');
    $replace     = array('NULL', '\\\\', '\\\'');
    $out         = '';
    
    // Indent
    $level++;
    for ($i = 1, $previndent = ''; $i < $level; $i++) {
        $previndent .= $indent;
    }

    $varType = gettype($var);

    // Handle object indentation oddity
    if ($inObject && $varType != 'object') {
        $previndent = substr($previndent, 0, -1);
    }


    // Handle each type
    switch ($varType) {
        // Array
        case 'array':
            if ($inObject) {
                $out .= $newline . $previndent;
            }
            $out .= 'array (' . $newline;
            foreach ($var as $key => $value) {
                // Key
                if (is_string($key)) {
                    // Make key safe
                    $key = str_replace($find, $replace, $key);
                    $key = $stringdelim . $key . $stringdelim;
                }
                
                // Value
                if (is_array($value)) {
                    $export = php_compat_var_export($value, true, $level);
                    $value = $newline . $previndent . $indent . $export;
                } else {
                    $value = php_compat_var_export($value, true, $level);
                }

                // Piece line together
                $out .= $previndent . $indent . $key . $doublearrow . $value . $lineend;
            }

            // End string
            $out .= $previndent . ')';
            break;

        // String
        case 'string':
            // Make the string safe
            for ($i = 0, $c = count($find); $i < $c; $i++) {
                $var = str_replace($find[$i], $replace[$i], $var);
            }
            $out = $stringdelim . $var . $stringdelim;
            break;

        // Number
        case 'integer':
        case 'double':
            $out = (string) $var;
            break;
        
        // Boolean
        case 'boolean':
            $out = $var ? 'true' : 'false';
            break;

        // NULLs
        case 'NULL':
        case 'resource':
            $out = 'NULL';
            break;

        // Objects
        case 'object':
            // Start the object export
            $out = $newline . $previndent;
            $out .= get_class($var) . '::__set_state(array(' . $newline;
            // Export the object vars
            foreach(get_object_vars($var) as $key => $value) {
                $out .= $previndent . $indent . ' ' . $stringdelim . $key . $stringdelim . $doublearrow;
                $out .= php_compat_var_export($value, true, $level, true) . $lineend;
            }
            $out .= $previndent . '))';
            break;
    }

    // Method of output
    if ($return === true) {
        return $out;
    } else {
        echo $out;
    }
}


// Define
if (!function_exists('var_export')) {
    function var_export($var, $return = false)
    {
        return php_compat_var_export($var, $return);
    }
}
