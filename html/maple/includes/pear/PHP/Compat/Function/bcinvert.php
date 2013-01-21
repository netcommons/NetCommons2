<?php
// $Id: bcinvert.php,v 1.4 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace bcinvert()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.bcinvert
 * @author      Sara Golemon <pollita@php.net>
 * @version     $Revision: 1.4 $
 * @since       PHP 5.2.0
 * @require     PHP 4.0.4 (call_user_func_array)
 */
function php_compat_bcinvert($a, $n)
{
    // Sanity check
    if (!is_scalar($a)) {
        user_error('bcinvert() expects parameter 1 to be string, ' .
            gettype($a) . ' given', E_USER_WARNING);
        return false;
    }

    if (!is_scalar($n)) {
        user_error('bcinvert() expects parameter 2 to be string, ' .
            gettype($n) . ' given', E_USER_WARNING);
        return false;
    }
    
    $u1 = $v2 = '1';
    $u2 = $v1 = '0';
    $u3 = $n;
    $v3 = $a;

    while (bccomp($v3, '0')) {
        $q0 = bcdiv($u3, $v3);
        $t1 = bcsub($u1, bcmul($q0, $v1));
        $t2 = bcsub($u2, bcmul($q0, $v2));
        $t3 = bcsub($u3, bcmul($q0, $v3));

        $u1 = $v1;
        $u2 = $v2;
        $u3 = $v3;

        $v1 = $t1;
        $v2 = $t2;
        $v3 = $t3;
    }

    if (bccomp($u2, '0') < 0) {
        return bcadd($u2, $n);
    } else {
        return bcmod($u2, $n);
    }    
}


// Define
if (!function_exists('bcinvert')) {
    function bcinvert($a, $n)
    {
        return php_compat_bcinvert($a, $n);
    }
}
