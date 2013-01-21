<?php
/**
 * CSV出力用コンポーネント
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Csv_Main
{
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
	 * @var	MIME Type
	 *
	 * @access	private
	 */
	var $mimeType;

	/**
	 * @var	区切り文字
	 *
	 * @access	private
	 */
	var $division;

	/**
	 * @var	拡張子
	 *
	 * @access	private
	 */
	var $extension;

	/**
	 * @var	csvデータ内容
	 *
	 * @access	private
	 */
	var $_csv;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Csv_Main() {
		$this->_LE = "\n";
		$this->charSet = "SJIS";
		$this->mimeType = "document/unknown";
		$this->division = ",";
		$this->extension = ".csv";
		$this->_csv = "";
	}

	/**
	 * CSVデータを作成する
	 *
	 * @param	array	$datas	データ配列
	 * @access	public
	 */
	function build($datas)
	{
		foreach(array_keys($datas) as $index) {
			$this->add($datas[$index]);
		}
	}

	/**
	 * CSVデータをセットする
	 *
	 * @param	string	$str	データ文字列
	 * @access	public
	 */
	function set($str)
	{
		$this->_csv = $str;
	}

	/**
	 * CSVデータを追加する
	 *
	 * @param	array	$data	行データ配列
	 * @access	public
	 */
	function add($data)
	{
		foreach(array_keys($data) as $index) {
			if (strpos($data[$index], "\"") !== false) {
				$data[$index] = preg_replace("/\"/s", "\"\"", $data[$index]);
			}
			//Excelの場合、IDという文字列はエラーとなってしまうため、小文字にする
			if ($data[$index] == "ID") {
				$data[$index] = "id";
			}
			$data[$index] = '"'. $data[$index]. '"';

			$data[$index] = preg_replace("/[\r\n]/s", " ", $data[$index]);
		}
		$string = implode($this->division, $data);
		$string .= "\n";

		$this->_csv .= mb_convert_encoding($string, $this->charSet, _CHARSET);
	}

	/**
	 * CSVデータをダウンロードする
	 *
	 * @access	public
	 */
	function download($fileName)
	{
		$container =& DIContainerFactory::getContainer();
		$uploadsView =& $container->getComponent("uploadsView");

		$fileName .= $this->extension;
		$uploadsView->download($this->_csv, $fileName, $this->mimeType);

		$request =& $container->getComponent("Request");
		$request->setParameter("_output", _OFF);
	}

	/**
	 * CSVファイルからfgetsする
	 *
	 * @access	public
	 */
	function fgets(&$handle, $length = null, $d = ',', $e = '"')
	{
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
        $_csv_line = preg_replace('/(?:\r\n|[\r\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for ($_csv_i=0; $_csv_i<count($_csv_data); $_csv_i++){
            $_csv_data[$_csv_i] = preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
	}
}
?>
