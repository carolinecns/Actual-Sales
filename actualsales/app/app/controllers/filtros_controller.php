<?php
class FiltrosController extends AppController {
	public $name = 'Filtros';
	public $components = array('Filtros', 'Session', 'Validator');
	public $helpers = array('Bajax','Tree');
	public $uses = array('Filtro');
	private $element_name;
	private $model_name;

	public function beforeFilter() {
		parent::beforeFilter();
		// $this->BAuth->allow('*');
	}

	public function filtrar() {
		$this->layout = 'ajax_placeholder';
		
		if ($this->RequestHandler->isAjax()) {
			$this->element_name = $this->passedArgs['element_name'];
			$this->model_name = $this->passedArgs['model'];
			$filterValidated = $this->validates();
			if ($filterValidated) {
				if (isset($this->data['Filtro']['salvar_filtro']) && $this->data['Filtro']['salvar_filtro'])
					$this->salvar_filtro();
				$this->Filtros->controla_sessao($this->data, $this->model_name);
				$this->set('filtrado', true);
			}
			$this->set(compact('filterValidated'));
			$this->carrega_combos();
			$this->render('/elements/filtros/' . $this->element_name);
		} else {
			$this->Filtros->controla_sessao($this->data, $this->passedArgs['model']);
			if (!isset($this->passedArgs['return'])) 
				$this->redirect('/');
				
			$this->redirect('/' . $this->passedArgs['return'] . (isset($this->passedArgs['return_action']) ? '/' . $this->passedArgs['return_action'] : ''));
		}
	}

	private function validates() {
		$return = true;
		if($this->element_name == 'r_issues') {
			$this->loadModel('RIssues');
			if($this->data['RIssues']['data_inicial'] == '')
				$this->RIssues->invalidate('data_inicial','Informe o período');
			
			if($this->data['RIssues']['data_final'] == '')
				$this->RIssues->invalidate('data_final','Informe o período');
		}
		if ($this->element_name == 'codigo_sm')
			$return = $this->validaCodigoSm();
		if ($this->element_name == 'itinerarios_sms_por_cliente')
			$return = $this->validaMSmitinerario();
		if ($this->element_name == 'veiculos_mapa_gr')
			$return = $this->validaVeiculosMapaGR();	
		if ($this->element_name == 'relatorios_sm_acompanhamento_viagens_analitico'
			|| $this->element_name == 'relatorios_sm_acompanhamento_viagens_sintetico' 
			|| $this->element_name == 'sintetico_temperatura'
			|| $this->element_name == 'relatorios_sm_custos_do_trajeto_analitico' )
			$return = $this->validaRelatoriosSmAcompanhamentosViagens();
		if ($this->element_name == 'relatorios_sm_veiculos_por_regiao')
			$return = $this->validaRelatoriosSmVeiculosPorRegiao();
		if ($this->element_name == 'relatorios_sm_situacao_frota')
			$return = $this->validaRelatoriosSmSituacaoFrota();
		if ($this->element_name == 'veiculos_posicao_frota')
			$return = $this->validaVeiculosPosicaoFrota();
		if ($this->element_name == 'alterar_produto')
			$return = $this->validaAlterarProduto();
		if ($this->element_name == 'consultar_pagador_preco')
			$return = $this->validaConsultarPagadorPreco();
		// if ($this->element_name == 'rma' || $this->element_name == 'rma_sintetico')
		// 	$return = $this->Validator->m_rma_estatistica($this->element_name == 'rma_sintetico');
		if ($this->element_name == 'checklist_sintetico')
			return $this->validaChecklistSintetico();
		if (in_array($this->element_name, array('pcp_sintetico', 'pcp_analitico','filtros_pcp')))
			return $this->validaPcp();
		if($this->element_name == 'utilizacao_servicos_configuracoes')
			$return = $this->validaLogsFaturamento();

		if( $this->element_name == 'atendimentos_sms_consulta' )
			return $this->periodo_validade_consulta('AtendimentoSm', 'data_inicial', 'data_final' );

		if($this->element_name == 'embarcador_transportador'){
			$this->loadModel('EmbarcadorTransportador');
			if(isset($this->data['EmbarcadorTransportador']['codigo_cliente']) && !$this->data['EmbarcadorTransportador']['codigo_cliente'])
				$this->EmbarcadorTransportador->invalidate('codigo_cliente','Informe o cliente');
		}
		if($this->element_name == 'gerar_rotas'){
			$this->loadModel('TRotaRota');
			if(empty($this->data['TRotaRota']['codigo_cliente'])) {
				$this->TRotaRota->invalidate('codigo_cliente','Informe o cliente');
				return false;
			}
		}
		if ($this->element_name == 'status_checklist')
			$return = $this->validaPosicaoChecklist();
		if ($this->element_name == 'inicio_viagem')
			$return = $this->validaChecklistOnline();
		if ($this->element_name == 'checklist_viagem_analitico' || $this->element_name == 'checklist_viagem_sintetico')
			$return = $this->validaChecklistAnalitico();
		if ($this->element_name == 'blq_veic_referencias')
			$return = $this->validaBlqVeicReferencias();

		if ($this->element_name == 'pgr_referencias')
			$return = $this->validaPgrReferencias();
		
		if ($this->element_name == 'tveiculos'){
			$this->loadModel('Tveiculos');
			if(isset($this->data['Tveiculos']['codigo_cliente']) && !$this->data['Tveiculos']['codigo_cliente']){
				$this->Tveiculos->invalidate('codigo_cliente','Informe o cliente');
				return false;
			}
		}		
		if ($this->element_name == 'tpecas'){
			$this->loadModel('Tpecas');
			if(isset($this->data['Tpecas']['codigo_cliente']) && !$this->data['Tpecas']['codigo_cliente']){
				$this->Tpecas->invalidate('codigo_cliente','Informe o cliente');
				return false;
			}
		}

		if ($this->element_name == 'autotrac_faturamento'){
			$this->loadModel('AutotracFaturamento');
			if(isset($this->data['AutotracFaturamento']['mes_referencia']) && !$this->data['AutotracFaturamento']['mes_referencia']){
				$this->AutotracFaturamento->invalidate('mes_referencia','Informe o mes');
				return false;
			}
			if(isset($this->data['AutotracFaturamento']['ano_referencia']) && !$this->data['AutotracFaturamento']['ano_referencia']){
				$this->AutotracFaturamento->invalidate('ano_referencia','Informe o ano');
				return false;
			}
		}

		if ($this->element_name == 'sms_status_profissional')
			$return = $this->validaRelatorioSmsStatusProfissional();

		if ($this->element_name == 'log_consultas')
			$return = $this->validaLogConsultas();		

		return $return;
	}

	private function validaPcp() {
		$this->loadModel('TIpcpInformacaoPcp');
		$return = true;
		if(empty($this->data['TIpcpInformacaoPcp']['codigo_cliente']) && !isset($this->passedArgs['validate'])){
			$this->TIpcpInformacaoPcp->invalidate('codigo_cliente','Informe o Cliente');
			$return = false;
		}
        if(!empty($this->data['TIpcpInformacaoPcp']['data_inicial']) && !empty($this->data['TIpcpInformacaoPcp']['data_final'])){
			$data_inicial = strtotime(AppModel::dateToDbDate($this->data['TIpcpInformacaoPcp']['data_inicial']));
			$data_final = strtotime(AppModel::dateToDbDate($this->data['TIpcpInformacaoPcp']['data_final']));
			if (floor(($data_final - $data_inicial)/(60*60*24)) > 31){
				$this->TIpcpInformacaoPcp->invalidate('data_final', 'Período maior que 1 mês');
				$return = false;
			}
		}
		return $return;
	}

	private function validaPosicaoChecklist() {
		$this->loadModel('Veiculo');
		if (empty($this->data['Veiculo']['racs_validade_checklist'])) {
			$this->Veiculo->invalidate('racs_validade_checklist', 'Favor cadastrar regra de aceite');
		}
		return true;
	}

	private function validaChecklistAnalitico() {
		$this->loadModel('ChecklistViagem');
		if (empty($this->data['ChecklistViagem']['codigo_cliente'])) {
			$this->ChecklistViagem->invalidate('codigo_cliente', 'Favor informar o cliente');
		}
		if (empty($this->data['ChecklistViagem']['data_inicial']) || empty($this->data['ChecklistViagem']['data_final'])) {
			$this->ChecklistViagem->invalidate('data_inicial','Favor informar o período');
			$this->ChecklistViagem->invalidate('data_final','');
		}
		return true;
	}	

	private function validaBlqVeicReferencias() {
		$this->loadModel('TBvreBlqVeicReferencia');
		if (empty($this->data['TBvreBlqVeicReferencia']['codigo_cliente'])) {
			$this->TBvreBlqVeicReferencia->invalidate('codigo_cliente', 'Favor informar o cliente');
		}
		return true;
	}	

	private function validaLogConsultas() {
		$this->loadModel('LogConsulta');
		if (empty($this->data['LogConsulta']['data_inclusao_inicial']) || 
				empty($this->data['LogConsulta']['data_inclusao_final']) ||
				empty($this->data['LogConsulta']['hora_inclusao_inicial']) ||
				empty($this->data['LogConsulta']['hora_inclusao_final']) ) {
			$this->LogConsulta->invalidate('data_inclusao_inicial', 'Favor informar o período');
			$this->LogConsulta->invalidate('data_inclusao_final', '');
			$this->LogConsulta->invalidate('hora_inclusao_inicial', '');
			$this->LogConsulta->invalidate('hora_inclusao_final', '');
			return false;
		}

        if(!empty($this->data['LogConsulta']['data_inclusao_inicial']) && !empty($this->data['LogConsulta']['data_inclusao_final'])){
			$data_inicial = strtotime(AppModel::dateToDbDate($this->data['LogConsulta']['data_inclusao_inicial']));
			$data_final = strtotime(AppModel::dateToDbDate($this->data['LogConsulta']['data_inclusao_final']));
			if (floor(($data_final - $data_inicial)/(60*60*24)) > 31){
				$this->LogConsulta->invalidate('data_inclusao_inicial', 'Período maior que 1 mês');
				$this->LogConsulta->invalidate('data_inclusao_final','');
				return false;
			}
		}		
		return true;
	}		

	private function validaChecklistOnline() {
		$this->loadModel('Recebsm');
		if (empty($this->data['Recebsm']['placa'])) {
			$this->Recebsm->invalidate('placa', 'Favor informar a placa do veículo');
		}
		if (empty($this->data['Recebsm']['racs_validade_checklist'])) {
			$this->Recebsm->invalidate('racs_validade_checklist', 'Favor cadastrar regra de aceite');
		}
		return true;
	}

	private function validaChecklistSintetico() {
		$this->loadModel('TCveiChecklistVeiculo');
		if (in_array($this->data['TCveiChecklistVeiculo']['agrupamento'],array(TCveiChecklistVeiculo::AGRP_TRANSPORTADOR, TCveiChecklistVeiculo::AGRP_ALVO_ORIGEM)) && empty($this->data['TCveiChecklistVeiculo']['codigo_cliente'])) {
			$this->TCveiChecklistVeiculo->invalidate('codigo_cliente', 'Informe o cliente para o agrupamento selecionado');
		}
		$this->data['TCveiChecklistVeiculo']['tran_pess_oras_codigo'] = '';
		return true;
	}

	private function validaAlterarProduto(){
		$this->loadModel('ClienteProduto');
		$produto = $this->ClienteProduto->listaProdutos($this->data['ClienteProduto']['codigo_cliente'],true);
		if (count($produto) > 1)
			$this->ClienteProduto->invalidate('codigo_cliente', 'Este cliente já possui mais de um produto.');
		return true;
	}

	private function validaConsultarPagadorPreco(){
		$this->loadModel('EmbarcadorTransportador');
		if (empty($this->data['EmbarcadorTransportador']['codigo_cliente_transportador'])) {
			$this->EmbarcadorTransportador->invalidate('codigo_cliente_transportador', 'Transportador não informado');

		}
		if (empty($this->data['EmbarcadorTransportador']['codigo_produto'])) {
			$this->EmbarcadorTransportador->invalidate('codigo_produto', 'Produto não informado');

		}
		return true;
	}
	private function validaMSmitinerario(){
		$this->loadModel('MSmitinerario');
		if(empty($this->data['MSmitinerario']['codigo_cliente'])){
			$this->MSmitinerario->invalidate('codigo_cliente', 'Cliente não informado');
			return false;
		}
		return true;
	}

	private function validaRelatoriosSmAcompanhamentosViagens(){
		$this->loadModel('RelatorioSm');
		$valido = true;

		if(empty($this->data['RelatorioSm']['data_inicial'])){
			$this->RelatorioSm->invalidate('data_inicial', 'Informe a data inicial');
			$valido = false;
		}
		if(empty($this->data['RelatorioSm']['data_final'])){
			$this->RelatorioSm->invalidate('data_final', 'Informe a data final');
			$valido = false;
		}
		if(!empty($this->data['RelatorioSm']['data_inicial']) && !empty($this->data['RelatorioSm']['data_final'])){
			$data_inicial = strtotime(AppModel::dateToDbDate($this->data['RelatorioSm']['data_inicial']));
			$data_final = strtotime(AppModel::dateToDbDate($this->data['RelatorioSm']['data_final']));
			if (floor(($data_final - $data_inicial)/(60*60*24)) > 31){
				$this->RelatorioSm->invalidate('data_final', 'Período maior que 1 mês');
				$valido = false;
			}
		}
		if ($this->element_name == 'relatorios_sm_acompanhamento_viagens_sintetico' || $this->element_name == 'sintetico_temperatura' || $this->element_name == 'relatorios_sm_custos_do_trajeto_analitico' ) {
			if (empty($this->data['RelatorioSm']['codigo_cliente'])) {
				$this->RelatorioSm->invalidate('codigo_cliente', 'Informe o cliente');
				$valido = false;
			}
		}
		return $valido;
	}
	
	private function validaRelatoriosSmVeiculosPorRegiao(){
		$this->loadModel('RelatorioSmVeiculosRegiao');
		$valido = true;
		if(empty($this->data['RelatorioSmVeiculosRegiao']['codigo_cliente'])){
			$this->RelatorioSmVeiculosRegiao->invalidate('codigo_cliente', 'Cliente não informado');
			$valido = false;
		}
		return $valido;
	}

	private function validaRelatoriosSmSituacaoFrota(){
		$this->loadModel('RelatorioSmSituacaoFrota');
		$valido = true;
		if(empty($this->data['RelatorioSmSituacaoFrota']['codigo_cliente'])){
			$this->RelatorioSmSituacaoFrota->invalidate('codigo_cliente', 'Cliente não informado');
			$valido = false;
		}
		if(empty($this->data['RelatorioSmSituacaoFrota']['cd_id'])){
			$this->RelatorioSmSituacaoFrota->invalidate('cd_id', 'Informe pelo menos 1 CD');
			$valido = false;
		} else {
			if(count($this->data['RelatorioSmSituacaoFrota']['cd_id']) > 3){
				$this->RelatorioSmSituacaoFrota->invalidate('cd_id', 'Informe no máximo 3 CDs');
				$valido = false;
			}
		}
		return $valido;
	}
	private function validaVeiculosPosicaoFrota(){
		$this->loadModel('VeiculoPosicaoFrota');
		$valido = true;
		if(empty($this->data['VeiculoPosicaoFrota']['codigo_cliente'])){
			$this->VeiculoPosicaoFrota->invalidate('codigo_cliente', 'Cliente não informado');
			$valido = false;
		}
		if(empty($this->data['VeiculoPosicaoFrota']['cd_id'])){
			$this->VeiculoPosicaoFrota->invalidate('cd_id', 'Informe pelo menos 1 CD');
			$valido = false;
		} else {
			if(count($this->data['VeiculoPosicaoFrota']['cd_id']) > 3){
				$this->VeiculoPosicaoFrota->invalidate('cd_id', 'Informe no máximo 3 CDs');
				$valido = false;
			}
		}
		return $valido;
	}

	private function validaSmsPorMes() {
		$authUsuario = $this->BAuth->user();
		$this->loadModel('Recebsm');
		if (empty($authUsuario['Usuario']['codigo_cliente']) === false && empty($this->data['Recebsm']['codigo_cliente_monitora'])) {
			$this->Recebsm->invalidate('codigo_cliente_monitora', 'Utilizador não informado');
			return false;
		}
		if (empty($this->data['Recebsm']['codigo_cliente']) === false && empty($this->data['Recebsm']['codigo_cliente_monitora'])) {
			$this->Recebsm->invalidate('codigo_cliente_monitora', 'Utilizador não informado');
			return false;
		}
		return true;
	}

	private function validaCodigoSm(){
		$this->loadModel('Recebsm');
		if(empty($this->data['Recebsm']['codigo_sm'])){
			$this->Recebsm->invalidate('codigo_sm', 'Código de SM não informado');
			return false;
		}
		return true;
	}
	private function validaLogsFaturamento(){
		$this->loadmodel('LogFaturamentoTeleconsult');
		$valido = true;
		$filtros  = $this->Filtros->controla_sessao($this->data, 'LogFaturamentoTeleconsult');
		if(!empty($filtros))
			$this->data['LogFaturamentoTeleconsult'] = $filtros;

		if(empty($this->data['LogFaturamentoTeleconsult']['data_inclusao_inicio'])){
			$this->LogFaturamentoTeleconsult->invalidate('data_inclusao_inicio', 'Informe a data inicial');
			$valido = false;
		}
		if(empty($this->data['LogFaturamentoTeleconsult']['data_inclusao_fim'])){
			$this->LogFaturamentoTeleconsult->invalidate('data_inclusao_fim', 'Informe a data final');
			$valido = false;
		}
		if(!empty($this->data['LogFaturamentoTeleconsult']['data_inclusao_inicio']) && !empty($this->data['LogFaturamentoTeleconsult']['data_inclusao_fim'])){
			$data_inclusao_inicio = strtotime(AppModel::dateToDbDate($this->data['LogFaturamentoTeleconsult']['data_inclusao_inicio']));
			$data_inclusao_fim = strtotime(AppModel::dateToDbDate($this->data['LogFaturamentoTeleconsult']['data_inclusao_fim']));
			if (floor(($data_inclusao_fim - $data_inclusao_inicio)/(60*60*24)) > 31){
				$this->LogFaturamentoTeleconsult->invalidate('data_inclusao_fim', 'Período maior que 1 mês');
				$valido = false;
			}
		}		
		return $valido;
	}
	
	private function validaPgrReferencias() {
		$this->loadModel('TPrefPgrReferencia');
		if (empty($this->data['TPrefPgrReferencia']['codigo_cliente'])) {
			$this->TPrefPgrReferencia->invalidate('codigo_cliente', 'Favor informar o cliente');
		}
		return true;
	}

	private function validaRelatorioSmsStatusProfissional() {
		$this->loadModel('RelatorioSmTeleconsult');
		if (empty($this->data['RelatorioSmTeleconsult']['codigo_cliente'])) {
			$this->RelatorioSmTeleconsult->invalidate('codigo_cliente', 'Favor informar o cliente');
			return false;
		}

		if (empty($this->data['RelatorioSmTeleconsult']['data_previsao_de'])) {
			$this->RelatorioSmTeleconsult->invalidate('data_previsao_de', 'Favor informe a data inicial');
			return false;
		}		
		if (empty($this->data['RelatorioSmTeleconsult']['data_previsao_ate'])) {
			$this->RelatorioSmTeleconsult->invalidate('data_previsao_ate', 'Favor informe a data final');
			return false;
		}	
		if(!empty($this->data['RelatorioSmTeleconsult']['data_previsao_de']) && !empty($this->data['RelatorioSmTeleconsult']['data_previsao_ate'])){
			$data_previsao_de = strtotime(AppModel::dateToDbDate($this->data['RelatorioSmTeleconsult']['data_previsao_de']));
			$data_previsao_ate = strtotime(AppModel::dateToDbDate($this->data['RelatorioSmTeleconsult']['data_previsao_ate']));

			if ($data_previsao_de > $data_previsao_ate) {
				$this->RelatorioSmTeleconsult->invalidate('data_previsao_ate', 'Data Final não pode ser maior que Data Inicial');
				return false;
			}

			if (floor(($data_previsao_ate - $data_previsao_de)/(60*60*24)) > 31){
				$this->RelatorioSmTeleconsult->invalidate('data_previsao_ate', 'Período maior que 1 mês');
				return false;
			}
		}					

		return true;
	}	

	private function validaVeiculosMapaGR() {
		$this->loadModel('RelatorioSmVeiculos');

		if (empty($this->data['RelatorioSmVeiculos']['codigo_cliente'])) {
			$this->RelatorioSmVeiculos->invalidate('codigo_cliente', 'Favor informar o cliente');
			return false;
		}

		if (empty($this->data['RelatorioSmVeiculos']['cd_id'])) {
			$this->RelatorioSmVeiculos->invalidate('cd_id', 'Deve-se informar ao menos 1(UM) CD');
			return false;
		}

		return true;
	}

	protected function carregaRazaoSocial() {
		if (!isset($this->Cliente)) {
			$this->loadModel('Cliente');
		}

		$codigo_cliente = $this->data[$this->model_name]['codigo_cliente'];
		$recursive = $this->Cliente->recursive;
		$this->Cliente->recursive = -1;
		$cliente = $this->Cliente->read('razao_social', $codigo_cliente);
		$this->data[$this->model_name]['razao_social'] = $cliente['Cliente']['razao_social'];
		$this->Cliente->recursive = $recursive;
	}

	protected function tratamentoFicha() {
		if (empty($this->data['Ficha']['codigo_documento'])) {
			$this->Ficha =& ClassRegistry::init('Ficha');
			$this->Ficha->invalidate('codigo_documento', 'Campo obrigatório');
			$this->data['Ficha']['codigo'] = 0;
		} else {
			$this->data['Ficha']['codigo'] = null;
		}
	}

	private function carregaCombosChecklist() {
		$this->loadModel('TCveiChecklistVeiculo');
		$this->loadModel('TTveiTipoVeiculo');
		$status = $this->TCveiChecklistVeiculo->listStatus();
		$agrupamentos = $this->TCveiChecklistVeiculo->agrupamentos();
		$veiculos_tipos = $this->TTveiTipoVeiculo->lista();
		$veiculos_tipos[99] = 'TODOS DIFERENTES DE CARRETA';
		$this->set(compact('agrupamentos', 'status','veiculos_tipos'));
	}

	private function carregaCombosPcp() {
		$this->loadModel('TIpcpInformacaoPcp');
		$this->loadModel('RelatorioSm');
		$this->loadModel('TStemStatusTempo');
		$this->loadModel('TMatrMotivoAtraso');
		$this->loadModel('StatusViagem');
		$status = $this->TStemStatusTempo->listStatus();
		$motivo = $this->TMatrMotivoAtraso->listStatus();
		$agrupamentos = $this->TIpcpInformacaoPcp->agrupamentos();
		$codigo_cliente = $this->data['TIpcpInformacaoPcp']['codigo_cliente'];
		if (!empty($this->authUsuario['Usuario']['codigo_cliente'])) 
			$codigo_cliente = $this->authUsuario['Usuario']['codigo_cliente'];
		$alvos_bandeiras_regioes = $this->RelatorioSm->carregaCombosAlvosBandeirasRegioes($codigo_cliente, false, true);
		$listaStatus = $this->StatusViagem->find();
		$this->set(compact('agrupamentos', 'status','listaStatus','motivo','codigo_cliente','isPost','filtrado', 'alvos_bandeiras_regioes'));
	}

	private function carregaCombosChecklistOnline() {
		$this->loadModel('TUcveUltimoChecklistVeiculo');
		$this->loadModel('Cliente');
		$this->loadModel('TPjurPessoaJuridica');
		$this->loadModel('TRacsRegraAceiteSm');

		$usuario =& $this->authUsuario['Usuario'];
		if (!empty($usuario['codigo_cliente']))
			$this->data['Recebsm']['codigo_cliente'] = $usuario['codigo_cliente'];

		$regras_aceite_sm = array();
		$checklist_posicao = array(
			TUcveUltimoChecklistVeiculo::POSICAO_VALIDO => 'Aprovado',
			TUcveUltimoChecklistVeiculo::POSICAO_INVALIDO => 'Reprovado',
			TUcveUltimoChecklistVeiculo::POSICAO_VENCIDO => 'Aprovado, porém Vencido',
			TUcveUltimoChecklistVeiculo::POSICAO_NAO_REALIZADO => 'Não Realizado',
		);

		if (!empty($this->data['Recebsm']['codigo_cliente'])) {
			$cliente = $this->Cliente->carregar($this->data['Recebsm']['codigo_cliente']);		
			if ($cliente) {
				$pjur = $this->TPjurPessoaJuridica->carregarPorCNPJ($cliente['Cliente']['codigo_documento']);
				if ($pjur) {
					$regras_aceite_sm = $this->TRacsRegraAceiteSm->listValidade($pjur['TPjurPessoaJuridica']['pjur_pess_oras_codigo']);
				}
			}
		}

		$this->set(compact('usuario','regras_aceite_sm'));
	}

	private function carregaCombosChecklistAnalitico() {
		$status = array(
			'S' => 'Aprovado',
			'N' => 'Reprovado',
		);


		$filtros = $this->Filtros->controla_sessao(array('ChecklistViagem' => array()), 'ChecklistViagem');
		if (isset($filtros['codigo_cliente'])) {
			$this->data['ChecklistViagem']['codigo_cliente'] = $filtros['codigo_cliente'];	
		}

		if (isset($filtros['data_inicial'])) {
			$this->data['ChecklistViagem']['data_inicial'] = $filtros['data_inicial'];	
		} else {
			$this->data['ChecklistViagem']['data_inicial'] = date('d/m/Y');
		}

		if (isset($filtros['data_final'])) {
			$this->data['ChecklistViagem']['data_final'] = $filtros['data_final'];	
		} else {
			$this->data['ChecklistViagem']['data_final'] = date('d/m/Y');			
		}


		$authUsuario	=& $this->authUsuario;
		if(!empty($authUsuario['Usuario']['codigo_cliente']))
			$this->data['ChecklistViagem']['codigo_cliente'] = $authUsuario['Usuario']['codigo_cliente'];


		$this->set(compact('authUsuario','status'));
	}

	protected function carrega_combos() {

		if($this->element_name == 'resultados_exames') {
			// $this->loadmodel('StatusPedidoExame');
			// $lista_status_pedidos_exames = array('' => 'TODOS OS STATUS') + $this->StatusPedidoExame->find('list', array('order' => array('StatusPedidoExame.codigo ASC'), 'fields' => array('StatusPedidoExame.codigo', 'StatusPedidoExame.descricao')));
			// $this->set(compact('lista_status_pedidos_exames'));
		}
		
		if (in_array($this->element_name, array('transacoes_recebimento_analitico','transacoes_recebimento_sintetico')))
			$this->carregaCombosTransacoesRecebimento();
		if (in_array($this->element_name, array('checklist_sintetico','checklist_analitico')))
			$this->carregaCombosChecklist();

		if (in_array($this->element_name, array('pcp_sintetico','pcp_analitico','filtros_pcp'))){
			$this->carregaCombosPcp();
		}
		if ($this->element_name == 'atualizacao_contratos_filtro')
			$this->carregaCombosClientesProdutosContratos();
		if ($this->element_name == 'pontuacoes_status_criterios')
			$this->carregaPontosStatusCriterios();
		if ($this->element_name == 'tarefas_desenvolvimento')
			$this->carregaTarefaDesenvolvimento();
		if ($this->element_name == 'consulta_fichas_pendententes')
			$this->carregaComboListarFichasPendentes();
		if ($this->element_name == 'fichas_scorecard' || $this->element_name == 'fichas_scorecard_excluir_vinculo' )
			$this->carregaComboListarFichasScorecard();
		if ($this->element_name == 'resultados_pesquisa')
			$this->carregaComboResultadosPesquisa();
		if ($this->element_name == 'liberacoes_provisorias'){
			$this->carregaRazaoSocial();
			$this->loadModel('Produto');
			$produtos = $this->Produto->find('list', array('conditions' => array('Produto.codigo' => array(1, 2, 134))));
			$this->set(compact('produtos'));
		}
		if($this->element_name == 'r_issues')
		{
			$this->loadModel('RUsers');
       		$users = $this->RUsers->comboUsers();

			$this->loadModel('RProjects');
       		$projects = $this->RProjects->comboProjects();

        	$this->set(compact('users', 'projects'));
		}
		if($this->element_name == 'o_ocorrencias')
		{
			$this->loadModel('OProblemas');
			$problemas = $this->OProblemas->comboProblemas();
			$this->set(compact('problemas'));
		}
		if (in_array($this->element_name, array(
			'clientes', 
			'gerenciar_clientes_produtos_descontos',
			'clientes_demonstrativos',
			'clientes_relacionamentos',
			'clientes_alertas_usuarios',
			'clientes_usuarios',
			'clientes_visualizar',
			'clientes_representantes',
			'clientes_funcionarios',
			'clientes_funcionarios_ppp',
			'clientes_produtos_contratos',
			'clientes_cadastrados',
			'clientes_operacoes',
			'clientes_configuracoes',
			'clientes_cargos',			
			'clientes_setores',
			'clientes_funcionarios_laudo_pcd'
			)))
			$this->carregaCombosClientes();
		if($this->element_name == 'estatisticas_sm_sintetico'){
			$this->carregarComboEstatisticasSmSintetico();
		}
		if ($this->element_name == 'clientes_buscar_codigo') {
			$this->carregaCombosClientes();
			$this->set('input_id', $this->passedArgs['searcher']);
		}
		// if ($this->element_name == 'prestadores_buscar_codigo') {
			//$this->carregaCombosClientes();
			// $this->set('input_id', $this->passedArgs['searcher']);
		// }
		if ($this->element_name == 'corretoras_buscar_codigo') {
			$this->set('input_id', $this->passedArgs['searcher']);
			$this->set('input_display', $this->passedArgs['display']);
		}
		if ($this->element_name == 'enderecos_buscar_cep') {
			$this->carregaComboEstadoCidade();
			$this->set('input_id', $this->passedArgs['searcher']);
		}
		if ($this->element_name == 'ocorrencias' || $this->element_name == 'ocorrencias_consulta')
			$this->carregaCombosOcorrencias();
		if ($this->element_name == 'clientes_produtos')
			$this->carregaCombosClientesProdutos();
		if ($this->element_name == 'clientes_data_cadastro' || $this->element_name == 'representantes')
			$this->carregaComboRegioes();
		if ($this->element_name == 'clientes_cadastrados')
			$this->carregaCombosClientesCadastrados();
		if ($this->element_name == 'solicitacoes_monitoramento' || $this->element_name == 'solicitacoes_monitoramento_historico' )
			$this->carregaCombosSolicitacoesMonitoramento();
		if ($this->element_name == 'atendimentos_sms' || $this->element_name == 'atendimentos_sms_consulta')
			$this->carregaCombosAtendimentosSms();
		if ($this->element_name == 'ranking_faturamento' || $this->element_name == 'ranking_faturamento2') {
			$this->carregaCombosRankingFaturamento($this->element_name == 'ranking_faturamento2');
		}
		if ($this->element_name == 'ranking_corretora' || $this->element_name == 'ranking_seguradora'
				|| $this->element_name == 'ranking_gestores' || $this->element_name == 'notas_fiscais_por_banco')
			$this->carregaComboListaAnosMeses();
		if ($this->element_name == 'fichas') {
			$this->tratamentoFicha();
			$this->carregaCombosFichas();
		}
		if($this->element_name =='taxa_administrativa_analitica'){
			$this->carregaComboListaAnosMesesTaxaAdm();
		}
		if($this->element_name == 'pesquisa_veiculo_consulta_fichas'){
			$this->PesquisaVeiculo = ClassRegistry::init('PesquisaVeiculo');
			if( $this->Session->read('veiculos_a_pesquisar' ) === TRUE ) {
				$status = array(
						PesquisaVeiculo::CADASTRADA => 'Cadastrada',
		            	PesquisaVeiculo::PESQUISA => 'Em Pesquisa'
	            	);
	         } else if( $this->Session->read('veiculos_finalizados' ) === TRUE ) {
	         	$status = array( 
			            PesquisaVeiculo::APROVADA => 'Aprovada',
			            PesquisaVeiculo::REPROVADA => 'Reprovada'
			        );   
	         }
			$this->set(compact('status'));
		}
		if ($this->element_name == 'transit_time')
			$this->carregaCombosTransitTime();
		if ($this->element_name == 'itinerarios_sms_por_cliente')
			$this->carregaCombosItinerariosSmsPorCliente();
		if ($this->element_name == 'duracao_sm')
			$this->carregaCombosDuracaoSm();
		if (in_array($this->element_name, array('veiculos', 'status_checklist'))) {
			$exibe_fields_checklist = ($this->element_name=='status_checklist');
			$this->carregaCombosVeiculo($exibe_fields_checklist);
		}

		if ($this->element_name == 'viagens_por_estacao'){
			$this->loadModel('TErasEstacaoRastreamento');
			$this->loadModel('StatusViagem');
			$estacao 		= $this->TErasEstacaoRastreamento->listaParaCombo();
			$status_viagens = $this->StatusViagem->find( array(StatusViagem::SEM_VIAGEM, StatusViagem::CANCELADO, StatusViagem::ENCERRADA ) );
        	$this->set(compact('estacao', 'status_viagens'));
		}

		if ($this->element_name == 'relatorios_sm_acompanhamento_viagens_analitico'
			|| $this->element_name == 'relatorios_sm_acompanhamento_viagens_sintetico'
			|| $this->element_name == 'relatorios_sm_ocorrencias_viagens_analitico'
			|| $this->element_name == 'sintetico_temperatura'
			|| $this->element_name == 'consulta_geral_sm'
			|| $this->element_name == 'relatorios_sm_custos_do_trajeto_analitico' ) {
			$exibe_bitrem = false;

			if( $this->element_name == 'consulta_geral_sm' ) {
				$this->loadModel('TTecnTecnologia');
				$this->loadModel('TErasEstacaoRastreamento');
				$estacao = $this->TErasEstacaoRastreamento->listaParaCombo();
				$tecnologias = $this->TTecnTecnologia->find('list', array('order' => 'tecn_descricao'));
				$this->loadModel('Seguradora');
				$seguradoras = $this->Seguradora->listarSeguradorasAtivas();
				if(!isset($this->data['RelatorioSmConsulta']['data_inicial']))
					$this->data['RelatorioSmConsulta']['data_inicial'] = date('d/m/Y');
				if(!isset($this->data['RelatorioSmConsulta']['data_final']))
					$this->data['RelatorioSmConsulta']['data_final'] = date('d/m/Y');
				
				$this->set(compact('tecnologias', 'seguradoras', 'estacao'));
			}

			if($this->element_name == 'relatorios_sm_acompanhamento_viagens_analitico' || $this->element_name == 'relatorios_sm_acompanhamento_viagens_sintetico' || $this->element_name == 'relatorios_sm_custos_do_trajeto_analitico') {
				$exibe_bitrem = true;
        		$qualidades = array('1' => 'Acima de', '2' => 'Abaixo de');
        		$this->set(compact('qualidades'));
			}
			
			$this->carregaComboTiposVeiculos($exibe_bitrem);

			$this->carregaComboAlvosBandeirasRegioes(false);

			if ($this->element_name == 'sintetico_temperatura') {
				$this->loadModel('RelatorioSm');
				$codigo_cliente = $this->data['RelatorioSm']['codigo_cliente'];
				$this->carregaCombosAlvosBandeirasRegioesCheckbox($codigo_cliente);
				$this->carregaComboStatusViagensEfetivas();
        		$qualidades = array('1' => 'Acima de', '2' => 'Abaixo de');
        		$this->set(compact('qualidades'));
			} else {
				$this->carregaComboStatusViagensSemSemViagem();
			}
			$this->carregarTiposTransporte();
			$this->carregarEstadoOrigem();
		}

		if ($this->element_name=='veiculos_mapa_gr') {
			$this->carregaCombosVeiculoMapaGr();
		}

		if($this->element_name == 'simulador_pgr'){
			$this->loadModel('TPgpgPg');
			$this->carregaCombosSimuladorPGR();
			if(empty($this->data['TPgpgPg']['codigo_transportador'])){
	            $this->TPgpgPg->invalidate('codigo_transportador', 'Informe o transportador');
	            return false;
	        }

	       	if(empty($this->data['TPgpgPg']['placa'])){
	            $this->TPgpgPg->invalidate('placa', 'Informe a placa');
	            return false;
	        }

	        if(empty($this->data['TPgpgPg']['ttra_codigo'])){
	            $this->TPgpgPg->invalidate('ttra_codigo', 'Informe o tipo do transporte');
	            return false;
	        }

	        $this->data['TPgpgPg']['filtro'] = true;
		}

		if ($this->element_name=='logsatendimento'){ 
			$this->carregaComboLogAtendimento();
		}

		if ($this->element_name=='logs_exclusao_vinculos'){
			$this->carregaComboLogAtendimento();
			if (empty($this->data['LogAtendimento']['data_inicial']) || empty($this->data['LogAtendimento']['data_final'])) {
				$this->loadModel('LogAtendimento');
				$this->LogAtendimento->invalidate('data_inicial','Favor informar a data início');
				$this->LogAtendimento->invalidate('data_final','Favor informar a data fim');
			}
		}

		if ($this->element_name == 'criterio_distribuicao') {
			$this->carregaComboCriterioDistribuicao();
		}

		if ($this->element_name == 'relatorios_sm_veiculos_por_regiao') {
			$this->carregaComboStatusViagens();
			$this->carregaComboTecnologia();
			$this->carregaComboTransportadores();
			$this->carregaComboClassesReferencias();
		}
		if ($this->element_name == 'relatorios_sm_situacao_frota' || $this->element_name == 'veiculos_posicao_frota')
			$this->carregaCheckboxAlvosOrigem($this->element_name);
		if ($this->element_name == 'tipos_referencias')
			$this->carregaCombosClientesTipos();
		if (in_array($this->element_name, array('referencias', 'referencias_buscar_codigo', 'referencias_compartilhadas', 'alvos_janelas'))) {
			$this->carregaCombosReferencia();
		}

		if ($this->element_name == 'informacoes_clientes')
			$this->carregaCombosInformacoesClientes();
		if ($this->element_name == 'faturamento_por_cliente') {
			$this->carregaCombosFatPorCliente();
		}
		if ($this->element_name == 'sinistros')
			$this->carregarComboSinistroEmbarcadorTransportador();

		if ($this->element_name == 'alertas')
			$this->carregarComboAlertas();

		if ($this->element_name == 'logs_integracoes_consultar')
			$this->carregarComboSistemaOrigem();
		if ($this->element_name == 'logs_aplicacoes_consultar')
			$this->carregarComboSistemas('completo');
		if ($this->element_name == 'logs_aplicacoes_resumido'){
			$this->carregarComboSistemas('resumido');
		}
		if ($this->element_name == 'forense')
			$this->carregarSeguradoras();

		if ($this->element_name == 'embarcadores_transportadores' || $this->element_name == 'matrizes_filiais' || $this->element_name == 'consultar_pagador_preco')
			$this->carregarComboProdutos();
		if ($this->element_name == 'rma' || $this->element_name == 'rma_sintetico') {
			$this->carregarComboRma($this->element_name == 'rma_sintetico');
		}
		if ($this->element_name == 'rma_estatistica') {
			$this->carregarComboRmaEstatistica();
		}
		if ($this->element_name == 'logrenovacao') {
			$this->LoadModel('ProfissionalTipo');
			$lista_tipo_profissional = $this->Fichas->listProfissionalTipoAutorizado();
				// debug( $this->data );
			if( empty($this->data['LogRenovacao']['data_inicial']) || empty($this->data['LogRenovacao']['data_final']) ){
				$this->data['LogRenovacao']['data_inicial'] = date("d/m/Y");
				$this->data['LogRenovacao']['data_final']   = date("d/m/Y");
				$this->Filtros->controla_sessao($this->data, 'LogRenovacao');
			}
			$this->set(compact('lista_tipo_profissional'));
        }
		if($this->element_name =='index_fichas_finalizadas'){
			$this->carregaComboResultadosPesquisa();
			if (empty($this->data['FichaScorecard']['data_inicial'])){
                $this->FichaScorecard->invalidate('data_inicial','Informe a Data Inicial');
			}			
			if (empty($this->data['FichaScorecard']['data_final'])){
            	$this->FichaScorecard->invalidate('data_final','Informe a Data Final');
			}
		}
		if ($this->element_name == 'fichas_scorecard_log')
				$this->carregarComboFichasScorecardLog();
		if ($this->element_name == 'viagens_operadores')
				$this->carregarComboOperadores();
		if ($this->element_name == 'veiculos_ocorrencias')
				$this->carregarComboVeiculosOcorrencias();
		if ($this->element_name == 'faturamento_analitico')
			$this->carregarComboDetalhesItensPedidos();
		if ($this->element_name == 'fichas_scorecard_relatorio_vinculo')
			$this->carregarComboRelatorioVinculo();
		if ($this->element_name == 'ficha_scorecard_consulta_profissional'){
			$this->loadModel('FichaScorecard');
			if(!$this->data['FichaScorecard']['codigo_cliente'])
				$this->FichaScorecard->invalidate('codigo_cliente','Informe o cliente');
			if(!$this->data['FichaScorecard']['codigo_documento'])
				$this->FichaScorecard->invalidate('codigo_documento','Informe o profissional');
			if($this->data['FichaScorecard']['codigo_cliente'] && !$this->data['FichaScorecard']['placa_veiculo'] && $this->FichaScorecard->verificaObrigatoriedadeDaPlaca($this->data['FichaScorecard']['codigo_cliente']) ){
				$this->FichaScorecard->invalidate('placa_veiculo','Informe a placa');
			}
			if (empty($this->data['FichaScorecard']['codigo_carga_tipo'])){
                $this->FichaScorecard->invalidate('codigo_carga_tipo','Informe o tipo da Carga');
			}
			if (empty($this->data['FichaScorecard']['codigo_carga_tipo'])){
                $this->FichaScorecard->invalidate('codigo_carga_tipo','Informe o tipo da Carga');
			}
            if (empty($this->data['FichaScorecard']['codigo_carga_valor'])){
            	$this->FichaScorecard->invalidate('codigo_carga_valor','Informe o Valor da Carga');
            }

            if (empty($this->data['FichaScorecard']['cidade_origem'])){
            	$this->FichaScorecard->invalidate('cidade_origem','Informe a Cidade Origem');
            }

            if (empty($this->data['FichaScorecard']['cidade_destino'])){
            	$this->FichaScorecard->invalidate('cidade_destino','Informe a Cidade Destino');
            }
            if (empty($this->data['FichaScorecard']['placa_veiculo'])){
            	$this->FichaScorecard->invalidate('placa_veiculo','Informe a Placa do Veículo');

            }

			if( empty($this->authUsuario['Usuario']['codigo_cliente']) && empty($this->data['FichaScorecard']['codigo_usuario']) )
				$this->FichaScorecard->invalidate('codigo_usuario','Informe o usuário');

			$this->carregarCombosFicha();
		}
		if ($this->element_name == 'ficha_scorecard_consulta_fichas')
			$this->carregaComboFichaScorecardConsultaFichas();
		if ($this->element_name == 'configuracao_comissao')
			$this->carregaComboConfiguracaoComissao();
		if ($this->element_name == 'configuracao_comissao_corretora')
			$this->carregaComboConfiguracaoComissaoCorretora();
		if ($this->element_name == 'comissoes_filiais')
			$this->carregaComboComissaoFilial();
		if ($this->element_name == 'log_integracao_outbox')
			$this->carregarCombosOutbox();
		if ($this->element_name == 'estatisticas_sm_analitico')
			$this->carregarCombosEstatisticasSmAnalitico();
		if ($this->element_name == 'veiculos_ocorrencias2')
			$this->carregarCombosVeiculosOcorrencias();

		if ($this->element_name == 'loadplan_analitico' || $this->element_name == 'loadplan_sintetico'){
        	$this->loadModel('StatusViagem');
        	$status_viagens = $this->StatusViagem->listarParaLoadplan();
        	$this->set( compact('status_viagens') );
		}
		if ($this->element_name == 'comissoes_analitico' || $this->element_name == 'comissoes_sintetico') {
			$this->carregarComboDetalhesItensPedidos();
			$this->AutorizacoesFiltros->defineVisualizacaoFiltroConfiguracao();
		}
		if($this->element_name == 'comissoes_por_corretora_sintetico' || $this->element_name == 'comissoes_por_corretora_analitico'){
			$this->carregarCombosComissoesPorCorretora();
			$this->AutorizacoesFiltros->defineVisualizacaoFiltroConfiguracao();
		}
		if ($this->element_name == 'sinistros' || $this->element_name == 'sinistros_analitico' || $this->element_name == 'sinistros_sintetico' || $this->element_name == 'mapa_sinistros' )
			$this->carregarCombosAnaliticoSinteticoSinistro();

		if ($this->element_name == 'ficha_scorecard_log_consulta'){
			$this->carregarlogConsultas();
        }
        if ($this->element_name == 'ws_configuracoes'){
			$this->carregarWsConfiguracao();
        }
		if ($this->element_name == 'profissional_negativado_cliente' ){
			$this->loadModel('TipoNegativacao');
			$tipo_negativacao = $this->TipoNegativacao->listar();
			$exibe_log = $this->Session->read('exibe_log');
			if( !empty($exibe_log) )
				$exibe_log = TRUE;
			$this->set(compact("tipo_negativacao", "exibe_log"));
		}

		if ($this->element_name == 'consulta_veiculos_checklist_vencido' ){
			$this->loadModel('Veiculo');
			if (empty($this->data['Veiculo']['codigo_cliente']) && $this->params['action'] == 'filtrar')
				$this->Veiculo->invalidate('codigo_cliente','Informe o Cliente');
			$filtros  = $this->Filtros->controla_sessao($this->data, 'Veiculo');
			if( !empty($filtros) ){
				$this->data['Veiculo'] = $filtros;
			} else {
				$this->data['Veiculo']['data_inicial'] = date('d/m/Y');
				$this->data['Veiculo']['data_final']   = date('d/m/Y');
			}
		}
		if ($this->element_name == 'estatisticas_viagens_por_agrupamento' )
			$this->carregarCombosEstatisticasViagensPorAgrupamento();
		if ($this->element_name == 'viagens_faturamento_total' || $this->element_name == 'viagens_faturamento_subtotal' || $this->element_name == 'viagens_faturamento')
			$this->carregarCombosViagensFaturamento();
		if ($this->element_name == 'janelas' ){
			$isPost = ($this->RequestHandler->isPost() || $this->RequestHandler->isAjax());
			$this->set(compact('isPost'));
		}

		if ($this->element_name == 'rotas_buscar_codigo')
			$this->carregaComboRota();

		if( $this->element_name == 'clientes_analitico_pgr' || $this->element_name == 'clientes_sintetico_pgr'  ){
			if( empty($this->data['ClientesPGR']))
				$this->data['ClientesPGR']['agrupamento']=1;
			$this->carregaCombosClientes(true);
			$this->loadModel('Cliente');
			$agrupamento = $this->Cliente->tipoAgrupamentoPgrSintetico();
			$this->set(compact('agrupamento'));
		}

		if( $this->element_name == 'atendimentos_sac_analitico' || $this->element_name == 'atendimentos_sac_sintetico'){
			$this->carregarCombosAtendimentos(); 
		}

		if( $this->element_name == 'objetivo_comercial' || $this->element_name == 'objetivo_comercial_analitico' || $this->element_name == 'objetivo_comercial_sintetico'){
			$this->carregaComboObjetivoComercial(); 
		}

		if( $this->element_name == 'operadores'){
			$status = array('1' =>'Ativo', '2'=> 'Inativo');
			$this->set(compact('status'));
		}

		if ($this->element_name == 'utilizacao_servicos_configuracoes') {
			$filtros = $this->Filtros->controla_sessao($this->data, 'LogFaturamentoTeleconsult');
			if(empty($filtros)) {
			$this->data['LogFaturamentoTeleconsult']['data_inclusao_inicio'] = date('d/m/Y');
            $this->data['LogFaturamentoTeleconsult']['data_inclusao_fim']   = date('d/m/Y'); 
			}
			$this->carregarCombosLogFaturamento();
		}
		
		if ($this->element_name == 'sensores_temperaturas') {			
			$this->loadModel('TStemSensoresTemperatura');
			if( empty($this->data['TStemSensoresTemperatura']) ){
		        $this->data['TStemSensoresTemperatura']['data_inicial'] = date("d/m/Y");
		        $this->data['TStemSensoresTemperatura']['data_final'] = date("d/m/Y");
			} else {
				if(!$this->data['TStemSensoresTemperatura']['codigo_cliente'])
					$this->TStemSensoresTemperatura->invalidate('codigo_cliente','Informe o Cliente');
				if(!$this->data['TStemSensoresTemperatura']['veic_placa'])
					$this->TStemSensoresTemperatura->invalidate('veic_placa','Informe a Placa');
			}
		}
		if ($this->element_name == 'pesquisa_satisfacao'){
			$filtros = $this->Filtros->controla_sessao($this->data, 'PesquisaSatisfacao');
			if(empty($filtros)) {
				$this->data['PesquisaSatisfacao']['data_inicial'] = '01/'.date("d/Y");
				$this->data['PesquisaSatisfacao']['data_final'] = date("d/m/Y");
				$this->data['PesquisaSatisfacao']['status_pesquisa'] = 1;
				$filtros = $this->Filtros->controla_sessao($this->data, 'PesquisaSatisfacao');
			}
		}
		if ($this->element_name == 'pesquisa_satisfacao_sintetico' || $this->element_name == 'pesquisa_satisfacao_analitico'){
			$this->loadModel('StatusPesquisaSatisfacao');
			$this->loadModel('PesquisaSatisfacao');
			$this->loadModel("Usuario");
			if(empty($this->data['PesquisaSatisfacao'])) {
				$this->data['PesquisaSatisfacao']['data_inicial'] = '01/'.date("d/Y");
				$this->data['PesquisaSatisfacao']['data_final']   = date("d/m/Y");
			}
			if( $this->element_name == 'pesquisa_satisfacao_sintetico' )
				$sintetico = true;
			$agrupamento 	   = $this->PesquisaSatisfacao->listaAgrupamento();
        	$usuarios_pesquisa = $this->Usuario->find('list', array('conditions'=>array('codigo_cliente'=>NULL)) );
			$status_pesquisa   = $this->StatusPesquisaSatisfacao->find('list',array('fields'=>'descricao_pesquisa'));
			$this->carrega_combos_pesquisa_satisfacao();
			$this->set(compact('status_pesquisa', 'usuarios_pesquisa', 'agrupamento','sintetico'));
		}

		if($this->element_name == 'pesquisa_satisfacao_anual') {
			$this->loadModel('Usuario');
			$this->loadModel('StatusPesquisaSatisfacao');
			$anos = Comum::listAnos(date('Y')-3);
	    	$filtros['PesquisaSatisfacao'] = $this->Filtros->controla_sessao($this->data, "PesquisaSatisfacaoAnual");
			$usuarios_pesquisa = $this->Usuario->find('list', array('conditions'=>array('codigo_cliente'=>NULL)) );
			$status_pesquisa   = $this->StatusPesquisaSatisfacao->find('list',array('fields'=>'descricao_pesquisa'));
			$status_pesquisa += array(5 => 'Cancelado', 6 => 'Bloqueado', 7 => 'Sem pesquisa');
			$this->data['PesquisaSatisfacaoAnual']['ano'] = (isset($this->data['PesquisaSatisfacaoAnual']['ano']) ? $this->data['PesquisaSatisfacaoAnual']['ano'] :  date("Y"));
			$this->set(compact('anos', 'usuarios_pesquisa', 'status_pesquisa'));
		}

		if ($this->element_name == 'mods_ivrs_pesquisas_analitico' || $this->element_name == 'mods_ivrs_pesquisas_sintetico'){
			$this->carregarCombosModsIvrsPesquisas();
		}
		if( $this->element_name =='embarcador_transportador' ){
			$usuario = $this->BAuth->user();
			$this->set(compact('usuario'));
		}		
		
		if ( $this->element_name =='metas_centro_custo' ){
			$this->carregarCombosMetaCentroCusto();
		}
		if ($this->element_name == 'registros_telecom_analitico' || $this->element_name == 'registros_telecom_sintetico'){
			$this->carregarCombosRegistrosTelecom();
		}
		if ($this->element_name == 'nivel_de_servicos'){
			$this->carregarCombosNiveldeServicos();
		}
		if ($this->element_name == 'tempo_maximo_sintetico' || $this->element_name == 'tempo_maximo_analitico'){
			$this->carregarCombosTempoMaximoSintetico();
		}
		if ($this->element_name == 'ponto_eletronico'){
			$this->carregarCombosPontoEletronico();
		}
		if ($this->element_name == 'itens_checklist'){
			$this->carregarCombosItensChecklist();
		}
		if($this->element_name == 'cargas_patio'){
			$this->carregarCombosCargasPatio();
		}
		if($this->element_name == 'estatistica_eventos' || $this->element_name == 'estatistica_eventos_analitico'){
			$this->carregarCombosEstatisticaCombos();
		}

		if($this->element_name == 'objetivo_comercial_excecoes'){
			$this->carregarCombosObjetivosComerciaisExcecoes();
		}
		
		if ($this->element_name == 'fichas_scorecard_renovacao' || $this->element_name == 'profissionais_renovacao_scorecard' ){
			$this->loadModel('RenovacaoAutomatica');
			if( empty($this->data['RenovacaoAutomatica']['codigo_cliente']))
				$this->RenovacaoAutomatica->invalidate('codigo_cliente', 'Informe o Cliente');
			if( empty($this->data['RenovacaoAutomatica']['dias_renovacao']))
				$this->RenovacaoAutomatica->invalidate('dias_renovacao', 'Informe a quantidade de dias');			
		}	
		if ( ($this->element_name == 'log_faturamento_filtros') || ($this->element_name == 'log_faturamento_excluido')){
			$this->loadModel('TipoOperacao');
			$tipo_operacao= $this->TipoOperacao->find('list',array('fields'=>array('descricao'),'order'=>array('descricao')));
        	$this->set(compact('tipo_operacao'));			
		}
		if ($this->element_name == 'inicio_viagem'){
			$this->carregaCombosChecklistOnline();
		}
		if ($this->element_name == 'checklist_viagem_analitico' || $this->element_name == 'checklist_viagem_sintetico'){
			$this->carregaCombosChecklistAnalitico();
		}
		if($this->element_name == 'tveiculos'){
			$this->Tveiculos = ClassRegistry::init('Tveiculos');
			$agrupamento = $this->Tveiculos->listaAgrupamento();
			$this->set(compact('agrupamento'));
		}
		if($this->element_name == 'tpecas'){
			$this->Tpecas = ClassRegistry::init('Tpecas');
			$agrupamento = $this->Tpecas->listaAgrupamento();
			$this->set(compact('agrupamento'));
		}
		if($this->element_name == 'autotrac_faturamento'){			
			$mes_referencia = Comum::listMeses();
	        $ano_referencia = Comum::listAnos('2015');          
	        $this->set(compact('mes_referencia','ano_referencia'))  ;   
		}

		if ($this->element_name == 'proposta_perguntas'){
			$this->carregaCombosPerguntaProposta();
		}
		if ($this->element_name == 'propostas' || $this->element_name == 'propostas_pendentes' || $this->element_name == 'propostas_pendentes_gerencia'){
			$this->carregaCombosProposta( (strpos($this->element_name,'propostas_pendentes')!==false) ,($this->element_name == 'propostas_pendentes_gerencia'?'G':'D') );
		}
		
		if ($this->element_name == 'pgr_referencias'){
			$this->carregaCombosPgr();
		}

		if (in_array($this->element_name, array('usuarios', 'usuarios_por_cliente_listagem', 'usuarios_alertas_por_cliente'))) {
			$this->carrega_combos_perfil();
		}
		
		if ($this->element_name == 'vinculo_veiculo_periferico') {
			$this->carrega_combos_vinculo_veiculo_periferico();
		}
		
		if ($this->element_name == 'proposta_limites'){
			$this->carregaCombosPropostaLimites();
		}
		if ($this->element_name == 'log_consultas'){
			$this->carregaCombosLogConsultas();
		}

        if ($this->element_name == 'mapa_prestadores') {
            $this->loadModel('Prestador');
            $isPost = ($this->RequestHandler->isPost() || $this->RequestHandler->isAjax());
            $filtrado = false;
            if($isPost){
                if(isset($this->data['Prestador']['latitude']) && isset($this->data['Prestador']['latitude']) && isset($this->data['Prestador']['latitude'])){
                    if(empty($this->data['Prestador']['latitude'])){
                        $this->Prestador->invalidate('latitude','Informe a Latitude');
                    }
                    if(empty($this->data['Prestador']['longitude'])){
                        $this->Prestador->invalidate('longitude','Informe a Longitude');
                    }
                    if(empty($this->data['Prestador']['raio'])){
                        $this->Prestador->invalidate('raio','Informe a Raio');
                    }
                    if(empty($this->Prestador->validationErrors)){
                        $filtrado = true;
                    }
                }
            }
            $input_id = !empty($this->passedArgs['searcher']) ? $this->passedArgs['searcher']:NULL;
            $this->set(compact( 'filterValidated', 'isPost','filtrado', 'input_id'));
        }

        if($this->element_name == 'prestadores_analitico' || $this->element_name == 'prestadores_sintetico') {
        	$this->CarregaCombosAcionamentoPrestadores();
        }

        if($this->element_name == 'eventos_compostos') {
        	$this->carregaCombosEventosCompostos();
        }


        if($this->element_name == 'pgr_relacao_clientes') {
        	$this->carregaPGRRelacaoCliente();
        }	


        if($this->element_name == 'diretoria_filtro') {
        	$this->carregaCombosDiretoria();
        }

        if($this->element_name == 'diretoria_usuario_filtro') {
        	$this->carregaCombosDiretoriaUsuario();
        }

        if($this->element_name == 'objetivo_comercial_exc_faturamento') {
        	$this->carregaObjetivoExcecaoFaturamento();
        }
        
        
		if ($this->element_name == 'supervisores_equipes'){
			$this->Uperfil = ClassRegistry::init('Uperfil');
			if( empty($this->data['Uperfil']['codigo'])){
				$this->Uperfil->invalidate('codigo', 'Informe o Perfil');			
				$this->set('filtrado', NULL);
			}
			$codigo_uperfil = (!empty($this->authUsuario['Usuario']['codigo_uperfil']) ? $this->authUsuario['Usuario']['codigo_uperfil'] : NULL );
	        if( $codigo_uperfil ){
	            $perfil_usuario = $this->Uperfil->carregar($codigo_uperfil);            
	            $perfis         = $this->Uperfil->listaPerfilFilho( $perfil_usuario['Uperfil']['codigo'] );
	        }
	        $this->set(compact('perfil_usuario', 'perfis'));
		}
		if ($this->element_name == 'fichas_scorecard_excluir_vinculo') {
			if( empty($this->data['FichaScorecard']['data_inicial']) || empty($this->data['FichaScorecard']['data_final'])) {
				$this->data['FichaScorecard']['data_inicial']   = date("d/m/Y");
		        $this->data['FichaScorecard']['data_final']     = date("d/m/Y");
			}
		}
        if($this->element_name == 'logserasa') {
        	if( empty($this->data['Usuario']['data_inclusao_inicio']) || empty($this->data['Usuario']['data_inclusao_fim']) ){
				$this->data['Usuario']['data_inclusao_inicio'] = date("d/m/Y");
		        $this->data['Usuario']['data_inclusao_fim']    = date("d/m/Y");
	    	}
			$this->Filtros->controla_sessao($this->data, 'Usuario');
        }

		if ($this->element_name == 'fichas_scorecard_relatorios_gerenciais') {
			$anos  = Comum::listAnos( date("Y")-2 );
			$meses = Comum::listMeses();
			$tipo_busca = (empty($this->data['FichaScorecard']['tipo_busca']) ? $this->params['pass'][0] : $this->data['FichaScorecard']['tipo_busca']);
			$this->data['FichaScorecard']['tipo_busca'] = $tipo_busca;
			if( $tipo_busca == 3 ){
				if( empty($this->data['FichaScorecard']['data']))
					$this->data['FichaScorecard']['data'] = date("d/m/Y");
				if( empty($this->data['FichaScorecard']['hora_inicio']))
					$this->data['FichaScorecard']['hora_inicio'] = date("h:00");
				if( empty($this->data['FichaScorecard']['hora_termino']))
					$this->data['FichaScorecard']['hora_termino'] = '23:59';
				if( empty($this->data['FichaScorecard']['tipo_origem']))
					$this->data['FichaScorecard']['tipo_origem'] = 0;				
			}
			if( empty($this->data['FichaScorecard']['ano']))
				$this->data['FichaScorecard']['ano'] = date('Y');
			if( empty($this->data['FichaScorecard']['tipo_mes']))
				$this->data['FichaScorecard']['tipo_mes'] = date('m');
			$tipo_profissional = $this->Fichas->listProfissionalTipoAutorizado();
			$this->Filtros->controla_sessao($this->data, 'FichaScorecard');
			$this->set(compact('anos', 'meses', 'tipo_profissional', 'tipo_busca'));
		}
       if($this->element_name == 'usuarios') {
			$action = 'editar';
			if (isset($this->params['pass'][0]) && ($this->params['pass'][0] == 'editar_configuracao' || $this->params['pass'][0] == 'configuracao' )) {
				$action = 'configuracao';
			}
			$this->data['Usuario']['action'] = $action;
			$this->Filtros->controla_sessao($this->data, 'Usuario');
		}

		if($this->element_name == 'fichas_scorecard_relatorio_vinculo_excluido' ) {
			if( empty($this->data['FichaScorecard']['data_alteracao_inicial']) || empty($this->data['FichaScorecard']['data_alteracao_final']) ){
				$this->data['FichaScorecard']['data_alteracao_inicial'] = date("d/m/Y");
				$this->data['FichaScorecard']['data_alteracao_final']   = date("d/m/Y");
			}
			$this->Filtros->controla_sessao($this->data, 'FichaScorecard');
		}

		if ($this->element_name == 'tratativas_eventos_sistema'){
			$this->TEspaEventoSistemaPadrao = ClassRegistry::init('TEspaEventoSistemaPadrao');
			$eventos = $this->TEspaEventoSistemaPadrao->find('list');
			$this->set(compact('eventos'));
		}

		if ($this->element_name == 'sms'){
			$this->loadmodel('SmsOutbox');	

			if (empty($this->data['SmsOutbox']['data_inicial']) ) {
				$this->SmsOutbox->invalidate('data_inicial','informe a data');
			}

			if (empty($this->data['SmsOutbox']['data_final']) ) {
				$this->SmsOutbox->invalidate('data_final','informe a data');
			}
			
			$this->periodo_validade_consulta('SmsOutbox');
			$modem = array(1 => 'MODEM 1', 2 => 'MODEM 2', 3 => 'MODEM 3', 4 => 'MODEM 4');
			$sistema_origem = array(SmsOutbox::MANUAL => SmsOutbox::MANUAL, SmsOutbox::PLANILHA => SmsOutbox::PLANILHA);
			$this->set(compact('modem','sistema_origem')); 	
		}
		
		if ($this->element_name == 'tipos_deficiencia'){
			$this->loadmodel('TipoDeficiencia');	
			$classificacao = array('AUDITIVA' => TipoDeficiencia::AUDITIVA, 'FISICA' =>TipoDeficiencia::FISICA, 'INTELECTUAL' =>TipoDeficiencia::INTELECTUAL, 'MENTAL' => TipoDeficiencia::MENTAL, 'MULTIPLA' => TipoDeficiencia::MULTIPLA, 'VISUAL' => TipoDeficiencia::VISUAL, 'REABILITACAO' => TipoDeficiencia::REABILITACAO);
        	$this->set(compact('classificacao'));
		}
		if ($this->element_name == 'motivos_afastamento'){
			$this->loadmodel('TipoAfastamento');

			$tipos_afastamento = $this->TipoAfastamento->find ('list', array('fields' => array('codigo','descricao'), 'order' => 'descricao'));
        	$this->set(compact('tipos_afastamento'));	
		}		
		if ($this->element_name == 'medicamentos'){
			$this->loadmodel('Laboratorio');				
			$laboratorios = $this->Laboratorio->find ('list', array('fields' => array('codigo','descricao'), 'order' => 'descricao'));
        	$this->set(compact('laboratorios'));   
		}
		if ($this->element_name == 'grupos_exposicao'){
			$this->loadmodel('Cargo');
			$this->loadmodel('Setor');
			$this->loadmodel('GrupoHomogeneo');
			$this->loadmodel('GrupoEconomicoCliente');

			$codigo_cliente = $this->passedArgs['codigo_cliente'];

			$cliente = $this->GrupoEconomicoCliente->retorna_dados_cliente($codigo_cliente);	
			$cargo = $this->Cargo->lista_por_cliente($codigo_cliente);      
        	$setor = $this->Setor->lista_por_cliente($codigo_cliente);
        	$grupo_homogeneo = $this->GrupoHomogeneo->lista_por_cliente($codigo_cliente);

			$this->data = array_merge($this->data, $cliente);
	    	$this->set(compact('cargo','setor', 'grupo_homogeneo'));    	
		}

		if ($this->element_name == 'medicao'){
			$this->loadmodel('Risco');
			$this->loadmodel('Cargo');
			$this->loadmodel('Setor');
			$this->loadmodel('Cliente');

			$this->set('array_risco', $this->Risco->find('list', array('fields' => array('codigo', 'nome_agente'), 'order' => array('nome_agente ASC'))));
	        $this->set('array_cargo', $this->Cargo->find('list', array('conditions' => array('ativo' => true), 'fields' => array('codigo', 'descricao'), 'order' => array('descricao ASC'))));
	        $this->set('array_setor', $this->Setor->find('list', array('conditions' => array('ativo' => true), 'fields' => array('codigo', 'descricao'), 'order' => array('descricao ASC'))));
	        $this->set('array_cliente', $this->Cliente->find('list', array(
	        	'conditions' => array('ativo' => true),
	        	'fields' => array('Cliente.codigo', 'Cliente.razao_social'), 
	        	'order' => array('Cliente.razao_social ASC')
	        )));
		}

		if ($this->element_name == 'sist_combate_incendio'){
			$this->loadmodel('TipoSistIncendio');
			$this->loadmodel('Setor');
			$this->loadmodel('Cliente');

			$this->set('array_tipo', $this->TipoSistIncendio->find('list', array('fields' => array('codigo', 'nome'), 'order' => array('nome ASC'))));
	        $this->set('array_setor', $this->Setor->find('list', array('conditions' => array('ativo' => true), 'fields' => array('codigo', 'descricao'), 'order' => array('descricao ASC'))));
	        $this->set('array_cliente', $this->Cliente->find('list', array(
	        	'conditions' => array('ativo' => true),
	        	'fields' => array('Cliente.codigo', 'Cliente.razao_social'), 
	        	'order' => array('Cliente.razao_social ASC')
	        )));
		}	

		if ($this->element_name == 'fispq'){
			$this->loadmodel('FornecedorUnidade');

	        $this->set('array_unidade', $this->FornecedorUnidade->find('list', array(
	        	'conditions' => array('ativo' => true),
	        	'joins' => array(
					array(
		            	'table'      => 'fornecedores',
		                'alias'      => 'Fornecedor',
		                'conditions' => 'Fornecedor.codigo = FornecedorUnidade.codigo_fornecedor_unidade',
		                'type'       => 'inner'
					)
				),
	        	'fields' => array('FornecedorUnidade.codigo', 'Fornecedor.nome'), 
	        	'order' => array('nome ASC')
	        )));
		}

		if (($this->element_name == 'propostas_credenciamento') || ($this->element_name == 'propostas_credenciamento_manutencao_valores_exames')){
			$this->loadmodel('StatusPropostaCred');

			$this->StatusPropostaCred->virtualFields = array(
					'ordenada' => 'CONCAT(StatusPropostaCred.ordenacao, " - ", StatusPropostaCred.descricao)'
			);			
			
	        $this->set('array_status', array('' => 'Todos os Status do Processo') + $this->StatusPropostaCred->find('list', array(
	        	'fields' => array('StatusPropostaCred.codigo', 'ordenada'), 
	        	'order' => array('ordenacao ASC')
	        )));
	        
	        $this->set('array_cadastro', array('' => 'Todos os Tipos', '1' => 'Cadastramento Ativo', '0' => 'Cadastramento Passivo'));
	        $this->set('array_polaridade', array('' => 'Todos os Status', '1' => 'Propostas Ativas', '0' => 'Propostas Inativas'));
		}		

		if ($this->element_name == 'riscos') {
			$this->set('array_grupo', array( '1' => 'Físico', '2' => 'Químico', '3' => 'Biológico', '4' => 'Ergonômico', '5' => 'Acidentes', '6' => 'Inespecífico' ));
		}		

		if ($this->element_name == 'aplicacao_exames') {
			
			$this->loadModel('Cargo');
			$this->loadModel('Setor');
			$this->loadModel('Exame');
			$this->loadModel('Cliente');
			$this->loadModel('GrupoEconomicoCliente');
			
	    	$dados_cliente = $this->GrupoEconomicoCliente->retorna_dados_cliente($this->passedArgs['codigo_cliente']);
	    	$cargos = $this->Cargo->lista_por_cliente($this->passedArgs['codigo_cliente']);
	    	$setores = $this->Setor->lista_por_cliente($this->passedArgs['codigo_cliente']);
	    	
	    	$codigo_cliente = $this->passedArgs['codigo_cliente'];
		   
		    $this->data['Matriz'] = $dados_cliente['Matriz'];
        	$this->data['Unidade'] = $dados_cliente['Unidade'];
		    
		    $this->set(compact('cargos', 'setores', 'dados_cliente'));

		    $cliente = $this->Cliente->find('first', array('conditions' => array('ativo' => true, 'codigo' => $codigo_cliente), 'fields' => array('codigo', 'razao_social')));
		    $this->data = array_merge($this->data, $cliente);
		}

		if ($this->element_name == 'riscos_exames') {
			$this->loadmodel('Risco');
			$this->loadmodel('Exame');
			
			$exames = $this->Exame->find('list', array('conditions' => array('ativo'=> 1), 'order' => 'descricao', 'fields' => array('codigo', 'descricao')));  
	        
	        $riscos = $this->Risco->find('list', array('order' => 'nome_agente', 'fields' =>  array('codigo', 'nome_agente')));  

	        $this->set(compact('exames', 'riscos'));

		}

		if ($this->element_name == 'cargos') {	
			$this->loadmodel('Cliente');

			$cliente = $this->Cliente->find('first', array('conditions' => array('codigo'=> $this->passedArgs['codigo_cliente'])));  					
			$this->data = array_merge($this->data, $cliente);

			$referencia = $this->passedArgs['referencia'];
			$this->set(compact('referencia'));
		}

		if ($this->element_name == 'setores') {	
			$this->loadmodel('Cliente');

			$cliente = $this->Cliente->find('first', array('conditions' => array('codigo'=> $this->passedArgs['codigo_cliente'])));  					
			$this->data = array_merge($this->data, $cliente);

			$referencia = $this->passedArgs['referencia'];
			$this->set(compact('referencia'));
		}

		if ($this->element_name == 'funcionarios') {
			$this->loadmodel('Cliente');
			$this->loadmodel('Setor');
			$this->loadmodel('Cargo');
			
			$codigo_cliente = $this->passedArgs['codigo_cliente'];
			$referencia = $this->passedArgs['referencia'];

			$cliente = $this->Cliente->find('first', array('conditions' => array('codigo'=> $codigo_cliente)));  					
			$this->data = array_merge($this->data, $cliente);


			$cargos = $this->Cargo->lista_por_cliente($codigo_cliente);
        	$setores = $this->Setor->lista_por_cliente($codigo_cliente);
			$this->set(compact('referencia', 'cargos', 'setores'));
		}
		
		if($this->element_name == 'localizar_credenciado') {
			
			
			
		}

		if ($this->element_name == 'clientes_estrutura') {
			$this->loadmodel('ClienteImplantacao');
		
			if(empty($this->data['ClienteImplantacao']['codigo_cliente'])){
				$this->ClienteImplantacao->invalidate('codigo_cliente', 'Informe o código do cliente');
			}
		}
		if ($this->element_name == 'buscar_risco') {
			$this->loadmodel('Risco');

			$input_id = $this->passedArgs['input_id'];
			$input_display  = $this->passedArgs['input_display'];
			$array_grupo = $this->Risco->carrega_grupo();
			$this->set(compact('input_id', 'input_display', 'array_grupo'));
		}

		if ($this->element_name == 'localiza_cbo') {
			$input_id = $this->passedArgs['input_id'];
			$input_display  = $this->passedArgs['input_display'];
			
			$this->set(compact('input_id', 'input_display'));
		}	

		if ($this->element_name == 'medicos') {
			$this->loadmodel('ConselhoProfissional');
			$this->loadmodel('EnderecoEstado');
			
			$conselho_profissional = $this->ConselhoProfissional->find('list', array('fields' => array('codigo', 'descricao'),'order' => 'codigo'));
       		$estado = $this->EnderecoEstado->find('list', array('conditions' => array('codigo_endereco_pais' => 1),'fields' => array('abreviacao', 'descricao'),'order' => 'descricao'));
        
        	$this->set(compact('conselho_profissional', 'estado'));
		}	
		if ($this->element_name == 'consulta_documentos_pendentes'){
			$this->loadmodel('EnderecoEstado');
			$this->loadmodel('EnderecoCidade');
			$this->loadmodel('TipoDocumento');
			
	  		$this->set('list_estados', array('' => 'UF') + $this->EnderecoEstado->find('list', array('conditions' => array('codigo_endereco_pais' => '1'), 'fields' => array('codigo', 'descricao'))));
	  		
			if(isset($this->data['Consulta']['codigo_estado_endereco']) && $this->data['Consulta']['codigo_estado_endereco']) {
	  			$this->set('list_cidades', array('' => 'Selecione o Estado Primeiro') + $this->EnderecoCidade->find('list', array('conditions' => array('codigo_endereco_estado' => $this->data['Consulta']['codigo_estado_endereco']), 'fields' => array('codigo', 'descricao'))));
	  		} else {
	  			$this->set('list_cidades', array('' => 'Selecione o Estado Primeiro'));
	  		}
	  		
	  		$this->set('list_documentos', array('' => 'Todos os Documentos') + $this->TipoDocumento->find('list', array('conditions' => array('obrigatorio' => '1', 'status' => '1'), 'fields' => array('codigo', 'descricao'))));
		}
		
		if ($this->element_name == 'consulta_propostas') {
			$this->loadmodel('EnderecoEstado');
			$this->loadmodel('EnderecoCidade');
			$this->loadmodel('StatusPropostaCred');
			$this->loadmodel('Usuario');
			$this->loadmodel('MotivoRecusa');
			
	  		$lista_estados = $this->EnderecoEstado->find('list', array('conditions' => array('codigo_endereco_pais' => '1'), 'fields' => array('codigo', 'descricao')));
	  		$this->set('list_estados', array('' => 'UF') + $lista_estados);
	  		
	  		if(isset($this->data['ConsultaProposta']['codigo_estado_endereco']) && $this->data['ConsultaProposta']['codigo_estado_endereco']) {
	  			$this->set('list_cidades', array('' => 'Selecione o Estado Primeiro') + $this->EnderecoCidade->find('list', array('conditions' => array('codigo_endereco_estado' => $this->data['ConsultaProposta']['codigo_estado_endereco']), 'fields' => array('codigo', 'descricao'))));
	  		} else {
	  			$this->set('list_cidades', array('' => 'Selecione o Estado Primeiro'));
	  		}
	  		
	  		$this->set('list_usuarios', array('' => 'Todos') + $this->Usuario->find('list', array('conditions' => array('codigo_uperfil <>' => '3'))));
	  		$this->set('list_motivos', array('' => 'Todos') + $this->MotivoRecusa->find('list', array('conditions' => array('ativo' => '1'), 'fields' => array('codigo', 'descricao'))));
	  		
	  		$this->StatusPropostaCred->virtualFields = array(
	  				'ordenada' => 'CONCAT(StatusPropostaCred.ordenacao, " - ", StatusPropostaCred.descricao)'
	  		);
	  		
	        $this->set('array_status', array('' => 'Todos os Status') + $this->StatusPropostaCred->find('list', array(
	        	'fields' => array('StatusPropostaCred.codigo', 'ordenada'),
	        	'order' => array('StatusPropostaCred.ordenacao ASC')
	        )));	  		
		}
		
		if($this->element_name == 'documentos_vencidos_fornecedor'){
			$this->loadmodel('EnderecoEstado');
			$this->loadmodel('EnderecoCidade');
			$this->loadmodel('TipoDocumento');
			
			$estados = $this->EnderecoEstado->retorna_estados();
			$tipos_documentos = $this->TipoDocumento->retorna_tipos_documentos();
			
			if(isset($this->data['Consulta']['estado']) && $this->data['Consulta']['estado']) {
				$cidades = array('' => 'Selecione o Estado Primeiro') + $this->EnderecoCidade->find('list', array('conditions' => array('codigo_endereco_estado' => $this->data['Consulta']['estado']), 'fields' => array('codigo', 'descricao')));
			} else {
				$cidades = array('' => 'Selecione o Estado Primeiro');
			}			
      		
      		$this->set(compact('estados','tipos_documentos', 'cidades'));
		}

		if($this->element_name == 'produtos_servicos'){
			$this->loadmodel('EnderecoEstado');
			$this->loadmodel('EnderecoCidade');
			$this->loadmodel('Produto');

			if(isset($this->data['Consulta']['estado']) && $this->data['Consulta']['estado']) {
				$cidades = array('' => 'Selecione o Estado Primeiro') + $this->EnderecoCidade->find('list', array('conditions' => array('codigo_endereco_estado' => $this->data['Consulta']['estado']), 'fields' => array('codigo', 'descricao')));
			} else {
				$cidades = array('' => 'Selecione o Estado Primeiro');
			}			
			$estados = $this->EnderecoEstado->retorna_estados();
			$produtos = $this->Produto->listar('list', array('ativo' => true));
      		
      		$this->set(compact('estados','produtos', 'cidades'));
		}

		if($this->element_name == 'fornecedores'){
			$this->loadmodel('EnderecoEstado');
			$this->loadmodel('EnderecoCidade');
						
			$estados = $this->EnderecoEstado->retorna_estados();
						
			if(isset($this->data['Fornecedor']['estado']) && $this->data['Fornecedor']['estado']) {
				$cidades = array('' => 'Selecione o Estado Primeiro') + $this->EnderecoCidade->find('list', array('conditions' => array('codigo_endereco_estado' => $this->data['Fornecedor']['estado']), 'fields' => array('codigo', 'descricao'), 'order' => 'descricao'));
			} else {
				$cidades = array('' => 'Selecione o Estado Primeiro');
			}			
      		
      		$this->set(compact('estados','cidades'));
		}
		
		if($this->element_name == 'fornecedores_capacidade_agenda') {
			$this->loadmodel('EnderecoEstado');
			$this->loadmodel('EnderecoCidade');
		
			$estados = $this->EnderecoEstado->retorna_estados();
			
			if(isset($this->data['FornecedorCapacidadeAgenda']['estado']) && $this->data['FornecedorCapacidadeAgenda']['estado']) {
				$cidades = array('' => 'Selecione o Estado Primeiro') + $this->EnderecoCidade->find('list', array('conditions' => array('codigo_endereco_estado' => $this->data['FornecedorCapacidadeAgenda']['estado']), 'fields' => array('codigo', 'descricao'), 'order' => 'descricao'));
			} else {
				$cidades = array('' => 'Selecione o Estado Primeiro');
			}
		
			$this->set(compact('estados','cidades'));
		}		

		if($this->element_name == 'buscar_grupo_exposicao'){
			$this->loadmodel('Cargo');
			$this->loadmodel('Setor');
			$this->loadmodel('Cliente');
			$this->loadmodel('Risco');


			if(!empty($this->data['GrupoExposicao']['unidade']))
				$codigo_cliente = $this->data['GrupoExposicao']['unidade'];
			else
				$codigo_cliente = $this->passedArgs['unidade'];

			$cargo = $this->Cargo->lista_por_cliente($codigo_cliente);      
	        $setor = $this->Setor->lista_por_cliente($codigo_cliente);
	        $risco = $this->Risco->lista_por_cliente($codigo_cliente);
               

			// $cliente = $this->Cliente->find('first', array('conditions' => array('ativo' => true, 'codigo' => $codigo_cliente), 'fields' => array('codigo', 'razao_social')));

			

			// $grupo_risco = $this->Risco->carrega_grupo();
			
			// if(!empty($this->data['GrupoExposicao']['codigo_risco'])){
			// 	$risco = $this->Risco->find('first', array('conditions' => array('codigo' => $this->data['GrupoExposicao']['codigo_risco']), 'fields' => array('codigo', 'nome_agente', 'codigo_grupo')));
			// 	$this->data['GrupoExposicao']['codigo_risco'] = $risco['Risco']['codigo'];
			// 	$this->data['GrupoExposicao']['nome_agente'] = $risco['Risco']['nome_agente'];
			// }
			
	    	$this->set(compact('cargo','setor', 'risco', 'codigo_cliente'));
		}

		if($this->element_name == 'buscar_fontes_geradoras_riscos'){
			$this->loadmodel('GrupoRisco');
			
			$codigo_fonte_geradora = $this->passedArgs['codigo_fonte_geradora'];
	        $grupo_risco = $this->GrupoRisco->retorna_grupo();
        	
        	$this->set(compact('grupo_risco','codigo_fonte_geradora'));

		}

		if($this->element_name == 'buscar_epi'){
			
	        $codigo_risco = $this->passedArgs['codigo_risco'];
        	
        	$this->set(compact('codigo_risco'));

		}

		if($this->element_name == 'buscar_epc'){
			
	        $codigo_risco = $this->passedArgs['codigo_risco'];
        	
        	$this->set(compact('codigo_risco'));
		}

		if($this->element_name == 'buscar_fonte_geradora'){
			
	        $codigo_risco = $this->passedArgs['codigo_risco'];
        	
        	$this->set(compact('codigo_risco'));
		}
		

				
		if($this->element_name == 'buscar_epi_riscos'){
			$this->loadmodel('GrupoRisco');
				
			$codigo_epi = $this->passedArgs['codigo_epi'];
			$grupo_risco = $this->GrupoRisco->retorna_grupo();
			 
			$this->set(compact('grupo_risco','codigo_epi'));
		}
		
		if($this->element_name == 'buscar_epc_riscos'){
			$this->loadmodel('GrupoRisco');
				
			$codigo_epc = $this->passedArgs['codigo_epc'];
			$grupo_risco = $this->GrupoRisco->retorna_grupo();
			 
			$this->set(compact('grupo_risco','codigo_epc'));
		}

		if($this->element_name == 'buscar_riscos_grupo_exposicao'){
			$this->loadmodel('GrupoRisco');

			$grupo_risco = $this->GrupoRisco->retorna_grupo();
        	$this->set(compact('grupo_risco'));
		}
			
		if($this->element_name == 'grupos_homogeneos'){
			$this->loadmodel('GrupoEconomicoCliente');

			$codigo_cliente = $this->passedArgs['codigo_cliente'];
			$referencia = $this->passedArgs['referencia'];
			
			$dados = $this->GrupoEconomicoCliente->retorna_dados_cliente($codigo_cliente);
			
			$this->data = array_merge($this->data, $dados);
			
        	$this->set(compact('codigo_cliente', 'referencia'));
		}
		
		if($this->element_name == 'fichas_clinicas') {
			
			if(!is_null($this->BAuth->user('codigo_cliente'))) {
				$this->set('codigo_cliente', $this->BAuth->user('codigo_cliente'));
			} else {
				$this->set('codigo_cliente', isset($this->data['FichaClinica']['codigo_cliente']) ? $this->data['FichaClinica']['codigo_cliente'] : null);
			}
		}		
		
		if(($this->element_name == 'funcionarios_cliente') || ($this->element_name == 'atestados_funcionarios_cliente')) {
			
			$this->loadmodel('GrupoEconomicoCliente');
			
			$codigo_funcionario = isset($this->data['ClienteFuncionario']['codigo_funcionario']) ? $this->data['ClienteFuncionario']['codigo_funcionario'] : '';
			$codigo_setor = isset($this->data['ClienteFuncionario']['codigo_setor']) ? $this->data['ClienteFuncionario']['codigo_setor'] : '';
			$codigo_cargo = isset($this->data['ClienteFuncionario']['codigo_cargo']) ? $this->data['ClienteFuncionario']['codigo_cargo'] : '';
			$codigo_cliente = isset($this->data['ClienteFuncionario']['codigo_cliente']) ? $this->data['ClienteFuncionario']['codigo_cliente'] : '';
			$codigo_unidade = isset($this->data['ClienteFuncionario']['codigo_unidade']) ? $this->data['ClienteFuncionario']['codigo_unidade'] : '';
			$codigo_grupo_economico = isset($this->data['GrupoEconomico']['codigo']) ? $this->data['GrupoEconomico']['codigo'] : null;
			$ativo = isset($this->data['ClienteFuncionario']['ativo']) ? $this->data['ClienteFuncionario']['ativo'] : '1';
			
			$lista_unidades = isset($this->data['GrupoEconomico']['codigo']) ? $this->GrupoEconomicoCliente->retorna_lista_de_unidades_de_um_grupo_economico($this->data['GrupoEconomico']['codigo']) : array();
			$lista_setores = isset($this->data['GrupoEconomico']['codigo']) ? $this->GrupoEconomicoCliente->listaSetores($this->data['GrupoEconomico']['codigo']) : array();
			$lista_cargos = isset($this->data['GrupoEconomico']['codigo']) ? $this->GrupoEconomicoCliente->listaCargos($this->data['GrupoEconomico']['codigo']) : array();
			$lista_funcionarios = isset($this->data['GrupoEconomico']['codigo']) ? $this->GrupoEconomicoCliente->listaFuncionarios($this->data['GrupoEconomico']['codigo']) : array();
			$lista_status = array(
				'1' => 'Ativos', 
				'0' => 'Inativos'
			);
			
			// $this->data['ClienteFuncionario'] = $this->Filtros->controla_sessao($this->data, 'ClienteFuncionario');
			$this->set(compact('lista_unidades', 'lista_cargos', 'lista_setores', 'lista_funcionarios', 'lista_status', 'codigo_cliente', 'codigo_unidade', 'codigo_setor', 'codigo_cargo', 'codigo_funcionario', 'codigo_grupo_economico', 'ativo'));
			
		}		

		if($this->element_name == 'buscar_medico'){
			$this->loadmodel('ConselhoProfissional');
			$this->loadmodel('EnderecoEstado');
			
			$codigo_fornecedor = $this->passedArgs['codigo_fornecedor'];

			$conselho_profissional = $this->ConselhoProfissional->find('list', array('fields' => array('codigo', 'descricao'),'order' => 'codigo'));
        	$estado = $this->EnderecoEstado->find('list', array('conditions' => array('codigo_endereco_pais' => 1),'fields' => array('abreviacao', 'descricao'),'order' => 'descricao'));
        	
        	$this->set(compact('codigo_fornecedor', 'conselho_profissional', 'estado'));
		}
		
		if($this->element_name == 'buscar_cid'){
			$codigo_atestado = $this->passedArgs['codigo_atestado'];
			$this->set(compact('codigo_atestado'));
		}		
		
		if($this->element_name == 'itens_pedidos_exames_baixa'){
			$this->loadmodel('StatusPedidoExame');
			$lista_status_pedidos_exames = array('' => 'TODOS OS STATUS') + $this->StatusPedidoExame->find('list', array('order' => array('StatusPedidoExame.codigo ASC'), 'fields' => array('StatusPedidoExame.codigo', 'StatusPedidoExame.descricao')));
			$this->set(compact('lista_status_pedidos_exames'));
		}		

		if($this->element_name == 'buscar_medico_readonly'){
			$this->loadmodel('ConselhoProfissional');
			$this->loadmodel('EnderecoEstado');	

			if (isset($this->passedArgs['input_id'])){
            $this->set('input_id', str_replace('-search', '', $this->passedArgs['input_id']));
            }
        
	        if (isset($this->passedArgs['input_crm_display']))
	            $this->set('input_crm_display', str_replace('-search', '', $this->passedArgs['input_crm_display']));
	        
			if (isset($this->passedArgs['input_uf_display']))
				$this->set('input_uf_display', str_replace('-search', '', $this->passedArgs['input_uf_display']));
	            
			if (isset($this->passedArgs['input_nome_display']))
				$this->set('input_nome_display', str_replace('-search', '', $this->passedArgs['input_nome_display']));
            	
			$conselho_profissional = $this->ConselhoProfissional->find('list', array('fields' => array('codigo', 'descricao'),'order' => 'codigo'));
        	$estado = $this->EnderecoEstado->find('list', array('conditions' => array('codigo_endereco_pais' => 1),'fields' => array('abreviacao', 'descricao'),'order' => 'descricao'));
        	
        	$this->set(compact('conselho_profissional', 'estado'));
		}

		if($this->element_name == 'buscar_cliente_fornecedor'){
			$codigo_cliente = $this->passedArgs['codigo_cliente'];
			$this->set(compact('codigo_cliente'));
		}	

		if ($this->element_name == 'laudo_pcd') {
			$this->loadmodel('GrupoEconomicoCliente');
			$this->loadmodel('Cliente');
			$this->loadmodel('Setor');
			$this->loadmodel('Cargo');

			$codigo_cliente = $this->passedArgs['codigo_cliente'];
		

			$matriz = $this->GrupoEconomicoCliente->retorna_dados_cliente($codigo_cliente);
			if(!empty($this->data)){
				$this->data = array_merge($this->data, $matriz);
			}
			else{
				$this->data = $matriz;
			}

			$this->data['Cliente']['codigo'] = $this->data['Unidade']['codigo'];

			$cargos = $this->Cargo->lista_por_cliente($codigo_cliente);
        	$setores = $this->Setor->lista_por_cliente($codigo_cliente);
			$this->set(compact('cargos', 'setores'));
		}
		

		if($this->element_name == 'clientes_funcionarios_exames') {
			App::import('Controller', 'Exames');
			ExamesController::carrega_combos_clientes();
		}
		if($this->element_name == 'clientes_responsaveis_registros_ambientais') {
			$this->loadmodel('ConselhoProfissional');	
			$conselho_profissional = $this->ConselhoProfissional->find('list', array('fields' => array('codigo', 'descricao'),'order' => 'codigo'));
			$this->set(compact('conselho_profissional'));
		}
		if($this->element_name == 'audiometrias') {
			App::import('Controller', 'Audiometrias');
			$Audiometrias = new AudiometriasController;
			$this->set('tipos_exames', $Audiometrias->tipos_exames);
		}
		if($this->element_name == 'fornecedores_buscar_codigo'){
			$searcher = $this->passedArgs['searcher'];
			$display  = $this->passedArgs['display'];

			$this->set(compact('searcher', 'display'));
		}
		

		$authUsuario =& $this->authUsuario;
		$this->set(compact('authUsuario', 'action'));
	}

	private function carregaCombosPgr(){
		$this->loadModel('TPgpgPg');
		$fields = Array("pgpg_codigo");
		$order = Array("pgpg_codigo");
		$conditions = Array("pgpg_estatus" =>"A");
		$pgrs = $this->TPgpgPg->find('list', compact("fields","conditions", "order"));
		$this->set(compact("pgrs"));
	}	
	
	private function carregaCombosPropostaLimites() {
        $this->loadModel('ListaDePrecoProduto');
		$this->loadModel('Produto');
		$arraySimNao = array('S'=>'Sim','N' => 'Não');
		$produtos = $this->Produto->listar('list',Array('codigo_naveg <>'=>''));

        if (!empty($this->data['PropostaLimiteDesconto']['codigo_produto'])) {
            $listaDePreco = $this->ListaDePrecoProduto->listarPorCodigoProduto($this->data['PropostaLimiteDesconto']['codigo_produto'],null);
            $servicos = Array();
            foreach ($listaDePreco['ListaDePrecoProdutoServico'] as  $dados) {
                $servicos[$dados['Servico']['codigo']] = $dados['Servico']['descricao'];
            }
        } else {
            $servicos = Array();
        }

        $this->set(compact('tipos_campo','arraySimNao','produtos','servicos'));
	}

	private function carregaCombosPerguntaProposta() {
		$this->loadModel('Produto');

		$arraySimNao = array('S'=>'Sim','N' => 'Não');

		$isPost = ($this->RequestHandler->isAjax() || $this->RequestHandler->isPost());

		$produtos = $this->Produto->listar('list',Array('codigo_naveg <>'=>''));		

		$this->set(compact('arraySimNao','produtos','isPost'));
	}

	private function carregaCombosProposta($apenas_pendentes = false, $tipo = 'D') {
	    App::Import('Model','Documento');

        $this->loadModel('Gestor');
        $this->loadModel('StatusProposta');

        $tipo_cliente = Array(Documento::PESSOA_FISICA=>'Pessoa Física',Documento::PESSOA_JURIDICA=>'Pessoa Jurídica');
        $arraySimNao = array('S'=>'Sim','N' => 'Não');

        $gestores = $this->Gestor->listarNomesGestoresAtivos();

        if (!$apenas_pendentes) {
        	$status_proposta = $this->StatusProposta->listar_ativos();
        } else {
        	if ($tipo=='G') {
        		$status_proposta = Array(StatusProposta::EM_APROVACAO_GERENCIA=>'Em Aprovação');
        		$status_tela = StatusProposta::EM_APROVACAO_GERENCIA;
        	} else {
        		$status_proposta = Array(StatusProposta::EM_APROVACAO_DIRETORIA=>'Em Aprovação');
        		$status_tela = StatusProposta::EM_APROVACAO_DIRETORIA;
        	}
        }

        $this->set(compact('tipo_cliente','arraySimNao','gestores','status_proposta','status_tela'));
   	}

	private function carregarWsConfiguracao(){
        $isPost = ($this->RequestHandler->isAjax() || $this->RequestHandler->isPost());
        $this->set(compact('isPost'));
	}

	private function carregarCombosViagensFaturamento(){
		$anos = Comum::listAnos();
        $meses = Comum::listMeses();
        $isPost = ($this->RequestHandler->isAjax() || $this->RequestHandler->isPost());
        $this->set(compact('meses','anos','isPost'));
	}

	private function carregaCombosTransacoesRecebimento() {
		$this->loadModel('EnderecoRegiao');
        $this->loadModel('Seguradora');
        $this->loadModel('Tranrec');
		$seguradoras = $this->Seguradora->listarSeguradorasAtivas();
        $filiais = $this->EnderecoRegiao->listarRegioes();
        $tranrec_status = $this->Tranrec->listStatus();
        $agrupamento = $this->Tranrec->listAgrupamentos();
        $this->set(compact('seguradoras', 'filiais', 'tranrec_status', 'agrupamento'));
	}

	private function carregarCombosEstatisticasViagensPorAgrupamento(){
		$this->loadModel('TEviaEstaViagem');
		$this->loadModel('TTecnTecnologia');
        $this->loadModel('Seguradora');
        $this->loadModel('TUsuaUsuario');
        $this->set('tipos', $this->TEviaEstaViagem->tipos());
        $this->set('tecnologias', $this->TTecnTecnologia->listaEmUso());
        $this->set('seguradoras', $this->Seguradora->find('list', array('conditions' => array('LTRIM(nome) <> "DESATIVADO"','LTRIM(nome) <> "DESATIVADA"'), 'order' => 'LTRIM(nome)')));
        $this->set('operadores', $this->TUsuaUsuario->listar_logins());
        $this->set('agrupamento', $this->TEviaEstaViagem->tiposAgrupamento());
        $this->set('isPost',$this->RequestHandler->isAjax());
	}

	private function carregarCombosOutbox(){
		$this->loadModel('LogIntegracaoOutbox');
		$sistemas = $this->LogIntegracaoOutbox->listarSistema();
		$this->set(compact('sistemas'));
	}

	private function carregarCombosAnaliticoSinteticoSinistro(){
        $this->loadModel('Corretora');
        $this->loadModel('Seguradora');
        $this->loadModel('Sinistro');
        $natureza            	 = $this->Sinistro->listNatureza();
        $agrupamento             = $this->Sinistro->tiposAgrupamento();
        $conditions['nome NOT '] = array('DESATIVADO','DESATIVADA');
        $corretoras  			 = $this->Corretora->find('list', array('order' => 'nome', 'conditions'=>$conditions ));
        $conditions['nome NOT '] = array('DESATIVADO','DESATIVADA');
        $seguradoras 			 = $this->Seguradora->find('list', array('order' => array('nome') , 'conditions'=>$conditions ) ) ;
        $this->set(compact('corretoras','seguradoras','agrupamento','natureza'));
	}

	private function carregarCombosVeiculosOcorrencias(){
		$this->loadModel('TSvocStatusVeiculoOco');
		$this->loadModel('TTvocTipoVeiculoOco');
		$status = $this->TSvocStatusVeiculoOco->find('list',array('fields' => 'svoc_descricao','conditions' => array('svoc_codigo ' => array(1,2,3))));
		$tipos = $this->TTvocTipoVeiculoOco->find('list',array('fields' => 'tvoc_descricao'));
		$is_post = $this->RequestHandler->isAjax();
		$this->set(compact('status','tipos','is_post'));
	}

	private function carregarCombosEstatisticasSmAnalitico(){
		$this->loadModel('Corretora');
		$this->loadModel('Seguradora');
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('TipoPerfil');
		$corretoras = $this->Corretora->find('list', array('order' => 'nome'));
		$seguradoras = $this->Seguradora->find('list', array('order' => 'nome'));
		$filiais = $this->EnderecoRegiao->listarRegioes();
		if(!isset($this->data['Recebsm']['data_inicial']))
			$this->data['Recebsm']['data_inicial'] = Date('01/m/Y');
		if(!isset($this->data['Recebsm']['data_final']))
			$this->data['Recebsm']['data_final'] = Date('d/m/Y');

		$this->TipoPerfil->carregaFiltrosPorTipoPerfil($this->data['Recebsm'],$this->BAuth->user());

		$this->Filtros->controla_sessao($this->data, 'Recebsm');
		$this->set(compact('corretoras','seguradoras','filiais'));
	}

	protected function carregarComboEstatisticasSmSintetico(){
		$this->loadModel('Corretora');
		$this->loadModel('Seguradora');
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('Recebsm');
		$this->loadModel('TipoPerfil');
		$corretoras = $this->Corretora->find('list', array('order' => 'nome'));
		$seguradoras = $this->Seguradora->find('list', array('order' => 'nome'));
		$filiais = $filiais = $this->EnderecoRegiao->listarRegioes();
		$agrupamento = $this->Recebsm->tiposAgrupamento();
		if(!isset($this->data['Recebsm']['data_inicial']))
			$this->data['Recebsm']['data_inicial'] = Date('01/m/Y');
		if(!isset($this->data['Recebsm']['data_final']))
			$this->data['Recebsm']['data_final'] = Date('d/m/Y');

		$this->TipoPerfil->carregaFiltrosPorTipoPerfil($this->data['Recebsm'],$this->BAuth->user());

		$this->Filtros->controla_sessao($this->data, 'Recebsm');
		$this->set(compact('corretoras', 'seguradoras','filiais','agrupamento'));
	}

	protected function carregaComboComissaoFilial(){
		$this->loadModel('EnderecoRegiao');
		$lista_filiais = $this->EnderecoRegiao->find('list');

		try{
			if(!$this->data['EnderecoRegiao']['data_inicial'])
				$this->EnderecoRegiao->invalidate('data_inicial','Data não informada');

			if(!$this->data['EnderecoRegiao']['hora_inicial'])
				$this->EnderecoRegiao->invalidate('hora_inicial','Hora não informada');

			if(!$this->data['EnderecoRegiao']['data_final'])
				$this->EnderecoRegiao->invalidate('data_final','Data não informada');

			if(!$this->data['EnderecoRegiao']['hora_final'])
				$this->EnderecoRegiao->invalidate('hora_final','Hora não informada');

			if($this->EnderecoRegiao->invalidFields())
				throw new Exception();

			$time_inic 	= strtotime(str_replace('/','-',"{$this->data['EnderecoRegiao']['data_inicial']} {$this->data['EnderecoRegiao']['hora_inicial']}"));
			$time_fim  	= strtotime(str_replace('/','-',"{$this->data['EnderecoRegiao']['data_final']} {$this->data['EnderecoRegiao']['hora_final']}"));

			$diff 		= Comum::diffDate($time_inic,$time_fim);

			if($diff['mes'] > 0){
				$this->EnderecoRegiao->invalidate('data_inicial','Intevalo superior a um mes');
				$this->EnderecoRegiao->invalidate('hora_inicial','');
				$this->EnderecoRegiao->invalidate('data_final','');
				$this->EnderecoRegiao->invalidate('hora_final','');
			}

		} catch (Exception $ex) {

		}

		$this->set(compact('lista_filiais'));
	}

	protected function carregaComboConfiguracaoComissao(){
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('NProduto');

		$filiais 	=& $this->EnderecoRegiao->find('list');
		$produtos 	=& $this->NProduto->listVinculadoPortal('list');

		$this->set(compact('filiais','produtos'));
	}

	protected function carregaComboConfiguracaoComissaoCorretora(){
		$this->loadModel('Corretora');
		$this->loadModel('Produto');
		$this->loadModel('ProdutoServico');

		$corretoras = $this->Corretora->listarCorretorasAtivas();
		$produtos = $this->Produto->listar('list',array('codigo_naveg IS NOT NULL'),'descricao ASC');
		$servicos = array();
		if(isset($this->data['ConfiguracaoComissaoCorre']['codigo_produto'])){
			$servicos = $this->ProdutoServico->servicosPorProduto($this->data['ConfiguracaoComissaoCorre']['codigo_produto']);
		}
		$this->set(compact('corretoras','produtos','servicos'));
	}

	protected function carregaComboRota(){
		//$this->data['TRotaRota']['codigo_cliente'] = $this->passedArgs['codigo'];
        if( !empty($this->passedArgs['codigo']))
            $this->data['TRotaRota']['codigo_cliente'] = $this->passedArgs['codigo']; 	

        $clientes = Array();
        if ((!empty($this->passedArgs['codigo_embarcador'])) || (!empty($this->passedArgs['codigo_transportador']))) {
            if (!empty($this->passedArgs['codigo_embarcador'])) {
                $clientes[] = $this->passedArgs['codigo_embarcador'];
            }
            if (!empty($this->passedArgs['codigo_transportador'])) {
                $clientes[] = $this->passedArgs['codigo_transportador'];
            }
            $this->data['TRotaRota']['codigo_cliente'] = $clientes;
            $filtro_rota = 'codigo_embarcador:'.$this->passedArgs['codigo_embarcador'].'/'.'codigo_transportador:'.$this->passedArgs['codigo_transportador'];
        } else {
            $filtro_rota = 'codigo:'.$this->passedArgs['codigo'];
        }		
        $this->data['TRotaRota'] = $this->Filtros->controla_sessao($this->data, 'TRotaRota');
        $this->set(compact('filtro_rota'));
	}

	protected function carregaComboFichaScorecardConsultaFichas(){
		$this->loadModel('ProfissionalTipo');
        $status = ClassRegistry::init('FichaScorecardStatus')->descricoes;
        $tipos_profissionais = $this->ProfissionalTipo->find('list');
        $this->set(compact('tipos_profissionais','status'));
	}

	protected function carregaComboLogAtendimento(){
		$this->loadModel('ProfissionalTipo');
		$tipos_profissional = $this->Fichas->listProfissionalTipoAutorizado();
		$statuses = ClassRegistry::init('FichaScorecardStatus')->descricoes;
		$this->loadModel('TipoOperacao');
		$tipos_operacoes = $this->TipoOperacao->listaTodosTiposOperacao();
		$this->data['LogAtendimento'] = $this->Filtros->controla_sessao( $this->data, 'LogAtendimento' );
		$this->set(compact('tipos_operacoes', $tipos_operacoes));
		$this->set(compact('tipos_profissional', 'statuses'));
	}

	protected function carregarComboDetalhesItensPedidos(){
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('Corretora');
		$this->loadModel('Seguradora');
		$this->loadModel('Gestor');

		$meses = Comum::listMeses();
		$mes_atual = Date('m');
		$anos = Comum::listAnos();
		$ano_atual = Date('Y');
		$regioes = $this->EnderecoRegiao->listarRegioes();
		$corretoras = $this->Corretora->listarCorretorasAtivas();
		$seguradoras = $this->Seguradora->listarSeguradorasAtivas();
		$gestores = $this->Gestor->listarNomesGestoresAtivos();

		$this->set(compact('regioes','corretoras','seguradoras','gestores','meses','anos','mes_atual','ano_atual'));
	}

	protected function carregarCombosComissoesPorCorretora(){
		$this->loadModel('Corretora');
        $this->loadModel('Produto');
        $this->loadModel('ProdutoServico');
        $meses = Comum::listMeses();
        $mes_atual = Date('m');
        $anos = Comum::listAnos();
        $ano_atual = Date('Y');
        $corretoras = $this->Corretora->listarCorretorasAtivas();
        $produtos = $this->Produto->listar('list',array('codigo_naveg IS NOT NULL'),'descricao ASC');
        $servicos = array();
        if(isset($this->data['Tranrec']['codigo_produto'])){
            $servicos = $this->ProdutoServico->servicosPorProduto($this->data['Tranrec']['codigo_produto']);
        }
        $is_post = $this->RequestHandler->isAjax();
        $this->set(compact('corretoras','meses','anos','ano_atual','mes_atual','produtos','servicos','is_post'));
	}

	protected function carregarComboRelatorioVinculo(){
		$this->loadModel('ProfissionalTipo');
		$tipos_profissionais = $this->Fichas->listProfissionalTipoAutorizado();
		if( !empty($this->authUsuario['Usuario']['codigo_cliente']) ) {
			$this->data['Cliente']['razao_social'] = $this->authUsuario['Usuario']['nome'];
        }        		
		if ( empty($this->data['FichaScorecard']['data_inicial']) || empty($this->data['FichaScorecard']['data_final']) ){
	        $this->data['FichaScorecard']['data_inicial'] = date('d/m/Y');
	        $this->data['FichaScorecard']['data_final']   = date('d/m/Y');			
		}
		$this->set(compact('tipos_profissionais'));
	}

	protected function carregarComboVeiculosOcorrencias(){
		$this->loadModel('VeiculoOcorrencia');
		if(!$this->data['VeiculoOcorrencia']['placa'])
			$this->VeiculoOcorrencia->invalidate('placa','Informe uma placa');
	}

	protected function carregarComboOperadores(){
		$this->loadModel('TAatuAreaAtuacao');
		$aatu_lista = $this->TAatuAreaAtuacao->listar();
		$this->loadModel('TErasEstacaoRastreamento');
		$estacao = $this->TErasEstacaoRastreamento->listaParaCombo();
		$this->set(compact('aatu_lista', 'estacao'));
	}

	protected function carregaComboCriterioDistribuicao(){
		$this->loadModel('TTecnTecnologia');
		$this->loadModel('TCdfvCriterioFaixaValor');
		$this->loadModel('TTtraTipoTransporte');
		$this->loadModel('TAatuAreaAtuacao');
		$this->loadModel('TErasEstacaoRastreamento');		
		$estacao = $this->TErasEstacaoRastreamento->listaParaCombo();				
		$tecnologias		= $this->TTecnTecnologia->listaEmUso();
		$faixas				 = $this->TCdfvCriterioFaixaValor->listar();
		$ttransportes	 = $this->TTtraTipoTransporte->listarParaFormulario();
		$aatuacao			 = $this->TAatuAreaAtuacao->find('list',array('order' => array('aatu_descricao')));
		$this->set(compact('tecnologias','faixas','ttransportes','aatuacao', 'estacao'));
	}

	protected function carregarComboRma($rma_sintetico = false) {
		$this->loadModel('TOrmaOcorrenciaRma');
		$this->loadModel('TGrmaGeradorRma');
		$this->loadModel('EmbarcadorTransportador');
		$this->loadModel('TTecnTecnologia');
		$this->loadModel('MRmaOcorrencia');
		$this->loadModel('TTrmaTipoRma');
		$this->loadModel('StatusViagem');
		$this->loadModel('Cliente');
		$this->loadModel('ClienteSubTipo');
        $this->TPjurPessoaJuridica = ClassRegistry::init('TPjurPessoaJuridica');
        $this->TRefeReferencia = ClassRegistry::init('TRefeReferencia');

		if (!empty($this->authUsuario['Usuario']['codigo_cliente'])) {
			$this->data['TOrmaOcorrenciaRma']['codigo_cliente'] = $this->authUsuario['Usuario']['codigo_cliente'];	
		}
		$dados = $this->EmbarcadorTransportador->dadosPorCliente($this->data['TOrmaOcorrenciaRma']['codigo_cliente']);
		$this->data['TOrmaOcorrenciaRma']['data_inicial'] = (!empty($this->data['TOrmaOcorrenciaRma']['data_inicial']) ? $this->data['TOrmaOcorrenciaRma']['data_inicial'] : date('d/m/Y'));
        $this->data['TOrmaOcorrenciaRma']['data_final'] = (!empty($this->data['TOrmaOcorrenciaRma']['data_final']) ? $this->data['TOrmaOcorrenciaRma']['data_final'] : date('d/m/Y'));
		$codigo_cliente = isset($this->data['TOrmaOcorrenciaRma']['codigo_cliente']) ? $this->data['TOrmaOcorrenciaRma']['codigo_cliente'] : null;
        $dados = $this->EmbarcadorTransportador->dadosPorCliente($codigo_cliente);
        $embarcadores = $dados['embarcadores'];

        $oras_codigo = $this->TPjurPessoaJuridica->buscaClienteCentralizador($codigo_cliente);
        $oras_codigo = $oras_codigo['TPjurPessoaJuridica']['pjur_pess_oras_codigo'];
        if(!empty($oras_codigo)){
            $cds = $this->TRefeReferencia->listaCds($oras_codigo);
        } else {
            $cds = array();
        }
        $alvos = Array('cds' => $cds);

        $cliente_sub_tipo = null;
        if (!empty($this->data['TOrmaOcorrenciaRma']['codigo_cliente'])) {
            $cliente_sub_tipo = $this->Cliente->retornarClienteSubTipo($this->data['TOrmaOcorrenciaRma']['codigo_cliente']);
        }

        if ($cliente_sub_tipo==ClienteSubTipo::SUBTIPO_EMBARCADOR) {
            $this->data['TOrmaOcorrenciaRma']['codigo_embarcador'] = $this->data['TOrmaOcorrenciaRma']['codigo_cliente'];
        } elseif ($cliente_sub_tipo==ClienteSubTipo::SUBTIPO_TRANSPORTADOR) {
            $this->data['TOrmaOcorrenciaRma']['codigo_transportador'] = $this->data['TOrmaOcorrenciaRma']['codigo_cliente'];
        }

		/*        
        if (count($embarcadores) == 1) {
            $this->data['TOrmaOcorrenciaRma']['codigo_embarcador'] = key($embarcadores);
        }
        $transportadores = $dados['transportadores'];
        if (count($transportadores) == 1) {
            $this->data['TOrmaOcorrenciaRma']['codigo_transportador'] = key($transportadores);
        }
        */

		$embarcadores = $dados['embarcadores'];
		$transportadores = $dados['transportadores'];
		$geradores_ocorrencia = $this->TGrmaGeradorRma->find('list');
		$tipos_ocorrencia =  $this->TTrmaTipoRma->find('list', array('conditions' => array('TTrmaTipoRma.trma_flg_ativo' => 'S')));
		$automatico =  $this->TOrmaOcorrenciaRma->tiposAutomatico();
        $tecnologias = $this->TTecnTecnologia->listaEmUso();
		if ($rma_sintetico) {
			$agrupamento = $this->TOrmaOcorrenciaRma->tiposAgrupamento();
		}
		$status_viagens = $this->TOrmaOcorrenciaRma->listStatusViagem();
        $status_viagens_atual = $this->StatusViagem->find(array(StatusViagem::SEM_VIAGEM,StatusViagem::CANCELADO,StatusViagem::AGENDADO));

		$this->set(compact('embarcadores', 'transportadores', 'geradores_ocorrencia', 'tipos_ocorrencia','automatico', 'tecnologias', 'agrupamento', 'status_viagens','status_viagens_atual','alvos'));
	}

	protected function carregarComboRmaEstatistica() {
		$this->loadModel('TOrmaOcorrenciaRma');
		$this->loadModel('TGrmaGeradorRma');
		$this->loadModel('EmbarcadorTransportador');
		$this->loadModel('TTecnTecnologia');
		$this->loadModel('MRmaOcorrencia');
		$this->loadModel('TTrmaTipoRma');
		$this->loadModel('StatusViagem');
        $this->loadModel('Cliente');
        $this->loadModel('ClienteSubTipo');

		if (!empty($this->authUsuario['Usuario']['codigo_cliente'])) {
			$this->data['TOrmaOcorrenciaRma']['codigo_cliente'] = $this->authUsuario['Usuario']['codigo_cliente'];	
		}
		$dados = $this->EmbarcadorTransportador->dadosPorCliente($this->data['TOrmaOcorrenciaRma']['codigo_cliente']);
		$this->data['TOrmaOcorrenciaRma']['data_inicial'] = (!empty($this->data['TOrmaOcorrenciaRma']['data_inicial']) ? $this->data['TOrmaOcorrenciaRma']['data_inicial'] : date('d/m/Y'));
        $this->data['TOrmaOcorrenciaRma']['data_final'] = (!empty($this->data['TOrmaOcorrenciaRma']['data_final']) ? $this->data['TOrmaOcorrenciaRma']['data_final'] : date('d/m/Y'));
		$codigo_cliente = isset($this->data['TOrmaOcorrenciaRma']['codigo_cliente']) ? $this->data['TOrmaOcorrenciaRma']['codigo_cliente'] : null;
        $dados = $this->EmbarcadorTransportador->dadosPorCliente($codigo_cliente);
        $embarcadores = $dados['embarcadores'];
        //if (count($embarcadores) == 1) {
        //    $this->data['TOrmaOcorrenciaRma']['codigo_embarcador'] = key($embarcadores);
        //}

        $cliente_sub_tipo = null;
        if (!empty($this->data['TOrmaOcorrenciaRma']['codigo_cliente'])) {
            $cliente_sub_tipo = $this->Cliente->retornarClienteSubTipo($this->data['TOrmaOcorrenciaRma']['codigo_cliente']);
        }

		// Comentado pois, para o cliente EXPRESSO NEPOMUCENO, o qual é um TRANSPORTADOR, existem SMs nos quais ele é o embarcador para um outro transportador
        /*
        if ($cliente_sub_tipo==ClienteSubTipo::SUBTIPO_EMBARCADOR) {
            $this->data['TOrmaOcorrenciaRma']['codigo_embarcador'] = $this->data['TOrmaOcorrenciaRma']['codigo_cliente'];
        } elseif ($cliente_sub_tipo==ClienteSubTipo::SUBTIPO_TRANSPORTADOR) {
            $this->data['TOrmaOcorrenciaRma']['codigo_transportador'] = $this->data['TOrmaOcorrenciaRma']['codigo_cliente'];
        }

        $transportadores = $dados['transportadores'];
        if (empty($this->data['TOrmaOcorrenciaRma']['codigo_transportador'])) {
	        if (count($transportadores) == 1) {
	            $this->data['TOrmaOcorrenciaRma']['codigo_transportador'] = key($transportadores);
	        }
	    }
        */
	    
		$geradores_ocorrencia = $this->TGrmaGeradorRma->find('list');
		$tipos_ocorrencia =  $this->TTrmaTipoRma->find('list', array('conditions' => array('TTrmaTipoRma.trma_flg_ativo' => 'S')));
		$automatico =  $this->TOrmaOcorrenciaRma->tiposAutomatico();
        $tecnologias = $this->TTecnTecnologia->listaEmUso();

		$status_viagens = $this->TOrmaOcorrenciaRma->listStatusViagem();
        $status_viagens_atual = $this->StatusViagem->find(array(StatusViagem::SEM_VIAGEM,StatusViagem::CANCELADO,StatusViagem::AGENDADO));

		$this->set(compact('embarcadores', 'transportadores', 'geradores_ocorrencia', 'tipos_ocorrencia','automatico', 'tecnologias', 'agrupamento', 'status_viagens','status_viagens_atual'));
	}

	protected function carregarComboFichasScorecardLog() {
		$tipos_profissional = $this->Fichas->listProfissionalTipoAutorizado();
		$statuses = ClassRegistry::init('FichaScorecardStatus')->descricoes;
		$filtros = $this->Filtros->controla_sessao($this->data, 'FichaScorecardLog');
        if( $filtros['data_inclusao_inicio'] ){
            $this->data['FichaScorecardLog'] = $filtros;
        } else{
            $this->data['FichaScorecardLog']['data_inclusao_inicio'] = date('d/m/Y');
            $this->data['FichaScorecardLog']['data_inclusao_fim']    = date('d/m/Y');   
        }			
		$this->set(compact('tipos_profissional', 'statuses'));
	}
	
	protected function carregarCombosAtendimentos() {
        $this->loadmodel('MotivoAtendimento');
        $this->loadmodel('Equipamento');
        $this->loadmodel('AtendimentoSac');
        
		if(empty($this->data['AtendimentoSac']['hora_inicial'])) {
            $this->data['AtendimentoSac']['hora_inicial'] = '00:00';
        }
        if(empty($this->data['AtendimentoSac']['hora_final'])) {
            $this->data['AtendimentoSac']['hora_final'] = '23:59';
        }
        if(empty($this->data['AtendimentoSac']['data_inicial'])) {
            $this->data['AtendimentoSac']['data_inicial'] = date('d-m-Y');
        }
        if(empty($this->data['AtendimentoSac']['data_final'])) {
            $this->data['AtendimentoSac']['data_final'] = date('d-m-Y');
        }


       	$motivos = $this->MotivoAtendimento->find('list');
        $agrupamento = $this->AtendimentoSac->listarAgrupamentos();
       	$tecnologia = $this->Equipamento->find('list');
        $this->set(compact('motivos','tecnologia','agrupamento'));

	}

	protected function carregaComboObjetivoComercial(){
		$this->loadModel('Gestor');
        $this->loadModel('EnderecoRegiao');
        $this->loadModel('Produto');
        $this->loadModel('ObjetivoComercial');
        $this->loadModel('Diretoria');
        $meses = Comum::listMeses();
        $anos = Comum::listAnos(2014);
        array_push($anos, date('Y', strtotime('+1 year')));
        $gestores = $this->Gestor->listarNomesGestoresAtivos();
        $filiais = $this->EnderecoRegiao->listarRegioes();       	
       	$produtos = $this->Produto->listarProdutosNavegarqCodigoBuonny();
       	$listaAgrupamento = $this->ObjetivoComercial->listarAgrupamentos();
        $listaTipoVisualizacao = $this->ObjetivoComercial->listarTipoVisualizacao();
        $diretoria = $this->Diretoria->find('list');
        $this->set(compact('diretoria','meses', 'anos','gestores','filiais','produtos','listaAgrupamento','listaTipoVisualizacao'));
    }

	protected function carregaCombosClientesProdutosContratos() {
		$this->loadModel('Produto');
		$this->loadModel('Igpm');
		$igpm_acumulado = $this->Igpm->ultimoIGPM();

		if (empty($this->data['ClienteProdutoContrato']['igpm']))
			$this->data['ClienteProdutoContrato']['igpm'] = $igpm_acumulado;
		if (empty($this->data['ClienteProdutoContrato']['data_inicial']))
			$this->data['ClienteProdutoContrato']['data_inicial'] = date('d/m/Y');
		if (empty($this->data['ClienteProdutoContrato']['data_inicial']))
			$this->data['ClienteProdutoContrato']['data_final'] = date('d/m/Y');

		$produtos = $this->Produto->listar();
		$produtos[0] = 'TODOS';
		ksort($produtos);
		$this->set(compact('produtos'));
	}

	protected function carregaCombosFatPorCliente() {
		$meses = Comum::listMeses();
		$this->set(compact('meses'));
	}

	protected function carregaPontosStatusCriterios() {
		$this->loadModel('Seguradora');
		$this->loadModel('Criterio');
		$this->loadModel('StatusCriterios');
		$criterios = $this->Criterio->find('list');
		$seguradora = $this->Seguradora->find('list');
		$this->set(compact('criterios','seguradora'));
	}

	protected function carregaTarefaDesenvolvimento(){
		$this->loadModel('TarefaDesenvolvimento');
		$this->loadModel('TarefaDesenvolvimentoTipo');
		$this->loadModel('Usuario');
		$tipo = $this->TarefaDesenvolvimentoTipo->listarTarefasDesenvolvimentoTipo();
		$this->data['TarefaDesenvolvimento'] = $this->Filtros->controla_sessao($this->data, 'TarefaDesenvolvimento');
		$lista_usuarios = $this->TarefaDesenvolvimento->find('list',array('fields'=>array('codigo_usuario_inclusao')));
		$conditions = array('codigo'=>$lista_usuarios);
		//$this->data['TarefaDesenvolvimento']['codigo_usuario_inclusao']);
		$fields =array('apelido');
		$nome_usuario = $this->Usuario->find('list',compact('fields','conditions'));
		$this->set(compact('nome_usuario','tipo'));

	}


	protected function carregaComboListarFichasPendentes() {
		$this->loadModel('TipoProfissional');
		$this->loadModel('Seguradora');
		$this->loadModel('Produto');
		$tipos_profissional = $this->TipoProfissional->find('list', array('conditions'=>array('descricao'=>array('CARRETEIRO','AGREGADO'))));
		$lista_seguradora = $this->Seguradora->find('list');
		$produto_descricao =$this->Produto->find('list',array('conditions'=>array('codigo'=>array(1,2))));
		// $data['FichaScorecard']['codigo_tipo_profissional'] = key($tipos_profissional);
		$this->set(compact('tipos_profissional','lista_seguradora','produto_descricao'));
	}

	protected function carregaComboListarFichasScorecard() {
		$this->loadModel('ProfissionalTipo');
		$this->loadModel('Seguradora');
		$this->loadModel('FichaScorecardStatus');
		$tipos_profissional = $this->Fichas->listProfissionalTipoAutorizado();
		$lista_seguradora   = $this->Seguradora->find('list');
		$action  			= $this->Session->read('fichas_a_pesquisar');
		$action_todas  		= $this->Session->read('todas_fichas');		
		if( !empty($action) ){
			$status_ficha   = array(
				FichaScorecardStatus::A_PESQUISAR => FichaScorecardStatus::descricao(FichaScorecardStatus::A_PESQUISAR),
				FichaScorecardStatus::EM_PESQUISA => FichaScorecardStatus::descricao(FichaScorecardStatus::EM_PESQUISA),
				FichaScorecardStatus::PENDENTE    => FichaScorecardStatus::descricao(FichaScorecardStatus::PENDENTE)
			);
		} else{
			if (!empty($action_todas)) {
				$status_ficha   = array(
					FichaScorecardStatus::RENOVADA    => FichaScorecardStatus::descricao( FichaScorecardStatus::RENOVADA ),
					FichaScorecardStatus::A_APROVAR   => FichaScorecardStatus::descricao( FichaScorecardStatus::A_APROVAR ),
					FichaScorecardStatus::EM_APROVACAO=> FichaScorecardStatus::descricao( FichaScorecardStatus::EM_APROVACAO )
				);

			} else {
				$status_ficha   = array(
					FichaScorecardStatus::A_APROVAR   => FichaScorecardStatus::descricao( FichaScorecardStatus::A_APROVAR ),
					FichaScorecardStatus::EM_APROVACAO=> FichaScorecardStatus::descricao( FichaScorecardStatus::EM_APROVACAO )
				);
			}
		}
		if( empty($this->data['FichaScorecard']['codigo_tipo_profissional']) ){
			$data['FichaScorecard']['codigo_tipo_profissional'] = key($tipos_profissional);
			$this->data = $this->Filtros->controla_sessao($data, 'FichaScorecard');
		}
		if( $this->element_name == 'fichas_scorecard_excluir_vinculo' ){
			$this->data['FichaScorecard'] = $this->Filtros->controla_sessao($data, 'FichaScorecard');	
		}

		$this->set(compact('tipos_profissional','lista_seguradora', 'status_ficha'));
	}

	protected function carregaCombosReferencia() {
		$this->loadModel('Cliente');
		$this->loadModel('TEstaEstado');
		$this->loadModel('TBandBandeira');
		$this->loadModel('TRegiRegiao');
		$this->loadModel('TPjurPessoaJuridica');
		$this->loadModel('TCrefClasseReferencia');
		$this->TPaisPais= ClassRegistry::init('TPaisPais');
		$classes 	= $this->TCrefClasseReferencia->combo();
		$bandeiras 	= array();
		$regioes	= array();
		$estados 	= $this->TEstaEstado->comboPorPais( TPaisPais::BRASIL );
		$filtros = $this->Filtros->controla_sessao($this->data, 'Referencia');
		if(!$filtros && isset($this->passedArgs['codigo'])){			
			$this->data['Referencia']['codigo_cliente'] = $this->passedArgs['codigo'];		
		}
		if(empty($this->data['Referencia']['codigo_cliente2']) && isset($this->passedArgs['codigo2'])){			
			$this->data['Referencia']['codigo_cliente2'] = $this->passedArgs['codigo2'];			
		}
		$filtros = $this->Filtros->controla_sessao($this->data, 'Referencia');

		$clientes_pjur = array();
		if(isset($this->data['Referencia']['codigo_cliente']) && $this->data['Referencia']['codigo_cliente']){
			$clientes_pjur[] = $this->TPjurPessoaJuridica->buscaClienteCentralizador($this->data['Referencia']['codigo_cliente']);
		}
		if(isset($this->data['Referencia']['codigo_cliente2']) && $this->data['Referencia']['codigo_cliente2']){
			$clientes_pjur[] = $this->TPjurPessoaJuridica->buscaClienteCentralizador($this->data['Referencia']['codigo_cliente2']);		
		}
		
		if(count($clientes_pjur)>0){
			$clientes = array();		
			foreach($clientes_pjur as $cliente_pjur){
				$clientes[] = $cliente_pjur['TPjurPessoaJuridica']['pjur_pess_oras_codigo'];
			}
			$bandeiras = $this->TBandBandeira->lista($clientes);
			$regioes   = $this->TRegiRegiao->lista($clientes);
		}
		$this->set(compact('estados','bandeiras','regioes','classes'));

	}

	protected function carregaCombosVeiculo($exibe_fields_checklist = true) {
		$TMvecModeloVeiculo		= classRegistry::init('TMvecModeloVeiculo');
		$TMveiMarcaVeiculo 		= classRegistry::init('TMveiMarcaVeiculo');
		$TVeicVeiculo 			= classRegistry::init('TVeicVeiculo');
		$TTveiTipoVeiculo		= classRegistry::init('TTveiTipoVeiculo');
		$TTecnTecnologia		= classRegistry::init('TTecnTecnologia');
		$TRefeReferencia		= classRegistry::init('TRefeReferencia');
		$this->loadModel('TPjurPessoaJuridica');
		$this->loadModel('Veiculo');
		$this->loadModel('Cliente');
		$this->loadModel('TRacsRegraAceiteSm');
		App::import('model','TUcveUltimoChecklistVeiculo');
		$filtros 				= $this->data['Veiculo'];

		$checklist 				= isset($this->passedArgs['checklist'])?$this->passedArgs['checklist']:0;

		$veiculos_modelos 		= array();
		if(isset($filtros['mvei_codigo']) && $filtros['mvei_codigo']){
			$veiculos_modelos 	= $TMvecModeloVeiculo->listaPorMarca($filtros['mvei_codigo']);
		}
		if(!empty($this->authUsuario['Usuario']['codigo_cliente'])){
			$this->data['Veiculo']['codigo_cliente'] = $this->authUsuario['Usuario']['codigo_cliente'];
		}
		$filtrado = FALSE;
		if($this->RequestHandler->isPost()) {
			if(empty($this->data['Veiculo']['codigo_cliente'])){
				$filtrado = FALSE;
				$this->Veiculo->invalidate('codigo_cliente','Informe o cliente');						
			}
			if(!$this->Veiculo->validationErrors){
				$filtrado = TRUE;
			}	
		}
		
		$veiculos_fabricantes 	= $TMveiMarcaVeiculo->lista();
		$veiculos_status 		= $TVeicVeiculo->status;
		$veiculos_tipos			= $TTveiTipoVeiculo->lista();
		$veiculos_tipos[99]		= 'TODOS DIFERENTES DE CARRETA';
		$teconlogia				= $TTecnTecnologia->listaFicticios();

		$authUsuario 			=& $this->authUsuario;
		$referencias			= array();
		if(!empty($authUsuario['Usuario']['codigo_cliente']) || !empty($filtros['codigo_cliente'])){
			$codigo_cliente = !empty($authUsuario['Usuario']['codigo_cliente']) ? $authUsuario['Usuario']['codigo_cliente'] : $filtros['codigo_cliente'];
			$oras_codigo = $this->TPjurPessoaJuridica->buscaClienteCentralizador($codigo_cliente);
			$oras_codigo = $oras_codigo['TPjurPessoaJuridica']['pjur_pess_oras_codigo'];
			$referencias				= $TRefeReferencia->listaCds($oras_codigo);
		}

		$checklist_posicao = array();
		$regras_aceite_sm = array();

		if ($exibe_fields_checklist) {
			//$checklist_status = array(TUcveUltimoChecklistVeiculo::STATUS_APROVADO => 'Aprovado', TUcveUltimoChecklistVeiculo::STATUS_REPROVADO => 'Reprovado', TUcveUltimoChecklistVeiculo::STATUS_SEM_CHECKLIST => 'Sem Checklist');
			//$checklist_validade = array(TUcveUltimoChecklistVeiculo::VALIDADE_NO_PRAZO => 'No Prazo', TUcveUltimoChecklistVeiculo::VALIDADE_VENCIDO => 'Vencido');
			$checklist_posicao = array(
				TUcveUltimoChecklistVeiculo::POSICAO_VALIDO => 'Aprovado',
				TUcveUltimoChecklistVeiculo::POSICAO_INVALIDO => 'Reprovado',
				TUcveUltimoChecklistVeiculo::POSICAO_VENCIDO => 'Aprovado, porém Vencido',
				TUcveUltimoChecklistVeiculo::POSICAO_NAO_REALIZADO => 'Não Realizado',
			);
			if (!empty($this->data['Veiculo']['codigo_cliente'])) {
				$cliente = $this->Cliente->carregar($this->data['Veiculo']['codigo_cliente']);		
				if ($cliente) {
					$pjur = $this->TPjurPessoaJuridica->carregarPorCNPJ($cliente['Cliente']['codigo_documento']);
					if ($pjur) {
						$regras_aceite_sm = $this->TRacsRegraAceiteSm->listValidade($pjur['TPjurPessoaJuridica']['pjur_pess_oras_codigo']);
					}
				}
			}
		}
		$this->set(compact('filtrado','checklist_posicao', 'teconlogia','veiculos_tipos', 'veiculos_fabricantes', 'veiculos_status','veiculos_modelos', 'referencias','checklist', 'regras_aceite_sm','exibe_fields_checklist'));
	}

	protected function carregaCombosDuracaoSm() {
		$anos = Comum::listAnos();
		$this->set(compact('anos'));
	}

	protected function carregaComboProdutoDoCliente($apenas_teleconsult = false) {
		$this->loadModel('ClienteProduto');
		$codigo_cliente = $this->data['Ficha']['codigo_cliente'];
		$codigo_cliente_session = $this->Session->read('Auth.Usuario.codigo_cliente');
		if (!empty($codigo_cliente_session)) $codigo_cliente = $codigo_cliente_session;
		$this->set('produtos_cliente', $this->ClienteProduto->listaProdutos($codigo_cliente, $apenas_teleconsult));
	}

	protected function carregaCombosItinerariosSmsPorCliente() {
		$this->loadModel('MSmitinerario');
		$this->carregaCombosClientesTipos();
		$anos = Comum::listAnos();
		$status = $this->MSmitinerario->listarStatus();
		$this->set(compact('clientes_monitora', 'status', 'anos'));
	}

	protected function carregaCombosTransitTime() {
		App::Import('Component',array('Maplink'));
		$this->loadModel('TUposUltimaPosicao');
		$status_posicao = $this->TUposUltimaPosicao->listStatusPosicao();
		$calculo = MaplinkComponent::listTipoCalculo();
		$this->set(compact('status_posicao','calculo'));
		$this->carregaCombosClientesTipos();
	}

	protected function carregaCombosClientesTipos() {
		$this->loadModel('ClientEmpresa');
		$this->loadModel('Cliente');
		$clientes_tipos = array();
		$label_empty = 'Selecione o cliente';
		$codigo_cliente = null;

		$authUsuario = $_SESSION['Auth'];

		if(isset($authUsuario['Usuario']['codigo_cliente']) && !empty($authUsuario['Usuario']['codigo_cliente']))
			$codigo_cliente = $authUsuario['Usuario']['codigo_cliente'];
		if (!empty($this->data[$this->model_name]['codigo_cliente']))
			$codigo_cliente = $this->data[$this->model_name]['codigo_cliente'];

		if (!empty($codigo_cliente)) {
			$cliente = $this->Cliente->carregar($codigo_cliente);
			$tipo_empresa = $this->Cliente->retornarClienteSubTipo($codigo_cliente);
			if ($tipo_empresa == Cliente::SUBTIPO_EMBARCADOR)
				$label_empty = 'Embarcador';
			elseif ($tipo_empresa == Cliente::SUBTIPO_TRANSPORTADOR)
				$label_empty = 'Transportadora';
			$clientes_tipos = $this->ClientEmpresa->porBaseCnpj(substr($cliente['Cliente']['codigo_documento'], 0, 8), $tipo_empresa);
		}
		$this->set(compact('clientes_tipos', 'label_empty'));
	}

	protected function carregaCombosNotasFiscaisPorBanco() {
		$this->set('anos', Comum::listAnos());
		$this->set('meses', Comum::listMeses());
	}

	protected function carregaComboListaAnosMeses() {
		$this->set('anos', Comum::listAnos());
		$this->set('meses', Comum::listMeses());
	}

	protected function carregaComboListaAnosMesesTaxaAdm() {
		$this->set('ano_faturamento', Comum::listAnos());
		$this->set('mes_faturamento', Comum::listMeses());
	}

	protected function carregaComboResultadosPesquisa() {
		$this->loadModel('ParametroScore');
		$this->loadModel('Seguradora');
		$this->loadModel('FichaScorecard');
		$lista_seguradora = $this->Seguradora->find('list');
		if( FichaScorecard::ENVIA_EMAIL_SCORECARD === FALSE ){
			$classificacao_tlc = array( 2 => 'PERFIL ADEQUADO AO RISCO', 7 => 'PERFIL INSUFICIENTE', 8 => 'PERFIL DIVERGENTE');
		} else {
			$classificacao_tlc = $this->ParametroScore->find('list');    
		}
		$this->set(compact('classificacao_tlc', 'lista_seguradora'));
	}

	protected function carregaCombosRankingFaturamento($carregar_agrupamento = false) {
		$this->loadModel('LojaNaveg');
		$this->loadModel('NProduto');
		$this->loadModel('Gestor');
		$this->loadModel('Seguradora');
		$this->loadModel('Corretora');
        $this->loadModel('EnderecoRegiao');
        $this->loadModel('TipoPerfil');
        $this->TipoPerfil->carregaFiltrosPorTipoPerfil($this->data['Notaite'],$this->BAuth->user());
		$this->set('empresas', $this->LojaNaveg->listEmpresas(isset($this->data[$this->model_name]['grupo_empresa']) ? $this->data[$this->model_name]['grupo_empresa'] : 1));
		$this->set('grupos_empresas', $this->LojaNaveg->listGrupos());
		$this->set('produtos', 			$this->NProduto->find(
					'list',
						array(
							'order' => 'descricao',
							'conditions' => array(
								//'ativo'	=> 'S',
								'grupo'	=> array(
		 					'050',
							'100',
							'200',
							'250',
		 					'265',
							'280',
							'290',
							'300',
							'600'
						)
							)
						)
				)
				);
		$this->set('gestores', 		$this->Gestor->listarNomesGestoresAtivos());
		$this->set('corretoras', 	$this->Corretora->find('list', array('order' => 'nome')));
		$this->set('seguradoras', 	$this->Seguradora->find('list', array('order' => 'nome')));
    	$this->set('filiais', $this->EnderecoRegiao->listarRegioes());
		$this->set('anos', Comum::listAnos());
		$this->set('meses', Comum::listMeses());
		if ($carregar_agrupamento) {
			$this->loadModel('Notaite');
			$this->loadModel('GrupoEconomico');
			$this->set('agrupamento',	 $this->Notaite->listarAgrupamentos());
			$this->set('grupos_economicos', $this->GrupoEconomico->find('list'));
		}
	}

	protected function carregaCombosSolicitacoesMonitoramento(){
		$this->loadModel('ClientEmpresa');
		$this->loadModel('Cliente');
		$this->loadModel('Equipamento');
		$this->loadModel('OperacaoMonitora');
		$this->loadModel('Cidade');
		$this->loadModel('Seguradora');

		$clientes_embarcadores	= array();
		$clientes_transportadores = array();

		$clientes	= $this->DbbuonnyMonitora->converteClienteBuonnyEmMonitora($this->data['Recebsm'], ClientEmpresa::SENTIDO_BUONNY_MONITORA);
		$authUsuario = $_SESSION['Auth'];

		if(isset($authUsuario['Usuario']['codigo_cliente']) && !empty($authUsuario['Usuario']['codigo_cliente']))
		{
			if(!empty($this->data['Recebsm']['codigo_embarcador'])){
				$clientes_embarcadores = $this->DbbuonnyMonitora->clientesMonitoraPorBaseCnpjETipoClienteBuonny($this->data['Recebsm']['codigo_embarcador'], Cliente::SUBTIPO_EMBARCADOR);
				$clientes_embarcadores = $clientes_embarcadores['clientes_tipos'];
			}
			else{
				if(isset($clientes['cliente_embarcador']))
				{
					$this->data[$this->model_name]['cliente_embarcador'] = $clientes['cliente_embarcador'];
				}else{
					$clientes_embarcadores = $this->DbbuonnyMonitora->clientesMonitoraPorBaseCnpjETipoClienteBuonny($authUsuario['Usuario']['codigo_cliente'], Cliente::SUBTIPO_EMBARCADOR);
					$clientes_embarcadores = $clientes_embarcadores['clientes_tipos'];
				}
			}

			if(!empty($this->data['Recebsm']['codigo_transportador'])){
				$clientes_transportadores = $this->DbbuonnyMonitora->clientesMonitoraPorBaseCnpjETipoClienteBuonny($this->data['Recebsm']['codigo_transportador'], Cliente::SUBTIPO_TRANSPORTADOR);
				$clientes_transportadores = $clientes_transportadores['clientes_tipos'];
			}else{
				if(isset($clientes['cliente_transportador']))
				{
					$this->data[$this->model_name]['cliente_transportador'] = $clientes['cliente_transportador'];
				}else{
					$clientes_transportadores = $this->DbbuonnyMonitora->clientesMonitoraPorBaseCnpjETipoClienteBuonny($authUsuario['Usuario']['codigo_cliente'], Cliente::SUBTIPO_TRANSPORTADOR);
					$clientes_transportadores = $clientes_transportadores['clientes_tipos'];
				}
			}
		}
		else
		{
			if(!empty($this->data['Recebsm']['codigo_embarcador'])){
				$clientes_embarcadores = $this->DbbuonnyMonitora->clientesMonitoraPorBaseCnpjETipoClienteBuonny($this->data['Recebsm']['codigo_embarcador'], Cliente::SUBTIPO_EMBARCADOR);
				$clientes_embarcadores = $clientes_embarcadores['clientes_tipos'];
			}

			if(!empty($this->data['Recebsm']['codigo_transportador'])){
				$clientes_transportadores = $this->DbbuonnyMonitora->clientesMonitoraPorBaseCnpjETipoClienteBuonny($this->data['Recebsm']['codigo_transportador'], Cliente::SUBTIPO_TRANSPORTADOR);
				$clientes_transportadores = $clientes_transportadores['clientes_tipos'];
			}
		}

		$seguradoras = $this->Seguradora->listarSeguradorasAtivas();
		$tecnologias = $this->Equipamento->find('list', array('order' => 'descricao'));
		$operacoes = $this->OperacaoMonitora->listaOperacoes();

		$this->set(compact('tecnologias', 'operacoes', 'clientes_transportadores', 'clientes_embarcadores','seguradoras'));
	}

	protected function carregaCombosAtendimentosSms() {
		$this->loadModel('Equipamento');
		$this->loadModel('OperacaoMonitora');
		$this->loadModel('PassoAtendimento');
		$this->loadModel('Uperfil');
		$tecnologias = $this->Equipamento->find('list', array('order' => 'descricao'));
		$passos_atendimentos = $this->PassoAtendimento->find('list');
		$operacoes = $this->OperacaoMonitora->listaOperacoes();

		$usuario = $this->BAuth->user();
		$admin = '';
		$buonnysat = array_search('Buonny Sat', $passos_atendimentos);
		$pronta_resposta = array_search('Pronta Resposta', $passos_atendimentos);

		if ($usuario['Usuario']['codigo_uperfil'] == Uperfil::ADMIN || $usuario['Usuario']['codigo_uperfil'] == 20) {
			$this->set(compact('admin'));
		} else {
			if ($this->BAuth->temPermissao($usuario['Usuario']['codigo_uperfil'], 'obj_operador-pronta-resposta')) {
				$this->set(compact('pronta_resposta'));
			}
			if ($this->BAuth->temPermissao($usuario['Usuario']['codigo_uperfil'], 'obj_acionamento-buonnysat')) {
				$this->set(compact('buonnysat'));
			}
		}

		$this->set(compact('tecnologias', 'operacoes', 'passos_atendimentos'));
	}

	protected function carregaCombosClientesCadastrados() {
		$this->loadModel('Seguradora');
		$this->loadModel('Corretora');
		$this->loadModel('Corporacao');
		$this->loadModel('EnderecoRegiao');
		$seguradoras = $this->Seguradora->find('list', array('order' => 'nome'));
		$corretoras = $this->Corretora->find('list', array('order' => 'nome'));
		$corporacoes = $this->Corporacao->find('list', array('order' => 'descricao'));
		$regioes = $this->EnderecoRegiao->listarRegioes();
		$this->set(compact('seguradoras', 'corretoras', 'corporacoes', 'regioes'));
	}

	protected function carregaComboRegioes() {
		$this->loadModel('EnderecoRegiao');
		$regioes = $this->EnderecoRegiao->listarRegioes();
		$this->set(compact('regioes'));
	}

	protected function carregaComboEstadoCidade() {
		$this->loadModel('EnderecoEstado');
		$this->loadModel('EnderecoCidade');
		$estados = $this->EnderecoEstado->comboPorPais(1);


		$cidades = array();
		if(isset($this->data['VEndereco']['endereco_codigo_estado'])) {
			$cidades = $this->EnderecoCidade->combo($this->data['VEndereco']['endereco_codigo_estado']);
		}
		$this->set(compact('estados', 'cidades'));
	}

	protected function carregaCombosClientes($listar_npe_nome = false) {
		$this->loadModel('ClienteSubTipo');
		$this->loadModel('Corretora');
		$this->loadModel('Seguradora');
		$this->loadModel('Gestor');
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('TipoPerfil');
		$this->loadModel('Usuario');
		$this->loadModel('MotivoBloqueio');
		$corretoras 	= $this->Corretora->find('list', array('order' => 'nome'));
		$filiais 		= $filiais = $this->EnderecoRegiao->listarRegioes();
		$gestores 		= $this->Gestor->listarNomesGestoresAtivos();
		$this->TipoPerfil->carregaFiltrosPorTipoPerfil($this->data['Cliente'],$this->BAuth->user(),'seguradora','corretora','endereco_regiao');
		if($this->element_name =='clientes_configuracoes')
			$exibe_combo_somente_buonnysay = TRUE;
		$somente_buonnysay = array( 1 => 'Cliente BuonnySat', 2 => 'Todos' );
		$motivos = $this->MotivoBloqueio->find('list', array('conditions' => array('codigo' => array(1,8,17)), 'order' => 'descricao DESC'));
		$this->set(compact('corretoras', 'gestores','filiais', 'somente_buonnysay', 'exibe_combo_somente_buonnysay', 'motivos'));
	}

	protected function carregaCombosOcorrencias() {
		$this->loadModel('Ocorrencia');
		$this->loadModel('Equipamento');
		$this->loadModel('OperacaoMonitora');
		$this->loadModel('PerfilStatusOcorrencia');
		$this->loadModel('StatusOcorrencia');
		$this->loadModel('TipoOcorrencia');
		if ($this->element_name == 'ocorrencias') {
			$usuario = $this->BAuth->user();
			$filtros_salvos = $this->Filtro->listaFiltros('ocorrencias', $usuario['Usuario']['codigo']);
			if ($this->BAuth->temPermissao($usuario['Usuario']['codigo_uperfil'], 'buonny')) {
				$tipoStatusSVizualizacao = $this->StatusOcorrencia->find('list');
			} else {
				$codigo_objeto = $this->Session->read('codigo_objeto');
				$tipoStatusSVizualizacao = $this->PerfilStatusOcorrencia->statusPorObjeto($codigo_objeto);
			}
		} elseif ($this->element_name == 'ocorrencias_consulta') {
			$filtros_salvos = null;
			$tipoStatusSVizualizacao = $this->StatusOcorrencia->find('list');
		}
		$tipos_ocorrencia = $this->TipoOcorrencia->listaTipoOcorrencia();
		$tecnologias = $this->Equipamento->find('list', array('order' => 'descricao'));
		$operacoes = $this->OperacaoMonitora->listaOperacoes();
		$this->set(compact('tipoStatusSVizualizacao', 'tipos_ocorrencia', 'tecnologias', 'operacoes', 'filtros_salvos'));
	}

	protected function carregaCombosFichas() {
		$this->loadModel('Produto');

		$produtos = $this->Produto->find('list', array(
			'conditions' => array(
				'codigo' => array(1, 2)
			)
				));

		$this->set('produtos', $produtos);
	}

	protected function carregaCombosClientesProdutos() {
		$this->loadModel('Produto');
		$this->loadModel('StatusContrato');
		$this->loadModel('MotivoBloqueio');

		$produtos = $this->Produto->find('list');
		$status_contrato = $this->StatusContrato->find('list');
		$status_produto = $this->MotivoBloqueio->find('list', array('order' => 'codigo asc'));

		$this->set(compact('produtos', 'status_contrato', 'status_produto'));
	}

	protected function carregaCombosInformacoesClientes() {
		$this->loadModel('InformacaoCliente');
		$this->loadModel('SistemaMonitoramento');
		$areasAtuacao = $this->InformacaoCliente->AreaAtuacao->find('list');
		$sistemasMonitoramento = SistemaMonitoramento::lista();
		$this->set(compact('areasAtuacao', 'sistemasMonitoramento'));
	}

	protected function carregaComboTiposVeiculos($exibe_bitrem = false) {
		$this->loadModel('TTveiTipoVeiculo');

		$tipos_veiculos = $this->TTveiTipoVeiculo->listaFormatada();
		if ($exibe_bitrem) $tipos_veiculos += array(99 => 'Bitrem');
		$this->set(compact('tipos_veiculos'));
	}

	protected function carregaComboTecnologia() {
		$veiculos_tecnologia = ClassRegistry::init('TTecnTecnologia')->listaFicticios();
		$this->set(compact('veiculos_tecnologia'));
	}

	protected function carregaComboTransportadores() {
		$transportadores = ClassRegistry::init('Cliente')->listaEmbTrans($this->data['RelatorioSmVeiculosRegiao']['codigo_cliente'], true);
		$this->set(compact('transportadores'));
	}

	protected function carregaComboClassesReferencias() {
		$classes_referencia = ClassRegistry::init('TCrefClasseReferencia')->listar();
		$this->set(compact('classes_referencia'));
	}

	protected function carregaCheckboxAlvosOrigem($element_name) {
		$this->loadModel('TRefeReferencia');		
		if($element_name == 'veiculos_posicao_frota')
			$cds = $this->TRefeReferencia->listaCdsQuantidadeVeiculos($this->data[$this->model_name]['codigo_cliente'], true, true);
		else
			$cds = $this->TRefeReferencia->listaCdsQuantidadeVeiculos($this->data[$this->model_name]['codigo_cliente']);
		$this->set(compact('cds'));
	}

	protected function carregaComboAlvosBandeirasRegioes($somente_cd = false) {
		$this->loadModel('TPjurPessoaJuridica');
		$this->loadModel('TRefeReferencia');
		$this->loadModel('TBandBandeira');
		$this->loadModel('TRegiRegiao');
		$this->loadModel('RelatorioSm');
		$this->loadModel('Cliente');

		$cds = $bandeiras = $regioes = $lojas  = $transportadores = array();

		if (!empty($this->data[$this->model_name]['codigo_cliente'])) {
			$transportadores = $this->Cliente->listaTransportadoresGuardian($this->data[$this->model_name]['codigo_cliente']);
			$oras_codigo = $this->TPjurPessoaJuridica->buscaClienteCentralizador($this->data[$this->model_name]['codigo_cliente']);
				$oras_codigo = $oras_codigo['TPjurPessoaJuridica']['pjur_pess_oras_codigo'];
				if(!empty($oras_codigo)){
					$cds = $this->TRefeReferencia->listaCds($oras_codigo);
					if(!$somente_cd){
						$bandeiras = $this->TBandBandeira->lista($oras_codigo);
						$regioes = $this->TRegiRegiao->lista($oras_codigo);
						$lojas = $this->TRefeReferencia->listaLojas($oras_codigo);
					}
				}
		}
		$agrupamento = $this->RelatorioSm->listaAgrupamento();

		$this->set(compact('cds', 'bandeiras', 'regioes', 'lojas', 'agrupamento', 'somente_cd', 'transportadores'));
	}

	protected function carregaComboStatusViagensSemSemViagem() {
		$this->loadModel('StatusViagem');

		$status_viagens = $this->StatusViagem->find(array(StatusViagem::SEM_VIAGEM));
		$this->set(compact('status_viagens'));
	}

	protected function carregaComboStatusViagens() {
		$this->loadModel('StatusViagem');

		$status_viagens = $this->StatusViagem->find(array(StatusViagem::CANCELADO, StatusViagem::ENCERRADA));
		$this->set(compact('status_viagens'));
	}

	protected function carregaComboStatusViagensEfetivas() {
		$this->loadModel('StatusViagem');

		$status_viagens = $this->StatusViagem->find(array(StatusViagem::SEM_VIAGEM, StatusViagem::CANCELADO, StatusViagem::AGENDADO));
		$this->set(compact('status_viagens'));
	}

	private function carregarComboAlertas(){
		$this->loadModel('Usuario');

		$usuarios = array();
		if (!empty($this->data[$this->model_name]['codigo_cliente']))
				$usuarios = $this->Usuario->listaPorClienteList($this->data[$this->model_name]['codigo_cliente']);

		$this->set(compact('usuarios'));
	}

	private function salvar_filtro() {
		$usuario = $this->Auth->user();
		$this->data['Filtro']['element_name'] = $this->element_name;
		$this->data['Filtro']['model_name'] = $this->model_name;
		$this->data['Filtro']['codigo_usuario'] = $usuario['Usuario']['codigo'];
		$this->Filtro->incluir($this->data);
		$this->data['Filtro']['salvar_filtro'] = null;
		$this->data['Filtro']['nome_filtro'] = null;
	}

	public function limpar() {
		$this->layout = 'ajax_placeholder';
		$this->element_name = $this->passedArgs['element_name'];
		$this->model_name = $this->passedArgs['model'];		
		$filtros = $this->Filtros->limpa_sessao($this->model_name);
		$this->data[$this->model_name] = $filtros;
		$this->set('filterValidated', false);
		$this->carrega_combos();
		$this->render('/elements/filtros/' . $this->element_name);
	}

	public function recuperar_filtro($codigo) {
		$data = $this->Filtro->recuperar($codigo);
		$this->passedArgs['element_name'] = $data['Filtro']['element_name'];
		$this->passedArgs['model'] = $data['Filtro']['model_name'];
		unset($data['Filtro']);
		$this->data = $data;
		$this->filtrar();
	}

	public function apagar_filtro($codigo) {
		$data = $this->Filtro->apagar($codigo);
		$this->redirect('/');
	}

	private function carregarComboSinistroEmbarcadorTransportador(){
		$clientes_embarcador = array();
		$clientes_transportador = array();
		$natureza = array();

		if( isset($this->data['Sinistro']['cliente_embarcador']) && !empty($this->data['Sinistro']['cliente_embarcador']) )
			$clientes_embarcador = $this->data['Sinistro']['cliente_embarcador'];

		if( isset($this->data['Sinistro']['clientes_transportador']) && !empty($this->data['Sinistro']['clientes_transportador']) )
			$clientes_embarcador = $this->data['Sinistro']['clientes_transportador'];

		if( isset($this->data['Sinistro']['natureza']) && !empty($this->data['Sinistro']['natureza']) )
			$natureza = $this->data['Sinistro']['natureza'];

		$this->set(compact('clientes_embarcador','clientes_transportador','natureza'));
	}

	private function carregarComboSistemaOrigem(){
		$this->loadModel('LogIntegracao');
		$sistema_origem = $this->LogIntegracao->listarSistemaOrigem();
		$this->set(compact('sistema_origem'));
	}

	private function carregarComboSistemas($tipo = 'completo'){	

		$this->loadModel('LogAplicacao');
		if($tipo == 'completo'){
			$sistemas = $this->LogAplicacao->listarSistemas();
		}else{
			$sistemas = $this->LogAplicacao->listarSistemasResumido();
		}
		$this->set(compact('sistemas'));
	}

	private function carregarComboProdutos(){
		$this->loadModel('Produto');
				$produtos = $this->Produto->find('list');
				$this->set(compact('produtos'));
	}

	public function carregarSeguradoras(){
		$this->loadModel('Seguradora');
		$seguradoras = $this->Seguradora->find('list', array(
						'fields'=>'nome',
						'conditions'=>array('nome <>'=>'DESATIVADO'),
						'order'=>'nome ASC')
				);
				$this->set(compact('seguradoras'));
	}

	public function carregarCombosFicha(){
		$this->Fichas->carregarCombos();
	}

	public function carregarTiposTransporte(){
		$this->loadModel('TTtraTipoTransporte');
		$tipos_transportes = $this->TTtraTipoTransporte->find('list');
		$this->set(compact('tipos_transportes'));
	}

	public function carregarEstadoOrigem(){
		$this->loadModel('TEstaEstado');
		$EstadoOrigem = $this->TEstaEstado->combo();
		$this->set(compact('EstadoOrigem'));
	}

	public function carregarlogConsultas(){
    	$this->loadModel('ProfissionalTipo');
    	$this->loadModel('TipoOperacao');
    	$tipos_profissional = $this->Fichas->listProfissionalTipoAutorizado();
    	$tipos_operacoes = $this->TipoOperacao->listaTodosTiposOperacao();
    	$tipos_faturamento = $this->TipoOperacao->listCustoSemCusto();
    	$this->set(compact('tipos_faturamento','tipos_profissional','tipos_operacoes'));

	}

	public function carregarCombosLogFaturamento() {
	    $this->loadmodel('Seguradora');
        $this->loadmodel('Corretora');
        $this->loadmodel('Gestor');
        $this->loadmodel('EnderecoRegiao');
        $this->loadmodel('Produto');
        $this->loadmodel('Servico');
        $seguradoras = $this->Seguradora->find('list', array('order' => 'nome'));
        $corretoras = $this->Corretora->find('list', array('order' => 'nome'));
        $gestores = $this->Gestor->listarNomesGestoresAtivos();
        $filiais = $this->EnderecoRegiao->listarRegioes();
        $produtos = $this->Produto->find('list', array('fields' => array('descricao')));
        $servicos = $this->Servico->find('list', array('fields' => array('descricao')));
        $this->set(compact('seguradoras', 'corretoras', 'gestores', 'filiais', 'produtos', 'servicos'));
	}

	public function carregarCombosModsIvrsPesquisas($filtros = null) {
       	$this->loadmodel('Departamento');
        $this->data['ModIvrPesquisa'] = $this->Filtros->controla_sessao($this->data, "ModIvrPesquisa");
        if (empty($this->data['ModIvrPesquisa'])) {
            $this->data['ModIvrPesquisa']['startq'] = date('d/m/Y');
            $this->data['ModIvrPesquisa']['endq'] = date('d/m/Y');
            $this->data['ModIvrPesquisa']['status'] = 2;
            $this->data['ModIvrPesquisa']['agrupamento'] = 1;
        }
        $status = array('1' => 'Não Avaliada', '2' => 'Avaliada', );
        $pontuacao = array('0', '1', '2', '3', '4', '5');
        $agrupamento = array(1 => 'Departamentos', 2 => 'Ramal');
        $departamento = $this->Departamento->find('list');
        $departamento += array('99' => 'Sem Departamento');
        $this->set(compact('status', 'pontuacao', 'agrupamento', 'departamento'));
	}


	public function periodo_validade_consulta( $model, $data_inicial = 'data_inicial', $data_final = 'data_final' ){
		if( !empty($this->data[$model][$data_inicial]) && !empty($this->data[$model][$data_final])){
			$this->loadModel($model);
			$data_inicio = strtotime(AppModel::dateToDbDate($this->data[$model][$data_inicial]));
			$data_fim    = strtotime(AppModel::dateToDbDate($this->data[$model][$data_final]));
			if ( floor(($data_fim - $data_inicio )/(60*60*24)) > 31 ){
				$this->$model->invalidate( $data_final, 'Período maior que 1 mês');
				return false;
			}
			return true;
		}
	}

	public function carregarCombosMetaCentroCusto(){
		$this->loadmodel('CentroCusto');
		$this->loadmodel('Grflux');
		$this->loadmodel('Sbflux');
        $meses = Comum::listMeses();
        $ano   = date('Y', strtotime('-1 year'));
        $anos  = Comum::listAnos( $ano );
        array_push($anos, date('Y', strtotime('+1 year')));        
        $dados_centro_custo = $this->CentroCusto->find('all', array( 'fields'=>array('codigo', 'descricao'), 'conditions'=>array('descricao <>'=>NULL,'descricao <>'=>'' )));
        $centro_custo = array();
        foreach ($dados_centro_custo as $key => $value ) {
            $centro_custo[$value['CentroCusto']['codigo']] = $value['CentroCusto']['codigo'].' '.$value['CentroCusto']['descricao'];
        }
        $fluxo = $this->Grflux->listar();
        $sub_fluxo  = array();        
        if( isset($this->data['MetaCentroCusto']['codigo_fluxo']) )
            $sub_fluxo = $this->Sbflux->listar( $this->data['MetaCentroCusto']['codigo_fluxo'] );
        $this->LojaNaveg = ClassRegistry::init('LojaNaveg');     
        $grupo = isset($this->data['MetaCentroCusto']['grupo_empresa']) ? $this->data['MetaCentroCusto']['grupo_empresa'] : '1';
        $empresas = $this->LojaNaveg->listEmpresas( $grupo);   
        $grupos_empresas = $this->LojaNaveg->listGrupos();
        $this->set(compact('centro_custo', 'fluxo', 'sub_fluxo', 'anos', 'meses','empresas','grupos_empresas'));

	}

	public function carregarCombosRegistrosTelecom() {
        $anos = Comum::listAnos(date('Y')-2);
        $meses = Comum::listMeses(true);
        $this->loadModel('TipoRetorno');
        $this->loadModel('RegistroTelecom');
        $this->loadModel('Departamento');

         $operadoras = array(
            RegistroTelecom::VIVO  => 'Vivo (Celular)', 
            RegistroTelecom::CLARO  => 'Claro (Celular)',   
            RegistroTelecom::NEXTEL  => 'Nextel (Radio)',
            RegistroTelecom::TARIFADOR  => 'Tarifador (Ramal)');

        $agrupamento = $this->RegistroTelecom->listarAgrupamentos();

        $conditions['TipoRetorno.usuario_interno'] = true;
        $tipo_cobranca = $this->RegistroTelecom->TipoRetorno->find('list', array('conditions' => $conditions));
        
        $departamentos = $this->Departamento->find('list');
        $isPost = ($this->RequestHandler->isAjax() || $this->RequestHandler->isPost());

        $filtros['RegistroTelecom'] = $this->Filtros->controla_sessao($this->data, "RegistroTelecom");

		$this->data['RegistroTelecom']['mes'] = (isset($filtros['RegistroTelecom']['mes']) ? $filtros['RegistroTelecom']['mes'] : date('m'));
		$this->data['RegistroTelecom']['ano'] = (isset($filtros['RegistroTelecom']['ano']) ? $filtros['RegistroTelecom']['ano'] : date('Y'));

        $this->set(compact('meses','anos','isPost','tipos_retorno','operadoras', 'tipo_contato', 'tipo_cobranca', 'departamentos', 'agrupamento'));
	}

	public function carregarCombosNiveldeServicos(){
		$this->loadModel('RelatorioSm');
		$this->loadModel('TTranTransportador');
		$this->loadModel('TViagViagem');
		$anos = Comum::listAnos();
		$alvos_bandeiras_regioes = $this->RelatorioSm->carregaCombosAlvosBandeirasRegioes(empty($this->data['TViagViagem']['codigo_cliente']) ? 0 : $this->data['TViagViagem']['codigo_cliente'], false, true);
		$transportadores['transportadores'] = $this->TTranTransportador->listaTranportador();
		$alvos_bandeiras_regioes = array_merge($alvos_bandeiras_regioes,$transportadores);	
		$agrupamento = $this->RelatorioSm->listaAgrupamento();
		if(isset($this->authUsuario['Usuario']['codigo_cliente']) && !empty($this->authUsuario['Usuario']['codigo_cliente'])){
			$this->data['TViagViagem']['codigo_cliente'] = $this->authUsuario['Usuario']['codigo_cliente'];
		}

		if(empty($this->data['TViagViagem']['agrupamento'])){
			$this->data['TViagViagem']['agrupamento'] = 1;
		}
		if(empty($this->data['TViagViagem']['base_cnpj'])){
			$this->data['TViagViagem']['base_cnpj'] = 0;
		}
		if(empty($this->data['TViagViagem']['mesclar_prazo_adiantado'])){
			$this->data['TViagViagem']['mesclar_prazo_adiantado'] = 0;
		}	
			
		
		if(empty($this->data['TViagViagem']['data_inicial']) || empty($this->data['TViagViagem']['data_final'])){	
			$this->data['TViagViagem']['data_inicial'] = date('d/m/Y');
			$this->data['TViagViagem']['data_final'] = date('d/m/Y');
		}

		$data_inicial = strtotime(AppModel::dateToDbDate($this->data['TViagViagem']['data_inicial']));
		$data_final = strtotime(AppModel::dateToDbDate($this->data['TViagViagem']['data_final']));	

		if(isset($this->data['TViagViagem']['codigo_cliente'])){
			$codigo_cliente = $this->data['TViagViagem']['codigo_cliente'];
		}else{
			$codigo_cliente = NULL;
		}

		if ($this->TViagViagem->nivel_de_servicos_validate($codigo_cliente,$data_inicial,$data_final)) {
			$this->data['nivel_de_servicos'] = $this->Filtros->controla_sessao($this->data, 'TViagViagem');
		}
		
	
		$this->set(compact('meses', 'anos','agrupamento','alvos_bandeiras_regioes'));
	}

	private function carregaCombosAlvosBandeirasRegioesCheckbox($codigo_cliente) {
		$alvos_bandeiras_regioes = $this->RelatorioSm->carregaCombosAlvosBandeirasRegioes($codigo_cliente, false, true);		
		$this->set(compact( 'alvos_bandeiras_regioes'));
	}

	private function carregarCombosTempoMaximoSintetico(){
		$this->loadModel('TEstaEstado');
		$this->loadModel('RelatorioSm');
		$this->loadModel('TViagViagem');
		$this->loadModel('TVlocViagemLocal');
		$this->loadModel('Cliente');
		
		if(isset($this->authUsuario['Usuario']['codigo_cliente']) && !empty($this->authUsuario['Usuario']['codigo_cliente'])){
			$this->data['TViagViagem']['codigo_cliente'] = $this->authUsuario['Usuario']['codigo_cliente'];
		}

		if(empty($this->data['TViagViagem']['data_inicial']) || empty($this->data['TViagViagem']['data_final'])){	
			$this->data['TViagViagem']['data_inicial'] = date('d/m/Y');
			$this->data['TViagViagem']['data_final'] = date('d/m/Y');
		}

		if($this->RequestHandler->isPost()) {
	        $this->data['TViagViagem']['filtrado'] = $this->TViagViagem->valida_tempo_maximo_minutos($this->data['TViagViagem']['codigo_cliente'],$this->data['TViagViagem']['maximo_minutos'],$this->data['TViagViagem']['data_inicial'],$this->data['TViagViagem']['data_final']);
		}

		$status_viagem  = array(TViagViagem::STATUS_EM_VIAGEM => 'Em viagem', TViagViagem::STATUS_ENCERRADO => 'Encerrado');
		$alvos_bandeiras_regioes = $this->RelatorioSm->carregaCombosAlvosBandeirasRegioes(empty($this->data['TViagViagem']['codigo_cliente']) ? 0 : $this->data['TViagViagem']['codigo_cliente'], false, true);
		$transportadores['transportadores'] = array();
		if(isset($this->data['TViagViagem']['codigo_cliente'])){
			$transportadores['transportadores']	= $this->Cliente->listaTransportadoresGuardian($this->data['TViagViagem']['codigo_cliente']);
		}
		$alvos_bandeiras_regioes = array_merge($alvos_bandeiras_regioes,$transportadores);	
		$agrupamento = $this->RelatorioSm->listaAgrupamento();	
		$UFOrigem = $this->TEstaEstado->combo();

		$status_permanencia = array(TVlocViagemLocal::STATUS_PERMANENCIA_ACIMA => 'Acima do tempo', TVlocViagemLocal::STATUS_PERMANENCIA_DENTRO => 'Dentro do tempo');
		$status_alvo  = array(TVlocViagemLocal::STATUS_ALVO_NAO_ENTREGUE => 'Não entregue', TVlocViagemLocal::STATUS_ALVO_ENTREGANDO => 'Entregando', TVlocViagemLocal::STATUS_ALVO_ENTREGUE => 'Entregue');
		$status_janelas  = array(TVlocViagemLocal::STATUS_JANELA_ADIANTADO => 'Adiantado', TVlocViagemLocal::STATUS_JANELA_NO_PRAZO => 'No Prazo', TVlocViagemLocal::STATUS_JANELA_ATRASADO => 'Atrasado');

		$this->set(compact('agrupamento','status_viagem', 'alvos_bandeiras_regioes','UFOrigem','status_permanencia','status_alvo','status_janelas'));	
	}

	function carregarCombosPontoEletronico(){
		$this->loadModel('Usuario');
		$authUsuario = $this->BAuth->user();
		$this->data['PontoEletronico']['codigo_gestor'] = $authUsuario['Usuario']['codigo'];

		$lista = $this->Usuario->listaUsuariosDepartamento($authUsuario['Usuario']['codigo_departamento']);
		$this->set(compact('lista'));
	}

	function carregarCombosItensChecklist(){
		$this->loadModel('TIcheItemChecklist');
		if (!empty($this->authUsuario['Usuario']['codigo_cliente'])){
			$this->data['TIcheItemChecklist']['iche_pjur_pess_oras_codigo'] = $this->authUsuario['Usuario']['codigo_cliente'];
		}
		
		if(empty($this->data['TIcheItemChecklist']['iche_pjur_pess_oras_codigo'])){
			$this->TIcheItemChecklist->invalidate('iche_pjur_pess_oras_codigo','informe o código do cliente');
		}	
	}

	function carregarCombosCargasPatio(){
		$this->loadModel('RelatorioSm');
		$this->loadModel('TCpatCargasPatio');
		if (!empty($this->authUsuario['Usuario']['codigo_cliente'])){
			$this->data['TCpatCargasPatio']['cpat_pjur_pess_oras_codigo'] = $this->authUsuario['Usuario']['codigo_cliente'];
		}
		
		$alvos_bandeiras_regioes = $this->RelatorioSm->carregaCombosAlvosBandeirasRegioes($this->data['TCpatCargasPatio']['cpat_pjur_pess_oras_codigo'], true, true);
		
		if(empty($this->data['TCpatCargasPatio']['cpat_pjur_pess_oras_codigo'])){
			$this->TCpatCargasPatio->invalidate('cpat_pjur_pess_oras_codigo','informe o código do cliente');
		}
		$this->set(compact('alvos_bandeiras_regioes'));	
	}

	function carregarCombosEstatisticaCombos(){
		$this->loadModel('TUsuaUsuario');
		$this->loadModel('TEeveEstatisticaEvento');	
		$this->loadModel('TEspaEventoSistemaPadrao');	
		$this->loadModel('TErasEstacaoRastreamento');	
		$agrupamentos = $this->TEeveEstatisticaEvento->lista_agrupametos();    	
    	$estacao = $this->TErasEstacaoRastreamento->find('list');
    	$evento = $this->TEspaEventoSistemaPadrao->find('list');

		$usuarios = $this->TUsuaUsuario->listar_logins(array('usua_perf_codigo' => array('7','60')));
		
	  	$this->data['TEeveEstatisticaEvento'] = $this->Filtros->controla_sessao($this->data, "TEeveEstatisticaEvento");

		if(empty($this->data['TEeveEstatisticaEvento']['data'])){
			$this->data['TEeveEstatisticaEvento']['data'] = date('d/m/Y');
		}

    	$this->set(compact('agrupamentos','estacao','evento','usuarios'));
	}

	function carregarCombosObjetivosComerciaisExcecoes(){
		$this->loadModel('Gestor');
		$this->loadModel('EnderecoRegiao');
		$this->loadModel('Produto');
		$this->loadModel('ObjetivoComercial');
		$meses = Comum::listMeses();       
        $anos = Comum::listAnos(2014);
        array_push($anos, date('Y', strtotime('+1 year')));
        $gestores = $this->Gestor->listarNomesGestoresAtivos();
        $filiais = $this->EnderecoRegiao->listarRegioes();
        $produtos = $this->Produto->listarProdutosNavegarqCodigoBuonny();
        unset($produtos[30]);        
        $listaAgrupamento = $this->ObjetivoComercial->listarAgrupamentos();
        $listaTipoVisualizacao = $this->ObjetivoComercial->listarTipoVisualizacao();
        $this->set(compact('meses', 'anos','gestores','filiais','produtos','listaAgrupamento','listaTipoVisualizacao'));
	}

	private function carrega_combos_perfil($codigo_cliente = null){
		$this->loadModel('Uperfil');
        $perfil = array('1' => 'ADMIN') + $this->Uperfil->find('list');
        $this->set(compact('perfil'));
	}	

	private function carrega_combos_vinculo_veiculo_periferico(){
		$this->loadModel('TTveiTipoVeiculo');
		$this->loadModel('TPpadPerifericoPadrao');
		$this->loadModel('TPtvePerifericoTipoVeiculo');
		$tipo_veiculo = $this->TTveiTipoVeiculo->listaFormatada('ASC');
        $periferico = $this->TPpadPerifericoPadrao->find('list',array('conditions' => array('ppad_ativo' => 'S')));
        $this->set(compact('tipo_veiculo','periferico'));
	}
	
	private function carregaCombosLogConsultas(){
		$this->loadModel('LogConsultaTipo');
        $this->data['LogConsulta'] = $this->Filtros->controla_sessao($this->data, "LogConsulta");
        $tipos_consulta = $this->LogConsultaTipo->listarTipoConsulta('list');
        $this->set(compact('tipos_consulta'));
	}

	public function CarregaCombosAcionamentoPrestadores() {
        $exibir_valores = $this->Session->read('exibir_valores');
        $this->loadModel('EmbarcadorTransportador');
        $this->loadModel('Tecnologia');
        $authUsuario = $this->BAuth->user();
        $tecnologia = array();
        $embarcadores = array();
        $transportadores = array();
        $agrupamento = array(1 => 'Transportador',
                             2 => 'Embarcador',
                             3 => 'Tecnologia',
                             4 => 'Prestador');
        $valores = array(1 => 'Sim',
                         2 => 'Não');

        $tecnologia = $this->Tecnologia->lista();
        if(!empty($authUsuario['Usuario']['codigo_cliente'])) {
            $this->data['PrestadoresPostgres']['codigo_cliente'] = $authUsuario['Usuario']['codigo_cliente'];
            $dados = $this->EmbarcadorTransportador->dadosPorCliente($this->data['PrestadoresPostgres']['codigo_cliente']);
            $embarcadores = $dados['embarcadores'];
            $transportadores = $dados['transportadores'];
        }elseif(!empty($this->data['PrestadoresPostgres']['codigo_cliente'])) {
            $dados = $this->EmbarcadorTransportador->dadosPorCliente($this->data['PrestadoresPostgres']['codigo_cliente']);
            $embarcadores = $dados['embarcadores'];
            $transportadores = $dados['transportadores'];
        }

       if(empty($this->data['PrestadoresPostgres'])) {
            $this->data['PrestadoresPostgres']['data_envio_prestador_inicial'] = date('01/m/Y');
            $this->data['PrestadoresPostgres']['data_envio_prestador_final'] = date('d/m/Y');
        }
        $this->set(compact('tecnologia', 'embarcadores', 'transportadores', 'agrupamento', 'valores' , 'exibir_valores'));
    }

    function carregaCombosEventosCompostos(){
        $sequencial = array( 1 => 'Sim', 2 => 'Não');
        $ativo = array( 'S' => 'Ativo', 'N' => 'Inativo');
        $this->set(compact('sequencial','ativo'));
    }

    function carregaCombosDiretoria(){
        $ativos_inativos = array(1 => 'Ativo',2 => 'Inativo');
        $this->set(compact('ativos_inativos'));
    }

    function carrega_combos_pesquisa_satisfacao() {
    	$this->loadModel('Usuario');
    	$this->loadModel('Gestor');
    	$authUsuario    = $this->authUsuario;
    	//$authUsuario['Usuario']['codigo'] = 30052;
    	$gestor_logado = FALSE;
    		if(!empty($authUsuario['Usuario']['codigo'])) {
    			$gestor = $this->Gestor->verifica_se_usuairo_gestor($authUsuario['Usuario']['codigo']);
    			if(!empty($gestor) && $gestor['Gestor']['codigo_departamento'] == Departamento::COMERCIAL){
    				$this->data['PesquisaSatisfacao']['codigo_gestor'] = $gestor['Gestor']['codigo'];
    				$gestor_logado = TRUE;
    			}elseif(!empty($gestor) && $gestor['Gestor']['codigo_departamento'] == Departamento::GESTOR_NPE) {
    				$this->data['PesquisaSatisfacao']['codigo_gestor_npe'] = $gestor['Gestor']['codigo'];
    				$gestor_logado = TRUE;
    			}
    		}
		$gestores_com 		= $this->Gestor->listarNomesGestoresAtivos();		
		$gestores_npe 	= $this->Usuario->lista_gestor_npe( (FALSE) );
		$this->set(compact('gestores_npe', 'gestores_com', 'gestor_logado'));
	}

    function carregaPGRRelacaoCliente(){
    	$this->loadModel('TPgpgPg');
    	$this->loadModel('TTtraTipoTransporte');
        $pgr = $this->TPgpgPg->find('list',array('fields' => array('pgpg_codigo'),'conditions' => array('pgpg_estatus' => 'A')));
    	$tipo_transporte = $this->TTtraTipoTransporte->find('list');
    	$this->set(compact('pgr','tipo_transporte'));
    }

    function carregaCombosDiretoriaUsuario(){
        $this->loadModel("Diretoria");
        $this->loadModel("Gestor");
        $gestores = $this->Gestor->listarNomesGestoresAtivos();
        $diretorias = $this->Diretoria->find('list');
        $this->set(compact('gestores','diretorias'));    
    }

     function carregaObjetivoExcecaoFaturamento(){
    	$this->loadModel('Produto');
        $meses = Comum::listMeses();       
        $anos = Comum::listAnos(2014);
        array_push($anos, date('Y', strtotime('+1 year')));
        $produtos = $this->Produto->listarProdutosNavegarqCodigoBuonny();
        unset($produtos[30]);        
        $this->set(compact('meses', 'anos','produtos'));
    }


    function carregaCombosVeiculoMapaGr() {
		$this->loadModel('TTtraTipoTransporte');
		$this->loadModel('TTveiTipoVeiculo');
		$this->loadModel('StatusViagem');
		$this->loadModel('RelatorioSm');
		$this->loadModel('TEstaEstado');

		$qualidades = array('1' => 'Acima de', '2' => 'Abaixo de');
		$tipos_veiculos = $this->TTveiTipoVeiculo->listaFormatada();
        $tipos_veiculos += array(99 => 'Bitrem');
		$tipos_transportes= $this->TTtraTipoTransporte->find('list');
		$status_viagens = $this->StatusViagem->find(array(StatusViagem::SEM_VIAGEM,StatusViagem::CANCELADO,StatusViagem::ENCERRADA));
        $alvos_bandeiras_regioes = $this->RelatorioSm->carregaCombosAlvosBandeirasRegioes($this->data['RelatorioSmVeiculos']['codigo_cliente'], false, true);
		$EstadoOrigem = $this->TEstaEstado->combo();
		$this->set(compact('label_empty', 'tipos_veiculos', 'status_viagens', 'agrupamento', 'is_post','tipos_transportes','EstadoOrigem', 'qualidades','alvos_bandeiras_regioes'));		
    }

    function carregaCombosSimuladorPGR(){
    	$this->loadModel("TTtraTipoTransporte");
        $tipo_transporte = $this->TTtraTipoTransporte->find('list');
        $this->set(compact('tipo_transporte'));
    }

}