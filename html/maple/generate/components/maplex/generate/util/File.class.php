<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.generate
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: File.class.php,v 1.1 2006/10/13 08:50:19 Ryuji.M Exp $
 */

/**
 * ディレクトリ・ファイル操作クラス
 *
 * @package     Maple.generate
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Maplex_Generate_Util_File
{
    /**
     * include_pathからファイルを検索し、絶対パスの形で返す
     * 見つからなかった場合はfalseを返す
     * 
     * @author  Hawk <scholar@hawklab.jp>
     * @since   3.2.0
     * @return  String or false
     */
    function findIncludableFile($path)
    {
        if (!is_array($include_paths = explode(PATH_SEPARATOR, get_include_path()))) {
            return realpath($path);
        }
        
        foreach ($include_paths as $include_path) {
            if (($realpath = realpath($include_path . DIRECTORY_SEPARATOR . $path)) !== false) {
                return $realpath;
            }
        }
        return false;
    }

    /**
     * ファイルを読み込む
     *
     * ファイルを読み込んで一つの文字列として返す。
     *
     * @param string $fileName  ファイル名
     * @return string  ファイルの内容
     * @access public
     */
    function read($fileName)
    {
        $buf = '';
        if (file_exists($fileName)) {
            if (function_exists('file_get_contents')) {
                $buf = file_get_contents($fileName);
            } else {
                $fh = fopen($fileName, "rb");
                $buf = fread($fh, filesize($fileName));
                fclose($fh); 
            }
        }
        return $buf;
    }

    /**
     * ファイルに追記する
     *
     * 指定した内容をファイルに追記で書き込む。
     * 途中のフォルダが存在しない場合は自動的に作成する。
     *
     * @param string $fileName  ファイル名
     * @param string $buf  書き込む内容
     * @return boolean
     * @access public
     */
    function append($fileName, $buf)
    {
        return Maplex_Generate_Util_File::write($fileName, $buf, "ab");
    }

    /**
     * ファイルに書き込む
     *
     * 指定した内容をファイルに上書きで書き込む。
     * 途中のフォルダが存在しない場合は自動的に作成する。
     *
     * @param string $fileName  ファイル名
     * @param string $buf  書き込む内容
     * @param string $mode  書き込みモード
     * @return boolean
     * @access public
     */
    function write($fileName, $buf, $mode = "wb")
    {
        Maplex_Generate_Util_File::makeDir(dirname($fileName));
        
        if (!($fh = fopen($fileName, $mode))) {
            return false;
        }
        if (!fwrite($fh, $buf)) {
            return false;
        }
        if (!fclose($fh)) {
            return false;
        }
        return true;
    }

    /**
     * ディレクトリを作成する。
     * 
     * 複数階層のディレクトリを指定した場合に、
     * 途中のディレクトリも自動的に作成する。
     * 
     * @param mixed $dirNames  作成するディレクトリ名、またはその配列
     * @access public
     */
    function makeDir($dirNames)
    {
        if (!is_array($dirNames)) {
            $dirNames = array($dirNames);
        }
        
        foreach($dirNames as $dir){
            Maplex_Generate_Util_File::_makeDir($dir);
        }
    }

    /**
     * ディレクトリを削除する
     *
     * 指定したディレクトリより下のディレクトリ・ファイルも
     * 自動的に削除する。
     *
     * @param mixed $dirNames  削除するディレクトリ名、またはその配列
     * @access public
     */
    function removeDir($dirNames)
    {
        if (!is_array($dirNames)) {
            $dirNames = array($dirNames);
        }
        
        foreach($dirNames as $dir){
            Maplex_Generate_Util_File::_removeDir($dir);
        }
    }

    /**
     * ディレクトリとファイルのリストを取得する
     *
     * 返値は配列で、最初がディレクトリの配列、次にファイルの配列となる。
     * list($dirs, $files) = Maplex_Generate_Util_File::ls($dirname);
     *
     * @param string $dirName  ディレクトリ名
     * @return array  ディレクトリとファイルの配列
     * @access public
     */
    function ls($dirName)
    {
        if (!$dh = @opendir($dirName)) {
            return false;
        }

        $dirs = $files = array();
        while (($file = readdir($dh)) !== false) {
            if (preg_match("/^[.]{1,2}$/", $file)) {
                continue;
            }
            $fullPath = Maplex_Generate_Util_File::_addTail($dirName, DIRECTORY_SEPARATOR).$file;
            if (is_dir($fullPath)) {
                $dirs[] = $fullPath;
            } else {
                $files[] = $fullPath;
            }
        }
        closedir($dh);
        return array($dirs, $files);
    }

    /**
     * 指定したディレクトリのファイルリストを取得する
     *
     * @param string $dirName  ディレクトリ名
     * @param string [$regex]  取得するファイル名の正規表現
     * @return array  ファイルの配列
     * @access public
     */
    function find($dirName, $regex = "")
    {
        $data = Maplex_Generate_Util_File::ls($dirName);
        if (!is_array($data)) {
            return array();
        }
        list($dirs, $files) = $data;

        if ($regex == "") {
            return $files;
        }
        $found = array();
        foreach ($files as $file) {
            if (preg_match($regex, basename($file))) {
                $found[] = $file;
            }
        }
        return $found;
    }

    /**
     * 指定したディレクトリのファイルリストを取得する
     * 
     * サブディレクトリがある場合は再帰的に取得する
     *
     * @param string $dirName  ディレクトリ名
     * @param string [$regex]  取得するファイル名の正規表現
     * @return array  ファイルの配列
     * @access public
     */
    function findRecursive($dirName, $regex = "")
    {
        $data = Maplex_Generate_Util_File::ls($dirName);
        if (!is_array($data)) {
            return array();
        }
        list($dirs, $files) = $data;
        
        $found = Maplex_Generate_Util_File::find($dirName, $regex);

        foreach ($dirs as $dir) {
            $found = array_merge($found, Maplex_Generate_Util_File::findRecursive($dir, $regex));
        }

        return $found;
    }

    /**
     * ディレクトリを作成する。
     *
     * 親ディレクトリがなければ自動的に作成する。
     *
     * @param string $dirName  ディレクトリ名
     * @access private
     */
    function _makeDir($dirName)
    {
        $dirstack = array();
        while (!@is_dir($dirName) && $dirName != DIRECTORY_SEPARATOR) {

        	array_unshift($dirstack, $dirName);
        	$dirName = dirname($dirName);
        }
        while ($newdir = array_shift($dirstack)) {
        	mkdir($newdir);
        }
    }

    /**
     * ディレクトリを削除する
     *
     * サブディレクトリがあれば自動的に削除する。
     * 指定したディレクトリ以下のファイルも自動的に削除する。
     *
     * @param string $dirName  ディレクトリ名
     * @access private
     */
    function _removeDir($dirName)
    {
        $data = Maplex_Generate_Util_File::ls($dirName);
        if (!is_array($data)) {
            return;
        }
        list($dirs, $files) = $data;
        
        if (is_dir($dirName)) {
            array_unshift($dirs, $dirName);
        }

        foreach($files as $file){
            if (file_exists($file)) {
                unlink($file);
            }
        }

        foreach(array_reverse($dirs) as $dir){
            if (file_exists($dir)) {
                rmdir($dir);
            }
        }
    }

    /**
     * 文字列の末尾に文字列を追加する
     *
     * 文字列の後ろが追加する文字列と同じ場合は
     * 何もしません。
     * 
     * @param string $target  追加対象の文字列
     * @param string $add  追加する文字列 
     * @return string 変換後の文字列
     * @access private
     */
    function _addTail($target, $add)
    {
        $regex = preg_quote($add);
        if (!preg_match("|.*{$regex}$|", $target)) {
            $target = $target.$add;
        }
        return $target;
    }
}
?>
