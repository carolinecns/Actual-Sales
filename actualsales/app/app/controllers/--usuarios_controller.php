<?php
class UsuariosController extends AppController {
	var $name = 'Usuarios';
	var $uses = array('Usuario');
	var $helpers = array('Paginator');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->BAuth->allow(array(
			*
			));
	}


	// Função login que renderiza sem layout por questão de modo de montagem da página de login
	public function login($adendo = null)
	{
		if($this->Session->check('validationErrors')) {
			$this->Usuario->validationErrors = $this->Session->read('validationErrors');
			if($this->Session->check('validationData')) {
				$this->data = $this->Session->read('validationData');
				$this->Session->write('validationData', null);
			}
			$this->set('error', true);
			$this->Session->write('validationErrors', null);
		}

		if (stripos(Router::url($_SERVER['HTTP_HOST'], true), 'todosbem') == true) {
			$this->layout = 'default_todosbem';
			$this->render('login_todosbem');
		}
		
		$this->do_login($adendo);
	}

	// Função que realiza o login, utilizada por $this->login_todosbem() e $this->login()
	public function do_login($adendo = null) {

		if (!empty($this->data)) {
			
			$usuario = $this->BAuth->user();

			if (!empty($usuario)) {
				$this->Usuario->bindLazy();
				$usuario = $this->Usuario->carregar($usuario['Usuario']['codigo']);

				if(isset($usuario['Uperfil']['codigo_tipo_perfil']) && $usuario['Uperfil']['codigo_tipo_perfil']){
					$this->Session->write('Auth.Usuario.codigo_tipo_perfil', $usuario['Uperfil']['codigo_tipo_perfil']);
				}

				if (!empty($usuario['Usuario']['codigo_cliente'])) {
					$data_atual      = strtotime(date("Y-m-d"));
					$data_vencimento = strtotime(preg_replace("/(\d{2})\/(\d{2})\/(\d{2,4})/", "$3-$2-$1", substr($usuario['Usuario']['data_senha_expiracao'], 0, 10)));
					if ($data_atual >= $data_vencimento)
						$this->redirect(array('controller' => 'usuarios', 'action' => 'trocar_senha', true));
				}
				if (isset($usuario['Usuario']['codigo_uperfil']) && $usuario['Usuario']['codigo_uperfil'] != null) {
					$this->Session->write('Auth.Usuario.codigo_perfil', $usuario['Usuario']['codigo_uperfil']);
					if (isset($this->data['Usuario']['adendo']) && !empty($this->data['Usuario']['adendo']) && !empty($usuario['Usuario']['codigo_cliente']))
						$this->redirect(array('controller' => 'clientes_produtos_servicos2', 'action' => 'adendo_contrato', false));
					$this->inicio();
				}
				$this->BSession->setFlash('sem_perfil');
				$this->redirect(array('action' => 'logout'));
			} else {
				$this->BSession->setFlash('invalid_login');

				if(!empty($this->data['ref']) && $this->data['ref'] == 'homepage'){
					$this->redirect(array('controller' => 'usuarios', 'action' => 'login'));
				}
			}
		} else {

			if ($adendo && $adendo != 'home') {                
				$this->set(compact('adendo'));
			}

			$authUsuario = $this->BAuth->user();

			if (!empty($authUsuario)) {                
				$this->inicio();
			}

		}
	}

	function inicio(){                   
		$url = Router::url( $this->here,true);
		$usuario = $this->BAuth->user();
		$this->Usuario->bindLazy();
		if(stripos($url, 'sgibr') == true) {
			$modulo_inicial = $this->Usuario->Uperfil->moduloInicialSgi($usuario['Usuario']['codigo_uperfil']);
		} else {
			$modulo_inicial = $this->Usuario->Uperfil->moduloInicial($usuario['Usuario']['codigo_uperfil']);
		}
		
		if ($modulo_inicial == null) {
			$this->BSession->setFlash('sem_modulo_inicial');
			$this->Redirect(array('action' => 'logout'));
		} else {
			$this->Session->write('inicioPortal', true);
			$this->Redirect($modulo_inicial['url']);
		}
	}

	function logout() {
		if (isset($_SESSION['Auth'])) {
			unset($_SESSION['Auth']);
		}

		if(isset($_SESSION['Config'])) {
			unset($_SESSION['Config']);
		}

		$this->Session->destroy();
		if($this->OnOffManutencao()){
			$this->redirect(array('controller'=>'sistemas','action'=>'aviso_manutencao'));
		}

		$this->redirect('/');
	}

	function index() {
		$action = 'editar';         
		$this->loadModel('Uperfil');
		$this->data['Usuario']['action'] = 'editar';
		$this->data['Usuario']['TipoPerfil'] = '';
		$this->data['Usuario']['codigo_cliente'] = '';
		$this->Filtros->limpa_sessao($this->Usuario->name);
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, $this->Usuario->name);
		$this->carrega_combos_perfil($this->data['Usuario']['codigo_cliente']);
		$this->set(compact('action'));
	}

	function listagem($export = false) {
		$this->layout = 'ajax';
		$this->loadModel('Uperfis');
		$filtros    = $this->Filtros->controla_sessao($this->data, $this->Usuario->name);       
		if( !empty($filtros['codigo_documento'])){
			$filtros['codigo_documento'] = Comum::soNumero($filtros['codigo_documento']);
		}
		$conditions = $this->Usuario->converteFiltroEmCondition($filtros);
		// Filtrando o status
		/*if(isset($filtros['ativo'])) {
			if(!($filtros['ativo'] == 'status')) {
				($filtros['ativo'] == 1) ? $conditions['Usuario.ativo'] = TRUE : $conditions['Usuario.ativo'] = FALSE;
			}
		}
		if (!empty($this->authUsuario['Usuario']['codigo_cliente'])) {
			$conditions['Usuario.ativo'] = TRUE;
		}
		
		$limit = 50;
		if ($export) {
			$limit = 9999;
		}
		
		$this->paginate['Usuario'] = array(
			'conditions' => $conditions,
			'limit' => $limit,
			'order' => 'Usuario.nome',
			'fields' => array('Usuario.codigo',
				'Usuario.codigo_cliente',
				'Usuario.codigo_usuario_inclusao',
				'Usuario.codigo_uperfil',
				'Usuario.data_inclusao',
				'Usuario.ativo',
				'Usuario.admin',
				'Usuario.nome',
				'Usuario.apelido',
				'Usuario.senha',
				'Usuario.email',
				'Uperfil.descricao'
				),
			'joins' => array(
				array(
					'table' => "{$this->Uperfis->useTable}",
					'alias' => 'Uperfil',
					'conditions' => 'Uperfil.codigo = Usuario.codigo_uperfil ',
					'type' => 'left'
					)
				)
			);
		$usuarios = $this->paginate('Usuario');
		if ($export) {
			$this->export($usuarios);
		}
		$action = 'editar';
		if(isset($filtros['action']) && ($filtros['action'] == 'configuracao')){
			$action = 'editar_configuracao';
		}else{
			$action = 'editar';
		} 
		$this->set(compact('usuarios', 'action'));
	}


	function incluir() {
		$this->pageTitle = 'Incluir Usuario';
		$this->loadModel('UsuarioAlertaTipo');
		$this->loadModel('AlertaTipo');
		$this->loadModel('Diretoria');
		$this->data['Usuario']['codigo_seguradora'] = NULL;
		$this->data['Usuario']['codigo_corretora']  = NULL;
		$this->data['Usuario']['codigo_cliente']  = NULL;
		$this->data['Usuario']['codigo_departamento']  = 1;

		$listar_tipos_alertas = $this->AlertaAgrupamento->verifica_existencia_agrupamento();
		if ( $this->authUsuario['Usuario']['admin'] ) {
			$this->data['Usuario']['codigo_cliente']      = $this->authUsuario['Usuario']['codigo_cliente'];
		}

		if ($this->RequestHandler->isPost()) {
			

			unset($this->data['Usuario']['codigo_filial']);
			
			try{
				$this->Usuario->query("BEGIN TRANSACTION");
				if(isset($this->data['Usuario']['alerta_sms'])  && $this->data['Usuario']['alerta_sms'] ){
					if( empty($this->data['Usuario']['celular']) ){
						$this->Usuario->invalidate('celular', 'Informe o número de celular para receber alertas por SMS');
						//$this->BSession->setFlash('save_error');                        
						throw new Exception();
					}
				}

				if ( $this->authUsuario['Usuario']['admin'] ) { //Cliente Admin
					if ($this->Usuario->incluir( $this->data )) {
						$insertId = $this->Usuario->getLastInsertId();
						$usuario  = $this->Usuario->find('first', array('conditions' => array('codigo' => $insertId)));
						$encriptacao = ClassRegistry::init('Buonny_Encriptacao');
						$this->data['Usuario']['senha']  = $encriptacao->desencriptar($usuario['Usuario']['senha']);
						$this->data['Usuario']['codigo'] = $insertId;
						$this->Cliente =& ClassRegistry::init('Cliente');
						$cliente = $this->Cliente->find('first', array('conditions' => array('Cliente.codigo' => $this->data['Usuario']['codigo_cliente'])));
						$email = explode(';', $this->data['Usuario']['email']);
						$this->enviaSenhaPorEmail(reset($email), $this->data['Usuario']['senha'], $cliente['Cliente']['razao_social'] ,$this->data['Usuario']['apelido']);
					} else {
					   // $this->BSession->setFlash('save_error');   
						throw new Exception();
					}
				} else {//Admin Portal
					
					if ( $this->Usuario->incluir( $this->data ) ) {
						$insertId = $this->Usuario->getLastInsertId();
					} else {
					   // $this->BSession->setFlash('save_error');                  
						throw new Exception('Erro ao salvar');
					}
				}
				$this->data['Usuario']['alerta_tipo'] = array();

				foreach ($listar_tipos_alertas as $lista_tipo_alerta) {                   
					if(!empty($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
						if(is_array($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
							foreach ($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] as $key => $valor_alerta) {
								array_push($this->data['Usuario']['alerta_tipo'], $valor_alerta);
							}
						}else{
							array_push($this->data['Usuario']['alerta_tipo'], $this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']]);
						} 
					}
				}
				$this->data['Usuario']['codigo'] = $insertId;
				$this->incluirUsuarioAlertaTipo();
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
				$this->redirect(array('action' => 'editar', $insertId));
			} catch(Exception $e) {
				if( !empty($insertId) )
					$this->Usuario->rollback();
				$this->BSession->setFlash('save_error');
			}
		}
		if(isset($this->data['Usuario']['email']) && $this->data['Usuario']['email']){
			$email = explode(';', $this->data['Usuario']['email']);
			$this->data['Usuario']['email'] = $email[0];
			unset($email[0]);
			$this->data['Usuario']['email_alternativo'] = implode(';', $email);
		}
		
		$interno = ( (!empty($this->authUsuario['Usuario']['codigo_cliente']))  ? Array(null,'N') : 'S');
		
		$filtros_alerta = Array(
			'AlertaTipo'=>Array('interno'=>$interno )
			);        
		$alertasTiposLista = $this->AlertaTipo->listarTipoAlerta($filtros_alerta);
		$alertasTipos = array();             
		

		$perfis = $this->Uperfil->carregaPerfisCliente();
		
		$codigo_perfil = $this->authUsuario['Usuario']['codigo_uperfil'];
		$codigo_usuario = $this->authUsuario['Usuario']['codigo'];
		$usuario_superior = $this->Usuario->find( 'list', array('conditions'=>array('ativo'=>1, 'codigo_cliente'=>$this->data['Usuario']['codigo_cliente'])));
		$listar_diretorias = $this->Diretoria->find('list',array('conditions' => array('ativo' => 1)));
		$this->set(compact('listar_diretorias','perfis','alertas_agrupados', 'usuario_superior','codigo_perfil','codigo_usuario'));

		$this->carrega_combos();
	}

	function incluir_por_cliente($codigo_cliente) 
	{
		$this->pageTitle = 'Incluir Usuario';
		$this->loadModel('Cliente');
		$this->loadModel('Uperfil');
		$this->loadModel('AlertaTipo');
		$this->loadModel('UsuarioAlertaTipo');
		$listar_tipos_alertas = $this->AlertaAgrupamento->verifica_existencia_agrupamento();
		$cliente = $this->Cliente->carregar($codigo_cliente);
		if($this->RequestHandler->isPost()) {
			try{
				$this->Usuario->query("BEGIN TRANSACTION");
				$this->setaPerfilUsuarioClienteAdm();
				if($this->data['Usuario']['email_alternativo']){
					$this->Usuario->validate['email'] = array();
					if(!Validation::email($this->data['Usuario']['email'])){
						$this->Usuario->invalidate('email', 'Informe um e-mail válido');
						throw new Exception();
					}
					$emails = explode(';',trim($this->data['Usuario']['email_alternativo'],';'));
					foreach($emails as $email){
						if(!Validation::email($email)){
							$this->Usuario->invalidate('email_alternativo', 'Informe um e-mail válido');
							$this->data['Usuario']['email'] = $this->data['Usuario']['email'].';'.implode(';', $emails);
							throw new Exception();
						}
					}
					$this->data['Usuario']['email'] = $this->data['Usuario']['email'].';'.implode(';', $emails);
				}
				if(!$this->Usuario->incluir($this->data)){
					throw new Exception();
				}
				$insertId = $this->Usuario->getLastInsertId();
				$this->data['Usuario']['alerta_tipo'] = array();

				foreach ($listar_tipos_alertas as $lista_tipo_alerta) {                   
					if(!empty($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
						if(is_array($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
							foreach ($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] as $key => $valor_alerta) {
								array_push($this->data['Usuario']['alerta_tipo'], $valor_alerta);
							}
						}else{
							array_push($this->data['Usuario']['alerta_tipo'], $this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']]);
						} 
					}
				}
				$this->data['Usuario']['codigo'] = $insertId;
				$this->incluirUsuarioAlertaTipo();
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
				$this->redirect(array('action' => 'editar_por_cliente', $insertId));
			}catch(Exception $e){
				if( !empty($insertId))
					$this->Usuario->rollback();
			}
		} else {
			$this->data['Usuario']['senha'] = rand('100000', '999999');
			$this->data['Usuario']['codigo_documento'] = $cliente['Cliente']['codigo_documento'];
			$this->data['Usuario']['codigo_departamento'] = Departamento::OUTROS;
			$this->data['Usuario']['token'] = $this->Usuario->gerarToken();
			$this->data['Usuario']['alerta_tipo'] = array();
			$this->data['Usuario']['codigo_cliente'] = $codigo_cliente;
		}
		if(isset($this->data['Usuario']['email']) && $this->data['Usuario']['email']){
			$email = explode(';', $this->data['Usuario']['email']);
			$this->data['Usuario']['email'] = $email[0];
			unset($email[0]);
			$this->data['Usuario']['email_alternativo'] = implode(';', $email);
		}

		$authUsuario = $this->BAuth->user();
		if(!empty($authUsuario['Usuario']['codigo_cliente'])){
			$conditionsUperfil = array(
				'OR' => array(
					'codigo_cliente' => $authUsuario['Usuario']['codigo_cliente'],
					'codigo' => $authUsuario['Usuario']['codigo_perfil'],
					),
				);
		}else{
			$conditionsUperfil = array(
				'OR' => array(
					'codigo_cliente' => $codigo_cliente,
					array(
						'codigo_tipo_perfil' => TipoPerfil::CLIENTE,
						'codigo_cliente IS NULL',
						)
					),
				);
		}

		$perfis = $this->Uperfil->find('list', array('conditions' => $conditionsUperfil));

		$usuario_superior = $this->Usuario->find( 'list', array('conditions'=>array('ativo'=>1, 'codigo_cliente'=>$this->data['Usuario']['codigo_cliente'])));
		$usuario_cliente = $this->Usuario->carregar($this->params['pass'][0]);
		$codigo_perfil = $usuario_cliente['Usuario']['codigo_uperfil'];
		$codigo_usuario = $usuario_cliente['Usuario']['codigo'];
		$this->set(compact('perfis','usuario_superior','codigo_perfil','codigo_usuario'));
	}

	function carrega_combos_por_cliente($cliente) {
		$this->loadModel('ClientEmpresa');
		$clientes_monitora = $this->ClientEmpresa->porCnpj($cliente['Cliente']['codigo_documento'], true);
		$this->set(compact('clientes_monitora'));
	}
	function carrega_combos() {
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('Departamento');
		if(isset($this->authUsuario['Usuario']['admin']) && $this->authUsuario['Usuario']['admin'] == 1) {
			$uPerfilCodigo = $this->authUsuario['Usuario']['codigo_uperfil'];
			$u_perfis = $this->Uperfil->find('list', array('order' => 'descricao', 'conditions' => array ('or' => array('codigo_cliente' => $this->authUsuario['Usuario']['codigo_cliente'], 'codigo' => $uPerfilCodigo ))));
		} else {
			$departamentos = $this->Departamento->find('list');
			$u_perfis = array('1' => 'Admin') + $this->Uperfil->find('list', array('order' => 'descricao', 'conditions' => array('codigo_cliente' => NULL )));
		}
		
		$this->set(compact('u_perfis', 'departamentos'));
	}

	function editar($codigo_usuario) {
		
		$this->loadModel('UsuarioAlertaTipo');
		//$this->loadModel('TRefeReferencia');
		$this->loadModel('AlertaTipo');
		$this->loadModel('Diretoria');
		$this->pageTitle = 'Atualizar Usuarios';
		$listar_diretorias = $this->Diretoria->find('list',array('conditions' => array('ativo' => 1)));
		$listar_tipos_alertas = $this->AlertaAgrupamento->verifica_existencia_agrupamento();
		
		if (!empty($this->data)) {

			$this->data['Usuario']['codigo_filial'] = NULL;
			
			try{
				$this->Usuario->query("BEGIN TRANSACTION");
				if( isset($this->data['Usuario']['alerta_sms']) && $this->data['Usuario']['alerta_sms'] ){
					$this->Usuario->validate['celular'] = array(
						'rule' => 'notEmpty',
						'message' => 'Informe o número de celular para receber alertas por SMS',
						);
				}
				if($this->data['Usuario']['email_alternativo']){
					$this->Usuario->validate['email'] = array();
					if(!Validation::email($this->data['Usuario']['email'])){
						$this->Usuario->invalidate('email', 'Informe um e-mail válido');
						throw new Exception();
					}
					$emails = explode(';',trim($this->data['Usuario']['email_alternativo'],';'));
					foreach($emails as $email){
						if(!Validation::email($email)){
							$this->Usuario->invalidate('email_alternativo', 'Informe um e-mail válido');
							$this->data['Usuario']['email'] = $this->data['Usuario']['email'].';'.implode(';', $emails);
							throw new Exception();
						}
					}
					$this->data['Usuario']['email'] = $this->data['Usuario']['email'].';'.implode(';', $emails);
				}
				$this->validaPerfilClienteAdm();//Validacao quando o cliente Adm for salvar um usuario
				$this->setaPerfilUsuarioClienteAdm();

				$this->data['Usuario']['alerta_tipo'] = array();

				foreach ($listar_tipos_alertas as $lista_tipo_alerta) {                   
					if(!empty($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
						if(is_array($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
							foreach ($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] as $key => $valor_alerta) {
								array_push($this->data['Usuario']['alerta_tipo'], $valor_alerta);
							}
						}else{
							array_push($this->data['Usuario']['alerta_tipo'], $this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']]);
						} 
					}
				}
				
				if(!$this->Usuario->atualizar($this->data))
					throw new Exception();
				$this->incluirUsuarioAlertaTipo();
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
				$this->redirect(array('action' => 'index'));
			} catch(Exception $e) {
				$this->Usuario->rollback();
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->Usuario->bindLazy();
			$this->data = $this->Usuario->read(null, $codigo_usuario);
			if($this->data['Uperfil']['codigo_tipo_perfil'] == 5 && empty($this->data['Usuario']['email']))
				$this->data['Usuario']['email'] = strtolower($this->data['Usuario']['apelido']).'@rhhealth.com.br';

			
			if( !empty($this->authUsuario['Usuario']['codigo_cliente'] ) ){//Para nao permitir que o cliente edite o cadastro de uma usuario que nao é dele
				if( empty($this->data['Usuario']['codigo_cliente']) || $this->data['Usuario']['codigo_cliente'] != $this->authUsuario['Usuario']['codigo_cliente'] ){
					$this->redirect("/usuarios");
				}
			}
			
			$usuarioAlertasTiposLista = $this->UsuarioAlertaTipo->listarTiposPorUsuario($codigo_usuario);
			$usuarioAlertasTipos = array();
			foreach($usuarioAlertasTiposLista as $value){
				$usuarioAlertasTipos[] = $value['UsuarioAlertaTipo']['codigo_alerta_tipo'];
			}
			$this->data['Usuario']['alerta_tipo'] = $usuarioAlertasTipos;


			if(isset($this->data['Usuario']['refe_codigo_origem']) && !empty($this->data['Usuario']['refe_codigo_origem'])){
				$refe_origem_padrao = $this->TRefeReferencia->carregar($this->data['Usuario']['refe_codigo_origem']);
				if($refe_origem_padrao){
					$this->data['Usuario']['refe_codigo_origem_visual'] = $refe_origem_padrao['TRefeReferencia']['refe_descricao'];
				}
			}
			
		}
		if ( $this->authUsuario['Usuario']['codigo'] == $this->data['Usuario']['codigo']) {
			$barrar_perfil = isset($this->authUsuario['Usuario']['admin']) && $this->authUsuario['Usuario']['admin'] == 1 ? 1: 0;
			if ($barrar_perfil && isset($this->authUsuario['Usuario']['codigo_perfil']))
				$this->data['Usuario']['codigo_perfil'] = $this->authUsuario['Usuario']['codigo_perfil'];
		}
		
		if(isset($this->data['Usuario']['email']) && $this->data['Usuario']['email']){
			$email = explode(';', $this->data['Usuario']['email']);
			$this->data['Usuario']['email'] = $email[0];
			unset($email[0]);
			$this->data['Usuario']['email_alternativo'] = implode(';', $email);
		}
		$perfil = $this->Usuario->carregar($codigo_usuario);
		$codigo_perfil = $perfil['Usuario']['codigo_uperfil'];
		
		foreach ($listar_tipos_alertas as $lista_tipo_alerta){ 
			$this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] = !empty($this->data['Usuario']['alerta_tipo']) ? $this->data['Usuario']['alerta_tipo'] : null;
		}
		$usuario_superior = $this->Usuario->listaUsuariosNaoSubordinados( $codigo_usuario, $this->authUsuario['Usuario']['codigo_cliente'] );
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, "Usuario");
		$this->set(compact('barrar_perfil', 'alertas_agrupados', 'usuario_superior','codigo_perfil','codigo_usuario','listar_diretorias'));
		$this->carrega_combos();
	}


	function editar_alertas_por_cliente($codigo_usuario) {
		$this->loadModel('UsuarioAlertaTipo');
		$this->loadModel('AlertaTipo');
		$listar_tipos_alertas = $this->AlertaAgrupamento->verifica_existencia_agrupamento();
		if (!empty($this->data)) {
			try {
				$this->Usuario->query('BEGIN TRANSACTION');
				if(!$this->Usuario->atualizar($this->data)){
					throw new Exception("Erro ao atualizar usuário");
				}
				$this->data['Usuario']['alerta_tipo'] = array();
				foreach ($listar_tipos_alertas as $lista_tipo_alerta) {                   
					if(!empty($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
						if(is_array($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
							foreach ($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] as $key => $valor_alerta) {
								array_push($this->data['Usuario']['alerta_tipo'], $valor_alerta);
							}
						}else{
							array_push($this->data['Usuario']['alerta_tipo'], $this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']]);
						} 
					}
				}
				$this->incluirUsuarioAlertaTipo();
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
				$this->redirect(array('action' => 'alertas_por_cliente', $this->data['Usuario']['codigo_cliente']));
			} catch (Exception $ex) {
				$this->Usuario->rollback();
			}
		} else {
			$this->data = $this->Usuario->read(null, $codigo_usuario);
			$usuarioAlertasTiposLista = $this->UsuarioAlertaTipo->listarTiposPorUsuario($codigo_usuario);
			$usuarioAlertasTipos = array();
			foreach($usuarioAlertasTiposLista as $value){
				$usuarioAlertasTipos[] = $value['UsuarioAlertaTipo']['codigo_alerta_tipo'];
			}
			$this->data['Usuario']['alerta_tipo'] = $usuarioAlertasTipos;
			if(isset($this->data['Usuario']['email']) && $this->data['Usuario']['email']){
				$email = explode(';', $this->data['Usuario']['email']);
				$this->data['Usuario']['email'] = $email[0];
				unset($email[0]);
				$this->data['Usuario']['email_alternativo'] = implode(';', $email);
			}
		}
		
		foreach ($listar_tipos_alertas as $lista_tipo_alerta){
			$this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] = $this->data['Usuario']['alerta_tipo'];
		}                   

		$perfil = $this->Usuario->carregar($codigo_usuario);
		$codigo_perfil = $perfil['Usuario']['codigo_uperfil'];
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, "Usuario");
		$this->set(compact('codigo_perfil','codigo_usuario'));
	}

	function editar_por_cliente($codigo_usuario) {
		$this->loadModel('Cliente');
		$this->loadModel('Uperfil');
		$this->loadModel('UsuarioAlertaTipo');
		$this->loadModel('AlertaTipo');
		$this->pageTitle = 'Atualizar Usuario';
		$listar_tipos_alertas = $this->AlertaAgrupamento->verifica_existencia_agrupamento();
		
		if (!empty($this->data)) {
			if(empty($this->data['Usuario']['senha'])){
				unset($this->data['Usuario']['senha']);
			}
			try{
				$this->Usuario->query("BEGIN TRANSACTION");

				if($this->data['Usuario']['alerta_sms']){
					$this->Usuario->validate['celular'] = array(
						'rule' => 'notEmpty',
						'message' => 'Informe o número de celular para receber alertas por SMS',
						);
				}
				if($this->data['Usuario']['email_alternativo']){
					$this->Usuario->validate['email'] = array();
					if(!Validation::email($this->data['Usuario']['email'])){
						$this->Usuario->invalidate('email', 'Informe um e-mail válido');
						throw new Exception();
					}
					$emails = explode(';',trim($this->data['Usuario']['email_alternativo'],';'));
					foreach($emails as $email){
						if(!Validation::email($email)){
							$this->Usuario->invalidate('email_alternativo', 'Informe um e-mail válido');
							$this->data['Usuario']['email'] = $this->data['Usuario']['email'].';'.implode(';', $emails);
							throw new Exception();
						}
					}
					$this->data['Usuario']['email'] = $this->data['Usuario']['email'].';'.implode(';', $emails);
				}
				$this->setaPerfilUsuarioClienteAdm();
				$this->loadModel('ClientEmpresa');
				$cliente = $this->Cliente->carregar( $this->data['Usuario']['codigo_cliente'] );
				$this->data['Usuario']['alerta_tipo'] = array();
				foreach ($listar_tipos_alertas as $lista_tipo_alerta) {                   
					if(!empty($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
						if(is_array($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']])){
							foreach ($this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] as $key => $valor_alerta) {
								array_push($this->data['Usuario']['alerta_tipo'], $valor_alerta);
							}
						}else{
							array_push($this->data['Usuario']['alerta_tipo'], $this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']]);
						} 
					}
				}

				if(!$this->Usuario->atualizar($this->data))
					throw new Exception();
				$this->incluirUsuarioAlertaTipo();
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
				// $this->redirect(array('action' => 'por_cliente', $this->data['Usuario']['codigo_cliente']));
			}catch(Exception $e){
				$this->Usuario->rollback();
			}
		} else {
			$this->data = $this->Usuario->read(null, $codigo_usuario);
			$usuarioAlertasTiposLista = $this->UsuarioAlertaTipo->listarTiposPorUsuario($codigo_usuario);
			$usuarioAlertasTipos = array();
			foreach($usuarioAlertasTiposLista as $value){
				$usuarioAlertasTipos[] = $value['UsuarioAlertaTipo']['codigo_alerta_tipo'];
			}
			$this->data['Usuario']['alerta_tipo'] = $usuarioAlertasTipos;

			if(isset($this->data['Usuario']['refe_codigo_origem']) && !empty($this->data['Usuario']['refe_codigo_origem'])){
				$refe_origem_padrao = $this->TRefeReferencia->carregar($this->data['Usuario']['refe_codigo_origem']);
				if($refe_origem_padrao){
					$this->data['Usuario']['refe_codigo_origem_visual'] = $refe_origem_padrao['TRefeReferencia']['refe_descricao'];
				}
			}
		}

		if(isset($this->data['Usuario']['email']) && $this->data['Usuario']['email']){
			$email = explode(';', $this->data['Usuario']['email']);
			$this->data['Usuario']['email'] = $email[0];
			unset($email[0]);
			$this->data['Usuario']['email_alternativo'] = implode(';', $email);
		}

		$cliente = $this->Cliente->carregar($this->data['Usuario']['codigo_cliente']);

		$authUsuario = $this->BAuth->user();
		if(!empty($authUsuario['Usuario']['codigo_cliente'])){
			$conditionsUperfil = array(
				'OR' => array(
					'codigo_cliente' => $authUsuario['Usuario']['codigo_cliente'],
					'codigo' => $authUsuario['Usuario']['codigo_perfil'],
					),
				);
		}else{
			$conditionsUperfil = array(
				'OR' => array(
					'codigo_cliente' => $this->data['Usuario']['codigo_cliente'],
					array(
						'codigo_tipo_perfil' => TipoPerfil::CLIENTE,
						'codigo_cliente IS NULL',
						)
					),
				);
		}
		$perfis = $this->Uperfil->find('list', array('conditions' => $conditionsUperfil));
		$this->carrega_combos_por_cliente($cliente);
		$this->carrega_combos();
		
		foreach ($listar_tipos_alertas as $lista_tipo_alerta){
			$this->data['Usuario']['alerta_tipo_'.$lista_tipo_alerta['AlertaAgrupamento']['descricao']] = $this->data['Usuario']['alerta_tipo'];
		}                 

		$usuario_superior = $this->Usuario->listaUsuariosNaoSubordinados( $codigo_usuario, $this->data['Usuario']['codigo_cliente'] );
		$perfil = $this->Usuario->carregar($codigo_usuario);
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, "Usuario");
		$codigo_perfil = $perfil['Usuario']['codigo_uperfil'];  
		
		$this->set(compact('perfis','alertas_agrupados', 'usuario_superior','codigo_perfil','codigo_usuario'));
	}

	function excluir($codigo_usuario) {
		$this->layout = 'ajax';

		if (!empty($codigo_usuario)) {
			if ($this->Usuario->excluir($codigo_usuario)) {
				$this->BSession->setFlash('save_success');
			} else {
				$this->BSession->setFlash('save_error');
			}
		}
		exit;
	}

	function excluir_usuario($codigo_usuario) {
		$this->layout = 'ajax';
		if (!empty($codigo_usuario)) {
			$this->data['Usuario']['codigo'] = $codigo_usuario;
			$this->data['Usuario']['ativo'] = 0;

			if ($this->Usuario->atualizar($this->data)) {
				print 1;
			} else {
				print 0;
			}
		}
		exit;
	}

	function por_cliente($codigo_cliente) {
		$this->Cliente =& ClassRegistry::init('Cliente');
		$this->Filtros->limpa_sessao($this->Usuario->name);
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, 'Usuario');
		$this->data['Usuario']['codigo_cliente'] = $codigo_cliente;
		$cliente = $this->Cliente->carregar($this->data['Usuario']['codigo_cliente']);
		$somente_ativos = !empty($this->authUsuario['Usuario']['codigo_cliente']);
		$usuarios = $this->Usuario->listaPorCliente($codigo_cliente, false, $somente_ativos);
		$this->carrega_combos_perfil();
		$this->set(compact('codigo_cliente'));
	}

	function por_fornecedor($codigo_fornecedor) {
		$this->loadModel('Fornecedor');
		$fornecedor = $this->Fornecedor->carregar($codigo_fornecedor);
		$usuarios = $this->Usuario->listaPorfornecedor($codigo_fornecedor);
		$this->set(compact('fornecedor', 'usuarios'));
	}

	function listagem_por_fornecedor($codigo_fornecedor) {
		$this->layout = 'ajax';
		$usuarios = $this->Usuario->listaPorfornecedor($codigo_fornecedor);
		$this->set(compact('usuarios'));
	}

	function incluir_por_fornecedor($codigo_fornecedor) {
		$this->pageTitle = 'Incluir Usuario';
		$this->loadModel('Fornecedor');
		$this->loadModel('Uperfil');
		$corretora = $this->Fornecedor->carregar($codigo_corretora);
		if($this->RequestHandler->isPost()) {
			$this->data['Usuario']['codigo_fornecedor'] = $codigo_fornecedor;
			$this->data['Usuario']['codigo_departamento'] = Departamento::OUTROS;
			if ($this->Usuario->incluir($this->data)) {
				$this->BSession->setFlash('save_success');
			}else{
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->data['Usuario']['senha'] = rand('100000', '999999');
			$this->data['Usuario']['codigo_documento'] = $fornecedor['Fornecedor']['codigo_documento'];
		}
		$perfis = $this->Uperfil->carregaPerfisFornecedor();
		$this->set(compact('perfis'));
	}

	function editar_por_fornecedor($codigo_fornecedor) {
		$this->pageTitle = 'Atualizar Usuario';
		if (!empty($this->data)) {
			if(empty($this->data['Usuario']['senha'])){
				unset($this->data['Usuario']['senha']);
			}
			if ($this->Usuario->atualizar($this->data)) {
				$this->BSession->setFlash('save_success');
			}
		} else {
			$this->data = $this->Usuario->read(null, $codigo_fornecedor);
		}
		$this->loadModel('Fornecedor');
		$this->loadModel('Uperfil');
		$fornecedor = $this->Fornecedor->carregar($this->data['Usuario']['codigo_fornecedor']);
		$perfis = $this->Uperfil->carregaPerfisfornecedor();
		$this->set(compact('perfis'));
	}

	function alertas_por_cliente($codigo_cliente) {
		$this->por_cliente($codigo_cliente);
	}

	function listagem_por_cliente($codigo_cliente) {
		$this->layout = 'ajax';
		$this->TipoPerfil =& ClassRegistry::init('TipoPerfil');
		$usu = $this->TipoPerfil->find('all', array('fields' => array('TipoPerfil.descricao')));
		$filtros = $this->Filtros->controla_sessao($this->data, 'Usuario');
		$options['conditions'] = $this->Usuario->converteFiltroEmCondition($filtros);
		$cliente = $this->Cliente->carregar($codigo_cliente);
		$somente_ativos = !empty($this->authUsuario['Usuario']['codigo_cliente']);
		$usuarios = $this->Usuario->listaPorCliente($codigo_cliente, false, $somente_ativos, $options);
		$this->set(compact('usuarios', 'usu', 'cliente'));
	}

	function listagem_alertas_por_cliente($codigo_cliente) {
		$this->listagem_por_cliente($codigo_cliente);
	}

	function json_por_cliente($codigo_cliente) {
		$usuarios = $this->Usuario->listaPorClienteList($codigo_cliente);
		echo json_encode($usuarios);
		exit;
	}

	function incluir_por_seguradora($codigo_seguradora) {
		$this->pageTitle = 'Incluir Usuario';
		$this->loadModel('Seguradora');
		$this->loadModel('Uperfil');
		$seguradora = $this->Seguradora->carregar($codigo_seguradora);
		if($this->RequestHandler->isPost()) {
			$this->data['Usuario']['codigo_seguradora'] = $codigo_seguradora;
			// $this->data['Usuario']['codigo_departamento'] = Departamento::OUTROS;
			if ($this->Usuario->incluir($this->data)) {
				$this->BSession->setFlash('save_success');
			}else{
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->data['Usuario']['senha'] = rand('100000', '999999');
			$this->data['Usuario']['codigo_documento'] = $seguradora['Seguradora']['codigo_documento'];
		}
		$perfis = $this->Uperfil->carregaPerfisSeguradora();
		$this->set(compact('perfis'));
	}

	function incluir_por_corretora($codigo_corretora) {
		$this->pageTitle = 'Incluir Usuario';
		$this->loadModel('Corretora');
		$this->loadModel('Uperfil');
		$corretora = $this->Corretora->carregar($codigo_corretora);
		if($this->RequestHandler->isPost()) {
			$this->data['Usuario']['codigo_corretora'] = $codigo_corretora;
			// $this->data['Usuario']['codigo_departamento'] = Departamento::OUTROS;
			if ($this->Usuario->incluir($this->data)) {
				$this->BSession->setFlash('save_success');
			}else{
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->data['Usuario']['senha'] = rand('100000', '999999');
			$this->data['Usuario']['codigo_documento'] = $corretora['Corretora']['codigo_documento'];
		}
		$perfis = $this->Uperfil->carregaPerfisCorretora();
		$this->set(compact('perfis'));
	}

	function editar_por_seguradora($codigo_usuario) {
		$this->pageTitle = 'Atualizar Usuario';
		if (!empty($this->data)) {
			if(empty($this->data['Usuario']['senha'])){
				unset($this->data['Usuario']['senha']);
			}
			if ($this->Usuario->atualizar($this->data)) {
				$this->BSession->setFlash('save_success');
			}
		} else {
			$this->data = $this->Usuario->read(null, $codigo_usuario);
		}
		$this->loadModel('Seguradora');
		$this->loadModel('Uperfil');
		$seguradora = $this->Seguradora->carregar($this->data['Usuario']['codigo_seguradora']);
		$perfis = $this->Uperfil->carregaPerfisSeguradora();
		$this->set(compact('perfis'));
	}

	function por_seguradora($codigo_seguradora) {
		$this->Seguradora =& ClassRegistry::init('Seguradora');
		$seguradora = $this->Seguradora->carregar($codigo_seguradora);
		$usuarios = $this->Usuario->listaPorSeguradora($codigo_seguradora);
		$this->set(compact('seguradora', 'usuarios'));
	}

	function listagem_por_seguradora($codigo_seguradora) {
		$this->layout = 'ajax';
		$usuarios = $this->Usuario->listaPorSeguradora($codigo_seguradora);
		$this->set(compact('usuarios'));
	}

	function por_corretora($codigo_corretora) {

		$this->Corretora =& ClassRegistry::init('Corretora');
		$corretora = $this->Corretora->carregar($codigo_corretora);
		$usuarios = $this->Usuario->listaPorCorretora($codigo_corretora);
		$this->set(compact('corretora', 'usuarios'));
	}

	function listagem_por_corretora($codigo_corretora) {
		$this->layout = 'ajax';
		$usuarios = $this->Usuario->listaPorCorretora($codigo_corretora);
		$this->set(compact('usuarios'));
	}

	function editar_por_corretora($codigo_corretora) {
		$this->pageTitle = 'Atualizar Usuario';
		if (!empty($this->data)) {
			if(empty($this->data['Usuario']['senha'])){
				unset($this->data['Usuario']['senha']);
			}
			if ($this->Usuario->atualizar($this->data)) {
				$this->BSession->setFlash('save_success');
			}
		} else {
			$this->data = $this->Usuario->read(null, $codigo_corretora);
		}
		$this->loadModel('Corretora');
		$this->loadModel('Uperfil');
		$corretora = $this->Corretora->carregar($this->data['Usuario']['codigo_corretora']);
		$perfis = $this->Uperfil->carregaPerfisCorretora();
		$this->set(compact('perfis'));
	}

	function incluir_por_filial($codigo_filial) {
		$this->pageTitle = 'Incluir Usuario';
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('Uperfil');
		$filial = $this->EnderecoRegiao->carregar($codigo_filial);
		if($this->RequestHandler->isPost()) {
			$this->data['Usuario']['codigo_filial'] = $codigo_filial;
			// $this->data['Usuario']['codigo_departamento'] = Departamento::OUTROS;
			$this->data['Usuario']['codigo_documento'] = '01648034000150';
			if ($this->Usuario->incluir($this->data)) {
				$this->BSession->setFlash('save_success');
			}else{
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->data['Usuario']['senha'] = rand('100000', '999999');
		}
		$perfis = $this->Uperfil->carregaPerfisFilial();
		$this->set(compact('perfis'));
	}

	function editar_por_filial($codigo_usuario) {
		$this->pageTitle = 'Atualizar Usuario';
		if (!empty($this->data)) {
			if(empty($this->data['Usuario']['senha'])){
				unset($this->data['Usuario']['senha']);
			}
			if ($this->Usuario->atualizar($this->data)) {
				$this->BSession->setFlash('save_success');
			}
		} else {
			$this->data = $this->Usuario->read(null, $codigo_usuario);
		}
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('Uperfil');
		$filial = $this->EnderecoRegiao->carregar($this->data['Usuario']['codigo_filial']);
		$perfis = $this->Uperfil->carregaPerfisFilial();
		$this->set(compact('perfis'));
	}

	function por_filial($codigo_filial) {
		$this->EnderecoRegiao =& ClassRegistry::init('EnderecoRegiao');
		$filial = $this->EnderecoRegiao->carregar($codigo_filial);
		$usuarios = $this->Usuario->listaPorFilial($codigo_filial);
		$this->set(compact('filial', 'usuarios'));
	}

	function listagem_por_filial($codigo_filial) {
		$this->layout = 'ajax';
		$usuarios = $this->Usuario->listaPorFilial($codigo_filial);
		$this->set(compact('usuarios'));
	}

	function recuperar_senha() {
		if (!empty($this->data)) {
			$usuario = $this->Usuario->findByApelido($this->data['Usuario']['apelido']);
			if (empty($usuario['Usuario']['codigo_cliente']) && empty($usuario['Usuario']['codigo_fornecedor']))
				$this->BSession->setFlash('no_client_user');
			else {
				App::import('Vendor', 'encriptacao');
				$encriptacao = new Buonny_Encriptacao();
				$this->data['Usuario']['senha'] = $encriptacao->desencriptar($usuario['Usuario']['senha']);
			}
		}
	}

	function recuperar_senha_cliente() {
		$this->pageTitle = 'Recuperar Senha Usuario';
		if (!empty($this->data)){
			$usuario = $this->Usuario->recuperarSenhaCliente($this->data);
			if ($usuario){
				$this->Cliente =& ClassRegistry::init('Cliente');
				$this->LogRecuperaSenha =& ClassRegistry::init('LogRecuperaSenha');
				$cliente = $this->Cliente->find('first', array('conditions' => array('Cliente.codigo' => $usuario['Usuario']['codigo_cliente'])));
				$email = explode(';', $usuario['Usuario']['email']);        
				if($this->enviaSenhaPorEmail(reset($email), $usuario['Usuario']['senha'], $cliente['Cliente']['razao_social'],$usuario['Usuario']['apelido'])){
					$this->LogRecuperaSenha->incluir_log($usuario,reset($email));
					$this->BSession->setFlash('envio_senha_email_success');
				}
			} else {
				$this->BSession->setFlash('envio_senha_email_error');
			}
		}
	}

	function envia_acesso_cliente($codigo_usuario) {
		require_once(ROOT . DS . 'app' . DS . 'vendors' . DS . 'buonny' . DS . 'encriptacao.php');
		$Encriptador = new Buonny_Encriptacao();

		$dados = $this->Usuario->find('first', array('fields' => array('senha','email','apelido'),'conditions' => array('Usuario.codigo' => $codigo_usuario)));
		
		if (!$dados){
			return false;
		}

		$senha = $Encriptador->desencriptar($dados['Usuario']['senha']);
		$nome_usuario = $dados['Usuario']['apelido'];
		$mensagens = array('Senha: '.$senha);

		// $this->StringView->set(compact('nome_usuario','mensagens', 'cliente','dados'));
		// $content = $this->StringView->renderMail('envio_senha_email', 'default');
		// $options = array(
		// 	'from' => 'portal@rhhealth.com.br',
		// 	'sent' => null,
		// 	'to' => $dados['Usuario']['email'],
		// 	'subject' => 'Sua Senha de Acesso ao Sistema!',
		// 	);
		// if($this->Scheduler->schedule($content, $options)) {
		// 	$this->BSession->setFlash('envio_senha_email_success');
		// } else {
		// 	$this->BSession->setFlash('envio_senha_email_error');
		// }
		// $this->redirect("/usuarios");
	}

	function enviaSenhaPorEmail($email, $senha, $nome_cliente,$nome_usuario) {
		// $mensagens = array('Sua senha: '.$senha);
		// $this->StringView->set(compact('nome_cliente','nome_usuario','mensagens'));
		// $content = $this->StringView->renderMail('envio_senha_email', 'default');
		// $options = array(
		// 	'from' => 'portal@rhhealth.com.br',
		// 	'sent' => null,
		// 	'to' => $email,
		// 	'subject' => 'Recuperação de senha',
		// 	);
		// return $this->Scheduler->schedule($content, $options) ? true: false;
	}

	function listar_clientes_monitora($codigo_cliente) {
		$somente_ativos = !empty($this->authUsuario['Usuario']['codigo_cliente']);
		$results = $this->Usuario->listaPorCliente($codigo_cliente, true, $somente_ativos);
		$this->set(compact('results'));
	}

	function listar_clientes($codigo_cliente) {
		$somente_ativos = !empty($this->authUsuario['Usuario']['codigo_cliente']);
		$results = $this->Usuario->listaPorCliente($codigo_cliente, false, $somente_ativos);
		$this->set(compact('results'));
	}

	function usuario_monitora($codigo_usuario) {
		$this->autoRender = false;
		$results = $this->Usuario->carregar($codigo_usuario);
		if ($results)
			echo $results['Usuario']['codigo_usuario_monitora'];
	}

	function minhas_configuracoes() {
		$this->pageTitle = 'Minhas Configurações';
		$usuario = $this->BAuth->user();
		$usuario_banco = $this->Usuario->find('first', array('conditions'=>array('codigo'=>$usuario['Usuario']['codigo']), 'fields'=>array('codigo', 'alerta_portal', 'alerta_email', 'alerta_sms', 'celular', 'email', 'cracha')));
		if (!empty($this->data)) {
			$this->data['Usuario']['codigo'] = $usuario['Usuario']['codigo'];
			if(!empty($usuario_banco['Usuario']['cracha'])){
				unset($data['Usuario']['cracha']);
			}
			try{
				$this->Usuario->query("BEGIN TRANSACTION");
				if(!$this->Usuario->atualizar($this->data))
					throw new Exception();
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
			}catch(Exception $e){
				$this->Usuario->rollback();
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->data = $usuario_banco;
		}
		$this->set(compact('usuario'));
	}

	function gerar_token(){
		echo json_encode($this->Usuario->gerarToken());
		exit;
	}

	function por_perfil($codigo_uperfil) {
		$this->pageTitle = 'Usuários por Perfil';
		$this->loadModel('Uperfil');
		$this->loadModel('Cliente');
		$uperfil = $this->Uperfil->carregar($codigo_uperfil);
		$this->paginate['Usuario'] = array(
			'conditions' => array('codigo_uperfil' => $codigo_uperfil),
			'order' => array('Cliente.razao_social'),
			'joins' => array(
				array(
					'table' => "{$this->Cliente->databaseTable}.{$this->Cliente->tableSchema}.{$this->Cliente->useTable}",
					'alias' => 'Cliente',
					'conditions' => array('Usuario.codigo_cliente = Cliente.codigo'),
					'fields' => array('razao_social'),
					'type' => 'LEFT',
					)
				),
			'fields' => array('Cliente.razao_social', 'Usuario.apelido', 'Usuario.nome')
			);
		$usuarios = $this->paginate('Usuario');
		$this->set(compact('usuarios', 'uperfil'));
	}

	function cadastrar_digital(){
		$this->Session->delete('Config');
		$this->pageTitle = "Cadastro da digital";
		$this->layout = 'new_window';
		$usuario = $this->BAuth->user();

		$this->set('codigo_usuario', $usuario['Usuario']['codigo']);
	}

	function carregar_usuario( $codigo ){
		if( $codigo > 0 ){
			$dados_usuario = $this->Usuario->carregar( $codigo );
			echo json_encode( $dados_usuario );
			die( );
		}
	}

	function OnOffManutencao(){
		$caminho = $_SERVER['DOCUMENT_ROOT']."/arquivos/desativar.txt";
		if(file_exists($caminho)){
			return true;
		}
		return false;
	}

	function validaPerfilClienteAdm(){
		if( !empty($this->authUsuario['Usuario']['codigo_cliente'] ) ){

			$perfis = $this->Uperfil->carregaPerfisCadastradosPeloCliente( $this->authUsuario['Usuario']['codigo_cliente'] );

			if( (!empty($this->data['Usuario']['codigo_uperfil']) && empty($perfis[$this->data['Usuario']['codigo_uperfil']])) &&
				$this->data['Usuario']['codigo_uperfil'] != $this->authUsuario['Usuario']['codigo_uperfil']) 
			{
				$this->Usuario->invalidate('codigo_uperfil', 'Perfil inválido.');
				throw new Exception();
			}
		}
	}

	function incluirUsuarioAlertaTipo() {
		if( isset($this->data['Usuario']['alerta_email']) && $this->data['Usuario']['alerta_email']
			|| isset($this->data['Usuario']['alerta_portal']) && $this->data['Usuario']['alerta_portal']
			|| isset($this->data['Usuario']['alerta_sms'])    && $this->data['Usuario']['alerta_sms'] ){
			$dados = array();
		if(!empty($this->data['Usuario']['alerta_tipo'])){
			foreach($this->data['Usuario']['alerta_tipo'] as $alertaTipo){
				$dados[] = array(
					'UsuarioAlertaTipo' => array(
						'codigo_usuario' => $this->data['Usuario']['codigo'],
						'codigo_alerta_tipo' => $alertaTipo,
						),
					);
			}
		}
		if(!$this->UsuarioAlertaTipo->excluirPorUsuario($this->data['Usuario']['codigo']))
			throw new Exception("Erro ao excluir Alertas Usuario");
		if(!empty($dados)){
			if(!$this->UsuarioAlertaTipo->incluirAlertasTipos($dados))
				throw new Exception("Erro ao incluir alertas para o usuário");
		}
	}
}

function setaPerfilUsuarioClienteAdm(){
	$usuario = $this->BAuth->user();
		//Cliente com permissao de cadastro de usuarios e perfis (ou seja, eu nao sou da TI)
		if(isset($usuario['Usuario']['codigo_perfil']) && ($usuario['Usuario']['codigo_perfil'] != Uperfil::ADMIN || $usuario['Usuario']['codigo_perfil'] != 20) ) {//Gerente TI
			if( isset($this->data['Usuario']['admin']) && $this->data['Usuario']['admin'] == 1 && $usuario['Usuario']['admin'] == 1 ) {
				$this->data['Usuario']['codigo_uperfil'] = $usuario['Usuario']['codigo_perfil'];
			} elseif( empty($this->data['Usuario']['codigo_uperfil']) && !empty($this->data['Usuario']['codigo']) && $usuario['Usuario']['admin'] == 1 ) {
				$conditions    = array('codigo' => $this->data['Usuario']['codigo'] );
				$dados_usuario = $this->Usuario->find('first', compact('conditions'));
				$this->data['Usuario']['codigo_uperfil'] = $dados_usuario['Usuario']['codigo_uperfil'];
			}
		}
	}

	function incluir_veiculo_alerta($codigo_usuario){
		$this->pageTitle = "Adicionar Veículo";
		$this->loadModel('UsuarioVeiculoAlerta');
		$this->loadModel('Veiculo');

		if($this->RequestHandler->isPost()){
			$i = 0;
			foreach($this->data['UsuarioVeiculoAlerta']['placa'] as $key => $placa){
				if(!$placa || $placa == '___-____'){
					unset($this->data['UsuarioVeiculoAlerta']['placa'][$key]);
				}else{
					$codigo_veiculo = $this->Veiculo->buscaCodigodaPlaca($placa);
					if(!$codigo_veiculo){
						$this->UsuarioVeiculoAlerta->validationErrors['placa'][$i] = 'Placa inválida';
						$this->UsuarioVeiculoAlerta->validationErrors['tipo'][$i] = '';
						$this->UsuarioVeiculoAlerta->validationErrors['tecnologia'][$i] = '';
					}
				}
				$i++;
			}

			if(count($this->data['UsuarioVeiculoAlerta']['placa']) == 0){
				$this->UsuarioVeiculoAlerta->validationErrors['placa'][0] = 'Placa inválida';
				$this->UsuarioVeiculoAlerta->validationErrors['tipo'][0] = '';
				$this->UsuarioVeiculoAlerta->validationErrors['tecnologia'][0] = '';
			}elseif(empty($this->UsuarioVeiculoAlerta->validationErrors)){
				$dados = array();
				foreach($this->data['UsuarioVeiculoAlerta']['placa'] as $placa){
					$codigo_veiculo = $this->Veiculo->buscaCodigodaPlaca($placa);
					$dados[] = array(
						'UsuarioVeiculoAlerta' => array(
							'codigo_usuario' => $codigo_usuario,
							'codigo_veiculo' => $codigo_veiculo,
							)
						);
				}
				if($this->UsuarioVeiculoAlerta->incluirVeiculosAlerta($dados)){
					$this->BSession->setFlash('save_success');
				}else{
					$this->BSession->setFlash('save_error');
				}
			}
		}

		$this->set(compact('codigo_usuario'));
	}

	function excluir_veiculo_alerta($codigo_veiculo,$codigo_usuario){
		$this->loadModel('UsuarioVeiculoAlerta');

		$usuario_veiculo_alerta = $this->UsuarioVeiculoAlerta->find('first',array('conditions' => array('codigo_veiculo' => $codigo_veiculo,'codigo_usuario' => $codigo_usuario)));
		if($usuario_veiculo_alerta){
			$this->UsuarioVeiculoAlerta->excluir($usuario_veiculo_alerta['UsuarioVeiculoAlerta']['codigo']);
		}
		exit;
	}

	function listar_veiculo_alerta($codigo_usuario){
		$this->loadModel('UsuarioVeiculoAlerta');

		$this->UsuarioVeiculoAlerta->bindVeiculo();
		$veiculos = $this->UsuarioVeiculoAlerta->listarPorUsuario($codigo_usuario);

		$this->set(compact('veiculos','codigo_usuario'));
	}

	function editar_configuracao($codigo_usuario) {
		$this->loadModel('UsuarioAlertaTipo'); 
		$this->loadModel('TRefeReferencia');
		$this->loadModel('AlertaTipo');
		$this->pageTitle = 'Atualizar Usuarios';
		if (!empty($this->data)) {
			try{
				$this->Usuario->query("BEGIN TRANSACTION");
				if(!$this->Usuario->atualizar_perfil_usuario($this->data)){
					throw new Exception();
				}
				$this->incluirUsuarioAlertaTipo();
				if( !$this->incluirUsuarioExpediente() )
					throw new Exception();   
				$this->Usuario->commit();
				$this->BSession->setFlash('save_success');
				// $this->redirect(array('action' => 'configuracao'));
			} catch(Exception $e) {
				$this->Usuario->rollback();
				$this->BSession->setFlash('save_error');
			}
		} else {
			$this->Usuario->bindLazy();
			$this->data = $this->Usuario->read(null, $codigo_usuario);
		}
		$this->carrega_combos();
		$this->carregarDadosExpediente( $codigo_usuario );
		$usuario_pai = $this->Usuario->listaUsuariosNaoSubordinados( $codigo_usuario );   
		$this->set(compact('usuario_pai'));
	}

	function configuracao(){
		$this->data['Usuario']['action'] = 'configuracao';
	   // $this->data['Usuario']['codigo_uperfil'] = 69;
		$this->data['Usuario']['ativo'] = 1;
		$action = 'editar_configuracao';
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, $this->Usuario->name);
		$this->carrega_combos_perfil();
		$this->set(compact('action'));
	}
	
	private function incluirUsuarioExpediente() {
		if(empty($this->data['Usuario']['escala'])) {
			$this->loadModel('UsuarioExpediente');
			foreach($this->data['UsuarioExpediente'] as $dia => $dados ){
				$expediente = array(
					'UsuarioExpediente' => array(
						'codigo_usuario' => $this->data['Usuario']['codigo'],
						'dia_semana' => $dia,
						'entrada' => $dados['entrada'],
						'saida' => $dados['saida'],
						),
					);                
				if(!$this->UsuarioExpediente->incluir_expediente( $expediente )){
					$this->UsuarioExpediente->validationErrors[$dia] = $this->UsuarioExpediente->validationErrors;
					return false;
				}
			}
		}
		return true;
	}

	private function carregarDadosExpediente( $codigo_usuario ){
		$this->loadModel('UsuarioExpediente');
		$expediente  = $this->UsuarioExpediente->find('all', array(
			'conditions'=>array('codigo_usuario'=>$codigo_usuario),
			'order' => 'dia_semana'
			));
		$dados_expediente = array();
		foreach ($expediente as $key => $value) {
			$dados_expediente[$value['UsuarioExpediente']['dia_semana']] = $value;
		}
		$dias_semana = array(1=>'Segunda-Feira', 2=>'Terça-Feira', 3=>'Quarta-Feira', 4=>'Quinta-Feira', 5=>'Sexta-Feira', 6=>'Sábado',7=>'Domingo');
		$this->set(compact('dados_expediente', 'dias_semana'));
	}

	function diretoria_usuario(){
		$filtrado = true;
		$this->pageTitle = "Diretoria de Gestores";
		$this->carregaCombosDiretoriaUsuario();
		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, "Usuario");
		$this->set(compact('filtrado'));    
	}

	function diretoria_usuario_listagem(){      
		$this->loadModel("Diretoria");

		$this->data['Usuario'] = $this->Filtros->controla_sessao($this->data, "Usuario");

		if (!empty($this->data['Usuario']['codigo'])){
			$conditions['Usuario.codigo'] = $this->data['Usuario']['codigo'];
		}

		if (!empty($this->data['Usuario']['codigo_diretoria'])){
			$conditions['Usuario.codigo_diretoria'] = $this->data['Usuario']['codigo_diretoria'];
		}

		//lista apenas gestores
		// $conditions['Usuario.codigo_departamento'] = 9;
		$conditions['Usuario.codigo_cliente'] = NULL;

		$this->Usuario->bindModel(array(
			'hasOne' => array(
				'Diretoria' => array(
					'foreignKey' => false,
					'conditions' => array("Diretoria.codigo = Usuario.codigo_diretoria"),
					'type' => 'LEFT'
					),
				)
			),false);

		$order = 'Usuario.nome ASC';   
		$this->paginate['Usuario'] = array(
			'limit' => 50,
			'conditions' => $conditions,
			'order' => $order
			);
		$listagem = $this->paginate('Usuario');
		$this->set(compact('listagem'));
		$this->carregaCombosDiretoriaUsuario();
	}

	function carregaCombosDiretoriaUsuario(){
		$this->loadModel("Diretoria");
		// $this->loadModel("Gestor");
		// $gestores = $this->Gestor->listarNomesGestoresAtivos();
		$diretorias = $this->Diretoria->find('list');
		$this->set(compact('diretorias'));
	}

	function diretoria_usuario_editar($codigo){
		$this->pageTitle = 'Atualizar Diretoria do Usuário';
		if($this->RequestHandler->isPost()) {
			if(!empty($this->data['Usuario']['codigo']) && !empty($this->data['Usuario']['codigo_diretoria'])){    
				$this->data['Usuario']['codigo'] = $codigo;
				if ($this->Usuario->atualizar($this->data)) {
					$this->BSession->setFlash('save_success');
					$this->redirect(array('action' => 'diretoria_usuario'));
				} else {
					$this->BSession->setFlash('save_error');
				}
			}
		}    
		$this->data = $this->Usuario->carregar($codigo);
		$this->data['Usuario']['codigo_exibicao'] = $this->data['Usuario']['codigo'];
		$this->carregaCombosDiretoriaUsuario();        
	}

*/
}