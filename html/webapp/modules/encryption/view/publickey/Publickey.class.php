<?php

class Encryption_View_Publickey extends Action
{
	// リクエストパラメータを受け取るため
	var $encrypt_data = null;
	var $expiration_time = null;
	
	// 使用コンポーネントを受け取るため
	var $encryption=null;
	var $db = null;
	
	var $public_key = null;
	
	function execute()
	{
		if($this->encrypt_data == null) {
			// 暗号データがなければ、有効期限もnull
			$this->expiration_time = null;
		} else {
			// 暗号化データが一致する場合は、このサイトでまちがいなくバックアップをとっていることが
			// 実証される
			$where_params = array(
				"encrypt_data" => $this->encrypt_data
			);
			// 有効期限がいついつの公開鍵を取得
			$result = $this->db->selectExecute("backup_encrypt_history", $where_params, null, 1);
			if ($result === false) {
		       	return $result;
			}
			if(!isset($result[0])) {
				// データなし
				$this->expiration_time = null;
			}
			
		}
		
		$this->public_key = $this->encryption->getPublickey($this->expiration_time);
		return 'success';
	}
}
?>
