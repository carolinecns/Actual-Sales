<?php

class Cadastro extends AppModel {

	public $name = 'Cadastro';
	public $tableSchema = 'dbo';
	public $databaseTable = 'actualsales';
	public $useTable = 'cadastro';
	public $primaryKey = 'codigo';
	// public $actsAs = array('Secure', 'Containable');
	//public $recursive = -1;

	public $validate = array(
		'nome' => array(
			'rule' => 'notEmpty',
			'message' => 'Informe o Nome',
			'required' => true
		),
		'email' => array(
			'rule' => 'notEmpty',
			'message' => 'Informe o E-mail',
			'required' => true
		),
		'data_nascimento' => array(
			'rule' => 'notEmpty',
			'message' => 'Informe a Data de Nascimento',
			'required' => true
		),
		'telefone' => array(
			'rule' => 'notEmpty',
			'message' => 'Informe o Telefone',
			'required' => true
		),
		'codigo_regiao' => array(
			'rule' => 'notEmpty',
			'message' => 'Informe a Região',
			'required' => true
		),
	);

}

?>