<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * コンテンツ追加
 *
 * @package	 NetCommons
 * @author	  Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license	 http://www.netcommons.org/license.txt  NetCommons License
 * @project	 NetCommons Project, supported by National Institute of Informatics
 * @access	  public
 */
class Multidatabase_Action_Mobile_Addcontent extends Action
{
	// リクエストパラメータを受け取るため
	var $temporary = null;
	var $multidatabase_id = null;
	var $password_checkbox = null;
	var $passwords = null;

	// 使用コンポーネントを受け取るため
	var $request = null;
	var $mdbView = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
	function execute()
	{
		if (isset($this->temporary)) {
			$this->request->setParameter("temporary_flag", _ON);
		} else {
			$this->request->setParameter("temporary_flag", _OFF);
		}

		$params = array(
			"multidatabase_id" => $this->multidatabase_id
		);
		$metadatas = $this->mdbView->getMetadatas($params);
		foreach($metadatas as $metadata_id => $metadata) {
			if (empty($this->password_checkbox[$metadata_id])){
				unset($this->passwords[$metadata_id]);
			}
		}
		$this->request->setParameter("passwords", $this->passwords);

		return 'success';
	}
}
?>