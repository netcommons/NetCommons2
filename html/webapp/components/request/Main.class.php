<?php
include_once MAPLE_DIR.'/includes/pear/HTTP/Request.php';
/**
 * 他サイトからのデータ取得
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Request_Main {
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Request_Main() {
	}

	/**
	 * 他サイトからデータ取得
	 * @param  string url
	 * @return string html or boolean false
	 * @access	public
	 */
	function getResponseHtml($url, $optParams=array()) {
		$params = array('method' => 'GET');
		$params = array_merge($params, $optParams);
		if (!isset($params['timeout'])) {
			$params['timeout'] = 30;
		}

		$req =& new HTTP_Request($url , $params);
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$proxyConfigs = $configView->getConfigByCatid(_SYS_CONF_MODID, _SERVER_CONF_CATID);
		if($proxyConfigs !== false) {
			if($proxyConfigs['proxy_mode']['conf_value'] == _ON) {
				$req->setProxy($proxyConfigs['proxy_host']['conf_value'], $proxyConfigs['proxy_port']['conf_value'], $proxyConfigs['proxy_user']['conf_value'], $proxyConfigs['proxy_pass']['conf_value']);
			}
		}
        //$req->addHeader('User-Agent', $this->user_agent);

        //$req->_readTimeout = array(0,1);
        //$req->_timeout = 1;
        $req->_allowRedirects = true;
		$req->sendRequest() ;
		$request_code = $req->getResponseCode();
		if( $request_code == "200" ){
			return $req->getResponseBody() ;
		} else
			return false;
	}

	/**
	 * 他サイトにデータを送信する
	 * @param  string url
	 * @return string html or boolean false
	 * @access	public
	 */
	function sendPost($url, $postData, $optParams=array()) {
		$params = array('method' => 'POST');
		$params = array_merge($params, $optParams);
		if (!isset($params['timeout'])) {
			$params['timeout'] = 30;
		}

		$req =& new HTTP_Request($url , $params);
		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$proxyConfigs = $configView->getConfigByCatid(_SYS_CONF_MODID, _SERVER_CONF_CATID);
		if($proxyConfigs !== false) {
			if($proxyConfigs['proxy_mode']['conf_value'] == _ON) {
				$req->setProxy($proxyConfigs['proxy_host']['conf_value'], $proxyConfigs['proxy_port']['conf_value'], $proxyConfigs['proxy_user']['conf_value'], $proxyConfigs['proxy_pass']['conf_value']);
			}
		}
        //$req->addHeader('User-Agent', $this->user_agent);

        //$req->_readTimeout = array(0,1);
        //$req->_timeout = 1;
        $req->_allowRedirects = true;
        $req->setMethod(HTTP_REQUEST_METHOD_POST);
        foreach ($postData as $name=>$value) {
        	$req->addPostData($name, $value);
        }
		$req->sendRequest() ;
		$request_code = $req->getResponseCode();
		if( $request_code == "200" ){
			return $req->getResponseBody() ;
		} else
			return false;
	}

	/*
	function get_http_header($target)
	{
	    // URIから各情報を取得
	    $info = (parse_url($target)) ? parse_url($target) : "";
		if (!isset($info)) {
			$ret = false;
			return $ret;
		}

		$scheme = (isset($info['scheme'])) ? $info['scheme'] : "";
		$host = (isset($info['host'])) ? $info['host'] : "";
		$port = (isset($info['port'])) ? $info['port'] : "80";		// ポートが空の時はデフォルトの80
		$path = (isset($info['path'])) ? $info['path'] : "";

	    // リクエストフィールドを制作
	    $msg_req = "HEAD " . $path . " HTTP/1.0\r\n";
	    $msg_req .= "Host: $host\r\n";
	    $msg_req .= "User-Agent: H2C/1.0\r\n";
	    $msg_req .= "\r\n";

	    // スキームがHTTPの時のみ実行
	    if ($scheme == 'http') {

	        $status = array();

	        // 指定ホストに接続。
	        if ($handle = @fsockopen($host, $port, $errno, $errstr, 1)) {

	            fputs ($handle, $msg_req);

	            if (socket_set_timeout($handle, 3)) {

	                $line = 0;
	                while(!feof($handle)) {

	                    // 1行めはステータスライン
	                    if($line==0) {
	                        $temp_stat = explode(' ', fgets($handle, 4096));
	                        $status['HTTP-Version'] = trim(array_shift($temp_stat));
	                        $status['Status-Code'] = trim(array_shift($temp_stat));
	                        $status['Reason-Phrase'] = trim(implode(' ', $temp_stat));

	                    // 2行目以降はコロンで分割してそれぞれ代入
	                    } else {
	                        $temp_stat = explode(':', fgets($handle, 4096));
	                        $name = array_shift( $temp_stat );
	                        // 通常:の後に1文字半角スペースがあるので除去
	                        $status[ $name ] = trim(substr(implode(':', $temp_stat), 1));
	                    }
	                    $line++;
	                }

	            } else {
					$status['HTTP-Version'] = '---';
					$status['Status-Code'] = '902';
					$status['Reason-Phrase'] = "No Response";
	            }
	            fclose ( $handle );

	        } else {
	            $status['HTTP-Version'] = '---';
	            $status['Status-Code'] = '901';
	            $status['Reason-Phrase'] = "Unable To Connect";
	        }

	    } else {
	        $status['HTTP-Version'] = '---';
	        $status['Status-Code'] = '903';
	        $status['Reason-Phrase'] = "Not HTTP Request";
	    }


	    return $status;
	}
	*/
}
?>
