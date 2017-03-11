<?php
class HomeController extends AppController {
    public $name = 'Home';
    var $uses = array( 'Cadastro', 'Regiao', 'Unidade');
    
    public function beforeFilter() {
        parent::beforeFilter();
    }


    function index() {
        $fields = array(
                'Cadastro.codigo',
                'Cadastro.nome',
                'Cadastro.email',
                'Cadastro.telefone',
                'Cadastro.data_nascimento',
                'Cadastro.codigo_regiao',
                'Cadastro.codigo_unidade',
                'Cadastro.score',
                'Cadastro.token',
                'Regiao.nome',
                'Unidade.nome',
                );

        $joins  = array(
            array(
                'table' => 'regiao',
                'alias' => 'Regiao',
                'type' => 'LEFT',
                'conditions' => 'Regiao.codigo = Cadastro.codigo_regiao',
                ),
            array(
                'table' => 'unidade',
                'alias' => 'Unidade',
                'type' => 'LEFT',
                'conditions' => 'Unidade.codigo = Cadastro.codigo_unidade',
                ),
            );

        $order = array('Cadastro.nome');

        $dados = $this->Cadastro->find('all', array('joins' => $joins, 'fields' => $fields,'order' => $order));

        $this->set(compact('dados'));
    }

    function detalhes($codigo) {
        if(!empty($codigo)){
            $conditions = array('Cadastro.codigo' => $codigo);

            $fields = array(
                'Cadastro.codigo',
                'Cadastro.nome',
                'Cadastro.email',
                'Cadastro.telefone',
                'Cadastro.data_nascimento',
                'Cadastro.codigo_regiao',
                'Cadastro.codigo_unidade',
                'Cadastro.score',
                'Cadastro.token',
                'Regiao.nome',
                'Unidade.nome',
                );

            $joins  = array(
            array(
                'table' => 'regiao',
                'alias' => 'Regiao',
                'type' => 'LEFT',
                'conditions' => 'Regiao.codigo = Cadastro.codigo_regiao',
                ),
            array(
                'table' => 'unidade',
                'alias' => 'Unidade',
                'type' => 'LEFT',
                'conditions' => 'Unidade.codigo = Cadastro.codigo_unidade',
                ),
            );
            $dados = $this->Cadastro->find('first', array('conditions' => $conditions, 'joins' => $joins, 'fields' => $fields));
            $this->set(compact('dados'));
        }
    }

    function gravaPrimeiroPasso(){
        $this->layout = 'ajax';
        $this->render(false, false);

        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];

        $valida_nome = $this->validaPalavras($nome);
        if(!$valida_nome){
            $erro = array('nome' => 'Nome inválido');
            echo json_encode($erro);
            exit;
        }
        

        // if ($this->Cadastro->incluir($dados)) { 
            echo 1;
        // }  
        // else{
        //     echo json_encode($this->Cadastro->validationErrors);
        // }
        
    }

    function validaPalavras($nome){
        $palavras = explode(" ", $nome);

        if(count($palavras)>2) {
            return true;
        } 
        else {
            return false;
        }
    }
    
     function carregaRegiao(){
        $this->layout = 'ajax';
        $this->render(false, false);
        
        $consulta = $this->Regiao->find("list", array('fields' => array('Regiao.codigo', 'Regiao.nome')));

        echo json_encode($consulta);

    }

    function carregaUnidade(){
        $this->layout = 'ajax';
        $this->render(false, false);
        
        $codigo_regiao = $_POST['codigo_regiao'];

        $consulta = $this->Unidade->find("list", array('fields' => array('Unidade.codigo', 'Unidade.nome'), 'conditions' => array('codigo_regiao' => $codigo_regiao)));

        echo json_encode($consulta);

    }

    function gravaSegundoPasso(){
        $this->layout = 'ajax';
        $this->render(false, false);

        $dados = array(
            'Cadastro' => array(
                'nome' => $_POST['nome'],
                'data_nascimento' => $_POST['data_nascimento'],
                'email' => $_POST['email'],
                'telefone' => $_POST['telefone'],
                'codigo_regiao' => $_POST['codigo_regiao'],
                'codigo_unidade' => $_POST['codigo_unidade'],
                'token' => $_POST['token']
            )
        );

        $score = $this->CalculaScore($dados);

        $dados['Cadastro']['score'] = $score;

        if ($this->Cadastro->incluir($dados)) { 
            echo 1;
        }  
        else{
            echo json_encode($this->Cadastro->validationErrors);
        }
    }

    function CalculaScore($dados){
        $this->render(false, false);
        $pontuacao = 10;

        switch ($dados['Cadastro']['codigo_regiao']) {
            case 2:
                $score = 2;
                break;
            case 3:
                if($dados['Cadastro']['codigo_unidade'] == 5){
                    $score = 0;
                }
                else{
                    $score = 1;
                }
                break;
            case 4:
                $score = 3;
                break;
            case 5:
                $score = 4;
                break;
            case 6:
                $score = 5;
                break;
            
            default:
                $score = 0;
                break;
        }

        $converte_data_nascimento = date_create( $dados['Cadastro']['data_nascimento']);
        $data_nascimento = strtotime(date_format($converte_data_nascimento,"Y-m-d"));
        $data_atual = strtotime(date("Y-m-d"));
        $calcula_idade = $data_atual - $data_nascimento;

        if($calcula_idade >= 100 || $calcula_idade <18){
            $score_data = 5;
        }
        elseif($calcula_idade > 39 || $calcula_idade <100){
            $score_data = 3;
        }
        elseif($calcula_idade > 17 || $calcula_idade <40){
            $score_data = 0;
        }

        $calculo_score = $score + $score_data;

        $resultado_score = $pontuacao - $calculo_score;
        return $resultado_score;

    }

}