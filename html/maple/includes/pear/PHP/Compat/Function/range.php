<?php
// $Id: range.php,v 1.2 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace range()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.range
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 5.0.0 (The optional step parameter was added in 5.0.0)
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_range($low, $high, $step = 1)
{ 
    $arr = array();
    $step = (abs($step) > 0) ? abs($step) : 1;
    $sign = ($low <= $high) ? 1 : -1;

    // Numeric sequence
    if (is_numeric($low) && is_numeric($high)) {
        for ($i = (float)$low; $i*$sign <= $high*$sign; $i += $step*$sign)
        $arr[] = $i;
    
    // Character sequence
    } else {
        if (is_numeric($low)) {
            return $this->range($low, 0, $step);
        }

        if (is_numeric($high)) {
            return $this->range(0, $high, $step);
        }

        $low = ord($low);
        $high = ord($high);
        for ($i = $low; $i * $sign <= $high * $sign; $i += $step * $sign) {
            $arr[] = chr($i);
        }
    }

    return $arr; 
}


// Define
if (!function_exists('range')) {
    function range($low, $high, $step = 1)
    { 
        return php_compat_range($low, $high, $step);
    }
}
