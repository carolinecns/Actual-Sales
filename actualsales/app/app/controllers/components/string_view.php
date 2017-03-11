<?php
class StringViewComponent{
	var $name = 'StringView';
        
	public function __construct(){
		App::import('Core', array('View', 'Controller'));
		$this->reset();
	}
	
	function renderMail($mailView, $layout = 'default'){
		$this->View->layout = '/email/html/' . $layout;
		return $this->View->render('/elements/email/html/' . $mailView);
	}
	
	function reset(){
        $controller = new Controller();
        $controller->helpers = array('Buonny', 'Html');
		$this->View = new View($controller);
	}
	
	function set($one, $two = null){
		$this->View->set($one, $two);
	}
}