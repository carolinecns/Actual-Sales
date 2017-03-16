<?php
class DadosController extends AppController {
    public $name = 'Dados';
    var $uses = array( 'Cadastro');
    
    public function beforeFilter() {
        parent::beforeFilter();
    }


    function index() {
        $this->pageTitle = 'Cadastros';
    }
   
    function listagem() {
        $this->layout = 'ajax'; 

        $filtros = $this->Filtros->controla_sessao($this->data, $this->Cadastro->name);
        
        $conditions = $this->Cadastro->converteFiltroEmCondition($filtros);
        
        $fields = array('Cadastro.codigo', 'Cadastro.nome', 'Cadastro.email', 'Cadastro.data_nascimento');
        $order = 'Cadastro.nome';

        $this->paginate['Cadastro'] = array(
                'fields' => $fields,
                'conditions' => $conditions,
                'limit' => 50,
                'order' => $order,
        );
       
        $dados = $this->paginate('Cadastro');

        $this->set(compact('dados'));
    }
       
}