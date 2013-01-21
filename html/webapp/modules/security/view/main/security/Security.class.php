<?php

/**
 * セキュリティ（一般設定）
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Security_View_Main_Security extends Action
{
	// リクエストパラメータを受け取るため
	
	// 使用コンポーネントを受け取るため
    var $session = null;
    var $configView = null;
    var $db = null;
    
    // フィルタによりセット
    
    // 値をセットするため
	var $config = null;
	var $bip_except = array(_OFF, _OFF, _OFF, _OFF, _OFF, _OFF);
	var $groups_denyipmove = array(_OFF, _OFF, _OFF, _OFF, _OFF, _OFF);
	var $bad_ips = null;
	var $relaible_ips = null;
	var $db_prefix = null;
	
	function execute()
	{
		$this->config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _SECURITY_CONF_CATID);
	    if ($this->config == false) {
            return 'error';
        }
        // 拒否IP登録の保護グループ
		$bip_except_conf = $this->config['bip_except']['conf_value'];
		if ($bip_except_conf != "") {
			$bip_excepts = unserialize($bip_except_conf);
    	    foreach( $bip_excepts as $element ) {
				if (empty($element))	break;
			
				if ($element == strval(_AUTH_ADMIN))	$this->bip_except[_AUTH_ADMIN] = _ON;
				elseif ($element == strval(_AUTH_CHIEF))	$this->bip_except[_AUTH_CHIEF] = _ON;
				elseif ($element == strval(_AUTH_MODERATE))	$this->bip_except[_AUTH_MODERATE] = _ON;
				elseif ($element == strval(_AUTH_GENERAL))	$this->bip_except[_AUTH_GENERAL] = _ON;
				elseif ($element == strval(_AUTH_GUEST))	$this->bip_except[_AUTH_GUEST] = _ON;
    	    }
		}
		// IP変動を禁止するベース権限
        $groups_denyipmove_conf = $this->config['groups_denyipmove']['conf_value'];
		if ($groups_denyipmove_conf != "") {
	        $groups_denyipmoves = unserialize($groups_denyipmove_conf);
    	    foreach( $groups_denyipmoves as $element ) {
				if (empty($element))	break;
			
				if ($element == strval(_AUTH_ADMIN))	$this->groups_denyipmove[_AUTH_ADMIN] = _ON;
				elseif ($element == strval(_AUTH_CHIEF))	$this->groups_denyipmove[_AUTH_CHIEF] = _ON;
				elseif ($element == strval(_AUTH_MODERATE))	$this->groups_denyipmove[_AUTH_MODERATE] = _ON;
				elseif ($element == strval(_AUTH_GENERAL))	$this->groups_denyipmove[_AUTH_GENERAL] = _ON;
				elseif ($element == strval(_AUTH_GUEST))	$this->groups_denyipmove[_AUTH_GUEST] = _ON;
	        }
		}
		// アクセス拒否IP
		$this->bad_ips = "";
		$bad_ips_conf = $this->config['bad_ips']['conf_value'];
		if ($bad_ips_conf != "") {
			$bad_ips_conf = unserialize($bad_ips_conf);
	    	foreach( $bad_ips_conf as $element ) {
				if (empty($element))	break;
				
				if (!empty($this->bad_ips)) $this->bad_ips = $this->bad_ips . "|";
				$this->bad_ips = $this->bad_ips . $element;
	    	}
		}
		// 拒否IPから除外するIPアドレス
    	$this->reliable_ips = "";
        $reliable_ips_conf = $this->config['reliable_ips']['conf_value'];
		if ($reliable_ips_conf != "") {
        	$reliable_ips_conf = unserialize($reliable_ips_conf);
        	foreach( $reliable_ips_conf as $element ) {
				if (empty($element))	break;
				
				if (!empty($this->reliable_ips)) $this->reliable_ips = $this->reliable_ips . "|"; 
				$this->reliable_ips = $this->reliable_ips . $element;
    		}
		}
		$this->db_prefix = $this->db->getPrefix();
    	        
		return 'success';
	}
}
?>
