<?php
include_once MAPLE_DIR.'/includes/pear/Crypt/RSA.php';
/**
 * 認証クラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Encryption_View {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	var $_container = null;

	// 有効期限（日）
	var $expiration_day = 365;

	// キーの長さ
	var $key_length = 1024;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Encryption_View() {
		$this->_container =& DIContainerFactory::getContainer();
		//DBオブジェクト取得
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 * パブリックキー取得
	 * @return string $public_key
	 * @access	public
	 */
	function getPublickey($expiration_time = null)
	{
		if($expiration_time != null) {
			$where_params = array(
				"expiration_time" => $expiration_time
			);
		} else {
			$where_params = array();
		}
		$order_params = array(
			"expiration_time" => "DESC"
		);
		// 有効期限がいついつの公開鍵を取得
		$result = $this->_db->selectExecute("encryption", $where_params, $order_params, 1);
		if ($result === false) {
	       	return $result;
		}
		if(!isset($result[0])) {
			// エラー
			// 設定有効期限での公開鍵は存在しない
			// ダミーで適当に公開鍵を返しておく
			$key_pair = new Crypt_RSA_KeyPair($this->key_length);
			$public_key = $key_pair->getPublicKey();
		} else {
			$public_key = $result[0]['public_key'];
		}
		return $public_key;
	}

	/**
	 * プライベートキー取得時使用
	 * @return array
	 * @access	public
	 */
	function &getEncryptionKeys()
	{
		// 有効期限が切れてないものを取得
		$int_time = mktime(date("H"), date("i"), date("s"), date("m"), date("d") - $this->expiration_day, date("Y"));
		$time = date("YmdHis", $int_time);
		$where_params = array(
			"expiration_time >= ".$time => null
		);

		$result = $this->_db->selectExecute("encryption", $where_params, null, 1);
		if ($result === false) {
	       	return $result;
		}
		if(!isset($result[0])) {
			// 有効期限が切れている or 新規作成
			$key_pair = new Crypt_RSA_KeyPair($this->key_length);
			$public_key = $key_pair->getPublicKey();
			$private_key = $key_pair->getPrivateKey();

			//insert
			$update_time = timezone_date();
			$container =& DIContainerFactory::getContainer();
	        $session =& $container->getComponent("Session");
			$user_id = $session->getParameter("_user_id");
			$int_time = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $this->expiration_day, date("Y"));
			$time = date("YmdHis", $int_time);
			$params = array(
				"public_key" => $public_key->toString(),
				"private_key" => $private_key->toString(),
				"key_length" => $this->key_length,
				"expiration_time" => $time,
				"update_time" => $update_time,
				"update_user" => $user_id
			);

			$result = $this->_db->insertExecute("encryption", $params, false);
        	if ($result === false) {
	       		return $result;
			}
		} else {
			$params = $result[0];
			//$private_key = $result[0]['private_key'];
		}
		return $params;
	}

	function encrypt($plain_text,$private_key)
	{
	    $key = Crypt_RSA_Key::fromString($private_key);
	    $this->_check_error($key);
	    $rsa_obj = new Crypt_RSA;
	    $this->_check_error($rsa_obj);

	    $enc_text = $rsa_obj->encrypt($plain_text, $key);
	    $this->_check_error($rsa_obj);

	    return $enc_text;
	}

	function decrypt($enc_text,$public_key)
	{
	    $key = Crypt_RSA_Key::fromString($public_key);
	    $this->_check_error($key);
	    $rsa_obj = new Crypt_RSA;
	    $this->_check_error($rsa_obj);
	    $rsa_obj->setParams(array('dec_key' => $key));
	    $this->_check_error($rsa_obj);

	    $plain_text = $rsa_obj->decrypt($enc_text);
	    $this->_check_error($rsa_obj);

	    return $plain_text;
	}

	//
	// error handler(暫定版)
	//
	function _check_error(&$obj)
	{
	    if ($obj->isError()) {
	        $error = $obj->getLastError();
	        switch ($error->getCode()) {
	        case CRYPT_RSA_ERROR_WRONG_TAIL :
	            // nothing to do
	            break;
	        default:
	            // echo error message and exit
	            echo 'error: ', $error->getMessage();
	            exit;
	        }
	    }
	}

	//
	// 他サイトから取得する場合の文字列作
	// 現状、未使用
	//
	function getRedirectUrl() {
		$session =& $this->_container->getComponent("Session");

		$url_parameters = "";
		$user_id = $this->_session->getParameter("_login_id");
		$auth_id = $this->_session->getParameter("_user_auth_id");
		$date = timezone_date();
		$token = $this->_randomkeys(8);

		$url_parameters .= "&_user_id=".$user_id;
		$url_parameters .= "&_auth_id=".$auth_id;
		//時間付与
		$url_parameters .=  "&_ts=".$date;
		//TODO:ランダム文字列付与 現状、8、後にdefeine等に移動
		$url_parameters .= "&_token=".$token;

		//暗号化
		$encryption_param = md5($user_id.":".$auth_id.":".$date.":".$token);
		$private_key = $this->getPrivatekey();

		$sig = $this->encrypt($encryption_param,$private_key);
		$url_parameters .= "&_sig=".$sig;

		//redirect_urlを付与
		$url_parameters .= "&_redirect_url=".BASE_URL.INDEX_FILE_NAME;

		return $url_parameters;
	}

	//
	// 乱数発生関数
	// 現状、未使用
	//
	function _randomkeys($length)
	{
		$key = "";
		$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
   		for($i=0;$i<$length;$i++){
     		$key .= $pattern{rand(0,35)};
   		}
		return $key;
	}
}
?>