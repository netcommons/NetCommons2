<?php
//
//
// $Id: ValidatorManager.class.php,v 1.24 2008/07/28 08:30:49 Ryuji.M Exp $
//

require_once VALIDATOR_DIR . '/Validator.interface.php';

/**
 * Validatorを管理するクラス
 *
 * @author	Ryuji Masukawa
 * @package	nc.util
 **/
class ValidatorManager {
	/**
	 * @var Validatorを保持する
	 *
	 * @access	private
	 **/
	var $_validators;

	/**
	 * @var 必須項目を保持する
	 *
	 * @access	private
	 **/
	var $_required;

	/**
	 * @var Validateルールを保持する
	 *
	 * @access	private
	 **/
	var $_list;

	/**
	 * @var stopperの状態を保持する
	 *
	 * @access	private
	 **/
	var $_stoppers;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function ValidatorManager() {
		$this->_validators = array();
		$this->_required   = array();
		$this->_list       = array();
	}

	/**
	 * Validateを行う
	 *
	 * @param	array	$params	Validateする条件が入った配列
	 * @access	public
	 **/
	function execute($params) {
		if (!is_array($params) || (count($params) < 1)) {
			return true;
		}

		// ValidatorのListを生成
		ValidatorManager::_buildValidatorList($params);

		// Validateを実行
		ValidatorManager::_validate($params);

		return true;
	}

	/**
	 * ValidatorのListを生成
	 *
	 * @param	array	$params	Validateする条件が入った配列
	 * @access	private
	 **/
	function _buildValidatorList($params) {
		$log =& LogFactory::getLog();
		
		$renderer =& SmartyTemplate::getInstance();
		$lang  = $renderer->get_template_vars("lang");
		$conf  = $renderer->get_template_vars("conf");
		
		foreach ($params as $key => $value) {
			$key   = preg_replace("/\s+/", "", $key);
			$value = preg_replace('/\s*,\s*/', ",", trim($value));

			if (($key == "") || ($value == "")) {
				$log->error("Validatorの指定が不正です", "ValidatorManager#_buildValidatorList");
				continue;
			}

			//
			// $key は attribute.name:group のパターン
			//      又は、attribute.module_name.name:group のパターン
			//
			$keyArray = explode(".", $key);
			$keyCount = count($keyArray);
			if ($keyCount == 1) {
				$keyArray[1] = $keyArray[0];
				$keyArray[0] = "";
				$keyCount = 2;
			}
			if ($keyCount != 2 && $keyCount != 3) {
				//attribute.name or attribute.module_name.name
				break;
			}
			//key配列でValidateするかどうか
			$keyarray = 0;
			for($i=0; $i<$keyCount; $i++) {
				if($i==0) {
					
					if (preg_match("/^key:/", $keyArray[$i])) {
						$keyArray[$i]  = preg_replace("/^key:/", "", $keyArray[$i]);
						//key配列でValidate
						$keyarray = 1;
					}
					$attribute = $keyArray[$i]; // 属性の名前
					$module_name = "";
				} else if($i == $keyCount - 1) {
					if (preg_match("/:/", $keyArray[$i])) {
						$keySubArray = explode(":", $keyArray[$i]);
						$name  = $keySubArray[0]; // Validatorの名前 
						$group = $keySubArray[1]; // ValidateGroupの名前
					} else {
						$name  = $keyArray[$i]; // Validatorの名前 
						$group = "";
					}
				}else {
					//$module_name = strtolower($keyArray[$i]); // モジュールの名前
					$module_name = $keyArray[$i];
				}
			}
			
			//$name = strtolower($name);
			
			//
			// $value は stopper,....(validateParams):errStrDef,....(Params)のパターン
			// errStr = sprintf(constant(errStrDef),Params1,Params2....)となる
			//
			$valueArray = explode(":", $value);
			if (count($valueArray) != 2) {
				break;
			}
			
			$valueSubArray = explode(",", $valueArray[0]);
			$valueMesArray = explode(",", $valueArray[1]);
			if (count($valueSubArray) < 1 || $valueMesArray < 1) {
				break;
			}
			
			$stopper = $valueSubArray[0]; // ストッパーかどうか？ vsprintf
			
			if (is_object($renderer) && preg_match("/^lang./", $valueMesArray[0])) {
				$errKey  = preg_replace("/^lang./", "", $valueMesArray[0]);
				if(isset($lang[$errKey])) {
					$errStr  =& $lang[$errKey]; // エラー文字列
				} else {
					$errStr = "Undefined";
				}
			} elseif (is_object($renderer) && preg_match("/^conf./", $valueMesArray[0])) {
				$errKey  = preg_replace("/^conf./", "", $valueMesArray[0]);
				if(isset($conf[$errKey])) {
					$errStr  =& $conf[$errKey]; // エラー文字列
				} else {
					$errStr = "Undefined";
				}
			}else {
				if(defined($valueMesArray[0])) {
					$errStr  = constant($valueMesArray[0]);
				} else {
					$errStr  = $valueMesArray[0]; // エラー文字列
				}
			}
			if (count($valueMesArray) > 1) {
				$printParams = array();
				$printParams = array_slice($valueMesArray, 1);
				$count = 0;
				foreach ($printParams as $subValue) {
					if (is_object($renderer) && preg_match("/^lang./", $subValue)) {
						$subValue  = preg_replace("/^lang./", "", $subValue);
						if(isset($lang[$subValue])) {
							$printParams[$count] =& $lang[$subValue];
						} else {
							$printParams[$count] = "Undefined";
						}
					} elseif (is_object($renderer) && preg_match("/^conf./", $subValue)) {
						$subValue  = preg_replace("/^conf./", "", $subValue);
						if(isset($conf[$subValue])) {
							$printParams[$count] =& $conf[$subValue];
						} else {
							$printParams[$count] = "Undefined";
						}
					} else {
						if(defined($subValue)) {
							$printParams[$count]  = constant($subValue); // エラー文字列
						} else {
							$printParams[$count]  = $subValue; // エラー文字列
						}
					}
					
					$count++;
				}
				$errStrPrint = vsprintf($errStr,$printParams);
			} else {
				$errStrPrint = $errStr;
			}
			//パラメーターをSmartyの定義に変換
			$validateParams = array();
			if (count($valueArray) > 1) {
				$validateParams = array_slice($valueSubArray, 1);
				foreach (array_keys($validateParams) as $i) {
					if (is_object($renderer) && preg_match("/^lang./", $validateParams[$i])) {
						$errKey  = preg_replace("/^lang./", "", $validateParams[$i]);
						if(isset($lang[$errKey])) {
							$validateParams[$i] =& $lang[$errKey]; // パラメータ文字列
						} else {
							$validateParams[$i] = "Undefined";
						}
					} elseif (is_object($renderer) && preg_match("/^conf./", $validateParams[$i])) {
						$errKey  = preg_replace("/^conf./", "", $validateParams[$i]);
						if(isset($conf[$errKey])) {
							$validateParams[$i] =& $conf[$errKey]; // パラメータ文字列
						} else {
							// 定義されていない
							$validateParams[$i] = "Undefined";
						}
					} else if(defined($validateParams[$i])) {
						$validateParams[$i] = constant($validateParams[$i]); // エラー文字列
					}
				}
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
			$name_sub = ($module_name != "") ? $module_name . "." . $name : $name;
			$validateRule = array(
				'attribute' => $attribute,
				'name'      => $name_sub,
				'keyarray'  => $keyarray,
				'stopper'   => $stopper,
				'errStr'    => $errStrPrint,
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
			if($module_name == "") {
				$className = "Validator_" . ucfirst($name);
				$filename  = VALIDATOR_DIR . "/${className}.class.php";
			} else {
				
				$className = "Validator_" . ucfirst($name);
				$filename  = MODULE_DIR. "/${module_name}" . VALIDATOR_DIR_NAME . "/${className}.class.php";
				$module_name = ucfirst($module_name);
				$className = "${module_name}_".$className;
			}
			if (!(@include_once $filename) or !class_exists($className)) {
				$log->error("存在していないValidatorが指定されています(${name}:${className}:${filename})", "ValidatorManager#_buildValidatorList");
				return false;
			}

			//
			// 既に同名のValidatorが追加されていたら何もしない
			//
			if (isset($this->_validators[$name_sub]) &&
				is_object($this->_validators[$name_sub])) {
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
			$this->_validators[$name_sub] =& $validator;
		}
	}

	/**
	 * Validateを実行
	 *
	 * @access	private
	 **/
	function _validate() {
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
	 * @param	string	$validateKey	Validateルールの名前
	 * @param	array	$validateRule	Validateルールの入った連想配列
	 * @access	private
	 **/
	function _execute($validateKey, $validateRule) {
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$errorList =& $actionChain->getCurErrorList();

		$attribute = $validateRule["attribute"];
		$name      = $validateRule["name"];
		$keyarray  = $validateRule["keyarray"];
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
		// ストップ状態になっていればチェックしない
		// 1項目のチェックでエラーが出た場合、同じ箇所を含む複数項目のチェックは行わない
		//
		$arrKey = explode(",",$validateKey);
		foreach ($arrKey as $value) {
			if (isset($this->_stoppers[$value]) &&
				($this->_stoppers[$value] == true)) {
				return false;
			}
		}
		

		//
		// リクエストパラメータを取得
		//
		$isEmpty = true;

		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");

		if (preg_match("/,/", $attribute)) {
			$attributes = array();
			$keys = array();
			foreach (explode(",", $attribute) as $key) {
				$param = $request->getParameter($key);
				if ($param != "") {
					$isEmpty = false;
				}
				if(!$keyarray) {
					$attributes[] = $param;
					$keys[] = $key;
				} else
					$attributes[$key] = $param;
			}
		} else if($attribute == "") {
			//keyが空の場合、そのまま通す
			$attributes = "";
			$keys = $attribute;
		} else {
			if($keyarray) {
				$attributes = array();
				$attributes[$attribute] = $request->getParameter($attribute);
				if ($attributes[$attribute] != "") {
					$isEmpty = false;
				}
			} else {
				$attributes = $request->getParameter($attribute);
				$keys = $attribute;
				if ($attributes != "") {
					$isEmpty = false;
				}
			}
			//if ($attributes != "") {
			//	$isEmpty = false;
			//}
		}

		//
		// 必須項目でなくて、値がはいってなければチェックしない
		//
		// 値が入っていなくても記述したバリデータは必ず通すように修正 by Ryuji Masukawa 08/03/21
		//if ($isEmpty && ($attribute!="" && !isset($this->_required[$attribute]))) {
		//	return false;
		//}

		//
		// Validateを取得
		//
		$validator =& $this->_validators[$name];
		if (!is_object($validator)) {
			return false;
		}
		//
		// Keyセット
		//
		if(isset($keys)) {
			$validator->setKeys($keys);
		}
		//
		// Validatorを適用
		//
		$result = $validator->validate($attributes, $errStr, $params);

		if ($result != "" && $result != NULL) {
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
