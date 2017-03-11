<div class='well'>
  <div id='filtros'>
    <?php echo $bajax->form('Cadastro', array('autocomplete' => 'off', 'url' => array('controller' => 'filtros', 'action' => 'filtrar', 'model' => 'Cadastro', 'element_name' => 'dados'), 'divupdate' => '.form-procurar')) ?>

      <?php echo $this->element('dados/fields_filtros') ?>
      <?php echo $this->BForm->submit('Buscar', array('div' => false, 'class' => 'btn')) ?>
      <?php echo $html->link('Limpar busca', 'javascript:void(0)', array('id' => 'limpar-filtro', 'class' => 'btn')) ;?>
    <?php echo $this->BForm->end() ?>
  </div>
</div>

<?php echo $this->Javascript->codeBlock('
    jQuery(document).ready(function(){
        atualizaListaDados();
        jQuery("#limpar-filtro").click(function(){
            // bloquearDiv(jQuery(".form-procurar"));
            jQuery(".form-procurar").load(baseUrl + "/filtros/limpar/model:Cadastro/element_name:dados/" + Math.random())
        });
        
        function atualizaListaDados() {
          var div = jQuery("div.lista");
          // bloquearDiv(div);
          div.load(baseUrl + "dados/listagem/" + Math.random());
        }
        
    });', false);
?>