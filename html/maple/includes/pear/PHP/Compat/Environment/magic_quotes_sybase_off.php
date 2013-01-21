<?php
// $Id: magic_quotes_sybase_off.php,v 1.5 2008/11/01 20:15:11 arpad Exp $


/**
 * Emulate enviroment magic_quotes_sybase=off
 *
 * See _magic_quotes_inputs.php for more details.
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/manual/en/ref.sybase.php#ini.magic-quotes-sybase
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.5 $
 */
function php_compat_magic_quotes_sybase_off()
{
    $stripping = true;
    require 'PHP/Compat/Environment/_magic_quotes_inputs.php';
    
    $compatMagicOn = !empty($GLOBALS['__PHP_Compat_ini']['magic_quotes_gpc']);
    $magicOn = get_magic_quotes_gpc() || $compatMagicOn;
    $allWorks = $allWorks || $compatMagicOn;
    $compatSybaseOn = !empty($GLOBALS['__PHP_Compat_ini']['magic_quotes_sybase']);
    $sybaseOn = ini_get('magic_quotes_sybase') || $compatSybaseOn;

    if (!$allWorks && $magicOn) {
        $inputCount = count($inputs);
        while (list($k, $v) = each($inputs)) {
            foreach ($v as $var => $value) {
                $isArray = is_array($value);
                $order1 = $k < $inputCount;
                $stripKeys = $magicOn
                     ? ($isArray
                        ? !$allWorks && !$order1
                        : $order1 && !$phpLt50 || !$phpLt434)
                     : !$phpLt50 && $phpLt51 && !$isArray;
                if ($stripKeys) {
                    $tvar = str_replace('\'\'', '\'', $var);
                    if ($var != $tvar) {
                        $tvalue = $inputs[$k][$var];
                        $inputs[$k][$tvar] = $tvalue;
                        unset($inputs[$k][$var]);
                        $var = $tvar;
                    }
                }
                if ($isArray) {
                    $inputs[] = &$inputs[$k][$var];
                } else {
                    $inputs[$k][$var] = $sybaseOn ? str_replace('\'\'', '\'', $value) : $value;
                }
            }
        }
    }
}

php_compat_magic_quotes_sybase_off();
   
// Register the change
ini_set('magic_quotes_sybase', 0);
$GLOBALS['__PHP_Compat_ini']['magic_quotes_sybase'] = false;
