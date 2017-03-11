<?php
class BSessionComponent{
	var $name = 'BSession';
	var $components = array('Session');
	
	function setFlash($msg = array(MSGT_NORMAL, 'Ops... Algo deu errado!'), $params_msg = array(), $params = array()){
		if(!is_array($msg)){
			$msg = Configure::read('Message.'.$msg);
		}
		
		$type = array_shift($msg);
		$base_msg = array_shift($msg);
		$msg = vsprintf($base_msg, $params_msg);
		
		$this->Session->setFlash($msg, 'default', array_merge(array('type'=>$type), $params));
	}
	
	function close() {
	    session_write_close();
	}
}
?>