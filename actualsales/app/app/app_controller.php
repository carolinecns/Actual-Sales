<?php
class AppController extends Controller {
    var $components = array(
        //'Security', 
        'Email', 'RequestHandler', 'Session', 'BSession', 'CachedAcl', 'Filtros'
    );
    var $helpers = array('Form', 'Html', 'Javascript', 'Time', 'Buonny', 'BForm', 'Bajax');
    var $layout = 'default';
    // var $uses = array('Modulo');
    var $authUsuario;

    function forceSSL() {
        $this->redirect('https://' . env('SERVER_NAME') . $this->here);
    }
    
    function beforeFilter() {
        parent::beforeFilter();
        
        $url = Router::url( $this->here, true );
        
        // Verifica qual layout mostar de acordo com a URL e do nivel do usuário
        $this->layout = 'default';
    
    }
    
    function afterFilter() {
        $this->BSession->close();
    }
    
    function beforeRender(){
        $this->set('isAjax', $this->RequestHandler->isAjax());
        if (isset($this->pageTitle)) $this->set('title_for_layout', $this->pageTitle);
    }
    
    function isAuthorized() {
    	debug($this->name);
        exit;
        $url = array('controller'=>$this->name, 'action'=>$this->params['action']);
        $authUser = $this->BAuth->user();
        
           $temPermissao = $this->BAuth->temPermissao(1, $url);
        
        
        if ($this->RequestHandler->isAjax() && !$temPermissao)
            $this->cakeError('error401');       
        return $temPermissao;
    }



    
}
?>
