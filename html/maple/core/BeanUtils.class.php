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
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: BeanUtils.class.php,v 1.2 2006/07/03 01:16:16 Ryuji.M Exp $
 */

/**
 * 渡されたクラスに属性を設定する
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class BeanUtils
{
    var $_pool = array();

    /**
     * 指定されたクラスから属性を取得
     *
     * @param   Object  $instance   属性をセットするクラスのインスタンス
     * @return  array   属性の値(配列)
     * @access  public
     * @since   3.0.0
     */
    function getAttributes(&$instance)
    {
        if (!is_object($instance)) {
            return;
        }

        $getterVars = BeanUtils::getGetterVars($instance);

        $attributes = array();

        foreach ($getterVars as $key => $value) {
            $method = "get${key}";
            $attributes[$key] =& $instance->$method();
        }

        return $attributes;
    }

    /**
     * 渡されたクラスに属性をセット
     * 
     * @param   Object  $instance   クラスのインスタンス
     * @param   array   $attributes 属性の値(配列)
     * @param   boolean $nullCheck  上書き時にnullかどうかをチェックするか？
     * @access  public
     * @since   3.0.0
     * @author  Hawk
     * @static
     */
    function setAttributes(&$instance, $attributes, $checkNull = false)
    {
        if (!is_object($instance) ||
            !is_array($attributes) ||(count($attributes) < 1)) {
            return;
        }

        $classVars = get_class_vars(get_class($instance));

        foreach ($attributes as $name => $value) {
            if (preg_match('/^_/', $name) ||
                !array_key_exists($name, $classVars)) {
                continue;
            }

            $setter = "set" . ucfirst($name);
            if (method_exists($instance, $setter)) {
                $instance->$setter($attributes[$name]);
            } elseif (!$checkNull ||
                      ($checkNull && (is_null($instance->$name)))) {
                if (is_object($value)) {
                    $instance->$name =& $attributes[$name];
                } else {
                    $instance->$name = $attributes[$name];
                }
            }
        }
    }

    /**
     * 渡されたクラスの変数を返却
     *
     * @param   Object  $instance   クラスのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function getVars(&$instance)
    {
        $varArray = array();

        foreach (get_class_vars(get_class($instance)) as $key => $value) {
            $varArray[strtolower($key)] = true;
        }

        return $varArray;
    }

    /**
     * 渡されたクラスのgetterがある変数を返却
     *
     * @param   Object  $instance   クラスのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function getGetterVars(&$instance)
    {
        return BeanUtils::getMethods($instance, "get");
    }

    /**
     * 渡されたクラスのsetterがある変数を返却
     *
     * @param   Object  $instance   クラスのインスタンス
     * @access  public
     * @since   3.0.0
     */
    function getSetterVars(&$instance)
    {
        return BeanUtils::getMethods($instance, "set");
    }

    /**
     * 指定された文字で始まるメソッド名のリストを返却
     *
     * @param   Object  $instanse   クラスのインスタンス
     * @param   string  $prefix     取得するメソッド名のプレフィックス
     * @return  array   メソッド名のリスト
     * @access  public
     * @static
     */
    function getMethods(&$instance, $prefix)
    {
        $vars = BeanUtils::getVars($instance);

        $methods = array();

        foreach (get_class_methods(get_class($instance)) as $method) {
            if (preg_match("/^${prefix}/", $method)) {
                $attribute = strtolower(preg_replace("/^${prefix}/", "", $method));
                if (isset($vars[$attribute])) {
                    $methods[$attribute] = true;
                }
            }
        }

        return $methods;
    }

    /**
     * オブジェクトを配列化する(合わせてエスケープ処理をする)
     * 
     * @param array $values 対象となる配列
     * @param array $result 処理結果
     * @param array $parent 親クラス
     */
    function toArray(&$values, &$result, &$parent, $escape = true)
    {
        foreach ($values as $key => $value) {
            if (preg_match('/^_/', $key)) {
                continue;
            }

            if (is_object($parent)) {
                $getter = "get" . ucfirst($key);
                if (method_exists($parent, $getter)) {
                    $value = $parent->$getter();
                }
            }

            if (is_object($value)) {
                $class = get_class($value);
                if (array_key_exists($class, $this->_pool)) {
                    if (array_key_exists($key, $this->_pool[$class])) {
                        if (is_object($result)) {
                            unset($result->$key);
                        } else {
                            unset($result[$key]);
                        }
                        continue;
                    }
                }
                $this->_pool[$class][$key] = $key;
                $result[$key] = array();
                $this->toArray(get_object_vars($value), $result[$key], $value, $escape);
            } else if (is_array($value)) {
                $dummy = null;
                $result[$key] = array();
                $this->toArray($value, $result[$key], $dummy, $escape);
            } else {
            	// リソースの場合エラーが出るため文字列のときのみ実施
                if ($escape && is_string($value)) {
                    $value = htmlspecialchars($value, ENT_QUOTES);
                }
                $result[$key] = $value;
            }
        }
    }
}
?>
