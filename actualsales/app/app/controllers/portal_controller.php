<?php
class PortalController extends AppController {
    public $name = 'Portal';
    var $uses = array( 'Cadastro');
    

    function index() {
        $this->pageTitle = 'Portal index;';

        // $this->retorna_dados_cliente($codigo_cliente);
        
        // $this->set(compact('referencia'));
    }

    function cadastro() {
        $this->redirect($this->Dados->index());

        // $this->retorna_dados_cliente($codigo_cliente);
        
        // $this->set(compact('referencia'));
    }

   /* function retorna_dados_cliente($codigo_cliente){
        $this->data = $this->Cliente->find('first', array('conditions' => array('codigo' => $codigo_cliente)));

        $this->set(compact('codigo_cliente'));
    }
   
    function listagem($codigo_cliente, $referencia) {
        $this->layout = 'ajax'; \

        $this->retorna_dados_cliente($codigo_cliente);

        $filtros = $this->Filtros->controla_sessao($this->data, $this->Setor->name);
        
        $conditions = $this->Setor->converteFiltroEmCondition($filtros);
        $conditions = array_merge($conditions, array('codigo_cliente' => $codigo_cliente));

        $fields = array('Setor.codigo', 'Setor.descricao', 'Setor.ativo');
        $order = 'Setor.descricao';

        $this->paginate['Setor'] = array(
                'fields' => $fields,
                'conditions' => $conditions,
                'limit' => 50,
                'order' => $order,
        );
       
        $setores = $this->paginate('Setor');

        $this->set(compact('setores', 'referencia'));
    }
    
    function incluir($codigo_cliente, $referencia) {
        if(empty($codigo_cliente) || empty($referencia)){
            $this->BSession->setFlash('save_error');
            $this->redirect($this->referer());
        }
        $this->pageTitle = 'Incluir Setor';

        if($this->RequestHandler->isPost()) {

            if($this->Setor->incluir($this->data)) {
                $this->BSession->setFlash('save_success');
                $this->redirect(array('controller' => 'setores', 'action' => 'index', $codigo_cliente, $referencia));
            } 
            else {
                $this->BSession->setFlash('save_error');
            }
        }

        $this->retorna_dados_cliente($codigo_cliente);
        $this->set(compact('referencia'));
    }
    
    function editar($codigo_cliente, $codigo_setor, $referencia) {
        $this->pageTitle = 'Editar Setor'; 
        
        if($this->RequestHandler->isPost()) {

            if ($this->Setor->atualizar($this->data)) {
                $this->BSession->setFlash('save_success');
                $this->redirect(array('controller' => 'setores', 'action' => 'index', $this->data['Setor']['codigo_cliente'], $referencia));
            } 
            else {
                $this->BSession->setFlash('save_error');
            }
        } 

        $this->retorna_dados_cliente($codigo_cliente);                      
    
        if (isset($this->passedArgs[1])) {   
            $setores= $this->Setor->find('first', array('conditions' => array('codigo' => $this->passedArgs[1])));

            $this->data = array_merge($this->data, $setores);   
        }

         $this->set(compact('referencia'));
    }

    function atualiza_status($codigo, $status){
        $this->layout = 'ajax';
        
        $this->data['Setor']['codigo'] = $codigo;
        $this->data['Setor']['ativo'] = ($status == 0) ? 1 : 0;

        if ($this->Setor->atualizar($this->data, false)) {   
            print 1;
        } else {
            print 0;
        }
        $this->render(false,false);
        // 0 -> ERRO | 1 -> SUCESSO        
    }*/
}