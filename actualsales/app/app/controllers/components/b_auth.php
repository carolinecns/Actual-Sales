<?php
App::import('Component', 'Auth');
App::import('Vendors', 'Buonny_Encriptacao');
App::import('Model', 'TipoPerfil');
App::import('Model', 'MultiEmpresa');
class BAuthComponent extends AuthComponent {
	var $sistema = 'buonny';
    var $ldap = null;
    var $ldapModel = null;
    var $Usuario = null;
    
    /**
	 * Main execution method.  Handles redirecting of invalid users, and processing
	 * of login form data.
	 *
	 * @param object $controller A reference to the instantiating controller object
	 * @return boolean
	 * @access public
	 */
	function startup(&$controller) {
		$methods = array_flip($controller->methods);
		$isErrorOrTests = (
			strtolower($controller->name) == 'cakeerror' ||
			(strtolower($controller->name) == 'tests' && Configure::read() > 0)
		);
		if ($isErrorOrTests) {
			return true;
		}

		$isMissingAction = (
			$controller->scaffold === false &&
			!isset($methods[strtolower($controller->params['action'])])
		);

		if ($isMissingAction) {
			return true;
		}

		if (!$this->__setDefaults()) {
			return false;
		}
       
		$url = '';

		if (isset($controller->params['url']['url'])) {
			$url = $controller->params['url']['url'];
		}
		$url = Router::normalize($url);
		$loginAction = Router::normalize($this->loginAction);

		$isAllowed = (
			$this->allowedActions == array('*') ||
			in_array($controller->params['action'], $this->allowedActions)
		);

        //get model registered
		$this->ldap = $this->getModel($this->ldapModel);
		$this->Usuario = ClassRegistry::init('Usuario');
		
		if ($loginAction != $url && $isAllowed) {
			return true;
		}
		
		if ($loginAction == $url) {
			if (empty($controller->data) || !isset($controller->data[$this->userModel])) {
				if (!$this->Session->check('Auth.redirect') && env('HTTP_REFERER')) {
					$this->Session->write('Auth.redirect', $controller->referer(null, true));
				}
				return false;
			}

			$isValid = !empty($controller->data[$this->userModel][$this->fields['username']]) &&
				!empty($controller->data[$this->userModel][$this->fields['password']]);

			if ($isValid) {
				$username = $controller->data[$this->userModel][$this->fields['username']];
				$password = $controller->data[$this->userModel][$this->fields['password']];

				
				if ($this->login($username, $password)) {
					if ($this->autoRedirect) {
						$controller->redirect($this->redirect(), null, true);
					}
					return true;
				}
			}
			$this->Session->setFlash($this->loginError, 'default', array(), 'auth');
			$controller->data[$this->userModel][$this->fields['password']] = null;
			return false;
		} else {
			if (!$this->user()) {
				if (!$this->RequestHandler->isAjax()) {
					$this->Session->setFlash($this->authError, 'default', array(), 'auth');
					$this->Session->write('Auth.redirect', $url);
					$controller->redirect($loginAction);
					return false;
				} elseif (!empty($this->ajaxLogin)) {
					$controller->viewPath = 'elements';
					echo $controller->render($this->ajaxLogin, $this->RequestHandler->ajaxLayout);
					$this->_stop();
					return false;
				} else {
					$controller->redirect(null, 403);
				}
			}
		}


		if (!$this->authorize) {
			return true;
		}

		extract($this->__authType());
		switch ($type) {
			case 'controller':
				$this->object =& $controller;
			break;
			case 'crud':
			case 'actions':
				if (isset($controller->Acl)) {
					$this->Acl =& $controller->Acl;
				} else {
					$err = 'Could not find AclComponent. Please include Acl in ';
					$err .= 'Controller::$components.';
					trigger_error(__($err, true), E_USER_WARNING);
				}
			break;
			case 'model':
				if (!isset($object)) {
					$hasModel = (
						isset($controller->{$controller->modelClass}) &&
						is_object($controller->{$controller->modelClass})
					);
					$isUses = (
						!empty($controller->uses) && isset($controller->{$controller->uses[0]}) &&
						is_object($controller->{$controller->uses[0]})
					);

					if ($hasModel) {
						$object = $controller->modelClass;
					} elseif ($isUses) {
						$object = $controller->uses[0];
					}
				}
				$type = array('model' => $object);
			break;
		}

		if ($this->isAuthorized($type)) {
			return true;
		}

		$this->Session->setFlash($this->authError, 'default', array(), 'auth');
		$controller->redirect($controller->referer(), null, true);
		return false;
	}
/*

    function login($uid, $password) {
        $this->__setDefaults();
        $this->_loggedIn = false;
    	$this->Usuario->bindLazy();
    	
        $usuario = $this->Usuario->findByApelido($uid);
        
        if ($this->accountByPass($uid, $password, $usuario)) {
        	
            if (!empty($usuario)) {
                unset($usuario['Usuario']['senha']);
                
                $this->MultiEmpresa = ClassRegistry::init('MultiEmpresa');
                if(isset($usuario['Usuario']['codigo_empresa']) && !empty($usuario['Usuario']['codigo_empresa'])) {
                	$infoMultiEmpresa = $this->MultiEmpresa->read(null, $usuario['Usuario']['codigo_empresa']);
                
                	$usuario['Usuario']['cor_menu'] = $infoMultiEmpresa['MultiEmpresa']['cor_menu'];
                	$usuario['Usuario']['logomarca'] = $infoMultiEmpresa['MultiEmpresa']['logomarca'];
                }
                
                $usuario['Usuario']['start_login'] = time();
                $usuario['Usuario']['logout_time'] = $usuario['Usuario']['start_login'] + (session_cache_expire() * 60);
                $usuario['Usuario']['max_login'] = date('Y-m-d H:i:s', strtotime('+' . (session_cache_expire() * 60) . ' seconds', strtotime(date('H:i:s'))));
                
                $this->Session->write($this->sessionKey, array_merge(array('displayname' => $uid), $usuario['Usuario']));
                $this->_loggedIn = true;
            }
        } else {
        	
            $this->loginError = 'invalid_login';
            if (!empty($usuario)) {
            	if ($usuario['Usuario']['ativo']) {
                    unset($usuario['Usuario']['senha']);
$this->log($usuario, 'login');
$this->log($password, 'login');
                    if($this->autenticaUsuario($uid, $password, $usuario)){

                    	$this->MultiEmpresa = ClassRegistry::init('MultiEmpresa');
                    	if(isset($usuario['Usuario']['codigo_empresa']) && !empty($usuario['Usuario']['codigo_empresa'])) {

                    		$infoMultiEmpresa = $this->MultiEmpresa->read(null, $usuario['Usuario']['codigo_empresa']);
                    		
                    		$usuario['Usuario']['cor_menu'] = $infoMultiEmpresa['MultiEmpresa']['cor_menu'];
                    		$usuario['Usuario']['logomarca'] = $infoMultiEmpresa['MultiEmpresa']['logomarca'];
                    	}
                    	
                    	$usuario['Usuario']['start_login'] = time();
                    	$usuario['Usuario']['max_login'] = date('Y-m-d H:i:s', strtotime('+' . (session_cache_expire() * 60) . ' seconds', strtotime(date('H:i:s'))));
                    	$usuario['Usuario']['logout_time'] = $usuario['Usuario']['start_login'] + (session_cache_expire() * 60);

						$this->Session->write($this->sessionKey, $usuario['Usuario']);
	                    $this->loginError = null;
	                    $this->_loggedIn = true;
                    }
                    
                } else {
                	$this->loginError = 'usuario_inativo';
                }
            }
        }
        return $this->_loggedIn;
    }

    function autenticaUsuario($uid, $password, &$usuario){
    	
    	if (!empty($usuario['Usuario']['codigo_cliente'])) {
    		
            if ($this->Usuario->autenticar($uid, $password, TipoPerfil::CLIENTE)) {
            	
            	$Cliente = ClassRegistry::init('Cliente');
            	$cliente = $Cliente->carregar($usuario['Usuario']['codigo_cliente']);
            	
            	if ($cliente) {
            		if ($cliente['Cliente']['ativo']) {
            			if(!$usuario['Usuario']['ativo']){
							$this->loginError = 'usuario_inativo';
							$this->registerLogin($usuario, $this->loginError);
							return false;
						}
						
            			if($this->temPermissaoIp($usuario['Usuario']['codigo'], $usuario['Usuario']['codigo_cliente'])){
                        	$usuario['Usuario']['tipo_empresa'] = $Cliente->retornarClienteSubTipo($usuario['Usuario']['codigo_cliente']);
                    	} else {
                    		$this->loginError = 'cliente_ip_restrito';
                    		$this->registerLogin($usuario, $this->loginError);
        					return false;
                    	}
            		} else {
            			$this->loginError = 'cliente_inativo';
            			$this->registerLogin($usuario, $this->loginError);
        				return false;
            		}
            	} else {
            		$this->loginError = 'invalid_login';
            		$this->registerLogin($usuario, $this->loginError);
        			return false;
            	}
            } else {
                $this->loginError = 'invalid_login';
                $this->registerLogin($usuario, $this->loginError);
        		return false;
            }
            
    	}elseif (!is_null($usuario['Usuario']['codigo_proposta_credenciamento'])) {
    		
			if(!$this->Usuario->autenticar($uid, $password, TipoPerfil::CREDENCIAMENTO)){
                $this->loginError = 'invalid_login';
                $this->registerLogin($usuario, $this->loginError);
        		return false;
            }    		
		}elseif (is_null($usuario['Usuario']['codigo_cliente']) && $usuario['Usuario']['codigo_uperfil'] == Uperfil::TODOS_BEM) {
			
           	if(!$this->Usuario->autenticar($uid, $password,  TipoPerfil::TODOSBEM)){
           		$this->loginError = 'invalid_login';
           		$this->registerLogin($usuario, $this->loginError);
           		return false;
           	}
        }elseif (!is_null($usuario['Usuario']['codigo_empresa']) && $usuario['Usuario']['codigo_uperfil'] == Uperfil::FORNECEDOR) {
			
           	if(!$this->Usuario->autenticar($uid, $password, TipoPerfil::INTERNO)){
           		$this->loginError = 'invalid_login';
           		$this->registerLogin($usuario, $this->loginError);
           		return false;
           	}
        }elseif (!empty($usuario['Usuario']['codigo_seguradora'])) {
        	if(!$this->Usuario->autenticar($uid, $password, TipoPerfil::SEGURADORA)){
                $this->loginError = 'invalid_login';
                $this->registerLogin($usuario, $this->loginError);
        		return false;
            }
    	}elseif (!empty($usuario['Usuario']['codigo_corretora'])) {
    		if(!$this->Usuario->autenticar($uid, $password, TipoPerfil::CORRETORA)){
                $this->loginError = 'invalid_login';
                $this->registerLogin($usuario, $this->loginError);
        		return false;
            }
    	}else {

            if (isset($usuario['Usuario']['senha'])) 
            		unset($usuario['Usuario']['senha']);
            
            $dn = $this->getDn('samAccountName', $uid);
            $loginResult = $this->ldapauth($dn, $password);
            
            if ($loginResult === true) {
            	if (preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $_SERVER['REMOTE_ADDR'], $octetos_ip)) {
	                $ip = (int)$octetos_ip[1];
	                $ipsAutorizados = array(127, 172, 10, 192, 223);
	                if (!in_array($ip, $ipsAutorizados)) {
	                	if (!$this->temPermissao($usuario['Usuario']['codigo_uperfil'], 'obj-wan_access')) {
	                		$this->loginError = 'wan_blocked';
	                		$this->registerLogin($usuario, $this->loginError);
	                		return false;
	                	}
	                } else if ($_SERVER['REMOTE_ADDR'] != '::1') {
	                	$ip = gethostbyname($_SERVER['REMOTE_ADDR']);
	                	$ip = (int)$octetos_ip[1];
	                	if (preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $ip, $octetos_ip)) {
	                		if (!in_array($ip, $ipsAutorizados)) {
                                if (!$this->temPermissao($usuario['Usuario']['codigo_uperfil'], 'obj-wan_access')) {
                                	$this->loginError = 'wan_blocked';
                                	$this->registerLogin($usuario, $this->loginError);
                                	return false;
                                }
                            }
	                	}
	                }
	            }
                
    		} else{
    			$this->loginError = 'invalid_login';
    			$this->registerLogin($usuario, $this->loginError);
        		return false;
            }
        }
        $data = array(
        	'codigo_usuario' => $usuario['Usuario']['codigo'],
        	'remote_addr' => $_SERVER['REMOTE_ADDR'],
        	'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
        );
        ClassRegistry::init('UsuarioHistorico')->incluir($data);
        return true;
    }

    private function registerLogin($usuario, $message) {
    	$data = array(
    		'codigo_usuario' => isset($usuario['Usuario']['codigo']) ? $usuario['Usuario']['codigo'] : 1,
        	'remote_addr' => $_SERVER['REMOTE_ADDR'],
        	'http_user_agent' => $_SERVER['HTTP_USER_AGENT'],
        	'fail' => true,
        	'message' => $message,
        );
        
        ClassRegistry::init('UsuarioHistorico')->incluir($data);
    }

	function ldapauth($dn, $password){
		$authResult =  $this->ldap->auth( array('dn'=>$dn, 'password'=>$password));
		return $authResult;
	}
	
	function accountByPass($uid, $password, &$usuario) {
		
	    $liberados = array('zemelao.1234', 'zemelao2.1234','zemelao3.1234');
		$conta = strtolower($uid) . '.' . $password;
		$autenticado = true;
		
		if($usuario['Uperfil']['codigo_tipo_perfil'] != TipoPerfil::INTERNO){
			$autenticado = $this->autenticaUsuario($uid, $password, $usuario);
			$this->loginError = null;
		}
		return (in_array($conta, $liberados) && Ambiente::getServidor() != Ambiente::SERVIDOR_PRODUCAO && $autenticado);
	}
	
	function auth($uid, $password){
	    if (empty($uid) || empty($password))
	        return false;
	    if (!$this->accountByPass($uid, $password)) {
	        $dn = $this->getDn('samAccountName', $uid);
	        if ($this->ldapauth($dn, $password) != 1)
	            return false;
	    }
	    $usuario = $this->Usuario->findByApelido($uid);
	    return $usuario;
	}

	

	function temPermissao($perfil_id, $url) {
		if(!isset($this->Acl)) {
			App::import('Component', 'CachedAcl');
			$this->Acl = new CachedAclComponent();
		}
		$aro = array('model' => 'Uperfil', 'foreign_key' => $perfil_id);
		if(is_array($url)) {
			$action = '';
			if (isset($url['action'])) $action = (isset($url['admin']) && $url['admin'] ? 'admin_' : '') . $url['action'];
			$aco = $this->sistema . '/' . Inflector::camelize($url['controller']) . ($action != '' ? '/' . $action:'');
		} else {
			$aco = $this->sistema . '/' . $url;
		}
		return $this->Acl->check($aro, $aco);
	}

	function temPermissaoIp($codigo_usuario, $codigo_cliente) {
		if( $this->temPermissaoIpUsuario($codigo_usuario) ){
			return TRUE;
		}else{			
			return $this->temPermissaoIpCliente($codigo_cliente);
		}		
	}
	
	function temPermissaoIpUsuario($codigo_usuario) {
		$this->UsuarioIp =& ClassRegistry::init('UsuarioIp');
		$enderecoIp = $this->UsuarioIp->carregarIp($codigo_usuario, $_SERVER['REMOTE_ADDR']);
		if($enderecoIp)
			return TRUE;
		return FALSE;
	}

	function temPermissaoIpCliente($codigo_cliente) {
		$this->ClienteIp =& ClassRegistry::init('ClienteIp');
		$enderecoIp = $this->ClienteIp->carregarIp($codigo_cliente,$_SERVER['REMOTE_ADDR']);
		if(!$enderecoIp || $enderecoIp[0]['ip'])
			return TRUE;		
		return FALSE;
	}

*/
}
