<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once MAPLE_DIR.'/core/Token.class.php';

/**
 * Token管理を行う
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class TokenExtra extends Token
{
	var $_build_flag = false;
	
    /**
     * コンストラクター
     *
     * @access  public
     */
    function TokenExtra()
    {
    	$this->Token();
    }
    
    /**
     * Tokenの名前を返却
     *
     * @return  string  Tokenの名前
     * @access  public
     */
    function getName()
    {
        if ($this->_name == "") {
            $this->_name = array("_token");
        }

        return $this->_name;
    }

    /**
     * Tokenの値を返却
     *
     * @return  string  Tokenの値を返却
     * @access  public
     */
    function getValue()
    {
    	//多次元配列化
    	$token_value = $this->getName();
    	if(is_array($token_value)) {
    		$token_value = array_merge(array("_token"), $token_value);
    		return $this->_session->getParameter($token_value);
    	} else {
        	return $this->_session->getParameter(array("_token",$this->getName()));
    	}
    }

    /**
     * Tokenの値を生成
     *
     * @access  public
     */
    function build()
    {
    	$this->_build_flag = true;
    	//srand(microtime()*100000);
    	
    	//_nameを付与することにより複雑にした
    	//多次元配列化
    	$token_value = $this->getName();

		$tokenString = $this->_name;
		if (is_array($tokenString)) {
			$tokenString = '_token';
		}

    	if(is_array($token_value)) {
    		$token_value = array_merge(array("_token"), $token_value);
    		$this->_session->setParameter($token_value, md5($tokenString . uniqid(rand(),1)));
    	} else {
        	$this->_session->setParameter(array("_token",$this->getName()), md5($tokenString . uniqid(rand(),1)));
    	}
    }
    
    /**
     * Tokenの値を比較
     *
     * @param   Object  $value  Requestクラスのインスタンス
     * @return  boolean Tokenの値が一致するか？
     * @access  public
     */
    function check(&$request)
    {
    	//_token固定で取得しチェック
    	return (($this->getValue() != '') &&
            ($this->getValue() == $request->getParameter("_token")));
        //return (($this->getValue() != '') &&
        //    ($this->getValue() == $request->getParameter($this->getName())));
    }

    /**
     * Tokenの値を削除
     *
     * @access  public
     */
    function remove()
    {
    	//多次元配列化
    	$token_value = $this->getName();
    	if(is_array($token_value)){
    		if($token_value[0] !== "_token") {
    			$token_value = array_merge(array("_token"), $token_value);
    		}
    		$this->_session->removeParameter($token_value);
    	} else {
        	$this->_session->removeParameter(array("_token",$this->getName()));
    	}
    }
    
    /**
     * buildされたかどうか
     *
     * @return  string  Tokenの値を返却
     * @access  public
     */
    function inbuild()
    {
    	return $this->_build_flag;
    }
}
?>
