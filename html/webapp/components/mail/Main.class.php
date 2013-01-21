<?php
require_once(MAPLE_DIR."/includes/mail/phpmailer/class.phpmailer.php");

/**
 * メール送信コンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mail_Main
{
	/**
	 * @var	メール送信オブジェクト
	 *
	 * @access	public
	 */
	var $_mailer = null;

	/**
	 * @var	送信元
	 *
	 * @access	public
	 */
	var $fromEmail;

	/**
	 * @var	送信元名称
	 *
	 * @access	public
	 */
	var $fromName;

	/**
	 * @var	重要度
	 *
	 * @access	public
	 */
	var $priority;

	/**
	 * @var	送信ユーザオブジェクト配列
	 *
	 * @access	public
	 */
	var $toUsers;

	/**
	 * @var	ヘッダ情報配列
	 *
	 * @access	public
	 */
	var $headers;

	/**
	 * @var	件名文字列
	 *
	 * @access	public
	 */
	var $subject;

	/**
	 * @var	本文文字列
	 *
	 * @access	public
	 */
	var $body;

	/**
	 * @var	loggerオブジェクト
	 *
	 * @access	private
	 */
	var $_log;

	/**
	 * @var	置換文字列配列
	 *
	 * @access	private
	 */
	var $_assignedTags;

	/**
	 * @var	改行コード
	 *
	 * @access	private
	 */
	var $_LE;

	/**
	 * @var	文字コード
	 *
	 * @access	private
	 */
	var $charSet;

	/**
	 * @var	エンコード
	 *
	 * @access	private
	 */
	var $encoding;


	/**
	 * @var	configの値が入っているかどうか
	 *
	 * @access	private
	 */
	var $setting_config = true;


	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Mail_Main() {
		$this->_mailer = new PHPMailer();
		$this->init();
	}

	/**
	 * 初期化処理
	 *
	 * @access	public
	 */
	function init()
	{
		$this->fromEmail = "";
		$this->fromName = "";
		$this->priority = "";
		$this->toUsers = array();
		$this->headers = array();
		$this->subject = "";
		$this->body = "";
		$this->_log =& LogFactory::getLog();
		$this->_assignedTags = array();
		$this->_LE = "\n";
		$this->charSet = _CHARSET;
		$this->encoding = "8bit";
		$this->isHTML = true;

		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$mailConfigs = $configView->getConfigByCatid(_SYS_CONF_MODID, _MAIL_CONF_CATID);

		$this->setFromEmail($mailConfigs["from"]["conf_value"]);
		$this->setFromName($mailConfigs["fromname"]["conf_value"]);

		$this->_mailer->Host = $mailConfigs["smtphost"]["conf_value"];
		$this->setting_config = (($mailConfigs["mailmethod"]["conf_value"] == "smtpauth" || $mailConfigs["mailmethod"]["conf_value"] == "smtp") && $this->_mailer->Host == "") ? false : true;
		if ($mailConfigs["mailmethod"]["conf_value"] == "smtpauth") {
		    $this->_mailer->Mailer = "smtp";
			$this->_mailer->SMTPAuth = TRUE;
			$this->_mailer->Username = $mailConfigs["smtpuser"]["conf_value"];
			$this->_mailer->Password = $mailConfigs["smtppass"]["conf_value"];
		} else {
			$this->_mailer->Mailer = $mailConfigs["mailmethod"]["conf_value"];
			$this->_mailer->SMTPAuth = FALSE;
			$this->_mailer->Sendmail = $mailConfigs["sendmailpath"]["conf_value"];
		}
		if($mailConfigs["mailmethod"]["conf_value"] == "sendmail") {
			$this->setting_config = ($this->_mailer->Sendmail == "") ? false : true;
		}
		if (isset($mailConfigs["htmlmail"]) && $mailConfigs["htmlmail"]["conf_value"] == _OFF) {
			// htmlメールかいなか
			$this->isHTML = false;
		}
	}


	/**
	 * Fromアドレスをセットする
	 *
	 * @param	string	$value	Fromアドレス
	 *
	 * @access	public
	 */
	function setFromEmail($value)
	{
		$this->fromEmail = trim($value);
	}

	/**
	 * From名称をセットする
	 *
	 * @param	string	$value	From名称
	 *
	 * @access	public
	 */
	function setFromName($value)
	{
		$this->fromName = trim($value);
	}

	/**
	 * 重要度をセットする
	 *
	 * @param	string	$value	重要度
	 *
	 * @access	public
	 */
	function setPriority($value)
	{
		$this->priority = trim($value);
	}

	/**
	 * 件名をセットする
	 *
	 * @param	string	$value	件名
	 *
	 * @access	public
	 */
	function setSubject($value)
	{
		$this->subject = trim($value);
	}

	/**
	 * 本文をセットする
	 *
	 * @param	string	$value	本文
	 *
	 * @access	public
	 */
	function setBody($value)
	{
		$this->body = trim($value);
		$this->body = str_replace("\n", "<br />", $this->body). "<br />";

		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent('commonMain');
		$escapeText =& $commonMain->registerClass(WEBAPP_DIR . '/components/escape/Text.class.php', 'Escape_Text', 'escapeText');

		$this->body = $escapeText->escapeWysiwyg($this->body);
	}

	/**
	 * メールを送信する
	 *
	 * @access	public
	 */
	function send()
	{
		if($this->setting_config == false) {
			$this->_log->error("システム管理の設定が正しくありません", "Mailer#send");
			return false;
		}
		if ( $this->body == "") {
			$this->_log->error("メッセージ本文がありません", "Mailer#send");
			return false;
		}

		if (!empty($this->priority)) {
			$this->headers[] = "X-Priority: ". $this->priority;
		}
		$this->headers[] = "X-Mailer: PHP/". phpversion();
		$this->headers[] = "Return-Path: ". $this->fromEmail;

		$container =& DIContainerFactory::getContainer();
		$configView =& $container->getComponent("configView");
		$this->assign("X-FROM_EMAIL", $this->fromEmail);
		$this->assign("X-FROM_NAME", htmlspecialchars($this->fromName));
		$confs = $configView->getConfigByConfname(_SYS_CONF_MODID, "sitename");
		$this->assign("X-SITE_NAME", htmlspecialchars($confs["conf_value"]));
		$this->assign("X-SITE_URL", BASE_URL.INDEX_FILE_NAME);

		$session =& $container->getComponent("Session");
		if (!isset($this->_assignedTags['X-ROOM'])) {
			$request =& $container->getComponent("Request");
			$pageView =& $container->getComponent("pagesView");
			$roomId = $request->getParameter("room_id");
			$pages = $pageView->getPageById($roomId);

			$this->assign("X-ROOM", htmlspecialchars($pages["page_name"]));
		}
		if (!isset($this->_assignedTags["X-USER"])) {
			$this->assign("X-USER", htmlspecialchars($session->getParameter("_handle")));
		}

		$commonMain =& $container->getComponent("commonMain");
		$convertHtml =& $commonMain->registerClass(WEBAPP_DIR.'/components/convert/Html.class.php', "Convert_Html", "convertHtml");
		foreach ($this->_assignedTags as $k => $v) {
			if (substr($k, 0, 4) == "X-TO" || $k == "X-URL") {
				continue;
			}

			$this->body = str_replace("{".$k."}", $v, $this->body);
			$this->subject = str_replace("{".$k."}", $convertHtml->convertHtmlToText($v), $this->subject);
		}
		$this->body = str_replace("\r\n", "\n", $this->body);
		$this->body = str_replace("\r", "\n", $this->body);
		$this->body = str_replace("\n", $this->_LE, $this->body);
		$this->body = $this->_insertNewLine($this->body);
	if(isset($this->_assignedTags["X-URL"])) {
			$this->body = str_replace("{X-URL}", "<a href=\"". $this->_assignedTags["X-URL"]. "\">". $this->_assignedTags["X-URL"]. "</a>", $this->body);
			$mobile_body = str_replace("{X-URL}", $this->_assignedTags["X-URL"], $this->body);
			unset($this->_assignedTags["X-URL"]);
		} else {
			$mobile_body = $this->body;
		}
		$mobile_body = $convertHtml->convertHtmlToText($mobile_body);
		$mobile_body = $this->_insertNewLine($mobile_body);
		if(count($this->toUsers) > 0) {
			foreach ($this->toUsers as $user) {
				$email = $user["email"];
				if (empty($email)) {
					continue;
				}
				if(isset($this->_assignedTags["X-TO_DATE"])) {
					$date = timezone_date_format($this->_assignedTags["X-TO_DATE"], _FULL_DATE_FORMAT);
				} else {
					$date = "";
				}
				if(!isset($user["handle"])) {
					$user["handle"] = "";
				}

				// type (html(email) or text(mobile_email))
				if(!isset($user["type"])) {
					$user["type"] = "html";
				}
				if(empty($user["lang_dirname"])) {
					$user["lang_dirname"] = $session->getParameter("_lang");
					if(!isset($user["lang_dirname"]) || $user["lang_dirname"] == "") {
						$user["lang_dirname"] = "japanese";
					}
				}
				$subject = $this->subject;
				if($this->isHTML == true && ($user["type"] == "html" || $user["type"] == "email")) {
					// htmlメール
					$this->_mailer->IsHTML(true);
					$body = $this->body;
					$body = str_replace("{X-TO_HANDLE}", htmlspecialchars($user["handle"]), $body);
				} else {
					// テキストメール
					$this->_mailer->IsHTML(false);
					$body = $mobile_body;
					$body = str_replace("{X-TO_HANDLE}", $user["handle"], $body);
				}

				$subject = str_replace("{X-TO_HANDLE}", $user["handle"], $subject);
				$subject = str_replace("{X-TO_EMAIL}", $email, $subject);
				$subject = str_replace("{X-TO_DATE}", $date, $subject);
				$body = str_replace("{X-TO_EMAIL}", $email, $body);
				$body = str_replace("{X-TO_DATE}", $date, $body);

				$localFilePath = WEBAPP_DIR. "/language/". strtolower($user["lang_dirname"]). "/Mailer_Local.php";
				if (file_exists($localFilePath)) {
					require_once($localFilePath);

					$className = "Mailer_Local_" . ucfirst(strtolower($user["lang_dirname"]));
					$local =& new $className();

					$this->_mailer->CharSet = $local->charSet;
					$this->_mailer->Encoding = $local->encoding;
					if (!empty($this->fromName)) {
						$this->_mailer->FromName = $local->encodeFromName($this->fromName);
					}
					$this->_mailer->Subject = $local->encodeSubject($subject);
					$this->_mailer->Body = $local->encodeBody($body);
				} else {
					$this->_mailer->CharSet = $this->charSet;
					$this->_mailer->Encoding = $this->encoding;
					if (!empty($this->fromName)) {
						$this->_mailer->FromName = $this->fromName;
					}
					$this->_mailer->Subject = $subject;
					$this->_mailer->Body = $body;
				}

				$this->_mailer->ClearAllRecipients();
				$this->_mailer->AddAddress($email);
				if (!empty($this->fromEmail)) {
					$this->_mailer->From = $this->fromEmail;
				}
				$this->_mailer->ClearCustomHeaders();
				foreach ($this->headers as $header) {
					$this->_mailer->AddCustomHeader($header);
				}

				if (!$this->_mailer->Send()) {
					$this->_log->warn($email. "宛にメールを送信できませんでした/". $this->_mailer->ErrorInfo, "Mailer#send");
				} else {
					$this->_log->trace($email. "宛にメールを送信しました", "Mailer#send");
				}

				//flush();	// ob_contentが送られてしまうためコメント
			}
		}
		return true;
	}

	/**
	 * 変換タグの追加
	 *
	 * @param	string	$tag	タグ名称
	 * @param	string	$value	変換する値
	 *
	 * @access	public
	 */
	function assign($tag, $value = null)
	{
		if (is_array($tag)) {
			foreach ($tag as $k => $v) {
				$this->assign($k, $v);
			}
		} else {
			if (!empty($tag) && isset($value)) {
				$tag = strtoupper(trim($tag));

				if (substr($tag, 0, 2) == "X-") {
					$this->_assignedTags[$tag] = $value;
				}
			}
		}
	}

	/**
	 * ヘッダの追加
	 *
	 * @param	string	$value	ヘッダの値
	 *
	 * @access	public
	 */
	function addHeaders($value)
	{
		$this->headers[] = trim($value). $this->_LE;
	}

	/**
	 * 送信先ユーザの設定
	 *
	 * @param	array	$users	ユーザ情報配列
	 *
	 * @access	public
	 */
	function setToUsers(&$users)
	{
		$this->toUsers = $users;
	}

	/**
	 * 送信先ユーザの追加
	 *
	 * @param	array	$user	ユーザ情報配列
	 *
	 * @access	public
	 */
	function addToUser(&$user)
	{
		$this->toUsers[] = $user;
	}

	/**
	 *
	 * 改行コード挿入
	 */
	function _insertNewLine($body)
	{
		$lines = explode($this->_LE, $body);
		$pos = 0;
		$max_line_length = 300;

		while(list(,$line) = @each($lines)) {
			while(mb_strlen($line) > $max_line_length) {
				$pos = strrpos(mb_substr($line, 0, $max_line_length), '<');
				if ($pos > 0) {
					$lines_out[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				} else {
					$lines_out[] = mb_substr($line, 0, $max_line_length);
					$line = mb_substr($line,  $max_line_length);
				}
			}
			$lines_out[] = $line;
		}
		return implode($this->_LE, $lines_out);
	}

}
?>
