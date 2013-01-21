<?php
//
//
// $Id: ConverterManager.class.php,v 1.2 2006/09/29 06:16:28 Ryuji.M Exp $
//

require_once 'converter/Converter.interface.php';

/**
 * Converterを管理するクラス
 *
 *
 * @author	Ryuji Masukawa
 * @package	nc.util
 */
class ConverterManager
{
    /**
     * @var Converterを保持する
     *
     * @access  private
     */
    var $_list;

    /**
     * コンストラクター
     *
     * @access  public
     */
    function ConverterManager()
    {
        $this->_list = array();
    }

    /**
     * Convertを行う
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  public
     */
    function execute($params)
    {
        if (!is_array($params) || (count($params) < 1)) {
            return true;
        }

        // ConverterのListを生成
        $this->_buildConverterList($params);

        //
        // Convertを実行
        //
        $this->_convert($params);

        return true;
    }

    /**
     * ConverterのListを生成
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  private
     */
    function _buildConverterList($params)
    {
        $log =& LogFactory::getLog();

        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace("/\s+/", "", $value);

            if ($key == "") {
                $log->error("Converterの指定が不正です", "ConverterManager#_buildConverterList");
                continue;
            }
            
            //
			// $key は attribute.name のパターン
			//      又は、attribute.module_name.name のパターン
			//
			$keyArray = explode(".", $key);
			$keyCount = count($keyArray);
			if ($keyCount < 2 && $keyCount > 3) {
				//attribute.name or attribute.module_name.nameでなければ次へ
				break;
			}
			
			for($i=0; $i<$keyCount; $i++) {
				if($i==0) {
					$attribute = $keyArray[$i]; // 属性の名前
					$module_name = "";
				} else if($i == $keyCount - 1) {
					$name = $keyArray[$i];
				}else {
					$module_name = strtolower($keyArray[$i]); // モジュールの名前
				}
			}

            //$name      = strtolower($name);
			
			if($module_name == "") {
            	$className = "Converter_" . ucfirst($name);
            	$filename  = CONVERTER_DIR . "/${className}.class.php";
			} else {
				$className = "Validator_" . ucfirst($module_name) . "_" . ucfirst($name);
				$filename  = MODULE_DIR. "/${module_name}" . CONVERTER_DIR_NAME . "/${className}.class.php";	
			}
			

            if (!(@include_once $filename) or !class_exists($className)) {
                $log->error("存在していないConverterが指定されています(${name})", "ConverterManager#_buildConverterList");
                continue;
            }
            
            $name = ($module_name != "") ? $module_name . "." . $name : $name;

            //
            // 既に同名のConverterが追加されていたら何もしない
            //
            if (isset($this->_list[$name]) && is_object($this->_list[$name])) {
                continue;
            }

            //
            // オブジェクトの生成に失敗していたらエラー
            //
            $converter =& new $className();

            if (!is_object($converter)) {
                $log->error("Convererの生成に失敗しました(${name})", "ConverterManager#_buildConverterList");
                return false;
            }

            $this->_list[$name] =& $converter;
        }
    }

    /**
     * Converterを実行
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  private
     */
    function _convert($params)
    {
        $log =& LogFactory::getLog();

        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace("/\s+/", "", $value);

            if ($key == "") {
                $log->error("Converterの指定が不正です", "ConverterManager#_convert");
                continue;
            }
            
            //
			// $key は attribute.name のパターン
			//      又は、attribute.module_name.name のパターン
			//
			$keyArray = explode(".", $key);
			$keyCount = count($keyArray);
			if ($keyCount < 2 && $keyCount > 3) {
				//attribute.name or attribute.module_name.nameでなければ次へ
				break;
			}
			
			for($i=0; $i<$keyCount; $i++) {
				if($i==0) {
					$attribute = $keyArray[$i]; // 属性の名前
					$module_name = "";
				} else if($i == $keyCount - 1) {
					$name = $keyArray[$i];
				}else {
					$module_name = strtolower($keyArray[$i]); // モジュールの名前
				}
			}

            //$name      = strtolower($name);
			
			$name = ($module_name != "") ? $module_name . "." . $name : $name;
            //
            // $value にはConvert後の値を入れる変数名がセットできる
            //
            $newAttribute = $value;

            //
            // Converterを取得
            //
            $converter =& $this->_list[$name];

            if (!is_object($converter)) {
                continue;
            }

            //
            // attributeに * が指定されている場合は
            // リクエストパラメータ全てが変換対象となる
            //
            $container =& DIContainerFactory::getContainer();
            $request =& $container->getComponent("Request");

            if ($attribute == '*') {
                $attribute = join(",", array_keys($request->getParameters()));
            }

            if (preg_match("/,/", $attribute)) {
                $attributes = array();
                foreach (explode(",", $attribute) as $param) {
                    if ($param) {
                       $attributes[$param] = $request->getParameter($param);
                    }
                }
            } else {
                $attributes = $request->getParameter($attribute);
            }

            //
            // Converterを適用
            //
            $result = $converter->convert($attributes);

            if ($newAttribute != "") {
                $request->setParameter($newAttribute, $result);
            } else {
                if (is_array($attributes)) {
                    foreach ($result as $key => $value) {
                        if ($key) {
                            $request->setParameter($key, $value);
                        }
                    }
                } else {
                    $request->setParameter($attribute, $result);
                }
            }
        }
    }
}
?>
