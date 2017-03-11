<?php
class SelecaoFiltro extends AppModel {

    var $name = 'SelecaoFiltro';
    var $tableSchema = 'dbo';
    var $databaseTable = 'RHHealth';
    var $useTable = 'selecoes_filtros';
    var $primaryKey = 'codigo';
    var $actsAs = array('Secure');
    
    const TIPO_INDIVIDUAL = 1;
    const TIPO_MULTIPLE = 2;
    
    function salvar($codigo_filtro, $filtro, $in_another_transaction = false) {
        unset($filtro['Filtro']);
        $data = $this->separaCamposFiltro($codigo_filtro, $filtro);
        try {
            if (!$in_another_transaction) $this->query('begin transaction');
            foreach ($data as $filtro) {
                if (!$this->incluir($filtro)) throw new Exception();;
            }
            if (!$in_another_transaction) $this->commit();
            return true;
        } catch (Exception $ex) {
            if (!$in_another_transaction) $this->rollback();
            return false;
        }
    }
    
    private function separaCamposFiltro($codigo_filtro, $filtro) {
        $data = array();
        foreach ($filtro as $model => $campos) {            
            foreach ($campos as $campo => $valor) {
                $campo_ = $model.'.'.$campo;
                if (is_array($valor)) {
                    $valor_ = implode($valor, '|');
                    $tipo_valor = SelecaoFiltro::TIPO_MULTIPLE;
                } else {
                    $valor_ = $valor;
                    $tipo_valor = SelecaoFiltro::TIPO_INDIVIDUAL;
                }
                $data[]['SelecaoFiltro'] = array(
                    'codigo_filtro' => $codigo_filtro,
                    'campo' => $campo_,
                    'tipo' => $tipo_valor,
                    'valor' => $valor_
                );
            }
        }
        return $data;
    }
    
    function incluir($data) {
        $this->create();
        return $this->save($data);
    }
    
    function recuperarFiltro($filtros) {
        $data = array();
        foreach ($filtros['SelecaoFiltro'] as $filtro) {
            list($model, $campo) = explode('.', $filtro['campo']);
            if ($filtro['tipo'] == SelecaoFiltro::TIPO_MULTIPLE) {
                $valor = explode('|', $filtro['valor']);
            } else {                
                $valor = (trim($filtro['valor']) == '' ? null : trim($filtro['valor']));
            }
            $data[$model][$campo] = $valor;
        }
        return $data;
    }
    
    function listaFiltros($tela, $codigo_usuario) {
        $fields = array('nome_filtro');
        return $this->find('all', array('fields' => $fields, 'group' => $fields, 'conditions' => array('tela' => $tela, 'codigo_usuario' => array(null, $codigo_usuario))));
    }
    
    function apagar($codigo_filtro, $in_another_transaction = false) {
        $registros = $this->find('all', array('fields' => 'codigo', 'conditions' => array('codigo_filtro' => $codigo_filtro)));

        try {
            if (!$in_another_transaction) $this->query('begin transaction');
            foreach ($registros as $registro) {
                if (!$this->delete(array('SelecaoFiltro.codigo' => $registro['SelecaoFiltro']['codigo']))) throw new Exception();
            }
            if (!$in_another_transaction) $this->commit();
            return true;
        } catch (Exception $ex) {
            if (!$in_another_transaction) $this->rollback();
            return false;
        }
    }
}

?>