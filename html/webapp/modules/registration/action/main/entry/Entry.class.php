<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * データ入力確定アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_Action_Main_Entry extends Action
{
    // 使用コンポーネントを受け取るため
    var $registrationAction = null;
	var $request = null;

	/**
	 * データ入力確定アクション
	 *
	 * @access  public
	 */
	function execute()
	{
		if (!$this->registrationAction->setData()) {
			return "error";
		}
		
		$this->request->setParameter("accept", _ON); 
		
		return "success";
	}
}
?>
