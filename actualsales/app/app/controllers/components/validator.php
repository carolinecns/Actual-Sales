<?php
class ValidatorComponent {
	var $name = 'CarregaCombo';
	
	function initialize(&$controller, $settings = array()) {        
		// saving the controller reference for later use
		$this->controller =& $controller;    
	}
        
	function m_rma_estatistica($sintetico = false) {
        if (!isset($this->controller->MRmaEstatistica)) {
            $this->controller->loadModel('MRmaEstatistica');
        }
        // if (empty($this->controller->data['MRmaEstatistica']['codigo_cliente'])) {
        //     $this->controller->MRmaEstatistica->invalidate('codigo_cliente', 'Informe o cliente');
        // }
        // if (empty($this->controller->data['MRmaEstatistica']['codigo_embarcador']) && empty($this->controller->data['MRmaEstatistica']['codigo_transportador'])) {
        //     $this->controller->MRmaEstatistica->invalidate('codigo_embarcador', 'Informe o embarcador ou transportador');
        // }
        if (empty($this->controller->data['MRmaEstatistica']['data_inicial'])) {
            $this->controller->MRmaEstatistica->invalidate('data_inicial', 'Informe a data inicial');
        }
        if (empty($this->controller->data['MRmaEstatistica']['data_final'])) {
            $this->controller->MRmaEstatistica->invalidate('data_final', 'Informe a data final');
        }
        if ($sintetico && empty($this->controller->data['MRmaEstatistica']['agrupamento'])) {
            return false;
        }
        return count($this->controller->MRmaEstatistica->invalidFields()) == 0;
    }
}