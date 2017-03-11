<?php
class Filtro extends AppModel {
    var $name = 'Filtro';
    var $tableSchema = 'dbo';
    var $databaseTable = 'RHHealth';
    var $useTable = 'filtros';
    var $primaryKey = 'codigo';
    var $actsAs = array('Secure');
    var $validate = array(
        'nome_filtro' => array(
            array(
                'rule' => 'notEmpty',
                'message' => 'Nome não informado'
            ),
            array(
                'rule' => 'verificaUnique',
                'message' => 'Este filtro já existe'
            ),
        ),
        'element_name' => array(
            'rule' => 'notEmpty',
            'message' => 'Tela não informada'
        ),
        'model_name' => array(
            'rule' => 'notEmpty',
            'message' => 'Tela não informada'
        )
    );
    var $hasMany = array(
        'SelecaoFiltro' => array(
            'className' => 'SelecaoFiltro',
            'foreignKey' => 'codigo_filtro'
        )
    );
    
    function incluir($data) {
        try {
            $this->query('begin transaction');
            $this->create();
            if (!$this->save($data['Filtro'])) throw new Exception();
            if (!$this->SelecaoFiltro->salvar($this->id, $data, true)) throw new Exception();
            $this->commit();
            return true;
        } catch (exception $ex) {
            $this->rollback();
            return false;
        }
    }
    
    function verificaUnique() {
        return $this->find('count', array('conditions' => array('nome_filtro' => $this->data['Filtro']['nome_filtro'], 'codigo_usuario' => $this->data['Filtro']['codigo_usuario'], 'element_name' => $this->data['Filtro']['element_name']))) < 1 ;
    }
    
    function listaFiltros($element_name, $codigo_usuario) {
        return $this->find('all', array('conditions' => array('element_name' => $element_name, 'codigo_usuario' => array(null, $codigo_usuario))));
    }
    
    
    function recuperar($codigo_filtro) {
        $data = $this->find('first', array('conditions' => array('Filtro.codigo' => $codigo_filtro)));
        $selecoes = $data;
        unset($selecoes['Filtro']);
        $selecoes = $this->SelecaoFiltro->recuperarFiltro($data);
        unset($data['SelecaoFiltro']);
        return array_merge($data, $selecoes);
    }
    
    function apagar($codigo_filtro) {
        try {
            $this->query('begin transaction');
            if (!$this->SelecaoFiltro->apagar($codigo_filtro, true)) throw new Exception();
            if (!$this->delete($codigo_filtro)) throw new Exception();
            $this->commit();
            return true;
        } catch (Exception $ex) {
            $this->rollback();
            return false;
        }
    }
}

?>