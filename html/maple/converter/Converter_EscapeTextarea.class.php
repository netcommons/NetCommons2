<?php
/**
 * WYSIWYGエディター出力テキストエスケープ
 *
 * @package     NetCommons.component
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Converter_EscapeTextarea extends Converter {
	
	/**
	 * @var オブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Converter_EscapeTextarea() {
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	/**
	 * @param   string  $attributes 変換する文字列
	 * @return  string  変換後の文字列
	 * @access  public
	 */
	function convert($attributes) {
		include_once COMPONENT_DIR.'/escape/Text.class.php';
		$escapeText = new Escape_Text();
		if (is_array($attributes)) {
			foreach ($attributes as $key => $value) {
				$attributes[$key] = $escapeText->escapeWysiwyg($value);
			}
		} else {
			$attributes = $escapeText->escapeWysiwyg($attributes);
		}
		return $attributes;
	}
}
?>
