<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * $_FILESで受け取った値を格納する
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class FileExtra
{   
    /**
     * @var $_FILESで受け取った値を保持する
     *
     * @access  private
     */
    var $_params = null;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function FileExtra()
    {
        if (!empty($_FILES)) {
        	$this->_params = $_FILES;
        }
    }

    /**
     * $_FILESの値を返却
     *
     * @param   string  $key    パラメータ名
     * @return  array  パラメータの値
     * @access  public
     */
    function getParameter($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        return null;
    }

    /**
     * $_FILESの値を返却(オブジェクトを返却)
     *
     * @param   string  $key    パラメータ名
     * @return  Object  パラメータの値
     * @access  public
     */
    function &getParameterRef($key)
    {
    	$ret = null;
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        return $ret;
    }

    /**
     * $_FILESの値をセット
     *
     * @param   string  $key    パラメータ名
     * @param   array  $value  パラメータの値
     * @access  public
     */
    function setParameter($key, $value)
    {
    	$this->_params[$key] = $value;
    }

    /**
     * $_FILESの値をセット(オブジェクトをセット)
     *
     * @param   string  $key    パラメータ名
     * @param   Object  $value  パラメータの値
     * @access  public
     */
    function setParameterRef($key, &$value)
    {
    	$this->_params[$key] =& $value;
    }

    /**
     * $_FILESの値を返却(配列で返却)
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値(配列)
     * @access  public
     */
    function getParameters()
    {
        return $this->_params;
    }
    
     /**
     * $_FILESの値をすべて削除
     * @param string $name
     * @access  public
     */
    function removeParameter($name)
    {
        unset($this->_params[$name]);
        $this->_params[$name] = null;
    }
}
?>
