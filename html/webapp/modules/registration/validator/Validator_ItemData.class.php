<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 入力項目チェックバリデータクラス
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Validator_ItemData extends Validator
{
	/**
	 * 入力項目チェックバリデータ
	 *
	 * @param mixed $attributes チェックする値
	 * @param string $errStr エラー文字列
	 * @param array $params オプション引数
	 * @return string エラー文字列(エラーの場合)
	 * @access public
	 */
	function validate($attributes, $errStr, $params)
	{
		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$actionChain =& $container->getComponent("ActionChain");
		$actionName =& $actionChain->getCurActionName();

		if ($actionName == "registration_action_main_confirm") {
			$fileUpload =& $container->getComponent("FileUpload");
			$commonMain =& $container->getComponent("commonMain");
			$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
			$files = $uploadsAction->uploads();
			$errormes = $fileUpload->getErrorMes();

			foreach(array_keys($files) as $itemID) {
				$attributes["item_data_values"][$itemID] = $files[$itemID];
			}
			foreach(array_keys($errormes) as $itemID) {
				if (empty($itemID)) {
					continue;
				}
				$attributes["item_data_values"][$itemID]["error_mes"] = $errormes[$itemID];
			}
		} else {
			$entryDatas = $session->getParameter("registration_entry_datas". $attributes["block_id"]);

			foreach($entryDatas as $entryData) {
				$itemID = $entryData["item_id"];
				$attributes["item_data_values"][$itemID] = $entryData["item_data_value"];
			}
		}

		$filterChain =& $container->getComponent("FilterChain");
		$smartyAssign =& $filterChain->getFilterByName("SmartyAssign");
		$registrationView =& $container->getComponent("registrationView");

		$items = $registrationView->getItems();
		if ($items === false) {
			$errStr = $smartyAssign->getLang("_invalid_input");
			return $errStr;
		}

		$mobileFlag = $session->getParameter("_mobile_flag");
		$errorElementID = "";
		$itemDataValues = $attributes["item_data_values"];
		$errors = array();
		$entryDatas = array();
		foreach($items as $item) {
			$itemID = $item["item_id"];

			if (!$mobileFlag
				&& empty($errors)) {
				$errorElementID = "registration_item_data_value". $itemID;

				if ($item["item_type"] == REGISTRATION_TYPE_CHECKBOX
						|| $item["item_type"] == REGISTRATION_TYPE_RADIO) {
					$errorElementID .= "_1";
				}
			}

			$entryDatas[$itemID]["item_id"] = $item["item_id"];
			$entryDatas[$itemID]["item_type"] = $item["item_type"];
			$entryDatas[$itemID]["item_data_value"] = "";
			if (isset($itemDataValues[$itemID])) {
				$entryDatas[$itemID]["item_data_value"]	= $itemDataValues[$itemID];
			}
			if ($item["item_type"] == REGISTRATION_TYPE_CHECKBOX
					|| $item["item_type"] == REGISTRATION_TYPE_RADIO) {
				$entryDatas[$itemID]["checkIterations"] = array();
			}
			if ($item["item_type"] == REGISTRATION_TYPE_CHECKBOX
					&& !empty($entryDatas[$itemID]["item_data_value"])) {
				$entryDatas[$itemID]["checkIterations"] = array_keys($entryDatas[$itemID]["item_data_value"]);
			}
			if ($item["item_type"] == REGISTRATION_TYPE_RADIO) {
				$checkIteration = array_search($entryDatas[$itemID]["item_data_value"], $item["option_values"]);
				if ($checkIteration !== false) {
					$entryDatas[$itemID]["checkIterations"][] = $checkIteration + 1;
				}
			}
			if ($item["item_type"] == REGISTRATION_TYPE_TEXTAREA) {
				$itemDataValues[$itemID] = preg_replace("/\r\n/", "\n", $itemDataValues[$itemID]);
				$entryDatas[$itemID]["item_data_value"] = $itemDataValues[$itemID];
			}
			if ($item["item_type"] == REGISTRATION_TYPE_FILE) {
				if ($mobileFlag
					&& !empty($entryDatas[$itemID]['item_data_value']['file_name'])) {
					$entryDatas[$itemID]['item_data_value']['file_name'] = mb_convert_encoding($entryDatas[$itemID]['item_data_value']['file_name'], 'utf-8', 'auto');
				}

				if (empty($itemDataValues[$itemID]['error_mes'])
						&& $mobileFlag
						&& $item['require_flag'] == _ON
						&& !is_array($itemDataValues[$itemID])
						&& $itemDataValues[$itemID] != '') {
					$errors[] = $smartyAssign->getLang("registration_mobile_unsupported_file");

					continue;
				}

				if (empty($itemDataValues[$itemID]["error_mes"])) {
					continue;
				}

				if ($itemDataValues[$itemID]["error_mes"] != _FILE_UPLOAD_ERR_UPLOAD_NOFILE
						|| ($item["require_flag"] == _ON
							&& $itemDataValues[$itemID]["error_mes"] == _FILE_UPLOAD_ERR_UPLOAD_NOFILE)) {
					$errors[] = $itemDataValues[$itemID]["error_mes"];
				}

				continue;
			}

			if ($item["require_flag"] == _ON
					&& (!isset($itemDataValues[$itemID])
						|| $itemDataValues[$itemID] == '')) {
				$errors[] = sprintf($smartyAssign->getLang("_required"), $item["item_name"]);
				continue;
			}

			if ($item["item_type"] != REGISTRATION_TYPE_EMAIL) {
				continue;
			}

			if ($item["require_flag"] == _ON
					&& empty($itemDataValues[$itemID]["first"])) {
				$errors[] = sprintf($smartyAssign->getLang("_required"), $item["item_name"]);
				continue;
			}

			if ($itemDataValues[$itemID]["first"] != $itemDataValues[$itemID]["second"]) {
				$errors[] = sprintf($smartyAssign->getLang("registration_second_email_invalid"), $item["item_name"]);
				continue;
			}

			if (empty($itemDataValues[$itemID]["first"])) {
				continue;
			}

			if (!preg_match("/^[a-zA-Z0-9\"\._\?\+\/-]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/", $itemDataValues[$itemID]["first"])) {
				$errors[] = sprintf($smartyAssign->getLang("registration_email_invalid"), $item["item_name"]);
			}
		}

		if (!empty($errorElementID)) {
			 $errorElementID = $errorElementID. REGISTRATION_ERROR_SEPARATOR;
		}
		if (!empty($errors)) {
			$errStr = $errorElementID. implode("<br />", $errors);
			return $errStr;
		}

		$session->setParameter("registration_entry_datas". $attributes["block_id"], $entryDatas);
		$request =& $container->getComponent("Request");
		$request->setParameter("items", $items);
		$request->setParameter("item_data_values", $itemDataValues);

		return;
	}
}?>