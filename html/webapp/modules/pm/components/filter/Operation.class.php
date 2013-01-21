<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * pmテーブル登録用クラス
 */
class Pm_Components_Filter_Operation
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Pm_Components_Filter_Operation() 
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}
	
	
	function addFlag($ids = array(), $parameter = '', $options = array()){
		if(is_array($ids)){
			foreach($ids as $id){
				 $params = array(
					"receiver_id" => intval($id),
					"importance_flag" => "1"
				);
				
				if(!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	function addTag($ids = array(), $parameter = '', $options = array()){
		$pmView =& $this->_container->getComponent("pmView");
		
		if(is_array($ids)){
			foreach($ids as $id){
				$tag_id = intval($parameter);
				$receiver_id = intval($id);
				if($tag_id > 0){
					$params = array(
						"tag_id" => $tag_id,
						"receiver_id" => $receiver_id
					);
					$count = $this->_db->countExecute("pm_message_tag_link", $params);
					if ($count == 0) {
						$params["message_id"] = $pmView->getMessageID($receiver_id);
						if(!$this->_db->insertExecute("pm_message_tag_link", $params)){
							return false;
						}
					}
				}
			}
		}
		
		return true;
	}
	
	function forward($ids = array(), $parameter = '', $options = array()){
		$email_filters = array();
		
		$pmAction =& $this->_container->getComponent("pmAction");
		$request =& $this->_container->getComponent("Request");
		$block_id = $request->getParameter("block_id");
		
		if(is_array($ids)){
			$email_address = $parameter;
			
			foreach($ids as $id){
				$email_filters[] = array('receiver_id' => $id,
										 'receiver_user_id' => $options["receiver_user_id"],
										 'receiver_user_name' => $options["receiver_user_name"], 
										 'receiver_auth_id' => $options["receiver_auth_id"], 
										 'email' => $email_address);
			}
		}
		
		if(sizeof($email_filters)){
			return $email_filters;
		}else{
			return true;
		}
	}
	
	function markRead($ids = array(), $parameter = '', $options = array()){	
		if(is_array($ids)){
			foreach($ids as $id){
				$params = array(
					"receiver_id" => intval($id),
					"read_state" => PM_READ_STATE
				);
				
				if(!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	function remove($ids = array(), $parameter = '', $options = array()){
		if(is_array($ids)){
			foreach($ids as $id){
				$params = array(
					"receiver_id" => intval($id),
					"delete_state" => PM_MESSAGE_STATE_TRASH
				);
			
				if (!$this->_db->updateExecute("pm_message_receiver", $params, "receiver_id", true)) {
					return false;
				}
			}
		}
		
		return true;
	}
}
?>