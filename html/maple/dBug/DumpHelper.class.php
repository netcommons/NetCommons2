<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: DumpHelper.class.php,v 1.2 2006/05/23 05:17:32 Ryuji.M Exp $
 */

/**
 * 配列・オブジェクトから循環参照を取り除くためのクラス
 * 
 * @package     Maple
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.1
 */
class DumpHelper
{
    
    /**
     * @var  array  $_refStack  参照を格納するスタック
     */
    var $_refStack;
    
    /**
     * @var  boolean  $_isPHP5  PHP5か否か
     */
    var $_isPHP5;
    

    /**
     * コンストラクタ
     * 
     * @access public
     */
    function DumpHelper()
    {
        $this->_isPHP5 = version_compare(phpversion(), '5', '>=');
        $this->reset();
    }
    
    /**
     * SimpleTest 
     * http://www.lastcraft.com/unit_test_documentation.php
     * 
     * assertReference が内部で用いている
     * SimpleTestCompatibility::isReference より
     * 
     * @access public
     * @param mixed
     * @param mixed
     * @return boolean
     */
    function isReference(&$first, &$second)
    {
        if ($this->_isPHP5 && is_object($first)) {
            return ($first === $second);
        }
        $temp = $first;
        $first = uniqid("dumphelper");
        $is_ref = ($first === $second);
        $first = $temp;
        return $is_ref;
    }
    
    /**
     * $var から循環参照を取り除いたものを返す
     * 
     * @access public
     * @param mixed
     * @return mixed
     */
    function removeCircularReference(&$var,$key=null)
    {
        $result = null;

        if(is_object($var)) {
        	//省略配列
        	$skip_array = array("_db","db","_container","container");
        	if($key!=null && array_search($key,$skip_array) !== false) {
        		//省略
        		$result = $this->_getSubstituteFor($var);
        	}
            elseif($this->_push($var)) {
            	//空の場合、エラーとなるので修正
				if($this->_isPHP5) {
					$result = $this->_cloneCreateObject($var);
				} else
					$result = $var;
                //$result = $this->_isPHP5 ? clone($var) : $var;
                //$skip_array = array("_db","db","_container","container");
                
                foreach(array_keys(get_object_vars($var)) as $k) {
                	$tmp = $this->removeCircularReference($var->$k,$k);
                    $result->$k =& $tmp;
                    unset($tmp);
                }
                $this->_pop();
                
            } else {
                $result = $this->_getSubstituteFor($var);
            }
        } elseif(is_array($var)) {
            if($this->_push($var)) {
                $result = array();
                
                foreach(array_keys($var) as $k) {
                    $result[$k] = $this->removeCircularReference($var[$k]);
                }
                $this->_pop();
            } else {
                $result = $this->_getSubstituteFor($var);
            }
        } else {
            $result = $var;
        }
        return $result;
    }
    
    /**
     * cloneラッパー関数
     * 
     * @access protected
     * @param mixed
     * @return mixed
     */
    function _cloneCreateObject(&$var)
    {
    	$result = null;
    	$count_flag = 0;
    	foreach($var as $sub_value) {
    		$count_flag = 1;
    		break;
    	}
    	if($count_flag)
			$result = clone($var);
		return $result;
    }
    /**
     * 循環しているobject or arrayの代替表現を返す
     * 
     * @access protected
     * @param mixed
     * @return mixed
     */
    function _getSubstituteFor($var)
    {
        if(is_object($var)) {
            return '&object('. get_class($var) .')';
        } elseif(is_array($var)) {
            return '&array';
        }
        return "";
    }

    /**
     * スタックをリセットする
     * 厳密には処理が終わった時点でスタックは空になっているはずだが
     * 念のため
     * 
     * @access public
     */
    function reset()
    {
        $this->_refStack = array();
    }
    

    /**
     * 参照$varをスタックに積む
     * 既にスタック内に同一の参照が存在する場合、
     * スタックには積まずfalseを返す
     * 
     * @param  mixed $var
     * @return boolean
     */
    function _push(&$var)
    {
        $vartype = gettype($var);
        foreach($this->_refStack as $i => $v) {
            if($vartype == gettype($this->_refStack[$i]) &&
                $this->isReference($var, $this->_refStack[$i])) {
                return false;
            }
        }
        $this->_refStack[] =& $var;
        return true;
    }
    
    /**
     * スタックから参照を取り除く
     * 
     * access private
     */
    function _pop()
    {
        array_pop($this->_refStack);
    }
}

?>