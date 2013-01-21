<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * リンク必須項目チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Linklist_Validator_LinkRequired extends Validator
{
	/**
	 * リンク必須項目チェックバリデータ
	 *
	 * @param   mixed   $attributes チェックする値
	 * @param   string  $errStr     エラー文字列
	 * @param   array   $params     オプション引数
	 * @return  string  エラー文字列(エラーの場合)
	 * @access  public
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain"); 
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
			
		if (empty($attributes["automatic_check"])
				&& isset($attributes["title"])
				&& empty($attributes["title"])) {
			$errors[] = sprintf($errStr, $smartyAssign->getLang("linklist_title"));
		} 
		
		if (isset($attributes["url"])
				&& (empty($attributes["url"])
						|| $attributes["url"] == LINKLIST_DEFAULT_TITLE)) {
			$errors[] = sprintf($errStr, $smartyAssign->getLang("linklist_url"));
		} 

		if (!empty($errors)) {
			$errStr = implode("<br />", $errors);
			return $errStr;
		}

		if (!isset($attributes["url"])) {
			return;
		}

		//許可するプロトコルを読み込み
		$db =& $container->getComponent("DbObject"); 
		$sql = "SELECT protocol FROM {textarea_protocol}";
		$protocolArr = $db->execute($sql);
		if ($protocolArr === false) {
			return _INVALID_INPUT;
		}
		
		if (preg_match("/^\.\//", $attributes["url"]) || preg_match("/^\.\.\//", $attributes["url"])) {
			return;
		}
		foreach ($protocolArr as $i=>$protocol) {
			if (preg_match("/^" . $protocol["protocol"] . "/", $attributes["url"])) {
				return;
			}
		}
		return _INVALID_INPUT;
	}
}
?>