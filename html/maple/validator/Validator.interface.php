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
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Validator.interface.php,v 1.2 2008/03/14 08:46:30 Ryuji.M Exp $
 */

/**
 * Validatorのインタフェースを規定するクラス
 *
 * @package     Maple.validator
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Validator
{
	var $_keys = null;
	
    /**
     * Validator特有の処理を実装する
     *
     * @param   mixed   $attributes チェックする値(配列の場合あり)
     * @param   string  $errStr エラー文字列
     * @param   array   $params チェック時に使用する引数(使用しない場合もあり)
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     * @since   3.0.0
     */
    function validate($attributes, $errStr, $params)
    {
        $log =& LogFactory::getLog();
        $log->fatal("Validatorでvalidate関数が作成されていません。", "Validator#validate");
        exit;
    }
    
    /**
     * Key配列 or Key文字列をセットする
     * @param  mixed  $keys  Key値(配列の場合あり)
     * @access  public
     */
    function setKeys($keys)
    {
        $this->_keys = $keys;
    }
    
    /**
     * Key配列 or Key文字列をゲットする
     *
     * @param   int    番号指定
     * @return  mixed  $keys  Key値(配列の場合あり)
     * @access  public
     */
    function getKeys($key_num = null)
    {
    	if($key_num == null || !isset($this->_keys[$key_num])) {
        	return $this->_keys;
    	} else {
    		return $this->_keys[$key_num];
    	}
    }
}
?>
