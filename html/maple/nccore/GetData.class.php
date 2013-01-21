<?php
//
// $Id: GetData.class.php,v 1.3 2007/06/26 03:10:06 Ryuji.M Exp $
//

/**
 * Dataをセットしておくクラス
 *
 * @author	Ryuji Masukawa
 **/
class GetData {
	
	var $data = array();
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function GetData() {
	}
	
	/**
     * 設定されている値を返却
     *
     * @param   string  $key or array($key,$key,...) $key  パラメータ名 
     * 			 object  $smarty_obj Smartyオブジェクト：テンプレートから呼ばれる場合
     * @return  string  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function &getParameter($key)
    {
    	$bool_value = false;
    	if(is_array($key)) {
    		$temp = $this->data;
    		foreach($key as $key_value) {
    			if(isset($temp[$key_value]))
    				$temp =& $temp[$key_value];
    		}
    		return $temp;
    	} else {
        	if (isset($this->data[$key])) {
            	return $this->data[$key];
        	}
    	}
    	return $bool_value;
    }
    
    /**
     * 値をセット
     *
     * @param   string  $key or array($key,$key,...) $key  パラメータ名 
     * @param   string  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameter($key, $value)
    {
    	if(is_array($key)) {
    		$tmpArg =& $this->data;
			foreach($key as $key_value) {
	    		$tmpArg =& $tmpArg[$key_value];
	    	}
	    	$tmpArg = $value;
    	} else {
        	$this->data[$key] =& $value;
    	}
    }

    /**
     * 値を削除する
     *
     * @param   string  $key    パラメータ名
     * @access  public
     * @since   3.0.0
     */
    function removeParameter($key)
    {
    	if(is_array($key)) {
    		$tmpArg =& $this->data;
			foreach($key as $key_value) {
	    		$tmpArg =& $tmpArg[$key_value];
	    	}
	    	unset($tmpArg);
    	} else {
        	unset($this->data[$key]);
    	}
        
    }
}
?>
