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
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter.interface.php,v 1.3 2006/09/29 06:16:27 Ryuji.M Exp $
 */

/**
 * Filterのインタフェースを規定するクラス
 *
 * @package     Maple.filter
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Filter
{
    /**
     * @var 必要に応じて属性を持つことができる
     *
     * @access  private
     * @since   3.0.0
     */
    var $_attributes;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Filter()
    {
        $this->_attributes = array();
    }

    /**
     * Filter特有の処理を実装する
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $log =& LogFactory::getLog();
        $log->fatal("Filterでexecute関数が作成されていません。", "Filter#execute");
        exit;
    }

    /**
     * 属性の数を返却
     *
     * @return  integer 属性の数
     * @access  public
     * @since   3.0.0
     */
    function getSize()
    {
        return count($this->_attributes);
    }

    /**
     * 指定された属性を返却
     *
     * @param   string  $key    属性名
     * @param   mixed  $default 指定された属性がない場合のデフォルト値
     * @return  string  属性の値
     * @access  public
     * @since   3.0.0
     */
    function getAttribute($key, $default = null)
    {
        if (isset($this->_attributes[$key])) {
            return $this->_attributes[$key];
        } else {
            return $default;
        }
    }

    /**
     * 指定された属性に値をセット
     *
     * @param   string  $key    属性名
     * @param   string  $value  属性の値
     * @access  public
     * @since   3.0.0
     */
    function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;
    }

    /**
     * 属性を配列で返却
     *
     * @return  array   属性の値(配列)
     * @access  public
     * @since   3.0.0
     */
    function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * 指定された属性に値をセット(配列でまとめてセット)
     *
     * @param   array   $attributes 属性の値(配列)
     * @access  public
     * @since   3.0.0
     */
    function setAttributes($attributes)
    {
        $log =& LogFactory::getLog();

        if (!is_array($attributes) || (count($attributes) < 1)) {
            $log->warn("引数が不正です", "Filter#setAttributes");
            return false;
        }

        foreach ($attributes as $key => $value) {
           $this->setAttribute($key, $value);
        }
    }
}
?>