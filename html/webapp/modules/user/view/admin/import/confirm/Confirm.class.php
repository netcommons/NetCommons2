<?php

/**
 * 会員管理>>インポート>>アップロード>>インポートファイルの確認結果
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class User_View_Admin_Import_Confirm extends Action
{
	// リクエストパラメータを受け取るため

	// 使用コンポーネントを受け取るため
	var $session = null;
	
	// 値をセットするため
	var $errdata = _OFF;		// 重複チェックの結果
	
	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		$errUsers = $this->session->getParameter(array("user", "import", "dispdata_err"));
		if (isset($errUsers) && is_array($errUsers)) {
			foreach ($errUsers as $errUser) {
				if ($errUser == _ON) {
					$this->errdata = _ON;
					break;
				}
			}
		}
		
		return 'success';
	}
}
?>