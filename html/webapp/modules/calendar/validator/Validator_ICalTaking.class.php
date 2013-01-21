<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * iCal取り込みチェック
 *
 * @package     NetCommons.validator
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Calendar_Validator_ICalTaking extends Validator
{
	// コンポーネントを保持するため
	var $_session = null;
	var $_calendarPlanAction = null;
	var $_uploadsAction = null;

   // 値を保持するため
	var $_vcalendar = array(
		"CALSCALE" => "",
		"PRODID" => "",
		"VERSION" => "",
		"METHOD" => ""
	);
	var $_vtimezone = array(
		"TZID" => "",
		"STANDARD" => array("DTSTART"=>"", "TZOFFSETFROM"=>0.0, "TZOFFSETTO"=>0.0),
		"DAYLIGHT" => array("DTSTART"=>"", "TZOFFSETFROM"=>0.0, "TZOFFSETTO"=>0.0)
	);
	var $_vevents = array();
	var $_vevent = array();
	
	var $_exec_vcaelndar = false;
	var $_exec_vtimezone = false;
	var $_exec_standard = false;
	var $_exec_daylight = false;
	var $_exec_vevent = false;
	var $_exec_valarm = false;

    /**
     * validate実行
     *
     * @param   mixed   $attributes チェックする値
     * @param   string  $errStr     エラー文字列
     * @param   array   $params     オプション引数
     * @return  string  エラー文字列(エラーの場合)
     * @access  public
     */
    function validate($attributes, $errStr, $params)
    {
    	$container =& DIContainerFactory::getContainer();
	   	$this->_session =& $container->getComponent("Session");
	   	$commonMain =& $container->getComponent("commonMain");
		$this->_uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$this->_calendarPlanAction =& $container->getComponent("calendarPlanAction");

		$this->_session->removeParameter(array("calendar", "ical", "vcalendar", $attributes));
		$this->_session->removeParameter(array("calendar", "ical", "vtimezone", $attributes));
		$this->_session->removeParameter(array("calendar", "ical", "vevents", $attributes));
    	
		$timezone_offset = $this->_session->getParameter("_timezone_offset");
		$this->_vtimezone["STANDARD"]["TZOFFSETFROM"] = $timezone_offset;
		$this->_vtimezone["STANDARD"]["TZOFFSETTO"] = $timezone_offset;
		$this->_vtimezone["DAYLIGHT"]["TZOFFSETFROM"] = $timezone_offset;
		$this->_vtimezone["DAYLIGHT"]["TZOFFSETTO"] = $timezone_offset;

    	$filelist = $this->_uploadsAction->uploads();
    	
    	$file = FILEUPLOADS_DIR."calendar/".$filelist[0]['physical_file_name'];
    	$handle = fopen($file, 'r');
    	if ($handle == false) {
    		return $errStr;
    	}
		$contents = fread($handle, filesize($file));
		fclose($handle); 
		
		$contents = preg_replace('/[\r\n]+ /', '', $contents);
		$rowData = preg_split('/[\r\n]+/', $contents);
		foreach ($rowData as $i=>$row) {
	    	$result = false;
			$row = mb_convert_encoding($row, "UTF-8", "auto");
	    	$row_arr = explode(":", $row);
	    	switch ($row_arr[0]) {
			case "BEGIN":
				$result = $this->_setBegin($row_arr[1]);
				break;
			case "END":
				$result = $this->_setEnd($row_arr[1]);
				break;
			default:
				if (!isset($row_arr[1])) { $result = true; break; }
				if (empty($row_arr[0]))  { $result = true; break; }
				if ($this->_exec_vevent) {
					$result = $this->_setVEvent($row_arr[0], $row_arr[1]);
					break;
				}
				if ($this->_exec_vtimezone) {
					$result = $this->_setVTimezone($row_arr[0], $row_arr[1]);
					break;
				}
				if ($this->_exec_vcaelndar) {
					$result = $this->_setVCalendar($row_arr[0], $row_arr[1]);
					break;
				}
	    	}
			if (!$result) { return $errStr; }
		}

		$this->_uploadsAction->delUploadsById($filelist[0]['upload_id']);
		$this->_session->setParameter(array("calendar", "ical", "vcalendar", $attributes), $this->_vcalendar);
		$this->_session->setParameter(array("calendar", "ical", "vtimezone", $attributes), $this->_vtimezone);
		$this->_session->setParameter(array("calendar", "ical", "vevents", $attributes), $this->_vevents);
        return;
    }

    function _setBegin($value)
    {
    	switch ($value) {
		case "VCALENDAR":
			$this->_exec_vcaelndar = true;
			break;
		case "VTIMEZONE":
			if (!$this->_exec_vcaelndar) { return false; }
			$this->_exec_vtimezone = true;
			break;
		case "STANDARD":
			if (!$this->_exec_vcaelndar) { return false; }
			if (!$this->_exec_vtimezone) { return false; }
			$this->_exec_standard = true;
			break;
		case "DAYLIGHT":
			if (!$this->_exec_vcaelndar) { return false; }
			if (!$this->_exec_vtimezone) { return false; }
			$this->_exec_daylight = true;
			break;
		case "VALARM":
			if (!$this->_exec_vcaelndar) { return false; }
			if (!$this->_exec_vevent) { return false; }
			$this->_exec_valarm = true;
			break;
		case "VEVENT":
			if (!$this->_exec_vcaelndar) { return false; }
			$this->_vevent = array();
			$this->_exec_vevent = true;
			break;
		default:
    	}
    	return true;
    }
    
    function _setEnd($value)
    {
    	switch ($value) {
		case "VCALENDAR":
			$this->_exec_vcaelndar = false;
			break;
		case "VTIMEZONE":
			$this->_exec_vtimezone = false;
			break;
		case "STANDARD":
			$this->_exec_standard = false;
			break;
		case "DAYLIGHT":
			$this->_exec_daylight = false;
			break;
		case "VALARM":
			$this->_exec_valarm = false;
			break;
		case "VEVENT":
			$this->_exec_vevent = false;
			$this->_vevents[] = $this->_vevent;
		default:
    	}
    	return true;
    }
    
    function _setVCalendar($key, $value)
    {
    	$keys = explode(";", $key);
    	$this->_vcalendar[$keys[0]] = $value;
    	return true;
    }

    function _setVTimezone($key, $value)
    {
    	$keys = explode(";", $key);
    	if ($this->_exec_standard) {
	    	if ($keys[0] == "TZOFFSETFROM" || $keys[0] == "TZOFFSETTO") {
				if (preg_match("/(\+|-)([0-9]{1,2}):?([0-9]{1,2})/i", $value, $matches)) {
					$this->_vtimezone["STANDARD"][$keys[0]] = floatval($matches[1].$matches[2].".".$matches[3]);
				} else {
					// matchしない場合、標準時間を返す
					$this->_vtimezone["STANDARD"][$keys[0]] = 0.0;	
				}
	    	} else {
		    	$this->_vtimezone["STANDARD"][$keys[0]] = $value;
	    	}
    		return true;
    	}
    	if ($this->_exec_daylight) {
	    	$this->_vtimezone["DAYLIGHT"][$keys[0]] = $value;
    		return true;
    	}
    	$this->_vtimezone[$keys[0]] = $value;
    	return true;
    }
    
    function _setVEvent($key, $value)
    {
    	$keys = explode(";", $key);
    	if ($this->_exec_valarm) {
	    	$this->_vevent["VALARM"][$keys[0]] = $value;
    		return true;
    	}
    	$this->_vevent[$keys[0]] = $value;
    	if ($keys[0] == "DTSTART" || $keys[0] == "DTEND") {
    		if (strlen($value) == 6) {
    			$value .= "000000";
    		}
    	}
    	if ($keys[0] == "DTSTART") {
    		if (isset($keys[1]) && $keys[1] == "VALUE=DATE") {
    			$this->_vevent["ALLDAY"] = _ON;
    		} else {
    			$this->_vevent["ALLDAY"] = _OFF;
    		}
    		if ($this->_vevent["ALLDAY"] == _ON) {
	    		$this->_vevent[$keys[0]] = $this->_calendarPlanAction->dateFormat(substr($value,0,8)."000000", $this->_vtimezone["STANDARD"]["TZOFFSETFROM"]);
    		} elseif (isset($keys[1]) && strpos($keys[1], "TZID=") !== false) {
	    		$this->_vevent[$keys[0]] = $this->_calendarPlanAction->dateFormat(substr($value,0,8).
	    											(!is_numeric(substr($value,8,1)) ? substr($value,9,6) : substr($value,8,6)), $this->_vtimezone["STANDARD"]["TZOFFSETFROM"]);
    		} else {
	    		$this->_vevent[$keys[0]] = $this->_calendarPlanAction->dateFormat(substr($value,0,8).
	    											(!is_numeric(substr($value,8,1)) ? substr($value,9,6) : substr($value,8,6)), 0.0);
    		}
    	}
    	if ($keys[0] == "DTEND") {
    		if ($this->_vevent["ALLDAY"] == _ON) {
	    		$this->_vevent[$keys[0]] = $this->_calendarPlanAction->dateFormat(substr($value,0,8)."000000", $this->_vtimezone["STANDARD"]["TZOFFSETTO"], true);
    		} elseif (isset($keys[1]) && strpos($keys[1], "TZID=") !== false) {
	    		$this->_vevent[$keys[0]] = $this->_calendarPlanAction->dateFormat(substr($value,0,8).
	    											(!is_numeric(substr($value,8,1)) ? substr($value,9,6) : substr($value,8,6)), $this->_vtimezone["STANDARD"]["TZOFFSETTO"], true);
	    	} else {
	    		$this->_vevent[$keys[0]] = $this->_calendarPlanAction->dateFormat(substr($value,0,8).
	    											(!is_numeric(substr($value,8,1)) ? substr($value,9,6) : substr($value,8,6)), 0.0, true);
	    	}
    	}
    	return true;
    }
}
?>
