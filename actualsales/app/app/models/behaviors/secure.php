<?php
class SecureBehavior extends ModelBehavior {
	var $config = array();
	
	function setup(&$model, $config = array()) {
	}

	function beforeSave(&$model) {
		
	    $saveAll = isset($model->data[0][$model->alias]) && is_array($model->data[0][$model->alias]);
	    
	    if ($saveAll) {
	        foreach ($model->data as &$data) {
	            if (isset($data[$model->alias])) {
	                $this->__inserirCamposAdicionais($data[$model->alias], $model->alias, $model->useTable, $model->_schema);
	            } else {
	                $this->__inserirCamposAdicionais($data, $model->alias, $model->useTable, $model->_schema);
	            }
	        }
	    } else {
            $this->__inserirCamposAdicionais($model->data, $model->alias, $model->useTable, $model->_schema);
	    }
	}
	
	
	/**
	 * Insere campos com dados adicionais de inclusão, alteração, etc...
	 */
	function __inserirCamposAdicionais(&$data, $alias, $table, $schema) {
		
		if((isset($schema['codigo_empresa']) && !empty($schema['codigo_empresa'])) && (isset($_SESSION['Auth']['Usuario']['codigo_empresa']) && !empty($_SESSION['Auth']['Usuario']['codigo_empresa']))) {
			$data[$alias]['codigo_empresa'] = $_SESSION['Auth']['Usuario']['codigo_empresa'];
		}
		
		if(preg_match('$^(T)([A-Z])(\w)*$', $alias)){
        	$Tabela = ClassRegistry::Init($alias);
			if(!isset($data[$alias][$Tabela->primaryKey]) || empty($data[$alias][$Tabela->primaryKey])) {
        		$data[$alias][substr($table,0,5).'data_cadastro'] = Date('Y-m-d H:i:s');
				if (isset($_SESSION['Auth']['Usuario']['apelido'])) {
					$field = substr($table,0,5).'usuario_adicionou';
					$field_alterou = substr($table,0,5).'usuario_alterou';
					$data[$alias][$field] = trim(substr($_SESSION['Auth']['Usuario']['apelido'],0,20));
					$data[$alias][$field_alterou] = $data[$alias][$field];
				}
			} else {
	        	$data[$alias][substr($table,0,5).'data_alteracao'] = Date('Y-m-d H:i:s');
				if (isset($_SESSION['Auth']['Usuario']['apelido'])) {
					$field = substr($table,0,5).'usuario_alterou';
					$field_manual = $field.'_manual';
					if (isset($data[$alias][$field_manual]) && !empty($data[$alias][$field_manual])) {
						$data[$alias][$field] = $data[$alias][$field_manual];
					} else {
						$data[$alias][$field] = trim(substr($_SESSION['Auth']['Usuario']['apelido'],0,20));
					}
				}
			}
        } else {
        	if (isset($data[$alias][$alias])) {
	            $dados =& $data[$alias][$alias];
		    } else if ($data[$alias]) {
	            $dados =& $data[$alias];
		    } else {
	            $dados =& $data;
		    }
		    
		    if (!isset($dados['codigo']) || empty($dados['codigo'])) {
	            $dados['data_inclusao'] = Date('Ymd H:i:s');
	            if (isset($_SESSION['Auth']['Usuario']['codigo']) && !isset($dados['codigo_usuario_inclusao'])) {
	                $dados['codigo_usuario_inclusao'] = $_SESSION['Auth']['Usuario']['codigo'];
	                $dados['codigo_usuario_alteracao'] = $_SESSION['Auth']['Usuario']['codigo'];
	            }
		    	if (isset($dados['data_inclusao_manual']) && !empty($dados['data_inclusao_manual'])) {
		    		$dados['data_inclusao'] = $dados['data_inclusao_manual'];
		    	}
		    	if (isset($dados['codigo_usuario_inclusao_manual']) && !empty($dados['codigo_usuario_inclusao_manual'])) {
		    		$dados['codigo_usuario_inclusao'] = $dados['codigo_usuario_inclusao_manual'];
	                $dados['codigo_usuario_alteracao'] = $dados['codigo_usuario_inclusao_manual'];
		    	}
	        } else {
	            if (isset($_SESSION['Auth']['Usuario']['codigo'])) {
	                $dados['codigo_usuario_alteracao'] = $_SESSION['Auth']['Usuario']['codigo'];
	            }
	            $dados['data_alteracao'] = Date('Ymd H:i:s');
	        	if (isset($dados['data_alteracao_manual']) && !empty($dados['data_alteracao_manual'])) {
	        		$dados['data_alteracao'] = $dados['data_alteracao_manual'];
	        	}
	            if (isset($dados['codigo_usuario_alteracao_manual']) && !empty($dados['codigo_usuario_alteracao_manual'])) {
	            	$dados['codigo_usuario_alteracao'] = $dados['codigo_usuario_alteracao_manual'];
	            }
	        }
        }
	}

	function beforeFind(&$model, &$queryData) {
		
		if(isset($_SESSION['Auth']['Usuario']['codigo_empresa']) && isset($model->_schema['codigo_empresa'])) {
			if(isset($_SESSION['Auth']['Usuario']['codigo_empresa']) && $_SESSION['Auth']['Usuario']['codigo_empresa']) {
				$condition_rest = array($model->alias . '.codigo_empresa' => $_SESSION['Auth']['Usuario']['codigo_empresa']);
		
				if(isset($queryData) && isset($queryData['conditions'])){
					$queryData['conditions'] = array_merge($condition_rest, $queryData['conditions']);
				} else {
					$queryData['conditions'] = $condition_rest;
				}
			}
		}
		
// 		if(isset($_SESSION['Auth']['Usuario']['codigo_cliente']) && isset($model->_schema['codigo_cliente'])) {
// 			if(isset($_SESSION['Auth']['Usuario']['codigo_cliente']) && $_SESSION['Auth']['Usuario']['codigo_cliente']) {
// 				$condition_rest = array($model->alias . '.codigo_cliente' => $_SESSION['Auth']['Usuario']['codigo_cliente']);
		
// 				if(isset($queryData) && isset($queryData['conditions'])){
// 					$queryData['conditions'] = array_merge($condition_rest, $queryData['conditions']);
// 				} else {
// 					$queryData['conditions'] = $condition_rest;
// 				}
// 			}
// 		}		
		
		return $queryData;
	}
	
}
?>