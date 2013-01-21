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
 * @version     CVS: $Id: ValidatorManager.class.php,v 1.3 2006/09/29 06:16:27 Ryuji.M Exp $
 */

require_once VALIDATOR_DIR . '/Validator.interface.php';

/**
 * Validatorを管理するクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class ValidatorManager
{
    /**
     * @var Validatorを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_validators;

    /**
     * @var 必須項目を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_required;

    /**
     * @var Validateルールを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_list;

    /**
     * @var stopperの状態を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_stoppers;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function ValidatorManager()
    {
        $this->_validators = array();
        $this->_required   = array();
        $this->_list       = array();
    }

    /**
     * Validateを行う
     *
     * @param   array   $params Validateする条件が入った配列
     * @access  public
     * @since   3.0.0
     */
    function execute($params)
    {
        if (!is_array($params) || (count($params) < 1)) {
            return true;
        }

        // ValidatorのListを生成
        $this->_buildValidatorList($params);

        // Validateを実行
        $this->_validate($params);

        return true;
    }

    /**
     * ValidatorのListを生成
     *
     * @param   array   $params Validateする条件が入った配列
     * @access  private
     * @since   3.0.0
     */
    function _buildValidatorList($params)
    {
        $log =& LogFactory::getLog();

        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace('/\s*,\s*/', ",", trim($value));

            if (($key == "") || ($value == "")) {
                $log->error("Validatorの指定が不正です", "ValidatorManager#_buildValidatorList");
                continue;
            }

            //
            // $key は attribute.name:group のパターン
            //
            $keyArray = explode(".", $key);
            if (count($keyArray) != 2) {
                break;
            }
            $attribute = $keyArray[0]; // 属性の名前

            if (preg_match("/:/", $keyArray[1])) {
                $keySubArray = explode(":", $keyArray[1]);
                $name  = $keySubArray[0]; // Validatorの名前 
                $group = $keySubArray[1]; // ValidateGroupの名前
            } else {
                $name  = $keyArray[1]; // Validatorの名前 
                $group = "";
            }

            //$name = strtolower($name);

            //
            // $value は stopper,errStr,....(validateParams) のパターン
            //
            $valueArray = explode(",", $value);
            if (count($valueArray) < 2) {
                break;
            }
            $stopper = $valueArray[0]; // ストッパーかどうか？
            $errStr  = $valueArray[1]; // エラー文字列
            $validateParams = array();
            if (count($valueArray) > 2) {
                $validateParams = array_slice($valueArray, 2);
            }

            //
            // 必須項目は無条件ストッパーになる
            //
            if ($name == "required") {
                $this->_required[$attribute] = true;
                $stopper = true;
            }

            //
            // ValidateRuleの組み立て
            //
            $validateRule = array(
                'attribute' => $attribute,
                'name'      => $name,
                'stopper'   => $stopper,
                'errStr'    => $errStr,
                'params'    => $validateParams,
            );

            if ($group) {
                $this->_list[$group][$attribute][] = $validateRule;
            } else {
                $this->_list[$attribute][] = $validateRule;
            }

            //
            // Validatorのファイルがあるかをチェック
            //
            $className = "Validator_" . ucfirst($name);
            $filename  = VALIDATOR_DIR . "/${className}.class.php";

            if (!(@include_once $filename) or !class_exists($className)) {
                $log->error("存在していないValidatorが指定されています(${name})", "ValidatorManager#_buildValidatorList");
                return false;
            }

            //
            // 既に同名のValidatorが追加されていたら何もしない
            //
            if (isset($this->_validators[$name]) &&
                is_object($this->_validators[$name])) {
                continue;
            }

            //
            // オブジェクトの生成に失敗していたらエラー
            //
            $validator =& new $className();

            if (!is_object($validator)) {
                $log->error("Convererの生成に失敗しました(${name})", "ValidatorManager#_buildValidatorList");
                return false;
            }

            $this->_validators[$name] =& $validator;
        }
    }

    /**
     * Validateを実行
     *
     * @access  private
     * @since   3.0.0
     */
    function _validate()
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $errorList =& $actionChain->getCurErrorList();

        foreach ($this->_list as $validateKey => $validateRules) {
            foreach ($validateRules as $value) {
                //
                // Validateルールがグルーピングされているかどうかで分岐
                //
                if (isset($value["attribute"])) {
                    if (!$this->_execute($validateKey, $value)) {
                        break;
                    }
                } else {
                    foreach ($value as $subValue) {
                        if (!$this->_execute($validateKey, $subValue)) {
                            break;
                        }
                    }
                }
            }
        }

        if ($errorList->isExists()) {
            $errorList->setType(VALIDATE_ERROR_TYPE);
        }
    }

    /**
     * Validateを実行(ルール単位)
     *
     * @param   string  $validateKey    Validateルールの名前
     * @param   array   $validateRule   Validateルールの入った連想配列
     * @access  private
     * @since   3.0.0
     */
    function _execute($validateKey, $validateRule)
    {
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $errorList =& $actionChain->getCurErrorList();

        $attribute = $validateRule["attribute"];
        $name      = $validateRule["name"];
        $stopper   = $validateRule["stopper"];
        $errStr    = $validateRule["errStr"];
        $params    = $validateRule["params"];

        //
        // ストップ状態になっていればチェックしない
        //
        if (isset($this->_stoppers[$validateKey]) &&
            ($this->_stoppers[$validateKey] == true)) {
            return false;
        }

        //
        // リクエストパラメータを取得
        //
        $isEmpty = true;

        $container =& DIContainerFactory::getContainer();
        $request =& $container->getComponent("Request");

        if (preg_match("/,/", $attribute)) {
            $attributes = array();
            foreach (explode(",", $attribute) as $key) {
                $param = $request->getParameter($key);
                if ($param != "") {
                    $isEmpty = false;
                }
                $attributes[] = $param;
            }
        } else {
            $attributes = $request->getParameter($attribute);
            if ($attributes != "") {
                $isEmpty = false;
            }
        }

        //
        // 必須項目でなくて、値がはいってなければチェックしない
        //
        if ($isEmpty && !isset($this->_required[$attribute])) {
            return false;
        }

        //
        // Validateを取得
        //
        $validator =& $this->_validators[$name];

        if (!is_object($validator)) {
            return false;
        }

        //
        // Validatorを適用
        //
        $result = $validator->validate($attributes, $errStr, $params);
        if ($result != "") {
            $errorList->add($validateKey, $result);

            //
            // ストッパーならばそのパラメータを記憶
            //
            if ($stopper) {
                $this->_stoppers[$validateKey] = true;
            }
        }

        return true;
    }
}
?>
