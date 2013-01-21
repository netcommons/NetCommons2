<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 登録フォーム入力画面表示アクションクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Registration_View_Edit_Registration_Entry extends Action
{
	// リクエストパラメータを受け取るため
    var $block_id = null;
    var $room_id = null;

    // 使用コンポーネントを受け取るため
    var $registrationView = null;
    var $session = null;
    var $db = null;

    // validatorから受け取るため
    var $registration = null;

    // 値をセットするため
    var $registrationNumber = null;
	var $oldRegistrations = array();

    /**
     * 登録フォーム力画面表示アクション
     *
     * @access  public
     */
    function execute()
    {
		if (!empty($this->registration["registration_id"])) {
			return "success";
		}

		$this->session->setParameter("registration_edit". $this->block_id, _ON);

		$container =& DIContainerFactory::getContainer();
		$filterChain =& $container->getComponent("FilterChain");
		$headerMenu =& $filterChain->getFilterByName("HeaderMenu");
		$headerMenu->setActive(2);

		$this->registrationNumber = $this->registrationView->getRegistrationCount();
		if ($this->registrationNumber === false) {
        	return "error";
        }
        $this->registrationNumber++;

    	//過去の登録フォーム
		$params = array($this->room_id);
		$sql = "SELECT registration_id, registration_name ".
				"FROM {registration} ".
				"WHERE room_id = ? ".
				"ORDER BY registration_id DESC";

		$this->oldRegistrations = $this->db->execute($sql, $params);
		if ($this->oldRegistrations === false) {
			$this->db->addError();
		}

		return "success";
    }
}
?>
