<?php

/**
 * 回覧板共通コンポーネントクラス
 *
 * @package     NetCommons Components
 * @author      WithOne Company Limited.
 * @copyright   2006-2007 NetCommons Project, 2011 WithOne Company Limited.
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access	public
 */
class Circular_Components_Common
{
	/**
	 * コンストラクタ
	 *
	 * @access public
	 */
	function Circular_Components_Common()
	{
	}

	/**
	 * キー・バリューマップを取得する
	 *
	 * @param   string $key_value_string  キー・バリュー文字列(key1:value1|key2:value2|...)
	 * @return  array  キー・バリューマップ
	 * @access  public
	 */
	function &getMap($key_value_string)
	{
		$key_value_array = explode("|", $key_value_string);
		foreach($key_value_array as $k) {
			$key_value = explode(":", $k);
			$key_value_map[$key_value[0]] = $key_value[1];
		}

		return $key_value_map;
	}
}
?>
