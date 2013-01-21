<?php
require_once(MAPLE_DIR.'/includes/mail/phpmailer/class.phpmailer.php');

/**
 * メール内容日本語エンコード用コンポーネント
 *
 * @package     [[package名]]
 * @author      Ryuji Masukawa
 * @copyright   copyright (c) 2006 NetCommons.org
 * @license     [[license]]
 * @access      public
 */
class Mailer_Local_Japanese
{
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
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Mailer_Local_Japanese() {
		$this->charSet = "iso-2022-jp";
		$this->encoding = "7bit";
	}
	
	/**
	 * 送信者名称エンコード処理
	 *
	 * @access	public
	 */
	function encodeFromName($str)
	{
		if (empty($str)) return $str;
		
		return "=?". $this->charSet. "?B?". base64_encode(mb_convert_encoding($str, $this->charSet, _CHARSET)). "?=";
	}

	/**
	 * 件名エンコード処理
	 *
	 * @access	public
	 */
	function encodeSubject($str)
	{
		if (empty($str)) return $str;
		
		return "=?". $this->charSet. "?B?". base64_encode(mb_convert_encoding($str, $this->charSet, _CHARSET)). "?=";
	}

	/**
	 * 本文エンコード処理
	 *
	 * @access	public
	 */
	function encodeBody($str)
	{
		return mb_convert_encoding($str, $this->charSet, _CHARSET);
	}

}
?>
