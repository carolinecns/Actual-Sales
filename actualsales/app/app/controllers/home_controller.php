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

        echo 1;       
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

        if(empty($consulta)){
            echo json_encode(array('' => 'INDISPONÍVEL'));
        }
        else{
            echo json_encode($consulta);
        }

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
        
        $dados_api = $this->enviarDadosApi($dados);

        if($dados_api->success== true){
        
            if ($this->Cadastro->incluir($dados)) { 
                echo 1;
            }  
            else{
                echo json_encode($this->Cadastro->validationErrors);
            }
        }
        else{
            $retorno = array('Cadastro' =>$dados_api->message);
            echo json_encode($retorno);
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

    function enviarDadosApi($dados){

        $nome = (is_string($dados['Cadastro']['nome']))? $dados['Cadastro']['nome']: strval($dados['Cadastro']['nome']);
        $email = (is_string($dados['Cadastro']['email']))? $dados['Cadastro']['email']: strval($dados['Cadastro']['email']);
        
        $converte_telefone = (is_string($dados['Cadastro']['telefone']))? $dados['Cadastro']['telefone']: strval($dados['Cadastro']['telefone']);
        $telefone = str_replace(' ', '', (str_replace('-', '', (str_replace(')', '',(str_replace('(', '', $converte_telefone)))))));

        $data_nascimento = date('Y-m-d', strtotime($dados['Cadastro']['data_nascimento']));

        $consulta_regiao = $this->Regiao->find('first', array('conditions' => array('codigo' => $dados['Cadastro']['codigo_regiao'])));
        $regiao = $consulta_regiao['Regiao']['nome'];

        if(empty($dados['Cadastro']['codigo_unidade']))
        {
            $unidade = 'INDISPONÍVEL';
        }
        else{
            $consulta_unidade = $this->Unidade->find('first', array('conditions' => array('codigo' => $dados['Cadastro']['codigo_unidade'])));
            $unidade = (empty($consulta_unidade['Unidade']['nome']))? 'INDISPONÍVEL': $consulta_unidade['Unidade']['nome'];
        }

        $score = (is_string($dados['Cadastro']['score']))? $dados['Cadastro']['score']: strval($dados['Cadastro']['score']);
        $token = (is_string($dados['Cadastro']['token']))? $dados['Cadastro']['token']: strval($dados['Cadastro']['token']);

        $data = array(
            'nome' => $nome,
            'email' => $email,
            'telefone' => $telefone,
            'regiao' => $regiao,
            'unidade' => $unidade,
            'data_nascimento' => $data_nascimento,
            'score' => $score,
            'token' => $token
        );

        $cURL = curl_init();
        curl_setopt( $cURL, CURLOPT_URL, "http://api.actualsales.com.br/join-asbr/ti/lead" );
        curl_setopt( $cURL, CURLOPT_POST, true );
        curl_setopt( $cURL, CURLOPT_POSTFIELDS, http_build_query( $data ) );
        curl_setopt( $cURL, CURLOPT_RETURNTRANSFER, true );
        $retorno = curl_exec( $cURL );
        curl_close($cURL);

        $analisa_retorno = json_decode($retorno);

        return $analisa_retorno;

    }

}