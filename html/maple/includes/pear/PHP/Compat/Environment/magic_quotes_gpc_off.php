<?php
// $Id: magic_quotes_gpc_off.php,v 1.7 2007/05/07 12:28:58 arpad Exp $

/**
 * Emulate environment magic_quotes_gpc=off
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/magic_quotes
 * @author      Arpad Ray <arpad@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 */

// wrap everything in a function to keep global scope clean
function php_compat_magic_quotes_gpc_off()
{
    $stripping = true;
    require 'PHP/Compat/Environment/_magic_quotes_inputs.php';

    $compatMagicOn = !empty($GLOBALS['__PHP_Compat_ini']['magic_quotes_gpc']);
    $compatMagicOff = isset($GLOBALS['__PHP_Compat_ini']['magic_quotes_gpc'])
        && !$GLOBALS['__PHP_Compat_ini']['magic_quotes_gpc'];
    $magicOn = get_magic_quotes_gpc() || $compatMagicOn;
    $allWorks = $allWorks || $compatMagicOn;
    $compatSybaseOn = !empty($GLOBALS['__PHP_Compat_ini']['magic_quotes_sybase']);
    $sybaseOn = ini_get('magic_quotes_sybase') || $compatSybaseOn;

    if ($magicOn && !$sybaseOn || !$phpLt50 && $phpLt51) {
        $inputCount = count($inputs);
        while (list($k, $v) = each($inputs)) {
            $order1 = $k < $inputCount;
            foreach ($v as $var => $value) {
                $isArray = is_array($value);
                $stripKeys = $magicOn
                     ? ($isArray
                        ? !$allWorks && !$order1
                        : ($order1 ? !$phpLt50 : !$phpLt434))
                     : !$phpLt50 && $phpLt51 && !$isArray;
                if ($stripKeys || $compatMagicOn) {
                    $tvar = stripslashes($var);
                    if ($var != $tvar) {
                        $tvalue = $inputs[$k][$var];
                        $inputs[$k][$tvar] = $tvalue;
                        unset($inputs[$k][$var]);
                        $var = $tvar;
                    }
                }
                if (is_array($value)) {
                    $inputs[] = &$inputs[$k][$var];
                } else {
                    $inputs[$k][$var] = $magicOn ? stripslashes($value) : $value;
                }
            }
        }
    }
}

php_compat_magic_quotes_gpc_off();

// Register the change
ini_set('magic_quotes_gpc', 0);
$GLOBALS['__PHP_Compat_ini']['magic_quotes_gpc'] = false;

