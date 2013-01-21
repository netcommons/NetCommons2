<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * モジュールアップデートクラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;
	var $configView = null;
	var $languagesView = null;

	function execute()
	{
		// 自動登録の入力キーを指定できるようにする
		$params = array("autoregist_use_input_key");
		$sql = "SELECT COUNT(*) FROM {config} WHERE conf_name = ?";
		$counts =$this->db->execute($sql, $params, null, null, false);
		if ($counts === false) {
			return false;
		}

		$count = intval($counts[0][0]);
		if ($count == 0) {
	        $params = array(
				"conf_modid" => _SYS_CONF_MODID,
				"conf_catid" => _ENTER_EXIT_CONF_CATID,
				"conf_name" => "autoregist_use_input_key",
				"conf_value" => _OFF
			);
			$result = $this->db->insertExecute("config", $params, true, "conf_id");
			if ($result === false) {
				return false;
			}

	        $params = array(
				"conf_modid" => _SYS_CONF_MODID,
				"conf_catid" => _ENTER_EXIT_CONF_CATID,
				"conf_name" => "autoregist_input_key",
				"conf_value" => "netcommons"
			);
			$result = $this->db->insertExecute("config", $params, true, "conf_id");
			if ($result === false) {
				return false;
			}
		}

		// 固定リンクを指定できるようにする
		$params = array("use_permalink");
		$sql = "SELECT COUNT(*) FROM {config} WHERE conf_name = ?";
		$counts =$this->db->execute($sql, $params, null, null, false);
		if ($counts === false) {
			return false;
		}

		$count = intval($counts[0][0]);
		if ($count == 0) {
	        $params = array(
				"conf_modid" => _SYS_CONF_MODID,
				"conf_catid" => _SERVER_CONF_CATID,
				"conf_name" => "use_permalink",
				"conf_value" => _OFF
			);
			$result = $this->db->insertExecute("config", $params, true, "conf_id");
			if ($result === false) {
				return false;
			}
		}

		// ldap_usesを指定できるようにする
		$params = array('ldap_uses');
		$sql = "SELECT COUNT(*) FROM {config} WHERE conf_name = ?";
		$counts =$this->db->execute($sql, $params, null, null, false);
		if ($counts === false) {
			return false;
		}

		$count = intval($counts[0][0]);
		if ($count == 0) {
	        $params = array(
				'conf_modid' => _SYS_CONF_MODID,
				'conf_catid' => _SERVER_CONF_CATID,
				'conf_name' => 'ldap_uses',
				'conf_value' => _OFF
			);
			$result = $this->db->insertExecute('config', $params, true, 'conf_id');
			if ($result === false) {
				return false;
			}
			$params = array(
				'conf_modid' => _SYS_CONF_MODID,
				'conf_catid' => _SERVER_CONF_CATID,
				'conf_name' => 'ldap_server',
				'conf_value' => ''
			);
			$result = $this->db->insertExecute('config', $params, true, 'conf_id');
			if ($result === false) {
				return false;
			}
			$params = array(
				'conf_modid' => _SYS_CONF_MODID,
				'conf_catid' => _SERVER_CONF_CATID,
				'conf_name' => 'ldap_domain',
				'conf_value' => ''
			);
			$result = $this->db->insertExecute('config', $params, true, 'conf_id');
			if ($result === false) {
				return false;
			}
		}

		// オートコンプリート有無
		$params = array('login_autocomplete');
		$sql = "SELECT COUNT(*) FROM {config} WHERE conf_name = ?";
		$counts =$this->db->execute($sql, $params, null, null, false);
		if ($counts === false) {
			return false;
		}
		$count = intval($counts[0][0]);
		if ($count == 0) {
			$params = array(
				'conf_modid' => _SYS_CONF_MODID,
				'conf_catid' => _GENERAL_CONF_CATID,
				'conf_name' => 'login_autocomplete',
				'conf_value' => _OFF
			);
			$result = $this->db->insertExecute('config', $params, true, 'conf_id');
			if ($result === false) {
				return false;
			}
		}

		$adodb = $this->db->getAdoDbObject();
		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."config_language", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."config_language` (" .
					"`conf_name`           varchar(64) NOT NULL default '',".
					"`lang_dirname`        varchar(64) NOT NULL default '',".
					"`conf_value`          text NOT NULL, ".
					"PRIMARY KEY (`conf_name`,`lang_dirname`)".
					") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
			$config_lang =& $this->configView->getConfigByConfname(_SYS_CONF_MODID, 'language');
			$site_name =& $this->configView->getConfigByConfname(_SYS_CONF_MODID, 'sitename');
			$items = explode(',', _MULTI_LANG_CONFIG_ITEMS);
			$in_sql = '';
			foreach($items as $item) {
				$in_sql .= "'".$item."',";
			}
			$in_sql = substr($in_sql, 0, -1);
			$sql = "SELECT conf_name, conf_value FROM `".$this->db->getPrefix()."config` WHERE conf_name in (".$in_sql.")";
			$configs = $this->db->execute($sql);
			if ($configs === false) return false;

			if(!empty($configs) && !empty($config_lang['conf_value'])) {
				foreach($configs as $config) {
					$result = $this->db->insertExecute('config_language',array('conf_name' => $config['conf_name'], 'lang_dirname' => $config_lang['conf_value'], 'conf_value' => $config['conf_value']));
					if ($configs === false) return false;
				}
			}

			$languages = $this->languagesView->getLanguagesList();
			if ($languages === false) return false;
			unset($languages[$config_lang['conf_value']]);

			foreach($languages as $lang => $lang_name) {
				foreach($items as $item) {
					if($item == 'sitename' && !empty($site_name['conf_value'])) {
						$config_value = $site_name['conf_value'];
					}else {
						$config_value = constant(strtoupper('INSTALL_CONF_'.$item.'_'.$lang));
					}
					if(!empty($config_value)) {
						$config_value = str_replace("\'", "'", $config_value);
						$result = $this->db->insertExecute('config_language',array('conf_name' => $item, 'lang_dirname' => $lang, 'conf_value' => $config_value));
						if ($result === false) return false;
					}
				}
			}
		} else {
			$params = array(
				'conf_name' => 'from'
			);
			$sql = "SELECT COUNT(*) "
					. "FROM {config_language} "
					. "WHERE conf_name = ?";
			$counts =$this->db->execute($sql, $params, null, null, false);
			if ($counts === false) {
				return false;
			}
			$count = intval($counts[0][0]);
			if ($count != 0) {
				$result = $this->db->deleteExecute('config_language', $params);
				if ($result === false) {
					return false;
				}
			}
		}

		// --- 携帯用画像クラス名の追加 ---
		$textarea_atr = $this->db->selectExecute('textarea_attribute', array('attribute'=>'class'));
		if ($textarea_atr == false) {
			return false;
		}

		if (!isset($textarea_atr[0]) || !isset($textarea_atr[0]['value_regexp'])) {
			return false;
		}

		$value_regexp = $textarea_atr[0]['value_regexp'];
		require_once(WEBAPP_DIR . '/config/mobile.inc.php');
		if (!preg_match('/' . MOBILE_IMAGE . '/', $value_regexp )) {
			$pos = strrpos($value_regexp, ')$');
			$new_value = substr_replace($value_regexp, '|' . MOBILE_IMAGE, $pos, 0);

			$result = $this->db->updateExecute('textarea_attribute', array('value_regexp'=>$new_value), array('attribute'=>'class'));
			if ($result == false) {
				return false;
			}
		}

		return true;
	}
}
?>
